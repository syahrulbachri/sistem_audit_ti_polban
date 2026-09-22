<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    /* CSS untuk Hover Effect (Menggantikan Inline JS) */
    .audit-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .audit-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
    }
</style>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
// ==========================================
// KALKULASI STATISTIK (Sebaiknya dipindah ke Controller nanti)
// ==========================================
$urgentCount = 0;
$totalQ = 0;
$answeredQ = 0;

if (!empty($audits)) {
    foreach ($audits as $a) {
        $totalQ += $a->total_questions ?? 0;
        $answeredQ += $a->answered_count ?? 0;

        if (!empty($a->deadline)) {
            // Gunakan ceil() agar konsisten dengan perhitungan di kartu tugas
            $daysLeft = ceil((strtotime($a->deadline) - time()) / 86400);
            if ($daysLeft <= 3 && $daysLeft > 0) {
                $urgentCount++;
            }
        }
    }
}
$avgProgress = $totalQ > 0 ? round(($answeredQ / $totalQ) * 100) : 0;
?>

<!-- Kartu Statistik -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 p-4 text-white h-100" style="background: var(--primary-navy); border-radius: 12px;">
            <div class="fs-2 fw-bold"><?= count($audits ?? []) ?></div>
            <div class="small opacity-75 mt-2 text-uppercase">Tugas Aktif</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 p-4 text-white h-100" style="background: var(--accent-orange); border-radius: 12px;">
            <div class="fs-2 fw-bold"><?= $urgentCount ?></div>
            <div class="small opacity-75 mt-2 text-uppercase">Deadline Dekat</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 p-4 text-dark h-100" style="background: #e9ecef; border-radius: 12px;">
            <div class="fs-2 fw-bold"><?= $avgProgress ?>%</div>
            <div class="small opacity-75 mt-2 text-uppercase">Rata-rata Progres</div>
        </div>
    </div>
</div>

