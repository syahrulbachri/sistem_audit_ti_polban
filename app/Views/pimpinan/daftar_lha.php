<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div>
    <h4 class="mb-0"><?= esc((string) $page_title) ?></h4>
    <small class="text-muted"><?= esc((string) $page_subtitle) ?></small>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- 4 Contextual Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Card 1: Total Audit Selesai -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="bi bi-folder-check fs-4 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted small">Total Audit Selesai</h6>
                            <h3 class="mb-0 fw-bold"><?= esc((string) $summary['total_audit']) ?></h3>
                            <small class="text-muted">Dokumen LHA Final</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Periode (Dinamis) -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="bi bi-calendar-event fs-4 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted small" title="<?= esc($summary['label_periode']) ?>">
                                <?= esc($summary['label_periode']) ?>
                            </h6>
                            <h3 class="mb-0 fw-bold"><?= esc((string) $summary['count_periode']) ?></h3>
                            <small class="text-muted"><?= esc($summary['note_periode']) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 3: Unit (Dinamis) -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="bi bi-building fs-4 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted small" title="<?= esc($summary['label_auditee']) ?>">
                                <?= esc($summary['label_auditee']) ?>
                            </h6>
                            <h3 class="mb-0 fw-bold"><?= esc((string) $summary['count_auditee']) ?></h3>
                            <small class="text-muted"><?= esc($summary['note_auditee']) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 4: Framework (Dinamis) -->
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="bi bi-diagram-3 fs-4 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0 text-muted small" title="<?= esc($summary['label_framework']) ?>">
                                <?= esc($summary['label_framework']) ?>
                            </h6>
                            <h3 class="mb-0 fw-bold"><?= esc((string) $summary['count_framework']) ?></h3>
                            <small class="text-muted"><?= esc($summary['note_framework']) ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section (Auto-submit, tanpa tombol Filter) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="/pimpinan/lha" id="filterForm" class="row g-3 align-items-end">

                <!-- Search Bar (Auto-submit dengan debounce) -->
                <div class="col-md-4">
                    <label class="form-label fw-bold small">🔍 Pencarian Global</label>
                    <input type="text"
                        name="search"
                        id="searchInput"
                        class="form-control"
                        value="<?= esc((string) $search) ?>"
                        placeholder="Cari judul, periode, unit, atau framework...">
                </div>

                <!-- Filter Periode (DINAMIS - Auto-submit) -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Periode</label>
                    <select name="periode" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Periode</option>
                        <?php if (!empty($periodes)): ?>
                            <?php foreach ($periodes as $p): ?>
                                <option value="<?= esc((string) $p->id) ?>" <?= $filterPeriode == $p->id ? 'selected' : '' ?>>
                                    <?= esc((string) $p->nama_periode) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Filter Unit (DINAMIS - Auto-submit) -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Unit</label>
                    <select name="auditee" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Unit</option>
                        <?php if (!empty($auditees)): ?>
                            <?php foreach ($auditees as $u): ?>
                                <option value="<?= esc((string) $u->id) ?>" <?= $filterAuditee == $u->id ? 'selected' : '' ?>>
                                    <?= esc((string) $u->fullname) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Filter Framework (DINAMIS - Auto-submit) -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Framework</label>
                    <select name="framework" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Framework</option>
                        <?php if (!empty($frameworks)): ?>
                            <?php foreach ($frameworks as $fw): ?>
                                <?php $fwName = (string) ($fw->framework ?? ''); ?>
                                <option value="<?= esc($fwName) ?>" <?= $filterFramework === $fwName ? 'selected' : '' ?>>
                                    <?= esc($fwName) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Tombol Reset / Status Filter -->
                <div class="col-md-2">
                    <?php if ($search || $filterFramework || $filterPeriode || $filterAuditee): ?>
                        <a href="/pimpinan/lha" class="btn btn-reset-filter w-100">
                            <i class="bi bi-x-circle-fill me-1"></i>Reset Filter
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-clean-state w-100" disabled>
                            <i class="bi bi-check-circle-fill me-1"></i>Tanpa Filter
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Audit -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-bold">Daftar Audit TI (Laporan Akhir)</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="lhaTable" class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th>Judul Audit</th>
                            <th>Periode</th>
                            <th>Unit</th>
                            <th>Framework</th>
                            <th>Audit Selesai</th>
                            <th>Status</th>
                            <th class="text-center" width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($audits)): ?>
                            <?php
                            $no = (int) (($currentPage - 1) * $perPage + 1);
                            foreach ($audits as $a):
                                $statusClass = match ($a->status) {
                                    'menunggu_jawaban' => 'bg-info text-dark',
                                    'aktif' => 'bg-primary',
                                    'menunggu_penilaian' => 'bg-warning text-dark',
                                    'revisi' => 'bg-danger',
                                    'selesai' => 'bg-success',
                                    default => 'bg-secondary'
                                };

                                $title     = (string) ($a->title ?? '-');
                                $periode   = (string) ($a->nama_periode ?? '-');
                                $auditee   = (string) ($a->auditee_name ?? '-');
                                $framework = (string) ($a->framework ?? '-');
                                $updated  = (string) ($a->updated_at ?? date('Y-m-d'));
                                $status    = (string) ($a->status ?? 'selesai');
                                $id        = (int) ($a->id ?? 0);
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="fw-semibold"><?= esc($title) ?></td>
                                    <td><?= esc($periode) ?></td>
                                    <td><?= esc($auditee) ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= esc($framework) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($updated)) ?></td>
                                    <td>
                                        <span class="badge <?= $statusClass ?> badge-status rounded-pill">
                                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="/pimpinan/lha/detail/<?= $id ?>" class="btn-detail-simple">
                                            <i class="bi bi-eye me-1"></i>Detail
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
                <div id="noResultRow" class="text-center py-5" style="display: none;">
                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                    <p class="text-muted mb-0">Tidak ada hasil yang cocok dengan pencarian Anda</p>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white border-0 p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <small id="resultInfo" class="text-muted">
                            Menampilkan <strong><?= (int) (($currentPage - 1) * $perPage + 1) ?></strong> -
                            <strong><?= (int) min($currentPage * $perPage, $total) ?></strong> dari
                            <strong><?= (int) $total ?></strong> audit
                        </small>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                $baseUrl = '/pimpinan/lha';
                                $queryParams = $_GET;
                                unset($queryParams['page']);
                                $queryString = http_build_query($queryParams);
                                $qs = $queryString ? '&' . $queryString : '';
                                ?>

                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= esc($baseUrl . '?page=' . ($currentPage - 1) . $qs) ?>">Previous</a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= esc($baseUrl . '?page=' . $i . $qs) ?>"><?= (int) $i ?></a>
                                        </li>
                                    <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= esc($baseUrl . '?page=' . ($currentPage + 1) . $qs) ?>">Next</a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS untuk Tombol Detail yang Lebih Menarik -->
