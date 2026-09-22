<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .filter-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .badge-findings {
        background-color: #dc3545;
        color: white;
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<!-- Filter -->
<div class="filter-card">
    <form method="get" action="/admin/findings" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label fw-bold small">Periode</label>
            <select name="periode" class="form-select">
                <option value="">Semua Periode</option>
                <?php foreach($periodes as $p): ?>
                    <option value="<?= $p->id ?>" <?= ($filterPeriode == $p->id) ? 'selected' : '' ?>>
                        <?= esc($p->nama_periode) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Auditor</label>
            <input type="text" name="auditor" class="form-control" value="<?= esc($filterAuditor) ?>" placeholder="Cari nama...">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Auditee</label>
            <input type="text" name="auditee" class="form-control" value="<?= esc($filterAuditee) ?>" placeholder="Cari nama...">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Framework</label>
            <select name="framework" class="form-select">
                <option value="">Semua Framework</option>
                <?php foreach($frameworks as $fw): ?>
                    <option value="<?= esc($fw->nama) ?>" <?= ($filterFramework == $fw->nama) ? 'selected' : '' ?>>
                        <?= esc($fw->nama) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status Temuan</label>
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="Open" <?= ($filterStatus == 'Open') ? 'selected' : '' ?>>Open</option>
                <option value="In_Progress" <?= ($filterStatus == 'In_Progress') ? 'selected' : '' ?>>In Progress</option>
                <option value="Closed" <?= ($filterStatus == 'Closed') ? 'selected' : '' ?>>Closed</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary me-2">
                <i class="bi bi-search"></i> Filter
            </button>
            <?php if ($filterPeriode || $filterAuditor || $filterAuditee || $filterFramework || $filterStatus || $filterRisiko): ?>
                <a href="/admin/findings" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset Filter
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Tabel -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">No</th>
                        <th>Judul Audit</th>
                        <th>Periode</th>
                        <th>Auditor</th>
                        <th>Auditee</th>
                        <th>Framework</th>
                        <th class="text-center">Jumlah Temuan</th>
                        <th class="text-center" width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($audits)): ?>
                        <?php $no = 1; foreach($audits as $a): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $no++ ?></td>
                            <td><strong><?= esc($a->title) ?></strong></td>
                            <td><?= esc($a->nama_periode ?? '-') ?></td>
                            <td><?= esc($a->auditor_name ?? '-') ?></td>
                            <td><?= esc($a->auditee_name ?? '-') ?></td>
                            <td><span class="badge bg-info text-dark rounded-pill"><?= esc($a->framework) ?></span></td>
                            <td class="text-center">
                                <span class="badge badge-findings rounded-pill px-3 py-2">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    <?= $a->total_findings ?> Temuan
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="/admin/findings/detail/<?= $a->id ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail Temuan">
                                    <i class="bi bi-eye"></i> Lihat Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada audit dengan temuan yang sesuai dengan filter
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>