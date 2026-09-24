<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    .border-navy {
        border-color: var(--primary-navy) !important;
    }
    .badge-navy {
        background-color: var(--primary-navy) !important;
        color: #ffffff !important; /* Teks putih agar kontras dengan background navy */
    }
</style>
<h4 class="fw-bold mb-4 d-none d-lg-block" style="color: var(--primary-navy);">Dashboard Auditor</h4>

<!-- Statistik Cards -->
<div class="row g-4 mb-5">
    <div class="col-xl-3 col-md-6">
        <div class="card p-4 text-white" style="background: var(--primary-navy); border-radius: 12px;">
            <div class="fs-2 fw-bold"><?= $stats['aktif'] ?? 0 ?></div>
            <div class="small opacity-75 mt-2 text-uppercase">Audit Aktif</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card p-4 text-white" style="background: var(--accent-orange); border-radius: 12px;">
            <div class="fs-2 fw-bold"><?= $stats['menunggu'] ?? 0 ?></div>
            <div class="small opacity-75 mt-2 text-uppercase">Menunggu Penilaian</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card p-4 bg-success text-white" style="border-radius: 12px;">
            <div class="fs-2 fw-bold"><?= $stats['selesai'] ?? 0 ?></div>
            <div class="small opacity-75 mt-2 text-uppercase">Selesai</div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card p-4 bg-light text-dark" style="border-radius: 12px;">
            <div class="fs-2 fw-bold"><?= $stats['total'] ?? 0 ?></div>
            <div class="small opacity-75 mt-2 text-uppercase">Total Audit</div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold m-0" style="color: var(--primary-navy);">Daftar Audit Terbaru</h5>
</div>

<!-- Legend / Keterangan Status -->
<div class="d-flex flex-wrap gap-3 mb-4 small text-muted">
    <div class="d-flex align-items-center">
        <span class="badge bg-primary me-2" style="width: 12px; height: 12px; border-radius: 50%;"></span>
        <span>Aktif: Auditor sedang memilih pertanyaan</span>
    </div>
    <div class="d-flex align-items-center">
        <span class="badge bg-info text-dark me-2" style="width: 12px; height: 12px; border-radius: 50%;"></span>
        <span>Menunggu Jawaban: Pertanyaan sudah dikirim ke Auditee</span>
    </div>
    <div class="d-flex align-items-center">
        <span class="badge bg-warning text-dark me-2" style="width: 12px; height: 12px; border-radius: 50%;"></span>
        <span>Menunggu Penilaian: Auditee sudah submit jawaban</span>
    </div>
    <div class="d-flex align-items-center">
        <span class="badge bg-success me-2" style="width: 12px; height: 12px; border-radius: 50%;"></span>
        <span>Selesai: Audit telah finalized</span>
    </div>
</div>

<div class="row g-4">
    <?php if (!empty($audits)): ?>
        <?php foreach ($audits as $audit):
            $border = match ($audit->status) {
                'menunggu_jawaban' => 'border-info',        // Biru muda
                'menunggu_penilaian' => 'border-warning',   // Kuning
                'aktif' => 'border-navy',                   // Biru tua
                'selesai' => 'border-success',              // Hijau
                default => 'border-secondary'               // Abu-abu (fallback)
            };
        ?>
            <div class="col-lg-4 col-md-6">
                <div class="card h-100 border-start border-4 <?= $border ?>" style="border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);">
                    <div class="card-body p-4">
                        <span class="badge rounded-pill px-3 py-2 mb-3 
    <?= match ($audit->status) {
                'menunggu_jawaban' => 'bg-info text-dark',      // Biru muda
                'menunggu_penilaian' => 'bg-warning text-dark', // Kuning
                'aktif' => 'badge-navy',                        // Biru tua
                'selesai' => 'bg-success',                      // Hijau
                default => 'bg-secondary'                       // Abu-abu (fallback)
            } ?>">
                            <?= ucfirst(str_replace('_', ' ', $audit->status)) ?>
                        </span>
                        <h6 class="fw-bold mb-2"><?= esc($audit->title) ?></h6>

                        <!-- Info Auditee -->
                        <p class="text-muted small mb-1">
                            <i class="bi bi-building me-1"></i> Auditee: <?= esc($audit->auditee_name ?? 'N/A') ?>
                        </p>

                        <!-- Info Auditor (BARU DITAMBAHKAN) -->
                        <p class="text-muted small mb-3">
                            <i class="bi bi-person-check me-1"></i> Auditor: <?= esc($audit->auditor_name ?? 'Administrator') ?>
                        </p>
                        <div class="mt-auto pt-3 border-top d-flex gap-2">
                            <a href="/audit/detail/<?= $audit->id ?>" class="btn btn-outline-secondary btn-sm flex-grow-1">Lihat Detail</a>
                            <?php if ($audit->status === 'menunggu_penilaian'): ?>
                                <a href="/audit/nilai/<?= $audit->id ?>" class="btn btn-warning flex-grow-1 text-black"">Nilai Sekarang</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class=" col-12 text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3"></i> Belum ada data audit.
                        </div>
                    <?php endif; ?>
                    </div>
                    <?= $this->endSection() ?>