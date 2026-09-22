<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);">Revisi Jawaban</h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= session()->getFlashdata('success') ?>
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
    Perbaiki jawaban dan/atau bukti di bawah ini, lalu simpan revisi.
</div>

<?php if (empty($temuans)): ?>
    <div class="alert alert-success">Tidak ada temuan yang perlu direvisi.</div>
<?php else: ?>
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
                        <div class="mb-2">
                            <label class="form-label fw-bold">Upload Bukti Baru <span class="text-muted fw-normal">(jika
                                    perlu)</span></label>
                            <input type="file" name="evidence[<?= $t->assignment_id ?>]" class="form-control">
                            <small class="text-muted">PDF, JPG, PNG, DOC, XLS, ZIP (maks 5MB)</small>
                            <?php if (!empty($t->evidence_filename)): ?>
                                <small class="d-block mt-1 text-muted">Bukti saat ini:
                                    <?= esc($t->evidence_filename) ?>
                                </small>
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

<?= $this->endSection() ?>