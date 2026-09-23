<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    .profile-header-banner {
        height: 110px;
        border-radius: 16px 16px 0 0;
        background: linear-gradient(135deg, var(--primary-navy) 0%, #3b4a9e 100%);
    }

    .profile-card {
        border-radius: 16px;
        overflow: visible;
    }

    .profile-avatar-wrapper {
        position: relative;
        width: 110px;
        height: 110px;
        margin: -55px auto 14px;
    }

    .profile-avatar-large {
        width: 110px;
        height: 110px;
        background-color: var(--primary-navy);
        color: white;
        font-size: 2.6rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    .profile-avatar-large img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .avatar-edit-btn {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 34px;
        height: 34px;
        background: var(--accent-orange);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease, background 0.2s ease;
    }

    .avatar-edit-btn:hover {
        transform: scale(1.08);
        background: #e06f10;
    }

    .remove-photo-link {
        font-size: 0.8rem;
        color: #dc3545;
        text-decoration: none;
        cursor: pointer;
    }

    .remove-photo-link:hover {
        text-decoration: underline;
    }

    .info-item {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .form-section-title {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #9aa1b1;
        font-weight: 700;
        margin-bottom: 14px;
    }

    .form-control:disabled {
        background-color: #f4f6f9;
    }

    .form-control:focus {
        border-color: var(--primary-navy);
        box-shadow: 0 0 0 0.2rem rgba(39, 50, 114, 0.12);
    }

    .btn-primary-custom {
        background: var(--primary-navy);
        border: none;
    }

    .btn-primary-custom:hover {
        background: #1d2557;
        color: #fff;
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form action="/profile/update" method="post" enctype="multipart/form-data" id="profileForm">
    <?= csrf_field() ?>
    <input type="hidden" name="remove_photo" id="removePhotoFlag" value="0">

    <div class="row">
        <!-- Kolom Kiri: Info Dasar + Foto -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm profile-card">
                <div class="profile-header-banner"></div>
                <div class="card-body text-center pt-0 pb-4 px-4">
                    <div class="profile-avatar-wrapper">
                        <div class="profile-avatar-large" id="avatarPreview">
                            <?php if (!empty($user->photo)): ?>
                                <img src="<?= base_url('uploads/profile_photos/' . esc($user->photo, 'url')) ?>"
                                    alt="Foto Profil">
                            <?php else: ?>
                                <?= strtoupper(substr($user->fullname, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <label for="photoInput" class="avatar-edit-btn" title="Ganti foto">
                            <i class="bi bi-camera-fill"></i>
                        </label>
                        <input type="file" id="photoInput" name="photo" accept=".jpg,.jpeg,.png,.webp" class="d-none">
                    </div>

                    <h5 class="fw-bold mb-1"><?= esc($user->fullname) ?></h5>
                    <span class="badge bg-primary rounded-pill px-3 py-2 mb-2"><?= ucfirst($user->role) ?></span>

                    <?php if (!empty($user->photo)): ?>
                        <div class="mb-2">
                            <a class="remove-photo-link" id="removePhotoLink">
                                <i class="bi bi-trash3 me-1"></i>Hapus Foto
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="info-item mt-2">
                        <i class="bi bi-calendar-event"></i>
                        <span>Bergabung sejak <?= date('d M Y', strtotime($user->created_at)) ?></span>
                    </div>
                    <small class="text-muted d-block mt-2">JPG, PNG, atau WEBP. Maks 2MB.</small>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Form Edit -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0" style="color: var(--primary-navy);">
                        <i class="bi bi-pencil-square me-2"></i>Edit Profil
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="form-section-title">Informasi Akun</div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" class="form-control" value="<?= esc($user->username) ?>" disabled>
                        <small class="text-muted">Username tidak dapat diubah.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="fullname" class="form-control"
                            value="<?= old('fullname', $user->fullname) ?>" required>
                    </div>

                    <hr class="my-4">

                    <div class="form-section-title">Keamanan</div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Ganti Password
                            <span class="text-muted fw-normal">(Kosongkan jika tidak ingin mengubah)</span>
                        </label>
                        <input type="password" name="new_password" class="form-control"
                            placeholder="Minimal 6 karakter">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-custom text-white">
                            <i class="bi bi-save me-2"></i>Simpan Perubahan
                        </button>
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    // Preview foto langsung di avatar sebelum disimpan
    document.getElementById('photoInput').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('avatarPreview').innerHTML =
                '<img src="' + ev.target.result + '" alt="Preview Foto">';
        };
        reader.readAsDataURL(file);
    });

    // Tombol hapus foto
    const removeLink = document.getElementById('removePhotoLink');
    if (removeLink) {
        removeLink.addEventListener('click', function() {
            if (confirm('Hapus foto profil saat ini?')) {
                document.getElementById('removePhotoFlag').value = '1';
                document.getElementById('profileForm').submit();
            }
        });
    }
</script>
<?= $this->endSection() ?>