<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
// Hitung status kunci langsung di view (aman walau controller tidak mengirim variabel 'locked')
$locked = in_array($audit->status, ['menunggu_penilaian', 'selesai']);
?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?? 'Isi Kuesioner' ?></h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Notifikasi Status -->
<?php if ($locked): ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-lock-fill me-2"></i>
        <strong>Jawaban sudah dikirim ke auditor.</strong> Anda tidak dapat mengedit jawaban lagi.
        <?php if ($audit->status === 'selesai'): ?> Audit ini sudah selesai dinilai.
        <?php else: ?> Silakan tunggu hasil penilaian dari auditor.
        <?php endif; ?>
    </div>
<?php elseif ($audit->status === 'revisi'): ?>
    <div class="alert alert-warning mb-4" style="border-left: 5px solid #f57e20;">
        <i class="bi bi-arrow-repeat me-2"></i>
        <strong>Auditor meminta revisi jawaban Anda.</strong>
        <?php if (!empty($audit->revision_note)): ?>
            <p class="mb-1 mt-2"><strong>Catatan auditor:</strong> <?= nl2br(esc($audit->revision_note)) ?></p>
        <?php endif; ?>
        <small class="text-muted">Silakan perbaiki jawaban/bukti Anda, lalu kirim kembali ke auditor.</small>
    </div>
<?php else: ?>
    <div class="alert alert-info mb-4">
        <i class="bi bi-info-circle me-2"></i>
        Isi jawaban dan upload bukti pendukung (bisa pilih lebih dari satu file). Setelah klik <strong>"Kirim ke
            Auditor"</strong>, jawaban akan
        <strong>terkunci</strong>.
    </div>
<?php endif; ?>

