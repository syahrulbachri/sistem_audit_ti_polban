<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);">
    <?= $page_title ?>
</h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Info Audit -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-1" style="color: var(--primary-navy);">
            <?= esc($audit->title) ?>
        </h5>
        <small class="text-muted">
            Isi Rencana Tindak Lanjut (Rencana, Anggaran, Estimasi Waktu) untuk setiap temuan di bawah ini,
            lalu kirim ke auditor untuk direview.
        </small>
    </div>
</div>

<?php if (!empty($temuans)): ?>
    <?php foreach ($temuans as $t):
        $riskBadge = match ($t->tingkat_risiko) {
            'Rendah' => 'bg-success', 'Sedang' => 'bg-warning text-dark',
            'Tinggi' => 'bg-danger', 'Kritis' => 'bg-dark', default => 'bg-secondary'
        };
        $rtlFilled = !empty($t->rtl_description);
        $rejected = !empty($t->auditor_note);      // Ada catatan auditor = RTL ditolak
        $waiting = $rtlFilled && !$rejected;      // RTL sudah dikirim, menunggu review
        ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border-left: 5px solid #dc3545;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <code class="badge bg-secondary me-2"><?= esc($t->clause_code ?? '-') ?></code>
                        <span class="badge <?= $riskBadge ?> rounded-pill">
                            <?= esc($t->tingkat_risiko) ?>
                        </span>
                    </div>
                    <?php if ($waiting): ?>
                        <span class="badge bg-warning text-dark rounded-pill">Menunggu Review Auditor</span>
                    <?php elseif ($rejected): ?>
                        <span class="badge bg-danger rounded-pill">RTL Ditolak - Perbaiki</span>
                    <?php endif; ?>
                </div>

                <h6 class="fw-bold mb-2">Deskripsi Temuan:</h6>
                <p class="text-muted">
                    <?= nl2br(esc($t->deskripsi_temuan)) ?>
                </p>
                <h6 class="fw-bold mb-2 text-primary">Rekomendasi Auditor:</h6>
                <p class="mb-3">
                    <?= nl2br(esc($t->rekomendasi)) ?>
                </p>

                <?php if ($rejected): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-chat-left-text me-2"></i><strong>Catatan Auditor:</strong>
                        <?= nl2br(esc($t->auditor_note)) ?>
                    </div>
                <?php endif; ?>

                <?php if ($waiting): ?>
                    <!-- RTL sudah dikirim → READ ONLY -->
                    <div class="bg-light p-3 rounded border">
                        <h6 class="fw-bold text-success mb-2"><i class="bi bi-arrow-repeat me-1"></i>RTL yang Anda ajukan:</h6>
                        <p class="mb-2 small"><strong>Rencana:</strong>
                            <?= nl2br(esc($t->rtl_description)) ?>
                        </p>
                        <div class="row small">
                            <div class="col-md-6">
                                <strong>Anggaran:</strong>
                                <?= (empty($t->rtl_anggaran) || $t->rtl_anggaran == 0) ? 'Tidak memerlukan anggaran' : 'Rp ' . number_format($t->rtl_anggaran, 0, ',', '.') ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Estimasi Waktu:</strong>
                                <?= date('d M Y', strtotime($t->rtl_deadline)) ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Form RTL (baru / setelah ditolak) -->
                    <form action="/auditee/rtl/submit/<?= $t->id ?>" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Rencana Perbaikan <span class="text-danger">*</span></label>
                            <textarea name="rtl_description" class="form-control" rows="3" required
                                placeholder="Jelaskan rencana perbaikan yang akan Anda lakukan..."><?= esc($t->rtl_description ?? '') ?></textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Anggaran (Rp)</label>
                                <input type="number" name="rtl_anggaran" class="form-control" min="0"
                                    value="<?= esc($t->rtl_anggaran ?? 0) ?>" placeholder="Isi 0 jika tidak perlu anggaran">
                                <small class="text-muted">Isi 0 jika perbaikan tidak memerlukan anggaran (misal: buat SOP, update
                                    policy).</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Estimasi Waktu (Target Selesai) <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="rtl_deadline" class="form-control"
                                    value="<?= esc($t->rtl_deadline ?? '') ?>" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-danger mt-3">
                            <i class="bi bi-send me-2"></i>Kirim RTL ke Auditor
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill me-2"></i>Tidak ada temuan yang perlu diisi RTL.
    </div>
<?php endif; ?>

<a href="/auditee/dashboard" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Kembali ke
    Dashboard</a>

<?= $this->endSection() ?>