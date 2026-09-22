<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .filter-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .badge-selesai {
        background-color: #10b981;
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<!-- Filter -->
<div class="filter-card">
    <form method="get" action="/admin/completed-audits" class="row g-3 align-items-end">
        <div class="col-md-3">
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
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Filter
            </button>
        </div>
        <?php if ($filterPeriode || $filterAuditor || $filterAuditee || $filterFramework): ?>
            <div class="col-12">
                <a href="/admin/completed-audits" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset Filter
                </a>
            </div>
        <?php endif; ?>
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
                        <th>Tanggal Selesai</th>
                        <th>Skor Akhir</th>
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
                            <td><?= date('d M Y', strtotime($a->updated_at)) ?></td>
                            <td>
                                <span class="badge bg-success rounded-pill px-3">
                                    <?= $a->framework === 'ISO 27001' ? round($a->final_score) . '%' : number_format($a->final_score, 2) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="/admin/completed-audits/detail/<?= $a->id ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada audit selesai yang sesuai dengan filter
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>