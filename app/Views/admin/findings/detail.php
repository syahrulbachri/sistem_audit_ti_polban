<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .finding-card {
        border-left: 5px solid #dc3545;
        transition: all 0.3s;
    }
    .finding-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .rtl-box {
        background: #f8f9fa;
        border-left: 4px solid #0d6efd;
        padding: 15px;
        border-radius: 6px;
        margin-top: 15px;
    }
    .closed-box {
        background: #d1fae5;
        border-left: 4px solid #10b981;
        padding: 10px;
        border-radius: 6px;
        margin-top: 10px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0" style="color: var(--primary-navy);"><?= $page_title ?></h4>
    <a href="/admin/findings" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>

<!-- Info Audit -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="fw-bold mb-1" style="color: var(--primary-navy);"><?= esc($audit->title) ?></h5>
                <span class="badge bg-success rounded-pill">Selesai</span>
            </div>
            <span class="badge bg-danger rounded-pill px-3 py-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <?= count($findings) ?> Temuan
            </span>
        </div>
        
        <div class="row g-3">
            <div class="col-md-6">
                <small class="text-muted d-block">Periode</small>
                <strong><?= esc($audit->nama_periode ?? '-') ?></strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Auditee</small>
                <strong><i class="bi bi-building me-1"></i><?= esc($audit->auditee_name ?? '-') ?></strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Auditor</small>
                <strong><i class="bi bi-person-check me-1"></i><?= esc($audit->auditor_name ?? '-') ?></strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Framework</small>
                <strong><i class="bi bi-book me-1"></i><?= esc($audit->framework) ?></strong>
            </div>
        </div>
    </div>
</div>

<!-- Daftar Temuan -->
<?php if(!empty($findings)): ?>
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="fw-bold mb-0"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Daftar Temuan Audit</h5>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php $no = 1; foreach($findings as $f): 
                $riskBadge = match($f->tingkat_risiko) {
                    'Rendah' => 'bg-success',
                    'Sedang' => 'bg-warning text-dark',
                    'Tinggi' => 'bg-danger',
                    'Kritis' => 'bg-dark',
                    default => 'bg-secondary'
                };
                $statusBadge = match($f->status ?? 'Open') {
                    'Open' => 'bg-primary',
                    'In_Progress' => 'bg-info text-dark',
                    'Closed' => 'bg-success',
                    default => 'bg-secondary'
                };
            ?>
            <div class="list-group-item finding-card p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary me-2"><?= $no++ ?></span>
                        <span class="badge bg-secondary me-2"><?= esc($f->clause_code ?? 'Umum') ?></span>
                        <span class="badge <?= $riskBadge ?> rounded-pill"><?= esc($f->tingkat_risiko) ?></span>
                    </div>
                    <span class="badge <?= $statusBadge ?> rounded-pill px-3 py-2">
                        Status: <?= str_replace('_', ' ', ucfirst($f->status ?? 'Open')) ?>
                    </span>
                </div>
                
                <div>
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Pertanyaan Audit:</h6>
                    <p class="mb-3"><?= esc($f->question_text ?? '-') ?></p>
                    
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Deskripsi Temuan:</h6>
                    <p class="mb-3"><?= nl2br(esc($f->deskripsi_temuan)) ?></p>
                    
                    <h6 class="fw-bold mb-2 small text-uppercase text-muted">Rekomendasi Auditor:</h6>
                    <p class="mb-3"><?= nl2br(esc($f->rekomendasi)) ?></p>

                    <!-- Rencana Tindak Lanjut (RTL) -->
                    <?php if (!empty($f->rtl_description)): ?>
                    <div class="rtl-box">
                        <h6 class="fw-bold mb-2 small text-primary"><i class="bi bi-arrow-repeat me-1"></i> Rencana Tindak Lanjut (Auditee):</h6>
                        <p class="mb-2 small"><strong>Rencana Perbaikan:</strong><br> <?= nl2br(esc($f->rtl_description)) ?></p>
                        <?php if (!empty($f->rtl_deadline)): ?>
                            <p class="mb-0 small"><strong>Target Selesai:</strong> <?= date('d M Y', strtotime($f->rtl_deadline)) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning py-2 px-3 mb-0 small">
                        <i class="bi bi-exclamation-circle me-1"></i> Auditee belum membuat Rencana Tindak Lanjut.
                    </div>
                    <?php endif; ?>

                    <!-- Info Penutupan Temuan -->
                    <?php if (($f->status ?? '') === 'Closed' && !empty($f->closed_at)): ?>
                    <div class="closed-box">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> 
                        <strong class="text-success">Temuan telah ditutup (Verified)</strong> pada <?= date('d M Y', strtotime($f->closed_at)) ?>.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-success border-0 shadow-sm">
    <i class="bi bi-check-circle-fill me-2"></i>
    <strong>Sempurna!</strong> Tidak ada temuan dalam audit ini.
</div>
<?php endif; ?>

<?= $this->endSection() ?>