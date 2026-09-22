<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<div class="card border-0 shadow-sm" style="border-radius: 12px; max-width: 900px;">
    <div class="card-body p-4">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0"><?php foreach (session()->getFlashdata('errors') as $error): ?><li><?= $error ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="/admin/frameworks/store" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label fw-bold">Nama Framework <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" value="<?= old('nama') ?>" required placeholder="Contoh: NIST CSF 2.0, ITIL 4">
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi (Opsional)</label>
                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Penjelasan singkat..."><?= old('deskripsi') ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tipe Penilaian <span class="text-danger">*</span></label>
                    <select name="scoring_type" id="scoring_type" class="form-select" required onchange="toggleOptionsForm()">
                        <option value="">-- Pilih Tipe --</option>
                        <option value="binary" <?= old('scoring_type') === 'binary' ? 'selected' : '' ?>>Biner (2 Pilihan)</option>
                        <option value="scale" <?= old('scoring_type') === 'scale' ? 'selected' : '' ?>>Skala (Tingkat Kematangan)</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Nilai Awal (Start) <span class="text-danger">*</span></label>
                    <input type="number" name="start_score" id="start_score" class="form-control" min="0" max="10" value="<?= old('start_score', '0') ?>" required>
                    <small class="text-muted" id="start_hint">Untuk biner, wajib 0</small>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold">Nilai Maksimum <span class="text-danger">*</span></label>
                    <input type="number" name="max_score" id="max_score" class="form-control" min="1" max="10" value="<?= old('max_score', '1') ?>" required>
                    <small class="text-muted" id="max_hint">Untuk biner, wajib 1</small>
                </div>
            </div>

            <!-- CONTAINER UNTUK OPTIONS DINAMIS -->
            <div id="options-container" class="mt-4" style="display: none;">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Keterangan untuk Setiap Level Penilaian</h6>
                    </div>
                    <div class="card-body" id="options-list">
                        <!-- Options akan digenerate oleh JavaScript -->
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan Framework</button>
                <a href="/admin/frameworks" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleOptionsForm() {
    const type = document.getElementById('scoring_type').value;
    const startInput = document.getElementById('start_score');
    const maxInput = document.getElementById('max_score');
    const container = document.getElementById('options-container');
    const startHint = document.getElementById('start_hint');
    const maxHint = document.getElementById('max_hint');
    
    if (type === 'binary') {
        startInput.value = 0;
        startInput.readOnly = true;
        startInput.classList.add('bg-light');
        startHint.textContent = 'Terkunci di angka 0';
        
        maxInput.value = 1;
        maxInput.readOnly = true;
        maxInput.classList.add('bg-light');
        maxHint.textContent = 'Terkunci di angka 1';
        
        container.style.display = 'block';
        generateOptions(0, 1);
    } else if (type === 'scale') {
        startInput.readOnly = false;
        startInput.classList.remove('bg-light');
        startHint.textContent = 'Biasanya 0 atau 1';
        
        maxInput.readOnly = false;
        maxInput.classList.remove('bg-light');
        maxHint.textContent = 'Maksimal 10';
        
        container.style.display = 'block';
        generateOptions(0, 5); // Default 0-5
    } else {
        container.style.display = 'none';
    }
}

function generateOptions(start, max) {
    const container = document.getElementById('options-list');
    const type = document.getElementById('scoring_type').value;
    container.innerHTML = '';
    
    // Untuk Biner: urutkan dari 1 ke 0 (nilai 1 di atas)
    // Untuk Skala: urutkan dari start ke max (normal)
    let values = [];
    if (type === 'binary') {
        values = [1, 0]; // Nilai 1 dulu, baru 0
    } else {
        for (let i = start; i <= max; i++) {
            values.push(i);
        }
    }
    
    values.forEach(i => {
        const div = document.createElement('div');
        div.className = 'mb-3 p-3 border rounded bg-light';
        
        // Untuk biner: tampilkan "Nilai = X"
        // Untuk skala: tampilkan "Level X"
        const labelTitle = type === 'binary' ? `Nilai = ${i}` : `Level ${i}`;
        
        div.innerHTML = `
            <div class="row">
                <div class="col-md-12 mb-2">
                    <strong class="text-primary">${labelTitle}</strong>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small">Label/Keterangan <span class="text-danger">*</span></label>
                    <input type="text" name="options[${i}][label]" class="form-control form-control-sm" 
                           placeholder="${type === 'binary' ? 'Contoh: Sesuai, Ya, Lulus' : 'Contoh: Incomplete, Performed'}" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="form-label small">Penjelasan Detail (Opsional)</label>
                    <input type="text" name="options[${i}][description]" class="form-control form-control-sm" 
                           placeholder="Penjelasan lebih detail...">
                </div>
            </div>
        `;
        container.appendChild(div);
    });
}

// Event listener untuk perubahan start/max score
document.getElementById('start_score').addEventListener('change', function() {
    const type = document.getElementById('scoring_type').value;
    if (type === 'scale') {
        const start = parseInt(this.value) || 0;
        const max = parseInt(document.getElementById('max_score').value) || 5;
        if (start <= max) {
            generateOptions(start, max);
        }
    }
});

document.getElementById('max_score').addEventListener('change', function() {
    const type = document.getElementById('scoring_type').value;
    if (type === 'scale') {
        const start = parseInt(document.getElementById('start_score').value) || 0;
        const max = parseInt(this.value) || 5;
        if (max >= start) {
            generateOptions(start, max);
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const type = document.getElementById('scoring_type').value;
    if (type) {
        toggleOptionsForm();
    }
});
</script>

<?= $this->endSection() ?>