<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    /* =========================================
       PENGATURAN CETAK LAPORAN (ADMIN)
       Disamakan dengan standar Auditor
       ========================================= */
    @media print {

        /* 1. SEMBUNYIKAN ELEMEN NON-CETAK */
        #actionButtons,
        .no-print,
        footer,
        .sidebar,
        .top-header,
        nav,
        .btn-close {
            display: none !important;
        }

        /* 2. UKURAN KERTAS */
        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        /* 3. RESET BODY / CONTAINER */
        html,
        body {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        .main-content {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* 4. HILANGKAN EFEK CARD & SHADOW */
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
            margin-bottom: 15px !important;
        }

        /* 5. PENTING: LIST-GROUP JANGAN FLEX SAAT PRINT */
        .list-group,
        .list-group-flush,
        .list-group-item {
            display: block !important;
        }

        /* 6. PERTANYAAN & TEMUAN JANGAN DIPOTONG */
        .list-group-item,
        .finding-item,
        .bg-light.p-3.rounded.border-start {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        /* 7. JANGAN MEMAKSA CARD-BODY UTUH (agar bisa wrap antar halaman) */
        .card-body {
            break-inside: auto !important;
            page-break-inside: auto !important;
        }

        /* 8. HEADER TEMUAN + ISINYA MENYATU */
        .card-header {
            break-after: avoid !important;
            page-break-after: avoid !important;
        }

        /* 9. JANGAN BUAT BARIS TEKS TERLALU MUDAH TERPISAH */
        p,
        h6,
        strong,
        h5,
        h4 {
            orphans: 3;
            widows: 3;
        }

        /* 10. MARGIN SUPAYA LEBIH RAPI */
        .mb-4 {
            margin-bottom: 15px !important;
        }

        /* 11. WARNA TETAP DICETAK (PENTING UNTUK BADGE & BACKGROUND) */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <h4 class="fw-bold mb-0" style="color: var(--primary-navy);"><?= $page_title ?></h4>
    <div>
        <a href="/admin/completed-audits" class="btn btn-outline-secondary me-2">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
        <button onclick="handlePrintAndLog(<?= $audit->id ?>)" class="btn btn-primary">
            <i class="bi bi-printer"></i> Cetak / Simpan PDF
        </button>
    </div>
</div>

<!-- Area yang akan dicetak -->
<div id="print-area">
    <!-- Header Laporan (Hanya muncul saat print) -->
    <div class="d-none d-print-block text-center mb-4">
        <h4 class="fw-bold">LAPORAN HASIL AUDIT IT</h4>
        <h5 class="text-muted">POLITEKNIK NEGERI BANDUNG</h5>
        <hr>
    </div>

    <!-- Info Audit -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="fw-bold mb-1" style="color: var(--primary-navy);"><?= esc($audit->title) ?></h5>
                    <span class="badge bg-success rounded-pill">Selesai</span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6"><small class="text-muted d-block">Periode</small><strong><?= esc($audit->nama_periode ?? '-') ?></strong></div>
                <div class="col-md-6"><small class="text-muted d-block">Auditee</small><strong><?= esc($audit->auditee_name ?? '-') ?></strong></div>
                <div class="col-md-6"><small class="text-muted d-block">Auditor</small><strong><?= esc($audit->auditor_name ?? '-') ?></strong></div>
                <div class="col-md-6"><small class="text-muted d-block">Tanggal Selesai</small><strong><?= date('d M Y', strtotime($audit->updated_at)) ?></strong></div>
            </div>

            <div class="mt-4 p-3 text-center text-white" style="background: var(--primary-navy); border-radius: 8px;">
                <h6 class="mb-2 opacity-75">SKOR AKHIR AUDIT</h6>
                <h1 class="display-4 fw-bold mb-0"><?= $isBinary ? round($audit->final_score) . '%' : number_format($audit->final_score, 2) ?></h1>
            </div>
        </div>
    </div>

    <!-- Daftar Pertanyaan -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0"><i class="bi bi-list-check me-2"></i>Hasil Penilaian</h5>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php if (!empty($assignedQuestions)): ?>
                    <?php $no = 1;
                    foreach ($assignedQuestions as $q): ?>
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <span class="badge bg-primary me-2"><?= $no++ ?></span>
                                    <code class="badge bg-secondary"><?= esc($q->clause_code) ?></code>
                                    <p class="fw-bold mb-2 mt-2"><?= esc($q->question_text) ?></p>

                                    <?php if (!empty($q->answer)): ?>
                                        <div class="bg-light p-3 rounded mb-2">
                                            <small class="text-primary fw-bold">Jawaban Auditee:</small>
                                            <p class="mb-0 small mt-1"><?= nl2br(esc($q->answer)) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-end ms-3">
                                    <span class="badge <?= $q->score == 1 ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-2">
                                        <?= $q->score == 1 ? 'Sesuai' : 'Tidak Sesuai' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Temuan Audit -->
    <?php if (!empty($findings)): ?>
        <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #dc3545;">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Temuan & Tindak Lanjut Audit</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php $no = 1;
                    foreach ($findings as $f):
                        $riskBadge = match ($f->tingkat_risiko) {
                            'Rendah' => 'bg-success',
                            'Sedang' => 'bg-warning text-dark',
                            'Tinggi' => 'bg-danger',
                            'Kritis' => 'bg-dark',
                            default => 'bg-secondary'
                        };
                        $statusBadge = match ($f->status ?? 'Open') {
                            'Open' => 'bg-primary',
                            'In_Progress' => 'bg-info text-dark',
                            'Closed' => 'bg-success',
                            default => 'bg-secondary'
                        };
                    ?>
                        <!-- ✅ TAMBAHKAN class 'finding-item' DI SINI agar tidak terpotong -->
                        <div class="list-group-item p-4 finding-item">
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

                                <!-- Rencana Tindak Lanjut (RTL) dari Auditee -->
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

                                <!-- Info Penutupan Temuan -->
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
    <?php else: ?>
        <div class="alert alert-success border-0 shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Sempurna!</strong> Tidak ada temuan ketidaksesuaian dalam audit ini.
        </div>
    <?php endif; ?>
</div>

<script>
    function handlePrintAndLog(auditId) {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mencatat...';
        btn.disabled = true;

        fetch('/admin/completed-audits/log-export/' + auditId)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.print();
                } else {
                    alert('Gagal mencatat log. Silakan coba lagi.');
                }
            })
            .catch(error => {
                console.error('Error logging:', error);
                window.print();
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }
</script>

<?= $this->endSection() ?>