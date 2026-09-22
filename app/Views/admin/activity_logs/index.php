<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .badge-login {
        background-color: #17a2b8;
    }

    .badge-logout {
        background-color: #6c757d;
    }

    .badge-create {
        background-color: #28a745;
    }

    .badge-update {
        background-color: #ffc107;
        color: #000;
    }

    .badge-delete {
        background-color: #dc3545;
    }

    .badge-export {
        background-color: #6f42c1;
    }

    .filter-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .log-row:hover {
        background-color: #f8f9fa;
    }

    .user-agent-text {
        font-size: 0.75rem;
        color: #6c757d;
        font-style: italic;
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                    <i class="bi bi-activity text-info fs-4"></i>
                </div>
                <div>
                    <div class="fs-5 fw-bold"><?= $total ?></div>
                    <small class="text-muted">Total Log</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                    <i class="bi bi-calendar-check text-success fs-4"></i>
                </div>
                <div>
                    <!-- PERBAIKAN: Gunakan $todayLogs, bukan $total -->
                    <div class="fs-5 fw-bold"><?= $todayLogs ?></div>
                    <small class="text-muted">Hari Ini</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="filter-card">
    <form method="get" action="/admin/activity-logs" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label fw-bold small">Dari Tanggal</label>
            <input type="date" name="date_from" class="form-control" value="<?= esc($filterDateFrom) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Sampai Tanggal</label>
            <input type="date" name="date_to" class="form-control" value="<?= esc($filterDateTo) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">User</label>
            <select name="user" class="form-select">
                <option value="">Semua User</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= esc($u->username) ?>" <?= ($filterUser == $u->username) ? 'selected' : '' ?>>
                        <?= esc($u->username) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-bold small">Aksi</label>
            <select name="action" class="form-select">
                <option value="">Semua Aksi</option>
                <option value="LOGIN" <?= ($filterAction == 'LOGIN') ? 'selected' : '' ?>>Login</option>
                <option value="LOGOUT" <?= ($filterAction == 'LOGOUT') ? 'selected' : '' ?>>Logout</option>
                <option value="CREATE" <?= ($filterAction == 'CREATE') ? 'selected' : '' ?>>Create</option>
                <option value="UPDATE" <?= ($filterAction == 'UPDATE') ? 'selected' : '' ?>>Update</option>
                <option value="DELETE" <?= ($filterAction == 'DELETE') ? 'selected' : '' ?>>Delete</option>
                <option value="EXPORT" <?= ($filterAction == 'EXPORT') ? 'selected' : '' ?>>Export</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search"></i> Filter
            </button>
        </div>
        <?php if ($filterDateFrom || $filterDateTo || $filterUser || $filterAction): ?>
            <div class="col-12">
                <a href="/admin/activity-logs" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Reset Filter
                </a>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- Tabel Log -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th width="15%">Waktu</th>
                        <th width="12%">User</th>
                        <th width="10%">Aksi</th>
                        <th width="10%">Tabel</th>
                        <th>Deskripsi</th>
                        <th width="15%">IP & Browser</th>
                        <th width="5%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php $no = ($currentPage - 1) * $perPage + 1; ?>
                        <?php foreach ($logs as $log):
                            $badgeClass = match ($log->action) {
                                'LOGIN'  => 'badge-login',
                                'LOGOUT' => 'badge-logout',
                                'CREATE' => 'badge-create',
                                'UPDATE' => 'badge-update',
                                'DELETE' => 'badge-delete',
                                'EXPORT' => 'badge-export',
                                default  => 'bg-secondary'
                            };
                            $parsedUA = parse_user_agent($log->user_agent ?? '');
                        ?>
                            <tr class="log-row">
                                <td class="text-center fw-bold"><?= $no++ ?></td>
                                <td>
                                    <small class="d-block fw-bold"><?= date('d M Y', strtotime($log->created_at)) ?></small>
                                    <small class="text-muted"><?= date('H:i:s', strtotime($log->created_at)) ?></small>
                                </td>
                                <td>
                                    <strong><?= esc($log->username ?? 'System') ?></strong>
                                    <?php if (!empty($log->fullname)): ?>
                                        <br><small class="text-muted"><?= esc($log->fullname) ?></small>
                                    <?php endif; ?>
                                </td>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?> rounded-pill">
                                        <?= $log->action ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="small"><?= esc($log->table_name ?? '-') ?></code>
                                </td>
                                <td>
                                    <small><?= esc($log->description) ?></small>
                                </td>
                                <td>
                                    <div class="small">
                                        <i class="bi bi-pc-display me-1"></i>
                                        <code><?= esc($log->ip_address ?? '-') ?></code>
                                    </div>
                                    <div class="user-agent-text mt-1">
                                        <i class="bi bi-browser-chrome me-1"></i><?= esc($parsedUA) ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="/admin/activity-logs/delete/<?= $log->id ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Yakin ingin menghapus log ini?')"
                                        title="Hapus Log">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada log aktivitas yang sesuai dengan filter
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
                    <strong><?= $total ?></strong> log
                </small>

                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <?php
                        $baseUrl = '/admin/activity-logs';
                        $queryParams = $_GET;
                        unset($queryParams['page']);
                        $queryString = http_build_query($queryParams);
                        $qs = $queryString ? '&' . $queryString : '';
                        ?>

                        <?php if ($currentPage > 1): ?>
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