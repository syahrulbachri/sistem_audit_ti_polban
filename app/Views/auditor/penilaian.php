<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .question-card {
        border-left: 4px solid var(--primary-navy);
        transition: all 0.3s;
        margin-bottom: 20px;
    }

    .question-card.has-finding {
        border-left-color: #dc3545;
        background-color: #fff5f5;
    }

    /* Highlight khusus untuk Request Improve */
    .question-card.is-improve-request {
        border-left-color: #0dcaf0;
        background-color: #f0fdff;
    }

    .finding-box {
        display: none;
        background: #fff5f5;
        border: 1px solid #ffc107;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }

    .finding-box.show {
        display: block;
    }

    .risk-selector label {
        cursor: pointer;
        padding: 8px 12px;
        border: 2px solid #dee2e6;
        border-radius: 6px;
        margin-right: 5px;
        transition: all 0.2s;
        display: inline-block;
    }

    .risk-selector input[type="radio"]:checked+label {
        border-color: var(--primary-navy);
        background-color: var(--primary-navy);
        color: white;
    }

    .answer-box {
        background: #f8f9fa;
        border-left: 3px solid #0d6efd;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .evidence-box {
        background: #e7f3ff;
        border: 1px dashed #0d6efd;
        border-radius: 6px;
        padding: 10px;
        margin-top: 10px;
    }

    .evidence-thumbnail {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 6px;
        cursor: pointer;
        border: 2px solid #dee2e6;
        transition: all 0.2s;
    }

    .evidence-thumbnail:hover {
        border-color: #0d6efd;
        transform: scale(1.05);
    }

    .btn-make-finding.saved {
        pointer-events: none;
        opacity: 0.7;
    }

    .modal-image {
        max-width: 100%;
        max-height: 80vh;
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php
// Hitung pertanyaan yang belum dijawab auditee
$unansweredCount = 0;
foreach ($items as $item) {
    if (empty($item->answer)) {
        $unansweredCount++;
    }
}

// Cek apakah audit sudah melewati deadline
$deadline = strtotime($audit->deadline);
$today = time();
$isPastDeadline = ($today >= $deadline); // Menggunakan >= agar pas di hari H sudah dianggap lewat
?>

<?php if ($unansweredCount > 0): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Peringatan!</strong> Masih ada <strong><?= $unansweredCount ?></strong> pertanyaan yang belum dijawab oleh auditee.
        <br>Tombol "Submit Final" dinonaktifkan sampai auditee melengkapi semua jawaban.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="alert alert-info mb-4">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Info:</strong>
    <?php if ($isPastDeadline): ?>
        <span class="text-danger fw-bold">Audit sudah melewati deadline (<?= date('d M Y', $deadline) ?>).</span><br>
        Tombol "Buat Temuan" akan membuat temuan resmi ke dalam sistem. Jawaban auditee sudah terkunci (Read-Only).
    <?php else: ?>
        Audit masih dalam periode aktif (sampai <strong><?= date('d M Y', $deadline) ?></strong>).<br>
        Tombol "Minta Revisi" akan menandai jawaban yang perlu diperbaiki auditee agar nilainya meningkat.
    <?php endif; ?>
</div>

<form action="<?= site_url('audit/simpan-nilai/' . $audit->id) ?>" method="post" id="formPenilaian">
    <?= csrf_field() ?>

    <?php
    $questionNumber = 1;
    foreach ($items as $item):
        $scoreValue = ($item->score === null || $item->score === '') ? null : (float) $item->score;
        $showFindingBtn = false;
        $hasFindingClass = '';
        $btnDisabled = '';
        $btnClass = 'btn-outline-secondary';
        $btnText = $isPastDeadline ? 'Buat Temuan' : 'Minta Revisi';
        $btnIcon = $isPastDeadline ? 'bi-exclamation-triangle' : 'bi-arrow-repeat';

        // Cek apakah sudah ada temuan/revisi untuk question ini (berdasarkan needs_revision)
        $hasExistingFinding = (($item->needs_revision ?? 0) == 1);

        if ($audit->framework === 'ISO 27001') {
            if ($scoreValue === 0) {
                $showFindingBtn = true;
                $hasFindingClass = 'has-finding';
                $btnClass = $isPastDeadline ? 'btn-outline-danger' : 'btn-outline-warning';
            } else if ($scoreValue === 1) {
                $showFindingBtn = false;
                $btnDisabled = 'disabled';
            } else {
                $showFindingBtn = false;
                $btnDisabled = 'disabled';
            }
        } else if ($audit->framework === 'COBIT 2019') {
            $showFindingBtn = true;
            $btnClass = $isPastDeadline ? 'btn-outline-danger' : 'btn-outline-warning';
        }

        // Cek jika ada request improve dari auditee
        $isImproveRequest = !empty($item->is_improve_request ?? 0);
        $improveClass = $isImproveRequest ? 'is-improve-request' : '';
    ?>
        <?php $isUnanswered = empty($item->answer); ?>
        <div class="card question-card <?= $hasFindingClass ?> <?= $improveClass ?> <?= $isUnanswered ? 'border-danger' : '' ?>"
            id="question-<?= $item->assignment_id ?>"
            style="<?= $isUnanswered ? 'opacity: 0.6;' : '' ?>">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="fw-bold mb-0">
                        <span class="badge bg-primary me-2"><?= $questionNumber ?></span>
                        <code><?= esc($item->clause_code) ?></code>
                        <?php if ($isUnanswered): ?>
                            <span class="badge bg-danger ms-2">
                                <i class="bi bi-exclamation-circle me-1"></i>Belum Dijawab
                            </span>
                        <?php endif; ?>

                        <!-- BADGE REQUEST IMPROVE (BARU) -->
                        <?php if ($isImproveRequest): ?>
                            <span class="badge bg-info text-dark ms-2">
                                <i class="bi bi-arrow-up-circle me-1"></i>Request Improve
                            </span>
                        <?php endif; ?>
                    </h6>
                    <span class="badge bg-info text-dark"><?= esc($item->framework) ?></span>
                </div>
            </div>
            <div class="card-body">
                <!-- Pertanyaan -->
                <p class="fw-bold mb-3"><?= esc($item->question_text) ?></p>

                <!-- Jawaban Auditee -->
                <?php if (!empty($item->answer)): ?>
                    <div class="answer-box">
                        <strong class="text-primary"><i class="bi bi-reply me-2"></i>Jawaban Auditee:</strong>
                        <p class="mb-0 mt-2"><?= nl2br(esc($item->answer)) ?></p>

                        <!-- Bukti yang Diupload (Support Multi-File) -->
                        <?php
                        // 1. Decode JSON menjadi Array
                        $evidenceFiles = [];
                        if (!empty($item->evidence_filename)) {
                            $decoded = json_decode($item->evidence_filename, true);
                            $evidenceFiles = is_array($decoded) ? $decoded : [$item->evidence_filename]; // Fallback jika data lama single string
                        }
                        ?>

                        <?php if (!empty($evidenceFiles)): ?>
                            <div class="evidence-box">
                                <strong class="text-primary"><i class="bi bi-paperclip me-2"></i>Bukti Pendukung (<?= count($evidenceFiles) ?> File):</strong>
                                <div class="mt-2">
                                    <?php foreach ($evidenceFiles as $fileName): ?>
                                        <?php
                                        // 2. Cek ekstensi per file
                                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                        $isPDF = ($ext === 'pdf');

                                        // 3. Buat URL dengan parameter ?file= agar controller tahu file mana yang diminta
                                        $fileUrl = base_url('auditor/view-evidence/' . $item->assignment_id . '?file=' . urlencode($fileName));
                                        ?>

                                        <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-white rounded border">
                                            <?php if ($isImage): ?>
                                                <i class="bi bi-image text-primary fs-4"></i>
                                                <span class="small flex-grow-1 text-truncate" style="max-width: 200px;" title="<?= esc($fileName) ?>"><?= esc($fileName) ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="showPreviewModal('<?= $fileUrl ?>', '<?= esc($fileName) ?>', 'image')">
                                                    <i class="bi bi-eye"></i> Preview
                                                </button>
                                            <?php elseif ($isPDF): ?>
                                                <i class="bi bi-file-pdf text-danger fs-4"></i>
                                                <span class="small flex-grow-1 text-truncate" style="max-width: 200px;" title="<?= esc($fileName) ?>"><?= esc($fileName) ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="showPreviewModal('<?= $fileUrl ?>', '<?= esc($fileName) ?>', 'pdf')">
                                                    <i class="bi bi-eye"></i> Preview PDF
                                                </button>
                                            <?php else: ?>
                                                <i class="bi bi-file-earmark text-secondary fs-4"></i>
                                                <span class="small flex-grow-1 text-truncate" style="max-width: 200px;" title="<?= esc($fileName) ?>"><?= esc($fileName) ?></span>
                                                <a href="<?= $fileUrl ?>" class="btn btn-sm btn-outline-secondary" download>
                                                    <i class="bi bi-download"></i> Download
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>

                                    <?php if (!empty($item->evidence_uploaded_at)): ?>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-clock me-1"></i>Terakhir diupdate: <?= date('d M Y H:i', strtotime($item->evidence_uploaded_at)) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning alert-sm mt-2 mb-0">
                                <i class="bi bi-exclamation-circle me-1"></i> Auditee belum upload bukti
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>Auditee belum menjawab pertanyaan ini.
                    </div>
                <?php endif; ?>

                <!-- Input Nilai -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <?php
                        $isBinaryFramework = false;
                        if (!empty($frameworkOptions)) {
                            $isBinaryFramework = (count($frameworkOptions) === 2);
                        }
                        ?>

                        <label class="form-label fw-bold">
                            <?php if ($isBinaryFramework): ?>
                                Penilaian <span class="text-danger">*</span>
                            <?php else: ?>
                                Tingkat Kematangan <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($isBinaryFramework): ?>
                            <div class="d-flex gap-2">
                                <?php
                                krsort($frameworkOptions);
                                foreach ($frameworkOptions as $scoreVal => $opt):
                                ?>
                                    <div class="form-check flex-fill">
                                        <input class="form-check-input score-input"
                                            type="radio"
                                            name="scores[<?= $item->assignment_id ?>]"
                                            value="<?= $scoreVal ?>"
                                            id="score-<?= $scoreVal ?>-<?= $item->assignment_id ?>"
                                            <?= ($scoreValue !== null && $scoreValue == $scoreVal) ? 'checked' : '' ?>
                                            onchange="handleScoreChange(<?= $item->assignment_id ?>, <?= $scoreVal ?>)">
                                        <label class="form-check-label w-100 btn <?= ($scoreValue !== null && $scoreValue == $scoreVal) ? ($scoreVal == 1 ? 'btn-success' : 'btn-danger') : ($scoreVal == 1 ? 'btn-outline-success' : 'btn-outline-danger') ?>"
                                            for="score-<?= $scoreVal ?>-<?= $item->assignment_id ?>" style="cursor: pointer;">
                                            <?= $scoreVal == 1 ? '✅' : '' ?> <?= esc($opt->label) ?> (<?= $scoreVal ?>)
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <select name="scores[<?= $item->assignment_id ?>]" class="form-select score-input" onchange="handleScoreChange(<?= $item->assignment_id ?>, this.value)">
                                <option value="">-- Pilih Level --</option>
                                <?php foreach ($frameworkOptions as $scoreVal => $opt): ?>
                                    <option value="<?= $scoreVal ?>" <?= $scoreValue == $scoreVal ? 'selected' : '' ?>>
                                        Level <?= $scoreVal ?>: <?= esc($opt->label) ?>
                                        <?php if (!empty($opt->description)): ?> - <?= esc($opt->description) ?><?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Catatan Auditor -->
                <div class="mb-3">
                    <label class="form-label small">
                        <?= $isPastDeadline ? 'Catatan Temuan' : 'Catatan Revisi untuk Auditee' ?> (Opsional)
                    </label>
                    <textarea name="notes[<?= $item->assignment_id ?>]" class="form-control form-control-sm" rows="2"><?= esc($item->note ?? '') ?></textarea>
                </div>

                <!-- Tombol Buat Temuan/Revisi -->
                <div class="finding-section" data-assignment-id="<?= $item->assignment_id ?>">
                    <?php if ($hasExistingFinding): ?>
                        <button type="button" class="btn btn-sm btn-success" disabled>
                            <i class="bi bi-check-circle me-2"></i><?= $isPastDeadline ? 'Temuan' : 'Revisi' ?> Sudah Dicatat
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-sm <?= $btnClass ?> btn-make-finding"
                            id="btn-finding-<?= $item->assignment_id ?>"
                            onclick="toggleFindingForm(<?= $item->assignment_id ?>)"
                            style="<?= $showFindingBtn ? 'display: inline-block;' : 'display: none;' ?>"
                            <?= $btnDisabled ?>>
                            <i class="bi <?= $btnIcon ?> me-2"></i><?= $btnText ?>
                        </button>
                    <?php endif; ?>

                    <!-- Form Temuan/Revisi Inline -->
                    <?php if (!$hasExistingFinding): ?>
                        <div class="finding-box" id="finding-form-<?= $item->assignment_id ?>">
                            <h6 class="fw-bold text-danger mb-3">
                                <i class="bi <?= $btnIcon ?> me-2"></i>Form <?= $isPastDeadline ? 'Temuan Audit' : 'Catatan Revisi' ?>
                            </h6>

                            <input type="hidden" name="findings[<?= $item->assignment_id ?>][question_id]" value="<?= $item->question_id ?>">

                            <?php if ($isPastDeadline): ?>
                                <!-- FORM TEMUAN (Setelah Deadline) - Ada Tingkat Risiko -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Tingkat Risiko <span class="text-danger">*</span></label>
                                    <div class="risk-selector">
                                        <input type="radio" name="findings[<?= $item->assignment_id ?>][tingkat_risiko]" value="Rendah"
                                            id="risk-rendah-<?= $item->assignment_id ?>" class="d-none">
                                        <label for="risk-rendah-<?= $item->assignment_id ?>" class="badge bg-success">🟢 Rendah</label>

                                        <input type="radio" name="findings[<?= $item->assignment_id ?>][tingkat_risiko]" value="Sedang"
                                            id="risk-sedang-<?= $item->assignment_id ?>" class="d-none" checked>
                                        <label for="risk-sedang-<?= $item->assignment_id ?>" class="badge bg-warning text-dark">🟡 Sedang</label>

                                        <input type="radio" name="findings[<?= $item->assignment_id ?>][tingkat_risiko]" value="Tinggi"
                                            id="risk-tinggi-<?= $item->assignment_id ?>" class="d-none">
                                        <label for="risk-tinggi-<?= $item->assignment_id ?>" class="badge bg-danger">🔴 Tinggi</label>

                                        <input type="radio" name="findings[<?= $item->assignment_id ?>][tingkat_risiko]" value="Kritis"
                                            id="risk-kritis-<?= $item->assignment_id ?>" class="d-none">
                                        <label for="risk-kritis-<?= $item->assignment_id ?>" class="badge bg-dark">⚫ Kritis</label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Deskripsi Temuan <span class="text-danger">*</span></label>
                                    <textarea name="findings[<?= $item->assignment_id ?>][deskripsi_temuan]" class="form-control form-control-sm" rows="3"
                                        placeholder="Jelaskan ketidaksesuaian yang ditemukan..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Rekomendasi Perbaikan <span class="text-danger">*</span></label>
                                    <textarea name="findings[<?= $item->assignment_id ?>][rekomendasi]" class="form-control form-control-sm" rows="2"
                                        placeholder="Berikan rekomendasi perbaikan..."></textarea>
                                </div>
                            <?php else: ?>
                                <!-- FORM REVISI (Sebelum Deadline) - TANPA Tingkat Risiko -->
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Alasan Perlu Revisi <span class="text-danger">*</span></label>
                                    <textarea name="findings[<?= $item->assignment_id ?>][deskripsi_temuan]" class="form-control form-control-sm" rows="3"
                                        placeholder="Jelaskan mengapa jawaban ini perlu diperbaiki..."></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Saran Perbaikan <span class="text-danger">*</span></label>
                                    <textarea name="findings[<?= $item->assignment_id ?>][rekomendasi]" class="form-control form-control-sm" rows="2"
                                        placeholder="Berikan saran untuk perbaikan..."></textarea>
                                </div>
                            <?php endif; ?>

                            <button type="button" class="btn btn-sm btn-danger" onclick="markFindingAsSaved(<?= $item->assignment_id ?>)">
                                <i class="bi bi-save me-2"></i>Simpan <?= $isPastDeadline ? 'Temuan' : 'Revisi' ?>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleFindingForm(<?= $item->assignment_id ?>)">
                                Batal
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success py-2 px-3 mt-3 mb-0 small">
                            <i class="bi bi-check-circle me-1"></i>
                            <strong><?= $isPastDeadline ? 'Temuan' : 'Revisi' ?> sudah dicatat.</strong>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    <?php $questionNumber++;
    endforeach; ?>

    <!-- Tombol Submit -->
    <div class="card mb-4">
        <div class="card-body text-center p-4">
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <button type="submit" name="submit_action" value="draft" class="btn btn-warning btn-lg px-4" id="btnDraft">
                    <i class="bi bi-save me-2"></i>Simpan Draft
                </button>
                <?php if ($isPastDeadline): ?>
                    <button type="submit" name="submit_action" value="final" class="btn btn-success btn-lg px-4" id="btnFinal" <?= $unansweredCount > 0 ? 'disabled' : '' ?>>
                        <i class="bi bi-check-circle me-2"></i>Finalisasi Audit & Buat Temuan
                    </button>
                <?php else: ?>
                    <button type="submit" name="submit_action" value="final" class="btn btn-success btn-lg px-4" id="btnFinal" <?= $unansweredCount > 0 ? 'disabled' : '' ?>>
                        <i class="bi bi-check-circle me-2"></i>Submit Penilaian Sementara
                    </button>
                <?php endif; ?>
            </div>
            <p class="text-muted small mt-3 mb-0">
                <strong>Draft:</strong> Progress tersimpan, status audit tetap "Menunggu Penilaian"<br>
                <?php if ($isPastDeadline): ?>
                    <strong class="text-danger">Final:</strong> Audit ditutup, nilai dikunci, temuan resmi dibuat
                <?php else: ?>
                    <strong>Final:</strong> Penilaian tersimpan, auditee dapat melakukan revisi sebelum deadline
                <?php endif; ?>
            </p>
            <?php if ($unansweredCount > 0): ?>
                <p class="text-danger small mt-2 mb-0">
                    <i class="bi bi-lock me-1"></i>Submit dinonaktifkan karena ada <?= $unansweredCount ?> pertanyaan yang belum dijawab auditee
                </p>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- MODAL PREVIEW UNIFIED (GAMBAR & PDF) -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="filePreviewTitle">Preview Bukti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-dark d-flex justify-content-center align-items-center" style="min-height: 80vh;">
                <img id="previewImg" src="" class="img-fluid p-3" style="max-height: 80vh; display: none;">
                <iframe id="previewPdf" src="" style="width: 100%; height: 80vh; border: none; display: none;"></iframe>
            </div>
            <div class="modal-footer bg-light">
                <a id="downloadFileBtn" href="#" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i>Download File
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    const framework = '<?= $audit->framework ?>';
    const isPastDeadline = <?= $isPastDeadline ? 'true' : 'false' ?>;

    // Fungsi Preview File
    function showPreviewModal(url, filename, type) {
        document.getElementById('filePreviewTitle').innerText = filename;
        document.getElementById('downloadFileBtn').href = url;
        document.getElementById('downloadFileBtn').download = filename;

        const imgEl = document.getElementById('previewImg');
        const pdfEl = document.getElementById('previewPdf');

        imgEl.style.display = 'none';
        pdfEl.style.display = 'none';
        imgEl.src = '';
        pdfEl.src = '';

        if (type === 'pdf') {
            pdfEl.style.display = 'block';
            pdfEl.src = url;
        } else {
            imgEl.style.display = 'block';
            imgEl.src = url;
        }

        var myModal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
        myModal.show();
    }

    // Inisialisasi tampilan score
    function initializeScoreDisplay() {
        document.querySelectorAll('.score-input').forEach(input => {
            if (input.type === 'radio') {
                if (input.checked) {
                    const assignmentId = input.name.match(/\[(\d+)\]/)[1];
                    handleScoreChange(assignmentId, input.value);
                }
            } else if (input.tagName === 'SELECT') {
                if (input.value !== '') {
                    const assignmentId = input.name.match(/\[(\d+)\]/)[1];
                    handleScoreChange(assignmentId, input.value);
                }
            }
        });
    }

    // Logic Tampilan Tombol Temuan/Revisi
    function handleScoreChange(assignmentId, value) {
        const card = document.getElementById(`question-${assignmentId}`);
        const findingBtn = document.getElementById(`btn-finding-${assignmentId}`);

        if (!findingBtn) {
            return;
        }

        if (framework === 'ISO 27001') {
            if (value == 0) {
                findingBtn.style.display = 'inline-block';
                findingBtn.disabled = false;
                if (card) card.classList.add('has-finding');
            } else if (value == 1) {
                findingBtn.style.display = 'none';
                findingBtn.disabled = true;
                if (card) card.classList.remove('has-finding');
            }
        } else {
            findingBtn.style.display = 'inline-block';
            findingBtn.disabled = false;
        }
    }

    // Buka / Tutup Form Temuan/Revisi
    function toggleFindingForm(id) {
        const form = document.getElementById(`finding-form-${id}`);
        const btn = document.getElementById(`btn-finding-${id}`);

        if (!form) {
            console.error('Form temuan tidak ditemukan untuk assignment ID:', id);
            return;
        }

        if (btn && btn.classList.contains('saved')) {
            return;
        }

        form.classList.toggle('show');
    }

    // Simpan Temuan/Revisi
    function markFindingAsSaved(id) {
        const form = document.getElementById(`finding-form-${id}`);

        if (!form) {
            console.error('Form temuan tidak ditemukan:', id);
            return;
        }

        const desc = form.querySelector('textarea[name*="deskripsi_temuan"]');
        const rec = form.querySelector('textarea[name*="rekomendasi"]');

        if (desc.value.trim() === '' || rec.value.trim() === '') {
            alert('Deskripsi dan Rekomendasi wajib diisi!');
            return;
        }

        const card = document.getElementById(`question-${id}`);
        const findingBtn = document.getElementById(`btn-finding-${id}`);

        if (card) card.classList.add('has-finding');

        if (findingBtn) {
            const btnText = isPastDeadline ? 'Temuan' : 'Revisi';
            findingBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>' + btnText + ' Sudah Dicatat';
            findingBtn.disabled = true;
            findingBtn.classList.remove('btn-outline-danger', 'btn-outline-warning', 'btn-danger');
            findingBtn.classList.add('btn-success', 'saved');
        }

        form.classList.remove('show');
    }

    // Validasi Submit Final
    document.getElementById('formPenilaian').addEventListener('submit', function(e) {
        const action = e.submitter.value;

        if (action === 'final') {
            let allFilled = true;

            document.querySelectorAll('.score-input').forEach(input => {
                if (input.type === 'radio') {
                    const name = input.name;
                    const isChecked = document.querySelector(`input[name="${name}"]:checked`);
                    if (!isChecked) allFilled = false;
                } else if (input.tagName === 'SELECT') {
                    if (input.value === '') allFilled = false;
                }
            });

            if (!allFilled) {
                e.preventDefault();
                alert("⚠️ Peringatan: Masih ada soal yang belum dinilai!");

                const firstEmpty = document.querySelector('.score-input:not(:checked), select.score-input[value=""]');
                if (firstEmpty) {
                    firstEmpty.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    firstEmpty.focus();
                }
            }
        }
    });

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        initializeScoreDisplay();
    });

    // Bersihkan modal saat ditutup
    document.getElementById('filePreviewModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('previewImg').src = '';
        document.getElementById('previewPdf').src = '';
    });
</script>

<?= $this->endSection() ?>