<!-- ============ CARD RTL (TAMPIL DULU) ============ -->
<?php if (!empty($rtlAudits)): ?>
    <h5 class="fw-bold mb-3" style="color: var(--primary-navy);">
        <i class="bi bi-clipboard-exclamation me-2"></i>Rencana Tindak Lanjut (RTL)
    </h5>
    <div class="row g-4 mb-4">
        <?php foreach ($rtlAudits as $a): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #dc3545;">
                    <div class="card-body p-4">
                        <?php if ($a->belum_rtl > 0): ?>
                            <span class="badge bg-danger rounded-pill mb-2">Perlu RTL</span>
                            <h6 class="fw-bold"><?= esc($a->title) ?></h6>
                            <p class="small text-muted mb-3">
                                <i class="bi bi-exclamation-circle me-1"></i><?= $a->belum_rtl ?> temuan perlu Anda isi Rencana
                                Tindak Lanjut.
                            </p>
                            <a href="/auditee/rtl/<?= $a->id ?>" class="btn btn-danger w-100">
                                <i class="bi bi-pencil-square me-1"></i>Isi RTL
                            </a>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark rounded-pill mb-2">Menunggu Review RTL</span>
                            <h6 class="fw-bold"><?= esc($a->title) ?></h6>
                            <p class="small text-muted mb-3">
                                <i class="bi bi-hourglass-split me-1"></i>RTL Anda sedang direview oleh auditor.
                            </p>
                            <a href="/auditee/rtl/<?= $a->id ?>" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-eye me-1"></i>Lihat RTL
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ============ CARD PERLU REVISI (RTL approved, revisi belum dikirim) ============ -->
<?php if (!empty($revisiAudits)): ?>
    <h5 class="fw-bold mb-3 text-danger">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>Perlu Revisi
    </h5>
    <div class="row g-4 mb-4">
        <?php foreach ($revisiAudits as $a): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #dc3545;">
                    <div class="card-body p-4">
                        <span class="badge bg-danger rounded-pill mb-2">Perlu Revisi</span>
                        <h6 class="fw-bold">
                            <?= esc($a->title) ?>
                        </h6>
                        <p class="small text-muted mb-3">
                            <i class="bi bi-exclamation-circle me-1"></i>RTL disetujui.
                            <?= $a->jumlah_revisi ?> temuan perlu Anda revisi sekarang.
                        </p>
                        <a href="/auditee/revisi/<?= $a->id ?>" class="btn btn-danger w-100">
                            <i class="bi bi-pencil-square me-1"></i>Lihat & Perbaiki
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- ============ CARD MENUNGGU VERIFIKASI AUDITOR (SETELAH RTL DI-APPROVE) ============ -->
<?php if (!empty($waitingAudits)): ?>
    <h5 class="fw-bold mb-3" style="color: var(--accent-orange);">
        <i class="bi bi-hourglass-split me-2"></i>Menunggu Verifikasi Auditor
    </h5>
    <div class="row g-4 mb-4">
        <?php foreach ($waitingAudits as $a): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm"
                    style="border-radius: 12px; border-left: 5px solid var(--accent-orange);">
                    <div class="card-body p-4">
                        <span class="badge bg-warning text-dark rounded-pill mb-2">Menunggu Verifikasi</span>
                        <h6 class="fw-bold"><?= esc($a->title) ?></h6>
                        <p class="small text-muted mb-3">
                            <i class="bi bi-hourglass-split me-1"></i><?= $a->jumlah_menunggu ?> revisi sedang diperiksa
                            auditor.
                        </p>
                        <a href="/auditee/revisi/<?= $a->id ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-eye me-1"></i>Lihat Status
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Daftar Tugas -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color: var(--primary-navy);">
            <i class="bi bi-list-check me-2"></i>Tugas Audit yang Harus Dikerjakan
        </h5>

        <?php if (!empty($audits)): ?>
            <div class="row g-4">
                <?php foreach ($audits as $audit):
                    $totalQuestions = $audit->total_questions ?? 0;
                    $answeredCount = $audit->answered_count ?? 0;
                    $progress = $totalQuestions > 0 ? round(($answeredCount / $totalQuestions) * 100) : 0;

                    $deadline = $audit->deadline ?? null;
                    // Perhitungan hari disamakan menggunakan ceil()
                    $daysLeft = $deadline ? ceil((strtotime($deadline) - time()) / 86400) : 0;

                    $isUrgent = $daysLeft <= 3 && $daysLeft > 0;
                    $isOverdue = $daysLeft <= 0 && $deadline !== null;
                    $isCompleted = $progress == 100 && $totalQuestions > 0;

                    // Tentukan warna border berdasarkan status prioritas
                    $borderColor = 'var(--primary-navy)';
                    if ($isOverdue)
                        $borderColor = '#dc3545';
                    elseif ($isUrgent)
                        $borderColor = 'var(--accent-orange)';
                    elseif ($isCompleted)
                        $borderColor = '#198754';

                    // Tentukan warna Badge Status Kiri
                    $statusBadgeClass = 'bg-primary';
                    $statusText = ucfirst(str_replace('_', ' ', $audit->status ?? 'aktif'));
                    if ($audit->status === 'menunggu_penilaian') {
                        $statusBadgeClass = 'bg-warning text-dark';
                    } elseif ($audit->status === 'selesai' || $isCompleted) {
                        $statusBadgeClass = 'bg-success';
                        $statusText = 'Selesai';
                    }
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <!-- Gunakan class audit-card untuk hover effect -->
                        <div class="card h-100 border-0 shadow-sm audit-card"
                            style="border-radius: 12px; border-left: 5px solid <?= $borderColor ?>;">
                            <div class="card-body p-4">
                                <!-- Header dengan Badge Status -->
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge <?= $statusBadgeClass ?> rounded-pill px-3 py-2">
                                        <?= $statusText ?>
                                    </span>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Terlambat
                                        </span>
                                    <?php elseif ($isUrgent): ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">
                                            <i class="bi bi-clock me-1"></i><?= $daysLeft ?> hari lagi
                                        </span>
                                    <?php elseif ($isCompleted): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                            <i class="bi bi-check-circle me-1"></i>100%
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Judul Audit -->
                                <h6 class="fw-bold mb-3" style="color: var(--primary-navy);">
                                    <?= esc($audit->title ?? 'Tanpa Judul') ?>
                                </h6>

                                <!-- Informasi Detail -->
                                <div class="mb-3">
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <strong>Deadline:</strong> <?= $deadline ? date('d M Y', strtotime($deadline)) : '-' ?>
                                    </p>
                                    <p class="text-muted small mb-2">
                                        <i class="bi bi-bookmark me-1"></i>
                                        <strong>Framework:</strong> <?= esc($audit->framework ?? '-') ?>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        <i class="bi bi-person me-1"></i>
                                        <strong>Auditor:</strong> <?= esc($audit->auditor_name ?? 'Belum ditunjuk') ?>
                                    </p>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-2">
                                        <span class="text-muted">Progres Jawaban</span>
                                        <span class="fw-bold"><?= $answeredCount ?>/<?= $totalQuestions ?></span>
                                    </div>
                                    <div class="progress" style="height: 10px; border-radius: 5px; background: #e9ecef;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: <?= $progress ?>%; background: <?= $isCompleted ? '#198754' : 'var(--primary-navy)' ?>;"
                                            aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?= $progress ?>% selesai</small>
                                </div>

                                <!-- Tombol Aksi -->
                                <a href="/auditee/fill/<?= $audit->id ?>" class="btn w-100 py-2"
                                    style="background: <?= $isCompleted ? '#198754' : 'var(--primary-navy)' ?>; color: white; border: none; border-radius: 8px;">
                                    <i class="bi bi-<?= $isCompleted ? 'eye' : 'pencil-square' ?> me-2"></i>
                                    <?= $isCompleted ? 'Lihat Jawaban' : ($progress > 0 ? 'Lanjutkan Mengisi' : 'Mulai Mengisi') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                <h6 class="text-muted mb-2">Belum Ada Tugas</h6>
                <p class="text-muted small mb-0">Belum ada tugas audit yang diberikan kepada Anda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>