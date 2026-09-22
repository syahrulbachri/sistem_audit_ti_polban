<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<div class="card border-0 shadow-sm" style="border-radius: 12px; max-width: 700px;">
    <div class="card-body p-4">
        <?php if(session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach(session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="/admin/periodes/update/<?= $periode->id ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Periode </label>
                <input type="text" name="nama_periode" class="form-control" 
                       value="<?= old('nama_periode', $periode->nama_periode) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Tahun </label>
                <input type="number" name="tahun" class="form-control" 
                       value="<?= old('tahun', $periode->tahun) ?>" required min="2020" max="2100">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tanggal Mulai </label>
                    <input type="date" name="tanggal_mulai" class="form-control" 
                           value="<?= old('tanggal_mulai', $periode->tanggal_mulai) ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Tanggal Selesai </label>
                    <input type="date" name="tanggal_selesai" class="form-control" 
                           value="<?= old('tanggal_selesai', $periode->tanggal_selesai) ?>" required>
                </div>
            </div>

            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Perhatian:</strong> Perubahan tanggal akan divalidasi ulang. Pastikan tidak ada overlap dengan periode lain.
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Update
                </button>
                <a href="/admin/periodes" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>