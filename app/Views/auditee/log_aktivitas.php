<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<!-- Kartu Total Aktivitas -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; max-width: 430px;">
    <div class="card-body p-4 d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
            style="width: 64px; height: 64px; background: #dce7fa;">
            <i class="bi bi-activity fs-4" style="color: var(--primary-navy);"></i>
        </div>
        <div>
            <div class="fs-4 fw-bold mb-0"><?= $totalAktivitas ?></div>
            <div class="text-muted">Total Aktivitas Anda</div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-4">
        <form method="get" action="/auditee/log-aktivitas">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control" value="<?= esc($filterDari ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control" value="<?= esc($filterSampai ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-1">Jenis Aksi</label>
                    <?php $j = $filterJenis ?? 'semua'; ?>
                    <select name="jenis" class="form-select">
                        <option value="semua">Semua Aksi</option>
                        <option value="LOGIN" <?= $j === 'LOGIN' ? 'selected' : '' ?>>Login</option>
                        <option value="LOGOUT" <?= $j === 'LOGOUT' ? 'selected' : '' ?>>Logout</option>
                        <option value="UPDATE" <?= $j === 'UPDATE' ? 'selected' : '' ?>>Update (Jawaban/RTL/Revisi/Bukti)
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Log -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th width="4%">#</th>
                        <th width="13%">Waktu</th>
                        <th width="10%">Aksi</th>
                        <th width="20%">Tabel</th>
                        <th>Deskripsi Aktivitas</th>
                        <th width="18%">IP & Browser</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php $no = 1;
                        foreach ($logs as $log):
                            $badge = match ($log->action) {
                                'LOGIN', 'LOGOUT' => 'bg-info text-white',
                                'CREATE' => 'bg-success',
                                'UPDATE' => 'bg-warning text-dark',
                                'DELETE' => 'bg-danger',
                                'EXPORT' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $no++ ?></td>
                                <td>
                                    <strong><?= date('d M Y', strtotime($log->created_at)) ?></strong><br>
                                    <small class="text-muted"><?= date('H:i:s', strtotime($log->created_at)) ?></small>
                                </td>
                                <td><span class="badge <?= $badge ?> rounded-pill px-3 py-2"><?= esc($log->action) ?></span>
                                </td>
                                <td><code><?= esc($log->table_name) ?></code></td>
                                <td><?= esc($log->description) ?></td>
                                <td>
                                    <div><i class="bi bi-phone me-1"></i><code
                                            class="text-danger"><?= esc($log->ip_address) ?></code></div>
                                    <div class="text-muted small"><i
                                            class="bi bi-globe me-1"></i><em><?= esc($log->browser) ?></em></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada aktivitas yang cocok dengan
                                filter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>