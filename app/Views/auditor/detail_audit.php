<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .question-item {
        transition: all 0.2s;
        border-left: 3px solid transparent;
    }

    .question-item:hover {
        background-color: #f8f9fa;
    }

    .question-item.selected {
        background-color: #e7f3ff;
        border-left-color: #0d6efd;
    }

    .sticky-header {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        padding: 15px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .badge-navy {
        background-color: var(--primary-navy) !important;
        color: white !important;
    }

    /* =========================================
   PENGATURAN CETAK LAPORAN
   ========================================= */
    @media print {

        /* =====================================
       1. SEMBUNYIKAN ELEMEN NON-CETAK
       ===================================== */
        #actionButtons,
        .no-print,
        footer,
        .sidebar,
        .top-header,
        nav {
            display: none !important;
        }


        /* =====================================
       2. UKURAN KERTAS
       ===================================== */
        @page {
            size: A4 portrait;
            margin: 15mm;
        }


        /* =====================================
       3. RESET BODY / CONTAINER
       ===================================== */
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


        /* =====================================
       4. HEADER STICKY JANGAN STICKY SAAT PRINT
       ===================================== */
        .sticky-header {
            position: static !important;
            top: auto !important;
            z-index: auto !important;
            background: #fff !important;
            padding: 10px 0 !important;
        }


        /* =====================================
       5. HILANGKAN EFEK CARD
       ===================================== */
        .card {
            box-shadow: none !important;
        }


        /* =====================================
       6. PENTING:
          LIST-GROUP JANGAN FLEX SAAT PRINT
       ===================================== */
        .list-group {
            display: block !important;
        }

        .list-group-flush {
            display: block !important;
        }

        .list-group-item {
            display: block !important;
        }


        /* =====================================
       7. PERTANYAAN JANGAN DIPOTONG
       ===================================== */
        .question-item,
        .list-group-item {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }


        /* =====================================
       8. ISI PERTANYAAN JANGAN DIPOTONG
       ===================================== */
        .question-item>div,
        .list-group-item>div {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }


        /* =====================================
       9. TEMUAN JANGAN DIPOTONG
       ===================================== */

        /* Setiap temuan harus dianggap 1 blok */
        .finding-item {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        /* Karena finding kamu sebenarnya menggunakan
       .list-group-item */
        .list-group .list-group-item {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }


        /* =====================================
       10. BAGIAN RTL JANGAN DIPOTONG
       ===================================== */
        .bg-light.p-3.rounded.border {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }


        /* =====================================
       11. JANGAN MEMAKSA CARD-BODY UTUH
       ===================================== */

        .card-body {
            break-inside: auto !important;
            page-break-inside: auto !important;
        }


        /* =====================================
       12. HEADER TEMUAN + ISINYA
       ===================================== */
        .card-header {
            break-after: avoid !important;
            page-break-after: avoid !important;
        }


        /* =====================================
       13. JANGAN BUAT BARIS TEKS TERLALU
           MUDAH TERPISAH
       ===================================== */
        p,
        h6,
        strong {
            orphans: 3;
            widows: 3;
        }


        /* =====================================
       14. MARGIN SUPAYA LEBIH RAPI
       ===================================== */
        .mb-4 {
            margin-bottom: 15px !important;
        }


        /* =====================================
       15. WARNA TETAP DICETAK
       ===================================== */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Info Audit -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
            <div>
                <h5 class="fw-bold mb-1" style="color: var(--primary-navy);"><?= esc($audit->title) ?></h5>
                <span class="badge bg-info text-dark rounded-pill"><?= esc($audit->framework) ?></span>
            </div>
            <span class="badge rounded-pill px-3 py-2 fs-6 
    <?= match ($audit->status) {
        'menunggu_jawaban' => 'bg-info text-dark',
        'aktif' => 'badge-navy',
        'menunggu_penilaian' => 'bg-warning text-dark',
        'selesai' => 'bg-success',
        default => 'bg-secondary'
    } ?>">
                <?= ucfirst(str_replace('_', ' ', $audit->status)) ?>
            </span>
        </div>

        <div class="row g-3">
            <div class="col-md-6"><small class="text-muted d-block">Periode</small><strong><?= esc($audit->nama_periode ?? '-') ?></strong></div>
            <div class="col-md-6"><small class="text-muted d-block">Auditee</small><strong><i class="bi bi-building me-1"></i><?= esc($audit->auditee_name ?? '-') ?></strong></div>
            <div class="col-md-6"><small class="text-muted d-block">Auditor</small><strong><i class="bi bi-person-check me-1"></i><?= esc($audit->auditor_name ?? '-') ?></strong></div>
            <div class="col-md-6"><small class="text-muted d-block">Deadline</small><strong><i class="bi bi-calendar-event me-1"></i><?= date('d M Y', strtotime($audit->deadline)) ?></strong></div>
        </div>
    </div>
</div>

<!-- Panel Daftar/Pilih Pertanyaan -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">

    <!-- Header Panel -->
    <div class="sticky-header px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="fw-bold mb-1">
                    <i class="bi bi-list-check me-2"></i>
                    <?= ($audit->status === 'aktif') ? 'Konfigurasi Pertanyaan Audit' : 'Daftar Pertanyaan untuk Audit Ini' ?>
                </h6>
                <small class="text-muted">
                    <?php if ($audit->status === 'aktif'): ?>
                        Total Dipilih: <strong id="selectedCount"><?= $selectedCount ?></strong> pertanyaan
                    <?php else: ?>
                        Total: <strong><?= count($assignedQuestions) ?></strong> pertanyaan
                    <?php endif; ?>
                </small>
            </div>

            <?php if ($audit->status === 'aktif'): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAll(true)">
                        <i class="bi bi-check-all me-1"></i>Pilih Semua Standar
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAll(false)">
                        <i class="bi bi-x-lg me-1"></i>Hapus Semua Standar
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body p-0">

        <!-- ========================================== -->
        <!-- KONDISI 1: STATUS AKTIF                    -->
        <!-- ========================================== -->
        <?php if ($audit->status === 'aktif'): ?>

            <?php if (isset($isLocked) && $isLocked): ?>
                <!-- === TAMPILAN TERKUNCI (READ-ONLY) === -->
                <div class="alert alert-info border-0 shadow-sm mb-3">
                    <i class="bi bi-lock-fill me-2"></i>
                    <strong>Pertanyaan Telah Dikunci.</strong>
                    Daftar pertanyaan ini sudah disimpan dan dikirim ke auditee, sehingga tidak dapat diubah lagi.
                </div>

                <div class="list-group list-group-flush">
                    <?php foreach ($lockedQuestions as $q): ?>
                        <!-- Gunakan class 'selected' agar berwarna biru sesuai CSS Anda -->
                        <div class="list-group-item question-item selected" style="cursor: default;">
                            <div class="d-flex align-items-start gap-3">
                                <div class="mt-1">
                                    <i class="bi bi-check-circle-fill text-primary fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <?php if ($q->is_standard == 0): ?>
                                        <span class="badge bg-warning text-dark rounded-pill mb-1 me-2">
                                            <i class="bi bi-star-fill me-1"></i>Custom
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-primary rounded-pill mb-1 me-2">Standar</span>
                                    <?php endif; ?>

                                    <code class="badge bg-secondary mb-1"><?= esc($q->clause_code) ?></code>
                                    <p class="mb-0 fw-medium"><?= esc($q->question_text) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card-footer bg-white p-4 text-end border-top">
                    <a href="/dashboard" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
                    </a> <!-- ✅ DIPERBAIKI: Ditambahkan tanda < di depan /a -->
                </div>

            <?php else: ?>
                <!-- === TAMPILAN FORM PILIH PERTANYAAN (BELUM PERNAH DISIMPAN) === -->
                <form action="/audit/save-assignments/<?= $audit->id ?>" method="post" id="formAssign">
                    <?= csrf_field() ?>

                    <!-- SECTION 1: PERTANYAAN CUSTOM -->
                    <?php if (!empty($customQuestions)): ?>
                        <div class="list-group list-group-flush border-bottom">
                            <div class="list-group-item bg-warning bg-opacity-10 py-3">
                                <h6 class="fw-bold mb-1 text-warning">
                                    <i class="bi bi-star-fill me-2"></i>Pertanyaan Custom dari Admin
                                </h6>
                            </div>
                            <?php foreach ($customQuestions as $q): ?>
                                <label class="list-group-item question-item selected" style="cursor: default; background-color: #fffbeb;">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" checked disabled>
                                            <input type="hidden" name="question_ids[]" value="<?= $q->id ?>">
                                        </div>
                                        <div class="flex-grow-1">
                                            <code class="badge bg-secondary mb-1"><?= esc($q->clause_code) ?></code>
                                            <p class="mb-0"><?= esc($q->question_text) ?></p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- SECTION 2: PILIH PERTANYAAN STANDAR -->
                    <div class="list-group list-group-flush">
                        <div class="list-group-item bg-light py-3">
                            <h6 class="fw-bold mb-1">Pilih Pertanyaan Standar (<?= esc($audit->framework) ?>)</h6>
                            <small class="text-muted">Total Dipilih: <strong id="selectedCount"><?= $selectedCount ?></strong></small>
                        </div>
                        <?php if (!empty($standardQuestions)): ?>
                            <?php foreach ($standardQuestions as $q): ?>
                                <label class="list-group-item question-item" for="q-<?= $q->id ?>" style="cursor: pointer;">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input question-checkbox" type="checkbox" name="question_ids[]" value="<?= $q->id ?>" id="q-<?= $q->id ?>" onchange="updateCount()">
                                        </div>
                                        <div class="flex-grow-1">
                                            <code class="badge bg-secondary mb-1"><?= esc($q->clause_code) ?></code>
                                            <p class="mb-0"><?= esc($q->question_text) ?></p>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-check-circle me-2"></i>Semua pertanyaan standar sudah dipilih atau tidak ada template untuk framework ini.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ✅ DIPERBAIKI: Ditambahkan penutup form dan tombol simpan -->
                    <div class="card-footer bg-white p-4 text-end border-top">
                        <a href="/dashboard" class="btn btn-outline-secondary me-2"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan & Lanjutkan</button>
                    </div>
                </form> <!-- ✅ Ditambahkan penutup form -->
            <?php endif; ?>

        <?php else: ?>
            <!-- ========================================== -->
            <!-- KONDISI 2: MENUNGGU PENILAIAN / SELESAI    -->
            <!-- ========================================== -->
            <!-- (Kode untuk status menunggu/selesai tetap di sini) -->
            <div class="list-group list-group-flush">
                <?php if (!empty($assignedQuestions)): ?>
                    <?php foreach ($assignedQuestions as $q): ?>
                        <div class="list-group-item">
                            <div class="d-flex align-items-start gap-3">
                                <div class="mt-1">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <!-- Badge Custom -->
                                    <?php if (isset($q->is_standard) && $q->is_standard == 0): ?>
                                        <span class="badge bg-warning text-dark rounded-pill mb-1 me-2" title="Pertanyaan Custom dari Admin">
                                            <i class="bi bi-star-fill me-1"></i>Custom
                                        </span>
                                    <?php endif; ?>

                                    <code class="badge bg-secondary mb-1"><?= esc($q->clause_code) ?></code>
                                    <p class="mb-0"><?= esc($q->question_text) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">Tidak ada pertanyaan yang di-assign.</div>
                <?php endif; ?>
            </div>

            <div class="card-footer bg-white p-4 text-center border-top">
                <?php if ($audit->status === 'menunggu_penilaian'): ?>
                    <a href="/audit/nilai/<?= $audit->id ?>" class="btn btn-warning text-dark px-4 fw-bold">
                        <i class="bi bi-star me-2"></i>Nilai Sekarang
                    </a>
                <?php elseif ($audit->status === 'selesai'): ?>

                    <!-- 1. Kartu Skor Akhir -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: linear-gradient(135deg, var(--primary-navy) 0%, #1e3a8a 100%);">
                        <div class="card-body p-4 text-white text-center">
                            <h6 class="text-uppercase opacity-75 mb-2">Skor Akhir Audit</h6>
                            <h1 class="display-4 fw-bold mb-2">
                                <?php if ($isBinary): ?>
                                    <?= round($audit->final_score ?? 0) ?>%
                                <?php else: ?>
                                    <?= number_format($audit->final_score ?? 0, 2) ?>
                                <?php endif; ?>
                            </h1>
                            <p class="mb-0 opacity-75">
                                <?php if ($isBinary): ?>
                                    Tingkat Kepatuhan (Compliance Rate) - Skala 0-100%
                                <?php else: ?>
                                    Rata-rata Tingkat Kematangan (Maturity Level) - Skala 0-<?= $maxScore ?>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <!-- 2. Rincian Penilaian per Pertanyaan -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white py-4 border-bottom">
                            <h4 class="fw-bold mb-0 text-start"><i class="bi bi-list-check me-2"></i>Rincian Penilaian per Pertanyaan</h4>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (!empty($assignedQuestions)): ?>
                                <?php $no = 1;
                                foreach ($assignedQuestions as $q): ?>
                                    <div class="list-group-item py-4">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div class="flex-grow-1 text-start">
                                                <span class="badge bg-primary me-2"><?= $no ?></span>

                                                <?php if (isset($q->is_standard) && $q->is_standard == 0): ?>
                                                    <span class="badge bg-warning text-dark rounded-pill me-1" title="Pertanyaan Custom dari Admin">
                                                        <i class="bi bi-star-fill me-1"></i>Custom
                                                    </span>
                                                <?php endif; ?>

                                                <code class="badge bg-secondary"><?= esc($q->clause_code) ?></code>
                                                <h6 class="fw-bold mb-2 mt-2"><?= esc($q->question_text) ?></h6>

                                                <div class="bg-light p-3 rounded mb-2">
                                                    <strong class="text-primary small"><i class="bi bi-reply me-1"></i>Jawaban Auditee:</strong>
                                                    <p class="mb-0 small mt-1"><?= nl2br(esc($q->answer ?? 'Tidak ada jawaban')) ?></p>
                                                </div>

                                                <?php if (!empty($q->note)): ?>
                                                    <small class="text-muted fst-italic"><i class="bi bi-chat-left-text me-1"></i>Catatan Auditor: <?= esc($q->note) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <?php if ($isBinary): ?>
                                                    <?php if ($q->score == 1): ?>
                                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                                            <i class="bi bi-check-circle me-1"></i>Sesuai
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger rounded-pill px-3 py-2">
                                                            <i class="bi bi-x-circle me-1"></i>Tidak Sesuai
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-info text-dark rounded-pill px-3 py-2">
                                                        Level <?= number_format($q->score ?? 0, 2) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php $no++;
                                endforeach; ?>
                            <?php else: ?>
                                <div class="p-4 text-center text-muted">Tidak ada data pertanyaan.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 3. Daftar Temuan & Status RTL (Read-Only) -->
                    <?php if (!empty($findings)): ?>
                        <div class="card border-0 shadow-sm" style="border-radius: 12px; border-left: 5px solid #dc3545;">
                            <div class="card-header bg-white py-4 border-bottom">
                                <h4 class="fw-bold mb-0 text-start text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Daftar Temuan & Tindak Lanjut</h4>
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
                                        $statusBadge = match ($f->status) {
                                            'Open' => 'bg-primary',
                                            'In_Progress' => 'bg-info text-dark',
                                            'Closed' => 'bg-success',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                        <div class="list-group-item p-4 finding-item">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="text-start">
                                                    <span class="badge bg-primary me-2"><?= $no ?></span>
                                                    <span class="badge bg-secondary me-2"><?= esc($f->clause_code ?? 'Umum') ?></span>
                                                    <span class="badge <?= $riskBadge ?> rounded-pill"><?= esc($f->tingkat_risiko) ?></span>
                                                </div>
                                                <span class="badge <?= $statusBadge ?> rounded-pill px-3 py-2">
                                                    Status: <?= str_replace('_', ' ', ucfirst($f->status)) ?>
                                                </span>
                                            </div>

                                            <div class="text-start">
                                                <h6 class="fw-bold mb-2">Deskripsi Temuan:</h6>
                                                <p class="text-muted mb-3"><?= nl2br(esc($f->deskripsi_temuan)) ?></p>

                                                <h6 class="fw-bold mb-2 text-primary">Rekomendasi Auditor:</h6>
                                                <p class="mb-3"><?= nl2br(esc($f->rekomendasi)) ?></p>

                                                <div class="bg-light p-3 rounded border">
                                                    <h6 class="fw-bold mb-2 text-success"><i class="bi bi-arrow-repeat me-1"></i>Rencana Tindak Lanjut (Auditee):</h6>
                                                    <?php if (!empty($f->rtl_description)): ?>
                                                        <p class="mb-2 small"><strong>Rencana:</strong> <?= nl2br(esc($f->rtl_description)) ?></p>
                                                        <?php if (!empty($f->rtl_deadline)): ?>
                                                            <p class="mb-0 small"><strong>Target Selesai:</strong> <?= date('d M Y', strtotime($f->rtl_deadline)) ?></p>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <p class="mb-0 text-muted small fst-italic">Auditee belum membuat Rencana Tindak Lanjut.</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php $no++;
                                    endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success border-0 shadow-sm">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Sempurna!</strong> Tidak ada temuan ketidaksesuaian dalam audit ini.
                        </div>
                    <?php endif; ?>

                    <!-- Tombol Aksi (Hanya Navigasi & Cetak) -->
                    <div class="card-footer bg-white p-4 text-center border-0 no-print" id="actionButtons">

                        <a href="/dashboard" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i>
                            Kembali ke Dashboard
                        </a>

                        <button onclick="window.print()" class="btn btn-primary px-4 ms-2">
                            <i class="bi bi-printer me-1"></i>
                            Cetak Laporan
                        </button>

                    </div>

                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
    // Script khusus untuk mode 'Aktif'
    function updateCount() {
        // Hitung custom questions (yang hidden input-nya selalu ada)
        const customCount = document.querySelectorAll('input[type="hidden"][name="question_ids[]"]').length;
        // Hitung standard questions yang dicentang
        const standardCheckboxes = document.querySelectorAll('.question-checkbox:checked');
        const items = document.querySelectorAll('.question-item');

        const totalSelected = customCount + standardCheckboxes.length;

        // Update teks counter jika elemennya ada
        const countElement = document.getElementById('selectedCount');
        if (countElement) {
            countElement.textContent = totalSelected;
        }

        items.forEach(item => {
            const checkbox = item.querySelector('.question-checkbox');
            if (checkbox && checkbox.checked) {
                item.classList.add('selected');
            } else if (!checkbox) {
                // Custom questions tidak punya class .question-checkbox, tapi selalu selected
                item.classList.add('selected');
            } else {
                item.classList.remove('selected');
            }
        });
    }

    function toggleAll(state) {
        document.querySelectorAll('.question-checkbox').forEach(cb => cb.checked = state);
        updateCount();
    }

    // Event listener saat form disubmit (TANPA POPUP)
    document.getElementById('formAssign')?.addEventListener('submit', function(e) {
        const customCount = document.querySelectorAll('input[type="hidden"][name="question_ids[]"]').length;
        const standardChecked = document.querySelectorAll('.question-checkbox:checked');
        const totalChecked = customCount + standardChecked.length;

        // Jika tidak ada yang dipilih, cegah submit (tanpa alert)
        if (totalChecked === 0) {
            e.preventDefault();
            return false;
        }

        // Langsung izinkan submit tanpa confirm()
    });

    // Jalankan saat halaman dimuat untuk menghitung awal
    document.addEventListener('DOMContentLoaded', function() {
        updateCount();
    });
</script>

<?= $this->endSection() ?>