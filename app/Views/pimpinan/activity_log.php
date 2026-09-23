<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div>
    <h4 class="mb-0" style="font-size: 1.1rem; font-weight: 700; color: var(--primary-navy); margin: 0;">
        <?= esc((string) $page_title) ?>
    </h4>
    <small style="font-size: 0.8rem; color: #6c757d;">
        <?= esc((string) $page_subtitle) ?>
    </small>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    :root {
        --primary-navy: #273272;
        --secondary-blue: #3B82F6;
        --success-green: #10B981;
        --warning-orange: #F59E0B;
        --danger-red: #EF4444;
        --info-cyan: #06B6D4;
        --gray-50: #F9FAFB;
        --gray-100: #F3F4F6;
        --gray-200: #E5E7EB;
        --gray-300: #D1D5DB;
        --gray-600: #4B5563;
        --gray-700: #374151;
        --gray-900: #111827;
    }

    .activity-log-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Stats Card */
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--gray-200);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-navy), #3B82F6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: white;
    }

    .stats-content {
        flex: 1;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 800;
        color: var(--gray-900);
        line-height: 1;
        margin-bottom: 4px;
    }

    .stats-label {
        font-size: 0.85rem;
        color: var(--gray-600);
        font-weight: 500;
    }

    /* Filter Card */
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--gray-200);
        margin-bottom: 20px;
    }

    .filter-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        align-items: end;
    }

    @media (max-width: 1024px) {
        .filter-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .filter-row {
            grid-template-columns: 1fr;
        }
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-700);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-input,
    .filter-select {
        border: 1px solid var(--gray-300);
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 0.85rem;
        background: white;
        transition: all 0.2s;
        width: 100%;
    }

    .filter-input:focus,
    .filter-select:focus {
        outline: none;
        border-color: var(--primary-navy);
        box-shadow: 0 0 0 3px rgba(39, 50, 114, 0.1);
    }

    .btn-filter {
        background: var(--primary-navy);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 42px;
    }

    .btn-filter:hover {
        background: #1e2860;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(39, 50, 114, 0.3);
    }

    /* Table Card */
    .table-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--gray-200);
        overflow: hidden;
    }

    .table-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .activity-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .activity-table thead th {
        background: var(--gray-50);
        color: var(--gray-700);
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        text-align: left;
        border-bottom: 2px solid var(--gray-200);
    }

    .activity-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--gray-100);
        color: var(--gray-700);
        vertical-align: middle;
    }

    .activity-table tbody tr:hover {
        background: var(--gray-50);
    }

    .activity-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Action Badges */
    .badge-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-action.login {
        background: #DCFCE7;
        color: #166534;
    }

    .badge-action.logout {
        background: #FEE2E2;
        color: #991B1B;
    }

    .badge-action.export {
        background: #DBEAFE;
        color: #1E40AF;
    }

    /* Table Name Badge */
    .badge-table {
        background: var(--gray-100);
        color: var(--gray-700);
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.72rem;
        font-weight: 600;
        font-family: 'Courier New', monospace;
    }

    /* IP & Browser */
    .ip-browser {
        font-size: 0.78rem;
        color: var(--gray-600);
        line-height: 1.5;
    }

    .ip-browser .ip {
        font-weight: 600;
        color: var(--gray-900);
    }

    .ip-browser .browser {
        font-style: italic;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: var(--gray-600);
    }

    .empty-state-icon {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 16px;
    }

    .empty-state-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 8px;
    }

    .empty-state-desc {
        font-size: 0.9rem;
        color: var(--gray-600);
        max-width: 500px;
        margin: 0 auto;
    }

    /* Info Banner */
    .info-banner {
        background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
        border: 1px solid #BFDBFE;
        border-radius: 10px;
        padding: 14px 18px;
        margin-top: 20px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .info-banner-icon {
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .info-banner-content {
        flex: 1;
    }

    .info-banner-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1E40AF;
        margin-bottom: 4px;
    }

    .info-banner-text {
        font-size: 0.8rem;
        color: #1E40AF;
        line-height: 1.5;
        margin: 0;
    }
</style>

<div class="activity-log-container">
    <!-- Stats Card -->
    <div class="stats-card">
        <div class="stats-icon">📊</div>
        <div class="stats-content">
            <div class="stats-number"><?= (int) $totalLogs ?></div>
            <div class="stats-label">Total Aktivitas Anda</div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="filter-card">
        <div class="filter-title">
            Filter Riwayat Aktivitas
        </div>
        <form method="GET" action="/pimpinan/activity-logs">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Dari Tanggal</label>
                    <input type="date" name="date_from" class="filter-input" value="<?= esc((string) $filterDateFrom) ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Sampai Tanggal</label>
                    <input type="date" name="date_to" class="filter-input" value="<?= esc((string) $filterDateTo) ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Jenis Aksi</label>
                    <select name="action" class="filter-select">
                        <option value="">Semua Aksi</option>
                        <?php foreach ($allowedActions as $action): ?>
                            <option value="<?= $action ?>" <?= $filterAction === $action ? 'selected' : '' ?>>
                                <?= ucfirst(strtolower($action)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label" style="visibility: hidden;">Aksi</label>
                    <button type="submit" class="btn-filter">
                        🔍 Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="table-card">
        <div class="table-header">
            <h5 class="table-title">📋 Daftar Aktivitas</h5>
            <small style="color: var(--gray-600); font-size: 0.8rem;">
                Menampilkan 50 aktivitas terakhir
            </small>
        </div>

        <div class="table-responsive">
            <table class="activity-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 15%;">Waktu</th>
                        <th style="width: 12%;">Aksi</th>
                        <th style="width: 10%;">Tabel</th>
                        <th style="width: 33%;">Deskripsi Aktivitas</th>
                        <th style="width: 25%;">IP & Browser</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php $no = 1;
                        foreach ($logs as $log):
                            $actionClass = match ($log['action']) {
                                'LOGIN' => 'login',
                                'LOGOUT' => 'logout',
                                'EXPORT' => 'export',
                                default => ''
                            };
                            $actionIcon = match ($log['action']) {
                                'LOGIN' => '🔐',
                                'LOGOUT' => '🚪',
                                'EXPORT' => '🖨️',
                                default => '📝'
                            };
                        ?>
                            <tr>
                                <td><strong><?= $no++ ?></strong></td>
                                <td>
                                    <div style="font-weight: 600; color: var(--gray-900);">
                                        <?= esc($log['created_at']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-action <?= $actionClass ?>">
                                        <?= $actionIcon ?> <?= $log['action'] ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-table"><?= esc($log['table_name']) ?></span>
                                </td>
                                <td>
                                    <div style="line-height: 1.5;">
                                        <?= esc($log['description']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="ip-browser">
                                        <div class="ip">️ <?= esc($log['ip_address']) ?></div>
                                        <div class="browser"> <?= esc($log['user_agent']) ?></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">📭</div>
                                    <div class="empty-state-title">Tidak Ada Aktivitas</div>
                                    <div class="empty-state-desc">
                                        Tidak ada riwayat aktivitas yang sesuai dengan filter yang Anda pilih.
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="info-banner">
        <div class="info-banner-icon">ℹ️</div>
        <div class="info-banner-content">
            <div class="info-banner-title">Informasi Log Aktivitas</div>
            <p class="info-banner-text">
                Halaman ini hanya menampilkan aktivitas <strong>Login</strong>, <strong>Logout</strong>, dan <strong>Export/Print</strong>
                yang dilakukan oleh user Pimpinan. User Pimpinan hanya memiliki akses read-only terhadap data audit,
                sehingga tidak ada aksi modifikasi data yang dicatat.
            </p>
        </div>
    </div>
</div>

<?= $this->endSection() ?>