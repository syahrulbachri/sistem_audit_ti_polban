<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);">Daftar Audit Saya</h4>

<!-- FILTER -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-3">
        <form method="get" action="/auditee/daftar-audit" class="row g-2 align-items-center">
            <div class="col-md-5">
                <input type="text" name="q" class="form-control" placeholder="Cari judul audit..."
                    value="<?= esc($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="semua">Semua Status</option>
                    <option value="aktif" <?= $fStatus === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                    <option value="menunggu_penilaian" <?= $fStatus === 'menunggu_penilaian' ? 'selected' : '' ?>>Menunggu
                        Penilaian</option>
                    <option value="selesai" <?= $fStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
            <div class="col-auto">
                <a href="/auditee/daftar-audit" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- TABEL DAFTAR AUDIT -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Judul Audit</th>
                        <th>Framework</th>
                        <th>Auditor</th>
                        <th>Deadline</th>
                        <th style="min-width:150px;">Progres Jawaban</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($audits)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Tidak ada audit yang cocok dengan filter.
                            </td>
                        </tr>
                    <?php else:
                        foreach ($audits as $i => $a):
                            $badge = match ($a->status) {
                                'aktif' => 'bg-primary',
                                'menunggu_penilaian' => 'bg-warning text-dark',
                                'selesai' => 'bg-success',
                                default => 'bg-secondary'
                            };
                            $label = ucfirst(str_replace('_', ' ', $a->status));
                            $late = ($a->status !== 'selesai') && !empty($a->deadline) && strtotime($a->deadline) < strtotime(date('Y-m-d'));
                            ?>
                            <tr>
                                <td>
                                    <?= $i + 1 ?>
                                </td>
                                <td><strong>
                                        <?= esc($a->title) ?>
                                    </strong></td>
                                <td><span class="badge bg-light text-dark border">
                                        <?= esc($a->framework) ?>
                                    </span></td>
                                <td>
                                    <?= esc($a->auditor_name ?? '-') ?>
                                </td>
                                <td>
                                    <?= date('d M Y', strtotime($a->deadline)) ?>
                                    <?= $late ? '<span class="badge bg-danger">Terlambat</span>' : '' ?>
                                </td>
                                <td>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" style="width: <?= $a->progress ?>%;"></div>
                                    </div>
                                    <small class="text-muted">
                                        <?= (int) $a->answered_count ?>/
                                        <?= (int) $a->total_questions ?> pertanyaan
                                    </small>
                                </td>
                                <td><span class="badge <?= $badge ?> rounded-pill">
                                        <?= $label ?>
                                    </span></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if ($a->status === 'aktif'): ?>
                                            <a href="/auditee/fill/<?= $a->id ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil-square me-1"></i>Isi Kuesioner
                                            </a>
                                        <?php elseif ($a->status === 'menunggu_penilaian'): ?>
                                            <a href="/auditee/fill/<?= $a->id ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye me-1"></i>Lihat Jawaban
                                            </a>
                                        <?php else: ?>
                                            <a href="/auditee/riwayat" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye me-1"></i>Lihat Riwayat
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($a->need_rtl > 0): ?>
                                            <a href="/auditee/rtl/<?= $a->id ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-clipboard-check me-1"></i>Isi RTL (
                                                <?= $a->need_rtl ?>)
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($a->need_revisi > 0): ?>
                                            <a href="/auditee/revisi/<?= $a->id ?>" class="btn btn-sm btn-danger">
                                                <i class="bi bi-arrow-repeat me-1"></i>Revisi (
                                                <?= $a->need_revisi ?>)
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($a->waiting > 0): ?>
                                            <span class="btn btn-sm btn-light border text-muted">
                                                <i class="bi bi-hourglass-split me-1"></i>Menunggu Verifikasi (
                                                <?= $a->waiting ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>