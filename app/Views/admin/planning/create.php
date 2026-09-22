<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<div class="card border-0 shadow-sm" style="border-radius: 12px; max-width: 800px;">
    <div class="card-body p-4">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach (session()->getFlashdata('errors') as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <form action="/admin/planning/store" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Judul Audit </label>
                <input type="text" name="title" class="form-control" value="<?= old('title') ?>" required placeholder="Contoh: Audit Infrastruktur Jaringan Kampus">
            </div>

                        <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Periode Audit </label>
                    <select name="periode_id" class="form-select" required>
                        <option value="">-- Pilih Periode (Hanya Open) --</option>
                        <?php foreach ($periodes as $p): ?>
                            <option value="<?= $p->id ?>" <?= old('periode_id') == $p->id ? 'selected' : '' ?>><?= esc($p->nama_periode) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($periodes)): ?>
                        <small class="text-danger">⚠️ Tidak ada periode yang berstatus Open. Silakan buat periode baru di menu Manajemen Periode.</small>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Framework </label>
                    <select name="framework" id="framework" class="form-select" required onchange="updateFrameworkInfo()">
                        <option value="">-- Pilih Framework --</option>
                        <?php if(!empty($frameworks)): ?>
                            <?php foreach($frameworks as $fw): ?>
                                <option value="<?= esc($fw->nama) ?>" 
                                        data-type="<?= $fw->scoring_type ?>" 
                                        data-max="<?= $fw->max_score ?>"
                                        <?= old('framework') == $fw->nama ? 'selected' : '' ?>>
                                    <?= esc($fw->nama) ?> 
                                    (<?= $fw->scoring_type === 'binary' ? 'Biner' : 'Skala 0-' . $fw->max_score ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <small class="text-muted" id="framework_info">Pilih framework untuk melihat detail penilaian</small>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Auditee (Unit TI) </label>
                    <select name="auditee_id" class="form-select" required>
                        <option value="">-- Pilih Auditee --</option>
                        <?php foreach ($auditees as $u): ?>
                            <option value="<?= $u->id ?>" <?= old('auditee_id') == $u->id ? 'selected' : '' ?>><?= esc($u->fullname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Auditor Penanggung Jawab </label>
                    <select name="created_by_auditor" class="form-select" required>
                        <option value="">-- Pilih Auditor --</option>
                        <?php foreach ($auditors as $u): ?>
                            <option value="<?= $u->id ?>" <?= old('created_by_auditor') == $u->id ? 'selected' : '' ?>><?= esc($u->fullname) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Deadline Audit </label>
                <input type="date" name="deadline" class="form-control" value="<?= old('deadline') ?>" required>
            </div>

                        <!-- ========================================== -->
            <!-- SECTION: PERTANYAAN CUSTOM (MULTIPLE)     -->
            <!-- ========================================== -->
            <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #ffc107; background: #fffbeb;">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-warning">
                        <i class="bi bi-plus-circle me-2"></i>Pertanyaan Custom dari Admin
                    </h5>
                    <p class="text-muted small mb-3">
                        Tambahkan pertanyaan khusus untuk audit ini. Pertanyaan custom akan otomatis ditugaskan dan tidak bisa diubah oleh Auditor.
                    </p>
                    
                    <div id="custom-questions-container">
                        <!-- Baris pertanyaan custom akan muncul di sini -->
                    </div>

                    <button type="button" class="btn btn-outline-warning btn-sm mt-2" id="btn-add-custom">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Pertanyaan Custom
                    </button>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>Anda bisa menambahkan banyak pertanyaan custom
                    </small>
                </div>
            </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('custom-questions-container');
    const btnAdd = document.getElementById('btn-add-custom');
    let questionCounter = 1;

    btnAdd.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-3 p-3 border rounded bg-white custom-q-row align-items-end shadow-sm';
        row.innerHTML = `
            <div class="col-md-3">
                <label class="form-label small fw-bold">Kode Klausul (Opsional)</label>
                <input type="text" name="custom_clause_code[]" class="form-control form-control-sm" placeholder="Cth: CUST-${questionCounter}">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Teks Pertanyaan <span class="text-danger">*</span></label>
                <textarea name="custom_question_text[]" class="form-control form-control-sm" rows="2" required placeholder="Ketik pertanyaan custom..."></textarea>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-danger btn-sm w-100 btn-remove-custom" title="Hapus">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        questionCounter++;
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-custom')) {
            if (confirm('Hapus pertanyaan custom ini?')) {
                e.target.closest('.custom-q-row').remove();
            }
        }
    });
});
</script>

            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i>
                <strong>Info:</strong> Status audit akan otomatis diset <strong>Aktif</strong> saat dibuat. Auditor dapat mulai mengerjakan setelah ini.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Rencana</button>
                <a href="/admin/planning" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function updateFrameworkInfo() {
    const select = document.getElementById('framework');
    const selectedOption = select.options[select.selectedIndex];
    const infoDiv = document.getElementById('framework_info');
    
    if (selectedOption.value) {
        const type = selectedOption.getAttribute('data-type');
        const max = selectedOption.getAttribute('data-max');
        
        if (type === 'binary') {
            infoDiv.innerHTML = '<strong>Penilaian:</strong> Biner <br><strong>Skor Akhir:</strong> 0-100%';
        } else {
            infoDiv.innerHTML = '<strong>Penilaian:</strong> Skala 0-' + max + '<br><strong>Skor Akhir:</strong> 0-100% (dihitung dari rata-rata)';
        }
    } else {
        infoDiv.textContent = 'Pilih framework untuk melihat detail penilaian';
    }
}
</script>
<?= $this->endSection() ?>