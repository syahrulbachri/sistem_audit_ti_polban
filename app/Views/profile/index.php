<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    .profile-avatar-large {
        width: 100px;
        height: 100px;
        background-color: var(--primary-navy);
        color: white;
        font-size: 2.5rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 auto 20px;
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            <?php foreach(session()->getFlashdata('errors') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Kolom Kiri: Info Dasar -->
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm text-center p-4" style="border-radius: 12px;">
            <div class="profile-avatar-large">
                <?= substr($user->fullname, 0, 1) ?>
            </div>
            <h5 class="fw-bold mb-1"><?= esc($user->fullname) ?></h5>
            <span class="badge bg-primary rounded-pill px-3 py-2 mb-3"><?= ucfirst($user->role) ?></span>
            <div class="text-muted small">
                <i class="bi bi-calendar-event me-1"></i> Bergabung sejak <?= date('d M Y', strtotime($user->created_at)) ?>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Form Edit -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0" style="color: var(--primary-navy);">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profil
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="/profile/update" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" value="<?= esc($user->username) ?>" disabled>
                        <small class="text-muted">Username tidak dapat diubah.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control" value="<?= old('fullname', $user->fullname) ?>" required>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Ganti Password <span class="text-muted fw-normal">(Kosongkan jika tidak ingin mengubah)</span></label>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                        </button>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>