<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .badge-status { padding: 6px 12px; font-size: 0.8rem; }
    .filter-card { background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .stat-card {
        border-radius: 12px;
        padding: 20px;
        color: white;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-3px); }
    
    /* Warna Solid (Bukan Gradasi) */
    .stat-total { background-color: #1e3a8a; }      /* Biru Navy */
    .stat-open { background-color: #0d6efd; }       /* Biru Muda */
    .stat-progress { background-color: #0dcaf0; }   /* Kuning */
    .stat-closed { background-color: #198754; }     /* Hijau */
    
    .badge-navy { background-color: var(--primary-navy) !important; color: white !important; }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-total">
            <div class="fs-4 fw-bold"><?= $stats['total'] ?></div>
            <div class="small opacity-75">Total Temuan</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-open">
            <div class="fs-4 fw-bold"><?= $stats['open'] ?></div>
            <div class="small opacity-75">Open (Belum RTL)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-progress">
            <div class="fs-4 fw-bold"><?= $stats['in_progress'] ?></div>
            <div class="small opacity-10">In Progress</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-closed">
            <div class="fs-4 fw-bold"><?= $stats['closed'] ?></div>
            <div class="small opacity-75">Closed (Selesai)</div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="filter-card">
    <form method="get" action="/auditor/monitoring-temuan" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold small">🔍 Cari (Audit/Auditee/Deskripsi)</label>
            <input type="text" name="search" class="form-control" value="<?= esc($search) ?>" placeholder="Ketik kata kunci...">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="Open" <?= ($filterStatus == 'Open') ? 'selected' : '' ?>>Open</option>
                <option value="In_Progress" <?= ($filterStatus == 'In_Progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="Closed" <?= ($filterStatus == 'Closed') ? 'selected' : '' ?>>Closed</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Risiko</label>
            <select name="risiko" class="form-select">
                <option value="">Semua Risiko</option>
                <option value="Kritis" <?= ($filterRisiko == 'Kritis') ? 'selected' : '' ?>>Kritis</option>
                <option value="Tinggi" <?= ($filterRisiko == 'Tinggi') ? 'selected' : '' ?>>Tinggi</option>
                <option value="Sedang" <?= ($filterRisiko == 'Sedang') ? 'selected' : '' ?>>Sedang</option>
                <option value="Rendah" <?= ($filterRisiko == 'Rendah') ? 'selected' : '' ?>>Rendah</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Periode</label>
            <select name="periode" class="form-select">
                <option value="">Semua Periode</option>
                <?php if(!empty($periodes)): ?>
                    <?php foreach($periodes as $p): ?>
                        <option value="<?= $p->id ?>" <?= ($filterPeriode == $p->id) ? 'selected' : '' ?>>
                            <?= esc($p->nama_periode) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filter</button>
        </div>
        
        <?php if($search || $filterStatus || $filterRisiko || $filterPeriode): ?>
        <div class="col-12">
            <a href="/auditor/monitoring-temuan" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Reset Filter
            </a>
        </div>
        <?php endif; ?>
    </form>
</div>

<!-- Alert -->
<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Tabel -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                               <thead class="table-light">
                    <tr>
                        <th width="10%">Kode Klausul</th>
                        <th>Judul Audit</th>
                        <th width="12%">Framework</th>
                        <th width="14%">Periode</th>
                        <th width="10%">Risiko</th>
                        <th width="12%">Status</th>
                        <th>Auditee (Unit)</th>
                        <th width="12%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($findings)): ?>
                        <?php foreach($findings as $f): 
                            $riskBadge = match($f->tingkat_risiko) {
                                'Rendah'   => 'bg-success',
                                'Sedang'   => 'bg-warning text-dark',
                                'Tinggi'   => 'bg-danger',
                                'Kritis'   => 'bg-dark',
                                default    => 'bg-secondary'
                            };
                            $statusBadge = match($f->status) {
                                'Open'        => 'bg-primary',
                                'In_Progress' => 'bg-info text-dark',
                                'Closed'      => 'bg-success',
                                default       => 'bg-secondary'
                            };
                            $fwBadge = ($f->framework === 'ISO 27001') ? 'bg-success' : 'bg-info text-dark';
                            $statusLabel = str_replace('_', ' ', ucfirst($f->status));
                        ?>
                        <tr>
                            <td><code class="badge bg-secondary"><?= esc($f->clause_code ?? '-') ?></code></td>
                            <td><strong><?= esc($f->audit_title ?? '-') ?></strong></td>
                            <td><span class="badge <?= $fwBadge ?> rounded-pill"><?= esc($f->framework ?? '-') ?></span></td>
                            <td><small><?= esc($f->nama_periode ?? '-') ?></small></td>
                            <td>
                                <span class="badge <?= $riskBadge ?> rounded-pill"><?= esc($f->tingkat_risiko) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $statusBadge ?> badge-status rounded-pill">
                                    <?= $statusLabel ?>
                                </span>
                            </td>
                            <td><small><?= esc($f->auditee_name ?? '-') ?></small></td>
                            <td class="text-center">
                                <?php if($f->status === 'Open'): ?>
                                    <a href="/auditor/temuan/<?= $f->id ?>" class="btn btn-sm btn-warning text-dark" title="Lihat RTL">
                                        <i class="bi bi-file-earmark-text me-1"></i>Lihat RTL
                                    </a>
                                <?php elseif($f->status === 'In_Progress'): ?>
                                    <a href="/auditor/temuan/<?= $f->id ?>" class="btn btn-sm btn-primary" title="Lihat Bukti">
                                        <i class="bi bi-clipboard-check me-1"></i>Lihat Bukti
                                    </a>
                                <?php else: ?>
                                    <a href="/auditor/temuan/<?= $f->id ?>" class="btn btn-sm btn-outline-secondary" title="Lihat Detail">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada data temuan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>