<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);">Revisi Jawaban & Temuan</h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('warning')): ?>
    <div class="alert alert-warning alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('warning') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-1">
            <?= esc($audit->title) ?>
        </h5>
        <small class="text-muted">Framework:
            <?= esc($audit->framework) ?>
        </small>
    </div>
</div>

<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <strong>Auditor menemukan ketidaksesuaian pada jawaban Anda.</strong>
    Perbaiki jawaban dan/atau unggah bukti perbaikan di bawah ini.
</div>

<?php if (empty($temuans)): ?>
    <div class="alert alert-success">Tidak ada temuan yang perlu direvisi.</div>
<?php else: ?>

    <!-- Peringatan upload dari dalam website (bukan popup browser) -->
    <div id="uploadWarning" class="alert alert-danger alert-dismissible fade show d-none shadow-sm" role="alert"
        style="border-left: 5px solid #dc3545;">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>
        <strong>Upload dibatalkan!</strong> <span id="uploadWarningText"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <form action="/auditee/revisi/save/<?= $audit->id ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <?php $no = 1;
        foreach ($temuans as $t):
            $riskBadge = match ($t->tingkat_risiko) {
                'Rendah' => 'bg-success', 'Sedang' => 'bg-warning text-dark',
                'Tinggi' => 'bg-danger', 'Kritis' => 'bg-dark', default => 'bg-secondary'
            };
            ?>
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border-left: 5px solid #dc3545;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                        <div>
                            <span class="badge bg-primary me-2">
                                <?= $no ?>
                            </span>
                            <code class="badge bg-secondary me-2"><?= esc($t->clause_code ?? '-') ?></code>
                            <span class="badge <?= $riskBadge ?> rounded-pill">
                                <?= esc($t->tingkat_risiko) ?>
                            </span>
                        </div>
                        <span class="badge bg-danger rounded-pill">Perlu Revisi</span>
                    </div>

                    <p class="fw-bold mb-2">
                        <?= esc($t->question_text ?? '-') ?>
                    </p>

                    <div class="p-3 rounded mb-3" style="background: #fff5f5; border-left: 4px solid #dc3545;">
                        <p class="mb-1"><strong class="text-danger">Temuan Auditor:</strong></p>
                        <p class="mb-2">
                            <?= nl2br(esc($t->deskripsi_temuan)) ?>
                        </p>
                        <p class="mb-0"><strong class="text-primary">Rekomendasi:</strong>
                            <?= nl2br(esc($t->rekomendasi ?? '-')) ?>
                        </p>
                    </div>

                    <?php if (!empty($t->assignment_id)): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Jawaban Anda sebelumnya:</label>
                            <div class="p-3 bg-light rounded small">
                                <?= nl2br(esc($t->answer ?? '(belum diisi)')) ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jawaban Revisi <span class="text-danger">*</span></label>
                            <textarea name="answers[<?= $t->assignment_id ?>]" class="form-control" rows="3"
                                required><?= esc($t->answer ?? '') ?></textarea>
                        </div>

                        <!-- Bagian Upload Bukti Perbaikan (Multi-Upload & JSON Support) -->
                        <div class="mb-2">
                            <label class="form-label fw-bold">Upload Bukti Perbaikan Baru <span class="text-muted fw-normal">(bisa
                                    pilih lebih dari satu file)</span></label>

                            <!-- Input file dengan atribut multiple dan name array -->
                            <input type="file" name="evidence[<?= $t->assignment_id ?>][]" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip" multiple>
                            <small class="text-muted">PDF, JPG, PNG, DOC, XLS, ZIP (Maks 5MB per file)</small>

                            <!-- Menampilkan daftar file bukti sebelumnya secara rapi -->
                            <?php if (!empty($t->evidence_filename)): ?>
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1"><i class="bi bi-paperclip me-1"></i>File Bukti Saat
                                        Ini:</small>
                                    <?php
                                    // Decode JSON dari database
                                    $oldFiles = json_decode($t->evidence_filename, true);
                                    if (!is_array($oldFiles)) {
                                        $oldFiles = [$t->evidence_filename];
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
                                            <!-- Tombol Lihat / Unduh file -->
                                            <a href="<?= base_url('uploads/bukti_perbaikan/' . $file) ?>" target="_blank"
                                                class="btn btn-sm btn-outline-primary py-0 px-2">
                                                <i class="bi bi-eye me-1"></i>Lihat/Unduh
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php $no++; endforeach; ?>

        <div class="d-flex gap-2 mb-4">
            <a href="/auditee/dashboard" class="btn btn-outline-secondary btn-lg px-4">Kembali</a>
            <button type="submit" class="btn btn-danger btn-lg px-5 flex-grow-1">
                <i class="bi bi-send me-2"></i>Kirim Revisi
            </button>
        </div>
    </form>
<?php endif; ?>

<!-- Blok Debug Info -->
<?php if (session()->getFlashdata('debug_info')): ?>
    <div class="alert alert-info alert-dismissible fade show border-info"
        style="background-color: #e7f3ff; border-left: 5px solid #0d6efd !important;">
        <i class="bi bi-bug me-2"></i>
        <strong>Laporan Debug Sistem:</strong><br>
        <?= session()->getFlashdata('debug_info') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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

        // 1) Cek begitu user memilih file (Mendukung Multiple Files)
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
                    inp.value = ''; // Reset input file
                    inp.classList.add('is-invalid');
                    setTimeout(function () { inp.classList.remove('is-invalid'); }, 5000);
                    showWarning('File berikut melebihi batas maksimal 5 MB: ' + oversizedFiles.join(', ') +
                        '. Seluruh pilihan file pada input ini dibatalkan. Silakan kompres atau bungkus menjadi ZIP.');
                }
            });
        });

        // 2) Pengaman terakhir saat tombol submit ditekan (Mendukung Multiple Files)
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
                    e.preventDefault(); // Batalkan pengiriman form
                    showWarning('File berikut melebihi 5 MB dan tidak ikut dikirim: ' + bad.join(', ') +
                        '. Silakan kompres atau bungkus menjadi ZIP terlebih dahulu.');
                }
            });
        });
    });
</script>

<?= $this->endSection() ?>