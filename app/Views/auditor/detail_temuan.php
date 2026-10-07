<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .info-card {
        border-radius: 12px;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e5e7eb;
    }

    .rtl-box {
        background: #f0fdf4;
        border-left: 4px solid #22c55e;
        border-radius: 8px;
        padding: 15px;
    }

    .revisi-box {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
        border-radius: 8px;
        padding: 15px;
    }

    .verifikasi-box {
        background: #fffbeb;
        border-left: 4px solid #f59e0b;
        border-radius: 8px;
        padding: 20px;
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Info Header -->
<div class="card border-0 shadow-sm info-card mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <?php
                $riskBadge = match ($finding->tingkat_risiko) {
                    'Rendah' => 'bg-success',
                    'Sedang' => 'bg-warning text-dark',
                    'Tinggi' => 'bg-danger',
                    'Kritis' => 'bg-dark',
                    default => 'bg-secondary'
                };
                $statusBadge = match ($finding->status) {
                    'Open' => 'bg-primary',
                    'In_Progress' => 'bg-info text-dark',
                    'Closed' => 'bg-success',
                    default => 'bg-secondary'
                };
                $fwBadge = ($finding->framework === 'ISO 27001') ? 'bg-success' : 'bg-info text-dark';
                ?>
                <h5 class="fw-bold mb-2">
                    <code class="badge bg-secondary me-2"><?= esc($finding->clause_code ?? '-') ?></code>
                    Temuan Audit
                </h5>
                <span class="badge <?= $fwBadge ?> rounded-pill me-2"><?= esc($finding->framework ?? '-') ?></span>
                <span class="badge <?= $riskBadge ?> rounded-pill me-2"><?= esc($finding->tingkat_risiko) ?></span>
                <span class="badge <?= $statusBadge ?> rounded-pill px-3 py-2">
                    Status: <?= str_replace('_', ' ', ucfirst($finding->status)) ?>
                </span>
            </div>
            <a href="/auditor/monitoring-temuan" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        <div class="row g-3">
            <div class="col-md-6"><small class="text-muted d-block">Audit</small><strong><?= esc($finding->audit_title ?? '-') ?></strong></div>
            <div class="col-md-6"><small class="text-muted d-block">Framework</small><strong><?= esc($finding->framework ?? '-') ?></strong></div>
            <div class="col-md-6"><small class="text-muted d-block">Periode</small><strong><?= esc($finding->nama_periode ?? '-') ?></strong></div>
            <div class="col-md-6"><small class="text-muted d-block">Auditee (Unit)</small><strong><i class="bi bi-building me-1"></i><?= esc($finding->auditee_name ?? '-') ?></strong></div>
            <div class="col-md-6"><small class="text-muted d-block">Tanggal Dibuat</small><strong><?= date('d M Y H:i', strtotime($finding->created_at)) ?></strong></div>
            <?php if (!empty($finding->closed_at)): ?>
                <div class="col-md-6"><small class="text-muted d-block">Tanggal Ditutup</small><strong><?= date('d M Y H:i', strtotime($finding->closed_at)) ?></strong></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Pertanyaan Terkait -->
<div class="card border-0 shadow-sm info-card mb-4">
    <div class="card-body p-4">
        <h6 class="section-title"><i class="bi bi-question-circle me-2"></i>Pertanyaan Terkait</h6>
        <p class="mb-0"><?= esc($finding->question_text ?? '-') ?></p>
    </div>
</div>

<!-- Deskripsi Temuan -->
<div class="card border-0 shadow-sm info-card mb-4" style="border-left: 4px solid #dc3545;">
    <div class="card-body p-4">
        <h6 class="section-title text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Deskripsi Temuan</h6>
        <p class="mb-0"><?= nl2br(esc($finding->deskripsi_temuan)) ?></p>
    </div>
</div>

<!-- Rekomendasi Auditor -->
<div class="card border-0 shadow-sm info-card mb-4" style="border-left: 4px solid #0d6efd;">
    <div class="card-body p-4">
        <h6 class="section-title text-primary"><i class="bi bi-lightbulb me-2"></i>Rekomendasi Auditor</h6>
        <p class="mb-0"><?= nl2br(esc($finding->rekomendasi)) ?></p>
    </div>
</div>

<!-- ✅ BARU: Jawaban Revisi Auditee -->
<div class="card border-0 shadow-sm info-card mb-4">
    <div class="card-body p-4">
        <h6 class="section-title text-info"><i class="bi bi-pencil-square me-2"></i>Jawaban Revisi Auditee</h6>
        <?php if (!empty($finding->jawaban_revisi)): ?>
            <div class="revisi-box">
                <p class="mb-0"><?= nl2br(esc($finding->jawaban_revisi)) ?></p>
            </div>
        <?php else: ?>
            <div class="alert alert-secondary mb-0">
                <i class="bi bi-info-circle me-2"></i>Auditee belum memberikan jawaban revisi.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Rencana Tindak Lanjut (Auditee) -->
<div class="card border-0 shadow-sm info-card mb-4">
    <div class="card-body p-4">
        <h6 class="section-title text-success"><i class="bi bi-arrow-repeat me-2"></i>Rencana Tindak Lanjut (Auditee)</h6>
        <?php if (!empty($finding->rtl_description)): ?>
            <div class="rtl-box">
                <div class="mb-3">
                    <p class="mb-1 fw-bold text-success"><i class="bi bi-clipboard-check me-1"></i>Rencana Perbaikan:</p>
                    <p class="mb-0"><?= nl2br(esc($finding->rtl_description)) ?></p>
                </div>
                <div class="row g-3 mt-2 pt-3 border-top">
                    <div class="col-md-6">
                        <p class="mb-1 fw-bold text-success"><i class="bi bi-cash-stack me-1"></i>Estimasi Anggaran:</p>
                        <?php if (empty($finding->rtl_anggaran) || $finding->rtl_anggaran == 0): ?>
                            <h5 class="mb-0 text-muted fst-italic">Tidak memerlukan anggaran</h5>
                        <?php else: ?>
                            <h5 class="mb-0 text-dark fw-bold">Rp <?= number_format($finding->rtl_anggaran, 0, ',', '.') ?></h5>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1 fw-bold text-success"><i class="bi bi-calendar-event me-1"></i>Target Selesai:</p>
                        <h5 class="mb-0 text-dark fw-bold"><?= date('d M Y', strtotime($finding->rtl_deadline)) ?></h5>
                        <?php
                        $deadline = strtotime($finding->rtl_deadline);
                        $today = time();
                        $diff = ($deadline - $today) / (60 * 60 * 24);
                        if ($diff < 0): ?>
                            <small class="text-danger fst-italic"><i class="bi bi-clock-history me-1"></i>Melebihi target (<?php echo abs((int)$diff); ?> hari)</small>
                        <?php elseif ($diff < 30): ?>
                            <small class="text-warning fst-italic"><i class="bi bi-hourglass-split me-1"></i>Segera (<?php echo (int)$diff; ?> hari lagi)</small>
                        <?php else: ?>
                            <small class="text-success fst-italic"><i class="bi bi-check-circle me-1"></i>Masih dalam batas waktu</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning mb-0"><i class="bi bi-info-circle me-2"></i>Auditee belum membuat Rencana Tindak Lanjut.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Form Review RTL (Tahap 1) -->
<?php if ($finding->status === 'Open'): ?>
    <div class="card border-0 shadow-sm info-card mb-4">
        <div class="card-body p-4">
            <h6 class="section-title text-warning"><i class="bi bi-file-earmark-text me-2"></i>Review RTL</h6>
            <p class="text-muted small mb-3">Berikan catatan review Anda untuk RTL yang diajukan Auditee.</p>
            <?php if (empty($finding->rtl_description)): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>Auditee belum mengisi RTL.</div>
            <?php else: ?>
                <form action="/auditor/temuan/review-rtl/<?= $finding->id ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Catatan Review RTL <span class="text-danger">*</span></label>
                        <textarea name="catatan_rtl" class="form-control" rows="3" required placeholder="Contoh: 'Rencana sudah baik, silakan eksekusi.'"><?= esc(old('catatan_rtl')) ?></textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="rtl_action" value="approve" class="btn btn-success btn-lg px-4"><i class="bi bi-check-circle me-2"></i>Approve RTL</button>
                        <button type="submit" name="rtl_action" value="reject" class="btn btn-danger btn-lg px-4"><i class="bi bi-x-circle me-2"></i>Reject RTL</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Form Verifikasi Bukti (Tahap 2) -->
<?php elseif ($finding->status === 'In_Progress'): ?>
    <div class="card border-0 shadow-sm info-card mb-4">
        <div class="card-body p-4">
            <h6 class="section-title text-primary"><i class="bi bi-clipboard-check me-2"></i>Verifikasi Bukti</h6>
            <p class="text-muted small mb-3">Periksa bukti perbaikan yang diupload Auditee dan berikan catatan verifikasi Anda.</p>

            <!-- AREA TAMPILAN BUKTI PERBAIKAN -->
           
<?php if(!empty($finding->bukti_perbaikan)): ?>
    <?php 
        $filename = $finding->bukti_perbaikan;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
        
        // PERBAIKAN: Gunakan base_url dengan urlencode untuk handle spasi
        $fileUrl = base_url('uploads/bukti_perbaikan/' . urlencode($filename));
    ?>
    
    <div class="mb-4">
        <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-paperclip me-2"></i>Bukti Perbaikan dari Auditee:</h6>
        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded border">
            <i class="bi <?= $isImage ? 'bi-image text-primary' : 'bi-file-earmark-text text-secondary' ?>" style="font-size: 2.5rem;"></i>
            <div class="flex-grow-1">
                <strong class="d-block"><?= esc($filename) ?></strong>
                <small class="text-muted">File bukti yang diupload oleh Auditee</small>
            </div>
            <?php if($isImage): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="previewImage('<?= $fileUrl ?>', '<?= esc($filename) ?>')">
                    <i class="bi bi-eye me-1"></i>Lihat Preview
                </button>
            <?php else: ?>
                <a href="<?= $fileUrl ?>" class="btn btn-primary btn-sm" download>
                    <i class="bi bi-download me-1"></i>Download
                </a>
            <?php endif; ?>
        </div>
    </div>
            <?php else: ?>
                <div class="alert alert-warning mb-4"><i class="bi bi-info-circle me-2"></i>Auditee belum mengupload bukti perbaikan.</div>
            <?php endif; ?>

            <!-- FORM CATATAN VERIFIKASI AUDITOR -->
            <form action="/auditor/temuan/verifikasi-bukti/<?= $finding->id ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label fw-bold">Catatan Verifikasi <span class="text-danger">*</span></label>
                    <textarea name="catatan_verifikasi" class="form-control" rows="3" required placeholder="Tuliskan hasil verifikasi bukti."><?= esc(old('catatan_verifikasi')) ?></textarea>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" name="verifikasi_action" value="approve" class="btn btn-success btn-lg px-4"><i class="bi bi-check-circle me-2"></i>Approve & Close</button>
                    <button type="submit" name="verifikasi_action" value="reject" class="btn btn-danger btn-lg px-4"><i class="bi bi-x-circle me-2"></i>Reject Bukti</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Read Only (Status: Closed) -->
<?php elseif ($finding->status === 'Closed'): ?>
    <?php if(!empty($finding->bukti_perbaikan)): ?>
    <?php 
        $filename = $finding->bukti_perbaikan;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
        
        // PERBAIKAN: URL langsung ke file di folder (karena sekarang nama file sudah benar)
        $fileUrl = base_url('uploads/bukti_perbaikan/' . urlencode($filename));
    ?>
    <div class="mb-4">
        <h6 class="fw-bold mb-2 text-primary"><i class="bi bi-paperclip me-2"></i>Bukti Perbaikan dari Auditee:</h6>
        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded border">
            <i class="bi <?= $isImage ? 'bi-image text-primary' : 'bi-file-earmark-text text-secondary' ?>" style="font-size: 2.5rem;"></i>
            <div class="flex-grow-1">
                <strong class="d-block"><?= esc($filename) ?></strong>
                <small class="text-muted">File bukti yang diupload oleh Auditee</small>
            </div>
            <?php if($isImage): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="previewImage('<?= $fileUrl ?>', '<?= esc($filename) ?>')">
                    <i class="bi bi-eye me-1"></i>Lihat Preview
                </button>
            <?php else: ?>
                <a href="<?= $fileUrl ?>" class="btn btn-primary btn-sm" download>
                    <i class="bi bi-download me-1"></i>Download
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-warning mb-4"><i class="bi bi-info-circle me-2"></i>Auditee belum mengupload bukti perbaikan.</div>
<?php endif; ?>
<?php endif; ?>

<!-- ========================================== -->
<!-- MODAL PREVIEW GAMBAR -->
<!-- ========================================== -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imagePreviewModalLabel">Preview Bukti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center bg-dark" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                <img id="previewImageSrc" src="" alt="Preview Bukti" class="img-fluid" style="max-height: 70vh; border-radius: 8px;">
            </div>
            <div class="modal-footer">
                <a id="downloadImageBtn" href="#" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i>Download Gambar Ini
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk Preview Gambar -->
<script>
    function previewImage(imageUrl, fileName) {
        // Set source gambar dan judul modal
        document.getElementById('previewImageSrc').src = imageUrl;
        document.getElementById('imagePreviewModalLabel').innerText = fileName;

        // Set link download di dalam modal
        document.getElementById('downloadImageBtn').href = imageUrl;
        document.getElementById('downloadImageBtn').download = fileName;

        // Tampilkan modal Bootstrap
        var myModal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
        myModal.show();
    }
</script>

<?= $this->endSection() ?>