<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .badge-status {
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .filter-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .pagination .page-link {
        color: var(--primary-navy);
        border-radius: 6px !important;
        margin: 0 2px;
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary-navy);
        border-color: var(--primary-navy);
        color: white;
    }

    .pagination .page-link:hover {
        background-color: var(--primary-navy);
        border-color: var(--primary-navy);
        color: white;
    }

    .badge-navy {
        background-color: var(--primary-navy) !important;
        color: white !important;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h4 class="fw-bold mb-0" style="color: var(--primary-navy);"><?= $page_title ?></h4>
    <a href="/admin/planning/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Buat Rencana Audit
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filter & Search -->
<div class="filter-card">
    <form method="get" action="/admin/planning" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold small">🔍 Cari (Judul/Nama/Framework)</label>
            <input type="text" name="search" class="form-control" value="<?= esc($search) ?>" placeholder="Ketik kata kunci...">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Status</label>
            <select name="status" class="form-select">
                <option value="">Semua Status</option>
                <option value="aktif" <?= ($filterStatus == 'aktif') ? 'selected' : '' ?>>Aktif</option>
                <option value="menunggu_jawaban" <?= ($filterStatus == 'menunggu_jawaban') ? 'selected' : '' ?>>Menunggu Jawaban</option>
                <option value="menunggu_penilaian" <?= ($filterStatus == 'menunggu_penilaian') ? 'selected' : '' ?>>Menunggu Penilaian</option>
                <option value="selesai" <?= ($filterStatus == 'selesai') ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Periode</label>
            <select name="periode" class="form-select">
                <option value="">Semua Periode</option>
                <?php foreach ($allPeriodes as $p): ?>
                    <option value="<?= $p->id ?>" <?= ($filterPeriode == $p->id) ? 'selected' : '' ?>><?= esc($p->nama_periode) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filter</button>
        </div>

        <?php if ($search || $filterFramework || $filterStatus || $filterPeriode): ?>
            <div class="col-12">
                <a href="/admin/planning" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset Filter
                </a>
                <small class="text-muted ms-2">Menampilkan hasil pencarian</small>
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
                        <th>Judul Audit</th>
                        <th>Periode</th>
                        <th>Auditee</th>
                        <th>Auditor</th>
                        <th>Framework</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th class="text-center" width="12%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($audits)): ?>
                        <?php foreach ($audits as $a):
                            $statusClass = match ($a->status) {
                                'menunggu_jawaban' => 'bg-info text-dark',  // Biru muda
                                'aktif' => 'badge-navy',                    // Biru navy
                                'menunggu_penilaian' => 'bg-warning text-dark', // Kuning
                                'selesai' => 'bg-success',                  // Hijau
                                default => 'bg-secondary'
                            };
                            $fwClass = ($a->framework === 'ISO 27001') ? 'bg-success' : 'bg-info text-dark';
                        ?>
                            <tr>
                                <td><strong><?= esc($a->title) ?></strong></td>
                                <td><?= esc($a->nama_periode ?? '-') ?></td>
                                <td><?= esc($a->auditee_name ?? '-') ?></td>
                                <td><?= esc($a->auditor_name ?? '-') ?></td>
                                <td><span class="badge <?= $fwClass ?> rounded-pill"><?= esc($a->framework) ?></span></td>
                                <td><?= date('d M Y', strtotime($a->deadline)) ?></td>
                                <td>
                                    <span class="badge <?= $statusClass ?> badge-status rounded-pill">
                                        <?= ucfirst(str_replace('_', ' ', $a->status)) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($a->status === 'aktif'): ?>
                                        <!-- Hanya status AKTIF yang boleh Edit & Hapus -->
                                        <a href="/admin/planning/edit/<?= $a->id ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit Rencana">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="/admin/planning/delete/<?= $a->id ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus rencana audit ini?')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <!-- Status selain aktif hanya bisa Lihat Detail -->
                                        <a href="/admin/planning/detail/<?= $a->id ?>" class="btn btn-sm btn-outline-info text-dark" title="Lihat Detail (Read-Only)">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada data rencana audit yang sesuai dengan filter
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white border-0 p-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <small class="text-muted">
                    Menampilkan <?= count($audits) ?> dari <?= $total ?> rencana audit
                </small>

                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <?php
                        $baseUrl = '/admin/planning';
                        $queryParams = $_GET;
                        unset($queryParams['page']);
                        $queryString = http_build_query($queryParams);

                        // Previous
                        if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>?<?= $queryString ?>&page=<?= $currentPage - 1 ?>">
                                    <i class="bi bi-chevron-left"></i> Prev
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link"><i class="bi bi-chevron-left"></i> Prev</span>
                            </li>
                        <?php endif; ?>

                        <?php
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($totalPages, $currentPage + 2);

                        if ($startPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>?<?= $queryString ?>&page=1">1</a>
                            </li>
                            <?php if ($startPage > 2): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl ?>?<?= $queryString ?>&page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($endPage < $totalPages): ?>
                            <?php if ($endPage < $totalPages - 1): ?>
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>?<?= $queryString ?>&page=<?= $totalPages ?>"><?= $totalPages ?></a>
                            </li>
                        <?php endif; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>?<?= $queryString ?>&page=<?= $currentPage + 1 ?>">
                                    Next <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">Next <i class="bi bi-chevron-right"></i></span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>