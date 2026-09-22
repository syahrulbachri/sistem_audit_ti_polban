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

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<!-- Filter & Search -->
<div class="filter-card">
    <form method="get" action="/auditor/list" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label fw-bold small">🔍 Cari (Judul/Auditee)</label>
            <input type="text" name="search" class="form-control" value="<?= esc($search) ?>" placeholder="Ketik kata kunci...">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Periode</label>
            <select name="periode" class="form-select">
                <option value="">Semua Periode</option>
                <?php if (!empty($periodes)): ?>
                    <?php foreach ($periodes as $p): ?>
                        <option value="<?= $p->id ?>" <?= ($filterPeriode == $p->id) ? 'selected' : '' ?>>
                            <?= esc($p->nama_periode) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
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
            <label class="form-label fw-bold small">Framework</label>
            <select name="framework" class="form-select">
                <option value="">Semua Framework</option>
                <option value="ISO 27001" <?= ($filterFramework == 'ISO 27001') ? 'selected' : '' ?>>ISO 27001</option>
                <option value="COBIT 2019" <?= ($filterFramework == 'COBIT 2019') ? 'selected' : '' ?>>COBIT 2019</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Filter</button>
        </div>

        <?php if ($search || $filterStatus || $filterFramework || $filterPeriode): ?>
            <div class="col-12">
                <!-- PERBAIKAN: Link reset dikembalikan ke /auditor/list agar filter benar-benar reset -->
                <a href="/auditor/list" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset Filter
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Info Filter Aktif -->
<?php if ($search || $filterStatus || $filterFramework || $filterPeriode): ?>
    <?php
    // Tentukan warna badge status agar konsisten dengan warna di tabel
    $statusBadgeClass = match ($filterStatus ?? '') {
        'menunggu_jawaban'   => 'bg-info text-dark',
        'aktif'              => 'badge-navy',
        'menunggu_penilaian' => 'bg-warning text-dark',
        'selesai'            => 'bg-success',
        default              => 'bg-secondary'
    };
    ?>
    <div class="alert alert-info py-2 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="d-flex flex-wrap gap-2 align-items-center">
            <span class="fw-bold"><i class="bi bi-funnel-fill me-1"></i>Filter aktif:</span>

            <?php if ($search): ?>
                <span class="badge bg-light text-dark border">Cari: "<?= esc($search) ?>"</span>
            <?php endif; ?>

            <?php if ($filterPeriode): ?>
                <span class="badge bg-light text-dark border">Periode: <?= esc($filterPeriode) ?></span>
            <?php endif; ?>

            <?php if ($filterStatus): ?>
                <span class="badge <?= $statusBadgeClass ?>">
                    <?= ucfirst(str_replace('_', ' ', $filterStatus)) ?>
                </span>
            <?php endif; ?>

            <?php if ($filterFramework): ?>
                <span class="badge bg-light text-dark border">Framework: <?= esc($filterFramework) ?></span>
            <?php endif; ?>

            <span class="badge bg-primary ms-1">Total: <?= $total ?> audit</span>
        </small>

        <!-- PERBAIKAN: Tombol Reset di dalam alert juga diarahkan ke /auditor/list -->
        <a href="/auditor/list" class="btn btn-sm btn-outline-danger border-0 fw-bold" title="Hapus semua filter">
            <i class="bi bi-x-circle me-1"></i>Reset
        </a>
    </div>
<?php endif; ?>

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
                        <th>Auditee</th>
                        <th>Framework</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th class="text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($audits)): ?>
                        <?php
                        $no = ($currentPage - 1) * $perPage + 1;
                        foreach ($audits as $a):
                            $statusClass = match ($a->status) {
                                'menunggu_jawaban'   => 'bg-info text-dark',
                                'aktif'              => 'badge-navy',
                                'menunggu_penilaian' => 'bg-warning text-dark',
                                'selesai'            => 'bg-success',
                                default              => 'bg-secondary'
                            };

                            // PERBAIKAN: Mapping framework lebih dinamis agar COBIT dan contoh lain juga punya warna yang rapi
                            $fwClass = match ($a->framework) {
                                'ISO 27001'   => 'bg-success',
                                'COBIT 2019'  => 'bg-primary',
                                'contoh biner' => 'bg-info text-dark',
                                'contoh skala' => 'bg-info text-dark',
                                default       => 'bg-secondary text-dark'
                            };
                        ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td><strong><?= esc($a->title) ?></strong></td>
                                <td><?= esc($a->nama_periode ?? '-') ?></td>
                                <td><?= esc($a->auditee_name ?? '-') ?></td>
                                <td><span class="badge <?= $fwClass ?> rounded-pill"><?= esc($a->framework) ?></span></td>
                                <td><?= date('d M Y', strtotime($a->deadline)) ?></td>
                                <td>
                                    <span class="badge <?= $statusClass ?> badge-status rounded-pill">
                                        <?= ucfirst(str_replace('_', ' ', $a->status)) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="/audit/detail/<?= $a->id ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada data audit yang sesuai filter
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
                    Menampilkan <strong><?= ($currentPage - 1) * $perPage + 1 ?></strong> -
                    <strong><?= min($currentPage * $perPage, $total) ?></strong> dari
                    <strong><?= $total ?></strong> audit
                </small>

                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <?php
                        $baseUrl = '/auditor/list';
                        $queryParams = $_GET;
                        unset($queryParams['page']);
                        $queryString = http_build_query($queryParams);
                        $qs = $queryString ? '&' . $queryString : '';

                        if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage - 1 ?><?= $qs ?>">
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

                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                <a class="page-link" href="<?= $baseUrl ?>?page=<?= $i ?><?= $qs ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage + 1 ?><?= $qs ?>">
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