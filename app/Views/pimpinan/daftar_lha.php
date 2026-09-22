<?= $this->extend('layouts/main') ?>

<!-- 1. Kirim Judul ke Topbar -->
<?= $this->section('page_header') ?>
<div>
    <h4 class="mb-0"><?= esc((string) $page_title) ?></h4>
    <small class="text-muted"><?= esc((string) $page_subtitle) ?></small>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <!-- Statistik Cards -->
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

        <!-- Card 3: Auditee / Unit (Dinamis berdasarkan Search & Filter lain) -->
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
                    <label class="form-label fw-bold small">🔍 Cari (Judul/Auditee)</label>
                    <input type="text"
                        name="search"
                        id="searchInput"
                        class="form-control"
                        value="<?= esc((string) $search) ?>"
                        placeholder="Ketik kata kunci...">
                </div>

                <!-- Filter Periode (Auto-submit) -->
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

                <!-- Filter Auditee (MENGGANTIKAN STATUS - Auto-submit) -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Auditee</label>
                    <select name="auditee" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Auditee</option>
                        <?php if (!empty($auditees)): ?>
                            <?php foreach ($auditees as $u): ?>
                                <option value="<?= esc((string) $u->id) ?>" <?= $filterAuditee == $u->id ? 'selected' : '' ?>>
                                    <?= esc((string) $u->fullname) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Filter Framework (Auto-submit) -->
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Framework</label>
                    <select name="framework" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Framework</option>
                        <option value="ISO 27001" <?= $filterFramework == 'ISO 27001' ? 'selected' : '' ?>>ISO 27001</option>
                        <option value="COBIT 2019" <?= $filterFramework == 'COBIT 2019' ? 'selected' : '' ?>>COBIT 2019</option>
                    </select>
                </div>

                <!-- Tombol Reset (Satu-satunya tombol yang tersisa) -->
                <div class="col-md-2">
                    <?php if ($search || $filterFramework || $filterPeriode || $filterAuditee): ?>
                        <a href="/pimpinan/lha" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    <?php else: ?>
                        <button type="button" class="btn btn-outline-secondary w-100" disabled>
                            <i class="bi bi-check-circle me-1"></i>Tanpa Filter
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- JavaScript untuk Auto-submit Search dengan Debounce -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const filterForm = document.getElementById('filterForm');
            let debounceTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    filterForm.submit();
                }, 500); // Delay 500ms setelah user berhenti mengetik
            });
        });
    </script>

    <!-- Tabel Audit -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="mb-0 fw-bold">Daftar Audit TI (Laporan Akhir)</h6>
        </div>
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

                                // === FIX INTELEPHENSE: Casting eksplisit ke string sebelum masuk esc() ===
                                $title     = (string) ($a->title ?? '-');
                                $periode   = (string) ($a->nama_periode ?? '-');
                                $auditee   = (string) ($a->auditee_name ?? '-');
                                $framework = (string) ($a->framework ?? '-');
                                $deadline  = (string) ($a->deadline ?? date('Y-m-d'));
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
                                    <td><?= date('d M Y', strtotime($deadline)) ?></td>
                                    <td>
                                        <span class="badge <?= $statusClass ?> badge-status rounded-pill">
                                            <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="/pimpinan/lha/detail/<?= $id ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
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

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white border-0 p-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <small class="text-muted">
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

                                <!-- Previous -->
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= esc($baseUrl . '?page=' . ($currentPage - 1) . $qs) ?>">Previous</a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Previous</span>
                                    </li>
                                <?php endif; ?>

                                <!-- Page Numbers -->
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 2 && $i <= $currentPage + 2)): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= esc($baseUrl . '?page=' . $i . $qs) ?>"><?= (int) $i ?></a>
                                        </li>
                                    <?php elseif ($i == $currentPage - 3 || $i == $currentPage + 3): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <!-- Next -->
                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= esc($baseUrl . '?page=' . ($currentPage + 1) . $qs) ?>">Next</a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">Next</span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>