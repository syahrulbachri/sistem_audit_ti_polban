<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    /* CSS Khusus untuk Tampilan Cetak (Print) */
    @media print {
        body * { visibility: hidden; }
        #print-area, #print-area * { visibility: visible; }
        #print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h4 class="fw-bold mb-0" style="color: var(--primary-navy);"><?= $page_title ?></h4>
    <a href="/admin/planning" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<!-- Area yang akan dicetak -->
<div id="print-area">
    <!-- Header Laporan (Hanya muncul saat print) -->
    <div class="d-none d-print-block text-center mb-4">
        <h4 class="fw-bold">LAPORAN HASIL AUDIT IT</h4>
        <h5 class="text-muted">POLITEKNIK NEGERI BANDUNG</h5>
        <hr>
    </div>

    <!-- 1. Info Dasar Audit -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--primary-navy);"><?= esc($audit->title) ?></h5>
                    <?php
                    $statusClass = match($audit->status) {
                        'aktif' => 'badge-navy',
                        'menunggu_jawaban' => 'bg-info text-dark',
                        'menunggu_penilaian' => 'bg-warning text-dark',
                        'selesai' => 'bg-success',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge rounded-pill px-3 py-2 <?= $statusClass ?>">
                        <?= ucfirst(str_replace('_', ' ', $audit->status)) ?>
                    </span>
                </div>
            </div>
            
            <div class="row g-3">
                <div class="col-md-6"><small class="text-muted d-block">Periode</small><strong><?= esc($audit->nama_periode ?? '-') ?></strong></div>
                <div class="col-md-6"><small class="text-muted d-block">Auditee</small><strong><?= esc($audit->auditee_name ?? '-') ?></strong></div>
                <div class="col-md-6"><small class="text-muted d-block">Auditor</small><strong><?= esc($audit->auditor_name ?? '-') ?></strong></div>
                <div class="col-md-6"><small class="text-muted d-block">Deadline</small><strong><?= date('d M Y', strtotime($audit->deadline)) ?></strong></div>
            </div>

            <!-- 2. Skor Akhir (Hanya jika Selesai) -->
            <?php if ($audit->status === 'selesai'): ?>
            <div class="mt-4 p-3 text-center text-white" style="background: var(--primary-navy); border-radius: 8px;">
                <h6 class="mb-2 opacity-75">SKOR AKHIR AUDIT</h6>
                <h1 class="display-4 fw-bold mb-0">
                    <?= $isBinary ? round($audit->final_score) . '%' : number_format($audit->final_score, 2) ?>
                </h1>
                <small class="opacity-75">
                    <?= $isBinary ? 'Tingkat Kepatuhan (Compliance Rate)' : 'Rata-rata Tingkat Kematangan' ?>
                </small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 3. Daftar Pertanyaan & Jawaban -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2"></i>Daftar Pertanyaan Audit</h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php if(!empty($assignedQuestions)): ?>
                    <?php $no = 1; foreach($assignedQuestions as $q): ?>
                    <div class="list-group-item py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <span class="badge bg-primary me-2"><?= $no++ ?></span>
                                <?php if ($q->is_standard == 0): ?>
                                    <span class="badge bg-warning text-dark me-2">Custom</span>
                                <?php endif; ?>
                                <code class="badge bg-secondary"><?= esc($q->clause_code) ?></code>
                                <p class="fw-bold mb-2 mt-2"><?= esc($q->question_text) ?></p>
                                
                                <!-- Tampilkan Jawaban Auditee jika status >= menunggu_penilaian -->
                                <?php if (in_array($audit->status, ['menunggu_penilaian', 'selesai']) && !empty($q->answer)): ?>
                                    <div class="bg-light p-3 rounded mb-2 border-start border-4 border-info">
                                        <small class="text-primary fw-bold"><i class="bi bi-reply me-1"></i> Jawaban Auditee:</small>
                                        <p class="mb-0 small mt-1"><?= nl2br(esc($q->answer)) ?></p>
                                        
                                        <?php if (!empty($q->evidence_filename)): ?>
                                            <small class="text-muted d-block mt-2">
                                                <i class="bi bi-paperclip me-1"></i> Bukti: <?= esc($q->evidence_filename) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                <?php elseif ($audit->status === 'menunggu_jawaban'): ?>
                                    <div class="alert alert-warning py-2 px-3 mb-0 small">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu jawaban dari Auditee.
                                    </div>
                                <?php endif; ?>

                                <!-- Tampilkan Catatan Auditor jika ada -->
                                <?php if (!empty($q->note)): ?>
                                    <small class="text-muted fst-italic d-block mt-2">
                                        <i class="bi bi-chat-left-text me-1"></i> Catatan Auditor: <?= esc($q->note) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Tampilkan Skor jika sudah dinilai -->
                            <?php if (in_array($audit->status, ['menunggu_penilaian', 'selesai']) && $q->score !== null): ?>
                            <div class="text-end ms-3">
                                <span class="badge <?= $q->score == 1 ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-2">
                                    <?= $isBinary ? ($q->score == 1 ? 'Sesuai' : 'Tidak Sesuai') : 'Level ' . number_format($q->score, 2) ?>
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">Belum ada pertanyaan yang di-assign.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 4. Temuan & Rencana Tindak Lanjut (Hanya jika Selesai) -->
    <?php if ($audit->status === 'selesai' && !empty($findings)): ?>
    <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #dc3545;">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Temuan & Tindak Lanjut Audit</h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php $no = 1; foreach($findings as $f): 
                    $riskBadge = match($f->tingkat_risiko) {
                        'Rendah' => 'bg-success', 'Sedang' => 'bg-warning text-dark',
                        'Tinggi' => 'bg-danger', 'Kritis' => 'bg-dark', default => 'bg-secondary'
                    };
                    $statusBadge = match($f->status ?? 'Open') {
                        'Open' => 'bg-primary', 'In_Progress' => 'bg-info text-dark',
                        'Closed' => 'bg-success', default => 'bg-secondary'
                    };
                ?>
                <div class="list-group-item p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary me-2"><?= $no++ ?></span>
                            <span class="badge bg-secondary me-2"><?= esc($f->clause_code ?? 'Umum') ?></span>
                            <span class="badge <?= $riskBadge ?> rounded-pill"><?= esc($f->tingkat_risiko) ?></span>
                        </div>
                        <span class="badge <?= $statusBadge ?> rounded-pill px-3 py-2">
                            Status: <?= str_replace('_', ' ', ucfirst($f->status ?? 'Open')) ?>
                        </span>
                    </div>
                    
                    <div>
                        <h6 class="fw-bold mb-1 small text-uppercase text-muted">Deskripsi Temuan:</h6>
                        <p class="mb-3"><?= nl2br(esc($f->deskripsi_temuan)) ?></p>
                        
                        <h6 class="fw-bold mb-1 small text-uppercase text-muted">Rekomendasi Auditor:</h6>
                        <p class="mb-3"><?= nl2br(esc($f->rekomendasi)) ?></p>

                        <!-- Rencana Tindak Lanjut (RTL) -->
                        <div class="bg-light p-3 rounded border-start border-4 border-info mb-3">
                            <h6 class="fw-bold mb-2 small text-info"><i class="bi bi-arrow-repeat me-1"></i> Rencana Tindak Lanjut (Auditee):</h6>
                            <?php if (!empty($f->rtl_description)): ?>
                                <p class="mb-2 small"><strong>Rencana Perbaikan:</strong><br> <?= nl2br(esc($f->rtl_description)) ?></p>
                                <?php if (!empty($f->rtl_deadline)): ?>
                                    <p class="mb-0 small"><strong>Target Selesai:</strong> <?= date('d M Y', strtotime($f->rtl_deadline)) ?></p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="mb-0 text-muted small fst-italic">Auditee belum membuat Rencana Tindak Lanjut.</p>
                            <?php endif; ?>
                        </div>

                        <!-- Info Penutupan -->
                        <?php if (($f->status ?? '') === 'Closed' && !empty($f->closed_at)): ?>
                            <div class="alert alert-success py-2 px-3 mb-0 small">
                                <i class="bi bi-check-circle-fill me-1"></i> 
                                <strong>Temuan telah ditutup (Verified)</strong> pada <?= date('d M Y', strtotime($f->closed_at)) ?>.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php elseif ($audit->status === 'selesai'): ?>
    <div class="alert alert-success border-0 shadow-sm">
        <i class="bi bi-check-circle-fill me-2"></i>
        <strong>Sempurna!</strong> Tidak ada temuan ketidaksesuaian dalam audit ini.
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>