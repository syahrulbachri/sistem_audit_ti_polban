<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div>
    <h4 class="mb-0"><?= esc((string) $page_title) ?></h4>
    <small class="text-muted"><?= esc((string) $page_subtitle) ?></small>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4">
    <!-- Info Audit Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h3 class="fw-bold mb-2" style="color: var(--primary-navy, #273272);">
                        <?= esc((string) ($audit->title ?? '-')) ?>
                    </h3>
                    <span class="badge bg-primary rounded-pill">
                        <?= esc((string) ($audit->framework ?? '-')) ?>
                    </span>
                </div>
                <?php
                $statusClass = match ($audit->status ?? '') {
                    'menunggu_jawaban' => 'bg-info text-dark',
                    'aktif' => 'bg-primary',
                    'menunggu_penilaian' => 'bg-warning text-dark',
                    'revisi' => 'bg-danger',
                    'selesai' => 'bg-success',
                    default => 'bg-secondary'
                };
                ?>
                <span class="badge <?= $statusClass ?> rounded-pill fs-6 px-3 py-2">
                    <?= ucfirst(str_replace('_', ' ', $audit->status ?? '')) ?>
                </span>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <small class="text-muted d-block">Periode</small>
                    <strong><?= esc((string) ($audit->nama_periode ?? '-')) ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Auditee</small>
                    <strong><i class="bi bi-building me-1"></i><?= esc((string) ($audit->auditee_name ?? '-')) ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Auditor</small>
                    <strong><i class="bi bi-person-badge me-1"></i><?= esc((string) ($audit->auditor_name ?? '-')) ?></strong>
                </div>
                <div class="col-md-3">
                    <small class="text-muted d-block">Deadline</small>
                    <strong><i class="bi bi-calendar-event me-1"></i><?= date('d M Y', strtotime($audit->deadline ?? 'now')) ?></strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-3" id="detailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#ringkasan" type="button" role="tab">
                <i class="bi bi-file-earmark-text me-1"></i>Ringkasan & Penilaian
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="temuan-tab" data-bs-toggle="tab" data-bs-target="#temuan" type="button" role="tab">
                <i class="bi bi-exclamation-triangle me-1"></i>Temuan & Tindak Lanjut
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat" type="button" role="tab">
                <i class="bi bi-clock-history me-1"></i>Riwayat
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="detailTabsContent">

        <!-- TAB 1: Ringkasan & Penilaian -->
        <div class="tab-pane fade show active" id="ringkasan" role="tabpanel">
            <!-- Daftar Pertanyaan -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-check me-2"></i>Daftar Pertanyaan untuk Audit Ini</h6>
                    <small class="text-muted">Total: <?= (int) $totalQuestions ?> pertanyaan</small>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if (!empty($assignedQuestions)): ?>
                            <?php foreach ($assignedQuestions as $q): ?>
                                <div class="list-group-item d-flex align-items-start gap-3 py-3">
                                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                    <div class="flex-grow-1">
                                        <span class="badge bg-secondary me-2"><?= esc((string) ($q->clause_code ?? '-')) ?></span>
                                        <span><?= esc((string) ($q->question_text ?? '-')) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada pertanyaan yang di-assign untuk audit ini.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Skor Akhir Audit -->
            <div class="card border-0 shadow-sm mb-4" style="background: var(--primary-navy, #273272); color: white;">
                <div class="card-body text-center py-4">
                    <h6 class="text-uppercase mb-2" style="letter-spacing: 1px;">Skor Akhir Audit</h6>
                    <h1 class="display-3 fw-bold mb-2"><?= esc((string) number_format((float) $finalScorePercent, 0)) ?>%</h1>
                    <p class="mb-0 opacity-75">Tingkat Kepatuhan (Compliance Rate) - Skala 0-100%</p>
                </div>
            </div>

            <!-- Rincian Penilaian per Pertanyaan -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-list-ol me-2"></i>Rincian Penilaian per Pertanyaan</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($assignedQuestions)): ?>
                        <?php $no = 1;
                        foreach ($assignedQuestions as $q):
                            $isSesuai = ($q->score !== null && (float) $q->score > 0);
                            $statusBadge = $isSesuai
                                ? '<span class="badge bg-success rounded-pill"><i class="bi bi-check-circle me-1"></i>Sesuai</span>'
                                : '<span class="badge bg-danger rounded-pill"><i class="bi bi-x-circle me-1"></i>Tidak Sesuai</span>';
                        ?>
                            <div class="border-bottom p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <span class="badge bg-primary me-2"><?= (int) $no++ ?></span>
                                        <span class="badge bg-secondary me-2"><?= esc((string) ($q->clause_code ?? '-')) ?></span>
                                    </div>
                                    <?= $statusBadge ?>
                                </div>
                                <h6 class="fw-bold mb-3"><?= esc((string) ($q->question_text ?? '-')) ?></h6>

                                <div class="bg-light p-3 rounded">
                                    <small class="text-primary fw-bold"><i class="bi bi-reply me-1"></i>Jawaban Auditee:</small>
                                    <p class="mb-0 mt-1"><?= esc((string) ($q->answer ?? '(Belum dijawab)')) ?></p>
                                </div>

                                <?php if (!empty($q->evidence_filename)): ?>
                                    <div class="mt-2">
                                        <small class="text-muted"><i class="bi bi-paperclip me-1"></i>Bukti: <?= esc((string) $q->evidence_filename) ?></small>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($q->note)): ?>
                                    <div class="mt-2">
                                        <small class="text-muted"><i class="bi bi-chat-left-text me-1"></i>Catatan: <?= esc((string) $q->note) ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            Belum ada penilaian untuk audit ini.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB 2: Temuan & Tindak Lanjut -->
        <div class="tab-pane fade" id="temuan" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Daftar Temuan & Tindak Lanjut</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($findings)): ?>
                        <?php $no = 1;
                        foreach ($findings as $f):
                            $risikoClass = match (strtolower($f->tingkat_risiko ?? '')) {
                                'kritis' => 'bg-danger',
                                'tinggi' => 'bg-danger',
                                'sedang' => 'bg-warning text-dark',
                                'rendah' => 'bg-success',
                                default => 'bg-secondary'
                            };
                            $statusClass = match (strtolower($f->status ?? '')) {
                                'open' => 'bg-danger',
                                'in_progress' => 'bg-info text-dark',
                                'closed' => 'bg-success',
                                default => 'bg-secondary'
                            };
                        ?>
                            <div class="border-bottom p-4">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <span class="badge bg-primary me-2"><?= (int) $no++ ?></span>
                                        <span class="badge bg-secondary me-2"><?= esc((string) ($f->clause_code ?? '-')) ?></span>
                                        <span class="badge <?= $risikoClass ?> rounded-pill"><?= ucfirst((string) ($f->tingkat_risiko ?? '-')) ?></span>
                                    </div>
                                    <span class="badge <?= $statusClass ?> rounded-pill">
                                        Status: <?= ucfirst(str_replace('_', ' ', $f->status ?? '')) ?>
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <strong class="text-dark">Deskripsi Temuan:</strong>
                                    <p class="mb-0 mt-1"><?= nl2br(esc((string) ($f->deskripsi_temuan ?? '-'))) ?></p>
                                </div>

                                <div class="mb-3">
                                    <strong class="text-primary"><i class="bi bi-lightbulb me-1"></i>Rekomendasi Auditor:</strong>
                                    <p class="mb-0 mt-1"><?= esc((string) ($f->rekomendasi ?? '-')) ?></p>
                                </div>

                                <div class="bg-light p-3 rounded">
                                    <strong class="text-success"><i class="bi bi-arrow-repeat me-1"></i>Rencana Tindak Lanjut (Auditee):</strong>
                                    <?php if (!empty($f->rtl)): ?>
                                        <p class="mb-0 mt-1"><?= esc((string) $f->rtl) ?></p>
                                    <?php else: ?>
                                        <p class="mb-0 mt-1 text-muted fst-italic">Auditee belum membuat Rencana Tindak Lanjut.</p>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($f->bukti_perbaikan)): ?>
                                    <div class="mt-3">
                                        <strong class="text-success"><i class="bi bi-check2-circle me-1"></i>Bukti Perbaikan:</strong>
                                        <p class="mb-0 mt-1"><?= esc((string) $f->bukti_perbaikan) ?></p>
                                    </div>
                                <?php endif; ?>

                                <!-- Tombol Aksi Monitoring Temuan -->
                                <div class="mt-3 text-end">
                                    <a href="/pimpinan/rtl/detail/<?= (int) $f->id ?>"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Lihat Detail Monitoring Temuan">
                                        <i class="bi bi-eye me-1"></i>Lihat Detail Monitoring
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-5 text-center text-muted">
                            <i class="bi bi-check-circle fs-1 d-block mb-2 text-success"></i>
                            <h5>Tidak Ada Temuan</h5>
                            <p class="mb-0">Audit ini tidak memiliki temuan ketidaksesuaian.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- TAB 3: Riwayat -->
        <div class="tab-pane fade" id="riwayat" role="tabpanel">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-clock-history me-2"></i>Riwayat Audit</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Aktivitas</th>
                                    <th>Oleh</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?= date('d M Y', strtotime($audit->created_at ?? 'now')) ?></td>
                                    <td>Audit Dibuat</td>
                                    <td><?= esc((string) ($audit->auditor_name ?? '-')) ?></td>
                                    <td>Rencana audit dibuat untuk periode <?= esc((string) ($audit->nama_periode ?? '-')) ?></td>
                                </tr>
                                <?php if ($audit->updated_at): ?>
                                    <tr>
                                        <td><?= date('d M Y', strtotime($audit->updated_at)) ?></td>
                                        <td>Audit Diperbarui</td>
                                        <td><?= esc((string) ($audit->auditor_name ?? '-')) ?></td>
                                        <td>Status terakhir: <?= ucfirst(str_replace('_', ' ', $audit->status ?? '')) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>