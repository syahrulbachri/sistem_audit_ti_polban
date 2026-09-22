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
    <strong>Info:</strong> Nilai setiap pertanyaan.
    <?php if ($audit->framework === 'ISO 27001'): ?>
        Tombol "Buat Temuan" akan muncul otomatis jika Anda memilih <strong>Tidak Sesuai</strong>.
    <?php else: ?>
        Tombol "Buat Temuan" selalu tersedia sesuai <strong>pertimbangan</strong> Anda.
    <?php endif; ?>
</div>

<form action="/audit/simpan-nilai/<?= $audit->id ?>" method="post" id="formPenilaian">
    <?= csrf_field() ?>

    <?php
    $questionNumber = 1;
    foreach ($items as $item):
        $scoreValue = ($item->score === null || $item->score === '') ? null : (int) $item->score;
        $showFindingBtn = false;
        $hasFindingClass = '';
        $btnDisabled = '';
        $btnClass = 'btn-outline-secondary';

        // Cek apakah sudah ada temuan untuk question ini
        $hasExistingFinding = isset($findingQuestionIds[$item->question_id]);

        if ($audit->framework === 'ISO 27001') {
            if ($scoreValue === 0) {
                $showFindingBtn = true;
                $hasFindingClass = 'has-finding';
                $btnClass = 'btn-outline-danger';
            } else if ($scoreValue === 1) {
                $showFindingBtn = false;
                $btnDisabled = 'disabled';
            } else {
                $showFindingBtn = false;
                $btnDisabled = 'disabled';
            }
        } else if ($audit->framework === 'COBIT 2019') {
            $showFindingBtn = true;
            $btnClass = 'btn-outline-danger';
        }
    ?>
        <?php $isUnanswered = empty($item->answer); ?>
        <div class="card question-card <?= $hasFindingClass ?> <?= $isUnanswered ? 'border-danger' : '' ?>"
            id="question-<?= $item->assignment_id ?>"
            style="<?= $isUnanswered ? 'opacity: 0.6;' : '' ?>">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">
                        <span class="badge bg-primary me-2"><?= $questionNumber ?></span>
                        <code><?= esc($item->clause_code) ?></code>
                        <?php if ($isUnanswered): ?>
                            <span class="badge bg-danger ms-2">
                                <i class="bi bi-exclamation-circle me-1"></i>Belum Dijawab
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

                        <!-- Bukti yang Diupload -->
                        <?php if (!empty($item->evidence_filename)): ?>
                            <div class="evidence-box">
                                <strong class="text-primary"><i class="bi bi-paperclip me-2"></i>Bukti:</strong>
                                <div class="mt-2">
                                    <?php
                                    $ext = strtolower(pathinfo($item->evidence_filename, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
                                    $isPDF = ($ext === 'pdf');
                                    ?>

                                    <?php if ($isImage): ?>
                                        <!-- Thumbnail Image -->
                                        <img src="/auditor/view-evidence/<?= $item->assignment_id ?>"
                                            alt="<?= esc($item->evidence_filename) ?>"
                                            class="evidence-thumbnail"
                                            onclick="showImageModal('/auditor/view-evidence/<?= $item->assignment_id ?>', '<?= esc($item->evidence_filename) ?>')">
                                        <div class="mt-2">
                                            <small class="text-muted"><?= esc($item->evidence_filename) ?></small>
                                        </div>

                                    <?php elseif ($isPDF): ?>
                                        <!-- PDF Preview -->
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-pdf text-danger fs-3"></i>
                                            <div>
                                                <strong><?= esc($item->evidence_filename) ?></strong>
                                                <br><small class="text-muted">PDF Document</small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="/auditor/view-evidence/<?= $item->assignment_id ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-box-arrow-up-right me-1"></i>Lihat PDF
                                            </a>
                                        </div>

                                    <?php else: ?>
                                        <!-- File Lain -->
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark text-primary fs-3"></i>
                                            <div>
                                                <strong><?= esc($item->evidence_filename) ?></strong>
                                                <br><small class="text-muted">File Document</small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <a href="/auditor/view-evidence/<?= $item->assignment_id ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-download me-1"></i>Download
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($item->evidence_uploaded_at)): ?>
                                        <small class="text-muted d-block mt-2">
                                            <i class="bi bi-clock me-1"></i>Uploaded: <?= date('d M Y H:i', strtotime($item->evidence_uploaded_at)) ?>
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
                        // 1. DEFINISIKAN VARIABEL DI SINI (Sebelum digunakan)
                        $isBinaryFramework = false;
                        if (!empty($frameworkOptions)) {
                            $isBinaryFramework = (count($frameworkOptions) === 2);
                        }
                        ?>

                        <!-- 2. BARU GUNAKAN VARIABELNYA -->
                        <label class="form-label fw-bold">
                            <?php if ($isBinaryFramework): ?>
                                Penilaian <span class="text-danger">*</span>
                            <?php else: ?>
                                Tingkat Kematangan <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>

                        <?php if ($isBinaryFramework): ?>
                            <!-- Untuk Framework Biner (Radio Button) -->
                            <div class="d-flex gap-2">
                                <?php
                                // Urutkan: nilai 1 di atas, nilai 0 di bawah
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
                            <!-- Untuk Framework Skala (Dropdown) -->
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
                    <label class="form-label small">Catatan Auditor (Opsional)</label>
                    <textarea name="notes[<?= $item->assignment_id ?>]" class="form-control form-control-sm" rows="2"><?= esc($item->note ?? '') ?></textarea>
                </div>

                <!-- Tombol Buat Temuan -->
                <div class="finding-section" data-assignment-id="<?= $item->assignment_id ?>">
                    <?php
                    // Cek apakah sudah ada temuan untuk question ini
                    $hasExistingFinding = isset($findingQuestionIds[$item->question_id]);

                    // Tentukan status tombol
                    if ($hasExistingFinding):
                    ?>
                        <!-- Tombol disabled jika sudah ada temuan -->
                        <button type="button" class="btn btn-sm btn-success" disabled>
                            <i class="bi bi-check-circle me-2"></i>Temuan Sudah Dibuat
                        </button>
                    <?php else: ?>
                        <!-- Tombol aktif jika belum ada temuan -->
                        <button type="button" class="btn btn-sm <?= $btnClass ?> btn-make-finding"
                            id="btn-finding-<?= $item->assignment_id ?>"
                            onclick="toggleFindingForm(<?= $item->assignment_id ?>)"
                            style="<?= $showFindingBtn ? 'display: inline-block;' : 'display: none;' ?>"
                            <?= $btnDisabled ?>>
                            <i class="bi bi-exclamation-triangle me-2"></i>Buat Temuan
                        </button>
                    <?php endif; ?>

                    <!-- Form Temuan Inline (HANYA muncul jika belum ada temuan) -->
                    <?php if (!$hasExistingFinding): ?>
                        <div class="finding-box" id="finding-form-<?= $item->assignment_id ?>">
                            <h6 class="fw-bold text-danger mb-3">
                                <i class="bi bi-exclamation-triangle me-2"></i>Form Temuan Audit
                            </h6>

                            <input type="hidden" name="findings[<?= $item->assignment_id ?>][question_id]" value="<?= $item->question_id ?>">

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
                                    <label for="risk-kritis-<?= $item->assignment_id ?>" class="badge bg-dark"> Kritis</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Deskripsi Temuan <span class="text-danger">*</span></label>
                                <textarea name="findings[<?= $item->assignment_id ?>][deskripsi_temuan]" class="form-control form-control-sm" rows="3" placeholder="Jelaskan ketidaksesuaian yang ditemukan..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Rekomendasi Perbaikan <span class="text-danger">*</span></label>
                                <textarea name="findings[<?= $item->assignment_id ?>][rekomendasi]" class="form-control form-control-sm" rows="2" placeholder="Berikan rekomendasi perbaikan..."></textarea>
                            </div>

                            <button type="button" class="btn btn-sm btn-danger" onclick="markFindingAsSaved(<?= $item->assignment_id ?>)">
                                <i class="bi bi-save me-2"></i>Simpan Temuan
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleFindingForm(<?= $item->assignment_id ?>)">
                                Batal
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Tampilkan info temuan yang sudah ada (opsional) -->
                        <div class="alert alert-success py-2 px-3 mt-3 mb-0 small">
                            <i class="bi bi-check-circle me-1"></i>
                            <strong>Temuan sudah dibuat.</strong>
                            <?php if (!empty($findingQuestionIds[$item->question_id])): ?>
                                Tingkat Risiko: <strong><?= esc($findingQuestionIds[$item->question_id]->tingkat_risiko) ?></strong>
                            <?php endif; ?>
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
                <button type="submit" name="submit_action" value="final" class="btn btn-success btn-lg px-4" id="btnFinal" <?= $unansweredCount > 0 ? 'disabled' : '' ?>>
                    <i class="bi bi-check-circle me-2"></i>Submit Final
                </button>
            </div>
            <p class="text-muted small mt-3 mb-0">
                <strong>Draft:</strong> Progress tersimpan, status audit tetap "Menunggu Penilaian"<br>
                <strong>Final:</strong> Penilaian selesai, status audit berubah menjadi "Selesai"
            </p>
            <?php if ($unansweredCount > 0): ?>
                <p class="text-danger small mt-2 mb-0">
                    <i class="bi bi-lock me-1"></i>Submit Final dinonaktifkan karena ada <?= $unansweredCount ?> pertanyaan yang belum dijawab auditee
                </p>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Modal untuk Image Preview -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Preview Bukti</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" alt="" class="modal-image" id="imageModalSrc">
            </div>
        </div>
    </div>
</div>

<script>
    const framework = '<?= $audit->framework ?>';

    // ✅ FUNGSI BARU: Inisialisasi tampilan saat halaman dimuat
    function initializeScoreDisplay() {
        document.querySelectorAll('.score-input').forEach(input => {
            if (input.type === 'radio') {
                // Untuk ISO (Radio Button) - cek yang checked
                if (input.checked) {
                    const assignmentId = input.name.match(/\[(\d+)\]/)[1];
                    handleScoreChange(assignmentId, input.value);
                }
            } else if (input.tagName === 'SELECT') {
                // Untuk COBIT (Dropdown)
                if (input.value !== '') {
                    const assignmentId = input.name.match(/\[(\d+)\]/)[1];
                    handleScoreChange(assignmentId, input.value);
                }
            }
        });
    }

    // 1. Logic Tampilan Tombol Temuan
function handleScoreChange(assignmentId, value) {
    const card = document.getElementById(`question-${assignmentId}`);
    const findingBtn = document.getElementById(`btn-finding-${assignmentId}`);

    // Jika elemen tombol tidak ada, hentikan fungsi
    // Ini bisa terjadi jika temuan sudah dibuat
    if (!findingBtn) {
        return;
    }

    if (framework === 'ISO 27001') {

        // ISO: tombol hanya muncul jika nilai = 0 (Tidak Sesuai)
        if (value == 0) {
            findingBtn.style.display = 'inline-block';
            findingBtn.disabled = false;

            if (card) {
                card.classList.add('has-finding');
            }

        } else if (value == 1) {
            findingBtn.style.display = 'none';
            findingBtn.disabled = true;

            if (card) {
                card.classList.remove('has-finding');
            }
        }

    } else {

        // COBIT / framework skala:
        // tombol selalu tersedia
        findingBtn.style.display = 'inline-block';
        findingBtn.disabled = false;
    }
}


// 2. Buka / Tutup Form Temuan
function toggleFindingForm(id) {
    const form = document.getElementById(`finding-form-${id}`);
    const btn = document.getElementById(`btn-finding-${id}`);

    // Pastikan form ditemukan
    if (!form) {
        console.error('Form temuan tidak ditemukan untuk assignment ID:', id);
        return;
    }

    // Jika tombol sudah disimpan, jangan buka lagi
    if (btn && btn.classList.contains('saved')) {
        return;
    }

    form.classList.toggle('show');
}


// 3. Simpan Temuan
function markFindingAsSaved(id) {
    const form = document.getElementById(`finding-form-${id}`);

    if (!form) {
        console.error('Form temuan tidak ditemukan:', id);
        return;
    }

    const desc = form.querySelector(
        'textarea[name*="deskripsi_temuan"]'
    );

    const rec = form.querySelector(
        'textarea[name*="rekomendasi"]'
    );

    // Validasi
    if (desc.value.trim() === '' || rec.value.trim() === '') {
        alert('Deskripsi dan Rekomendasi wajib diisi!');
        return;
    }

    const card = document.getElementById(`question-${id}`);
    const findingBtn = document.getElementById(`btn-finding-${id}`);

    if (card) {
        card.classList.add('has-finding');
    }

    if (findingBtn) {
        findingBtn.innerHTML =
            '<i class="bi bi-check-circle me-2"></i>Temuan Sudah Dibuat';

        findingBtn.disabled = true;

        findingBtn.classList.remove(
            'btn-outline-danger',
            'btn-danger'
        );

        findingBtn.classList.add(
            'btn-success',
            'saved'
        );
    }

    form.classList.remove('show');
}

    // 3. ✅ FUNGSI BARU: Tampilkan Preview Gambar di Modal
    function showImageModal(imageUrl, filename) {
        // Set judul modal
        document.getElementById('imageModalTitle').textContent = filename;

        // Set source gambar
        const imgElement = document.getElementById('imageModalSrc');
        imgElement.src = imageUrl;
        imgElement.alt = filename;

        // Tampilkan modal Bootstrap
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
    }

    // 4. VALIDASI SUBMIT FINAL (DIPERBAIKI untuk ISO & COBIT)
    document.getElementById('formPenilaian').addEventListener('submit', function(e) {
        const action = e.submitter.value; // 'draft' atau 'final'

        if (action === 'final') {
            let allFilled = true;

            // Cek setiap input penilaian
            document.querySelectorAll('.score-input').forEach(input => {
                if (input.type === 'radio') {
                    // Untuk ISO (Radio Button)
                    const name = input.name;
                    const isChecked = document.querySelector(`input[name="${name}"]:checked`);
                    if (!isChecked) allFilled = false;
                } else if (input.tagName === 'SELECT') {
                    // Untuk COBIT (Dropdown)
                    if (input.value === '') allFilled = false;
                }
            });

            if (!allFilled) {
                e.preventDefault(); // Batalkan submit
                alert("⚠️ Peringatan: Masih ada soal yang belum dinilai. Silakan lengkapi semua penilaian sebelum Submit Final!");

                // Scroll otomatis ke soal pertama yang kosong
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

    // ✅ PANGGIL FUNGSI INISIALISASI SAAT HALAMAN SELESAI DIMUAT
    document.addEventListener('DOMContentLoaded', function() {
        initializeScoreDisplay();
    });
</script>

<?= $this->endSection() ?>