<!-- Peringatan upload dari dalam website (bukan popup browser) -->
<div id="uploadWarning" class="alert alert-danger alert-dismissible fade show d-none shadow-sm" role="alert"
    style="border-left: 5px solid #dc3545;">
    <i class="bi bi-exclamation-octagon-fill me-2"></i>
    <strong>Upload dibatalkan!</strong> <span id="uploadWarningText"></span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<form action="/auditee/save-answer/<?= $audit->id ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <?php $no = 1;
    foreach ($questions as $q): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-primary rounded-pill"><?= $no ?></span>
                    <?php if (!empty($q->clause_code)): ?>
                        <code class="badge bg-secondary"><?= esc($q->clause_code) ?></code>
                    <?php endif; ?>
                </div>
                <p class="fw-bold mb-3"><?= esc($q->question_text) ?></p>

                <textarea name="answers[<?= $q->id ?>]" class="form-control" rows="3"
                    placeholder="Tulis jawaban Anda di sini..." <?= $locked ? 'readonly' : '' ?>><?= esc($q->answer ?? '') ?></textarea>

                <!-- Bukti Pendukung (Multi-Upload & JSON Support) -->
                <div class="mt-3">
                    <?php if (!empty($q->evidence_filename)): ?>
                        <div class="mb-2">
                            <small class="text-muted d-block mb-1"><i class="bi bi-paperclip me-1"></i>File Bukti
                                Pendukung:</small>
                            <?php
                            // Mengubah string JSON dari database kembali menjadi array PHP
                            $oldFiles = json_decode($q->evidence_filename, true);

                            // Fallback jika format data lama masih berupa string biasa (bukan JSON)
                            if (!is_array($oldFiles)) {
                                $oldFiles = [$q->evidence_filename];
                            }

                            foreach ($oldFiles as $file):
                                if (empty($file))
                                    continue;
                                ?>
                                <div class="d-flex align-items-center justify-content-between p-2 bg-light rounded border mb-1">
                                    <div class="d-flex align-items-center gap-2 text-truncate">
                                        <i class="bi bi-file-earmark-text text-primary"></i>
                                        <span class="text-truncate small mb-0" style="max-width: 250px;" title="<?= esc($file) ?>">
                                            <?= esc($file) ?>
                                        </span>
                                    </div>
                                    <!-- Tombol Download / Lihat File -->
                                    <a href="<?= base_url('uploads/evidence/' . $file) ?>" target="_blank"
                                        class="btn btn-sm btn-outline-primary py-0 px-2">
                                        <i class="bi bi-eye me-1"></i>Lihat/Unduh
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="mb-2">
                            <span class="text-muted small fst-italic">Belum ada file bukti yang diunggah.</span>
                        </div>
                    <?php endif; ?>

                    <?php if (!$locked): ?>
                        <!-- Input file dengan atribut multiple dan name array -->
                        <input type="file" name="evidence[<?= $q->id ?>][]" class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip" multiple>
                        <small class="text-muted">PDF, JPG, PNG, DOC, XLS, ZIP (Maks 5MB per file). Anda dapat memilih lebih
                            dari satu file sekaligus untuk menambah atau mengganti bukti.</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php $no++; endforeach; ?>

    <?php if (!$locked): ?>
        <div class="d-flex gap-2 mb-4">
            <a href="/auditee/dashboard" class="btn btn-outline-secondary btn-lg px-4">Batal</a>
            <button type="submit" name="action" value="draft" class="btn btn-primary btn-lg flex-grow-1">
                <i class="bi bi-save me-2"></i>Simpan Jawaban
            </button>
        </div>
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border-left: 5px solid #f57e20;">
            <div class="card-body p-4 text-center">
                <p class="text-muted small mb-3">
                    Pastikan semua pertanyaan sudah dijawab. Setelah dikirim, jawaban <strong>terkunci</strong>
                    dan tidak bisa diedit kecuali auditor meminta revisi.
                </p>
                <button type="submit" name="action" value="submit" class="btn btn-lg px-5 text-white"
                    style="background: var(--accent-orange);">
                    <i class="bi bi-send me-2"></i>Kirim ke Auditor
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center mb-4">
            <a href="/auditee/dashboard" class="btn btn-outline-secondary btn-lg px-5">
                <i class="bi bi-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>
    <?php endif; ?>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var MAX = 5 * 1024 * 1024; // 5 MB
        var box = document.getElementById('uploadWarning');
        var txt = document.getElementById('uploadWarningText');
        if (!box || !txt) return;

        function showWarning(pesan) {
            txt.textContent = pesan;
            box.classList.remove('d-none');
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
            clearTimeout(box._timer);
            box._timer = setTimeout(function () { box.classList.add('d-none'); }, 10000);
        }

        // 1) Cek begitu user memilih file (mendukung multiple files)
        document.querySelectorAll('input[type="file"]').forEach(function (inp) {
            inp.addEventListener('change', function () {
                let oversizedFiles = [];
                for (let i = 0; i < inp.files.length; i++) {
                    let f = inp.files[i];
                    if (f.size > MAX) {
                        let mb = (f.size / 1024 / 1024).toFixed(1);
                        oversizedFiles.push(f.name + ' (' + mb + ' MB)');
                    }
                }

                if (oversizedFiles.length > 0) {
                    inp.value = ''; // Reset pilihan file jika ada yang melebihi batas
                    inp.classList.add('is-invalid');
                    setTimeout(function () { inp.classList.remove('is-invalid'); }, 5000);
                    showWarning('File berikut melebihi batas maksimal 5 MB: ' + oversizedFiles.join(', ') +
                        '. Seluruh pilihan file pada input ini dibatalkan. Silakan kompres atau jadikan ZIP.');
                }
            });
        });

        // 2) Pengaman terakhir saat tombol submit ditekan
        document.querySelectorAll('form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var bad = [];
                form.querySelectorAll('input[type="file"]').forEach(function (inp) {
                    for (let i = 0; i < inp.files.length; i++) {
                        let f = inp.files[i];
                        if (f.size > MAX) {
                            bad.push(f.name + ' (' + (f.size / 1024 / 1024).toFixed(1) + ' MB)');
                        }
                    }
                });
                if (bad.length) {
                    e.preventDefault(); // batalkan pengiriman
                    showWarning('File berikut melebihi 5 MB dan tidak ikut dikirim: ' + bad.join(', ') +
                        '. Silakan kompres atau bungkus menjadi ZIP terlebih dahulu.');
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>