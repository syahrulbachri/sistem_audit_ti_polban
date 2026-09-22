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
        Isi jawaban dan upload bukti pendukung. Setelah klik <strong>"Kirim ke Auditor"</strong>, jawaban akan
        <strong>terkunci</strong>.
    </div>
<?php endif; ?>

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

                <!-- Bukti Pendukung -->
                <div class="mt-3">
                    <?php if (!empty($q->evidence_filename)): ?>
                        <div class="d-flex align-items-center gap-2 p-2 bg-light rounded border mb-2">
                            <i class="bi bi-paperclip text-primary"></i>
                            <small class="mb-0"><?= esc($q->evidence_filename) ?></small>
                        </div>
                    <?php endif; ?>
                    <?php if (!$locked): ?>
                        <input type="file" name="evidence[<?= $q->id ?>]" class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip">
                        <small class="text-muted">PDF, JPG, PNG, DOC, XLS, ZIP (Maks 5MB). Upload file baru untuk mengganti
                            bukti lama.</small>
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

<?= $this->endSection() ?>