<style>
    .btn-detail-simple {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        background-color: #273272;
        color: #ffffff;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: 1px solid #273272;
    }

    .btn-detail-simple:hover {
        background-color: #1e2860;
        color: #ffffff;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(39, 50, 114, 0.25);
    }

    .btn-detail-simple:active {
        background-color: #161d4a;
        transform: translateY(1px);
    }

    .btn-detail-simple i {
        font-size: 0.85rem;
    }


    /* ===== TOMBOL RESET FILTER ===== */
    .btn-reset-filter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 14px;
        background-color: #f57e20;
        /* Accent orange dari tema */
        color: #ffffff;
        text-decoration: none;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        transition: all 0.2s ease;
        border: 1px solid #f57e20;
        box-shadow: 0 2px 4px rgba(245, 126, 32, 0.2);
    }

    .btn-reset-filter:hover {
        background-color: #d96a15;
        border-color: #d96a15;
        color: #ffffff;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(245, 126, 32, 0.3);
    }

    .btn-reset-filter:active {
        transform: translateY(0);
        box-shadow: 0 1px 2px rgba(245, 126, 32, 0.2);
    }

    .btn-reset-filter i {
        font-size: 0.9rem;
    }

    /* ===== TOMBOL TANPA FILTER (Clean State) ===== */
    .btn-clean-state {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 14px;
        background-color: #e8f5e9;
        /* Soft green - menandakan "bersih/aman" */
        color: #2e7d32;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        border: 1px solid #a5d6a7;
        cursor: default;
        opacity: 1;
        /* Override default disabled opacity */
    }

    .btn-clean-state i {
        font-size: 0.9rem;
    }
</style>

<!-- JavaScript untuk Auto-submit Search dengan Debounce -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        const tableBody = document.querySelector('#lhaTable tbody');
        const rows = tableBody ? tableBody.querySelectorAll('tr') : [];
        let debounceTimer;

        // ===== CLIENT-SIDE SEARCH (Instant, tanpa reload) =====
        if (searchInput && rows.length > 0) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();

                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    let visibleCount = 0;

                    rows.forEach(row => {
                        // Ambil text dari SEMUA kolom yang relevan
                        const no = row.cells[0]?.textContent.toLowerCase() || '';
                        const judul = row.cells[1]?.textContent.toLowerCase() || '';
                        const periode = row.cells[2]?.textContent.toLowerCase() || '';
                        const unit = row.cells[3]?.textContent.toLowerCase() || '';
                        const framework = row.cells[4]?.textContent.toLowerCase() || '';
                        const audit = row.cells[5]?.textContent.toLowerCase() || '';
                        const status = row.cells[6]?.textContent.toLowerCase() || '';

                        // Gabungkan semua kolom untuk pencarian
                        const allText = `${no} ${judul} ${periode} ${unit} ${framework} ${audit} ${status}`;

                        // Cek apakah mengandung search term
                        if (allText.includes(searchTerm)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Update result info
                    const resultInfo = document.getElementById('resultInfo');
                    if (resultInfo) {
                        const totalData = <?= (int) $total ?>;
                        if (searchTerm === '') {
                            resultInfo.textContent = `Menampilkan ${visibleCount} dari ${totalData} Dokumen LHA`;
                        } else {
                            resultInfo.textContent = `Ditemukan ${visibleCount} hasil untuk "${searchTerm}"`;
                        }
                    }

                    // Show/hide "no result" message
                    const noResultRow = document.getElementById('noResultRow');
                    if (noResultRow) {
                        if (visibleCount === 0) {
                            noResultRow.classList.add('show');
                        } else {
                            noResultRow.classList.remove('show');
                        }
                    }
                }, 150); // Debounce 150ms
            });
        }

        // ===== DROPDOWN FILTER (Tetap auto-submit ke server) =====
        const dropdowns = filterForm.querySelectorAll('select[name="periode"], select[name="auditee"], select[name="framework"]');
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('change', function() {
                filterForm.submit();
            });
        });

        // ===== RESET BUTTON =====
        const resetBtn = document.querySelector('.btn-reset-filter');
        if (resetBtn) {
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = '/pimpinan/lha';
            });
        }
    });
</script>

<?= $this->endSection() ?>