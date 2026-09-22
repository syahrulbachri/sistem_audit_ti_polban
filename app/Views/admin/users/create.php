<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<div class="card border-0 shadow-sm" style="border-radius: 12px; max-width: 600px;">
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

        <form action="/admin/users/store" method="post">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text" name="username" class="form-control" value="<?= old('username') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Nama Lengkap</label>
                <input type="text" name="fullname" class="form-control" value="<?= old('fullname') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required>
                <small class="text-muted">Minimal 6 karakter</small>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Role</label>
                <select name="role" class="form-select" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="auditor" <?= old('role') == 'auditor' ? 'selected' : '' ?>>Auditor</option>
                    <option value="auditee" <?= old('role') == 'auditee' ? 'selected' : '' ?>>Auditee</option>
                    <option value="pimpinan" <?= old('role') == 'pimpinan' ? 'selected' : '' ?>>Pimpinan</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Simpan</button>
                <a href="/admin/users" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>