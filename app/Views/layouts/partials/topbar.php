<?php
// Ambil foto profil langsung dari database (bukan dari session) supaya
// selalu akurat tanpa perlu mengubah Auth.php dan tanpa risiko basi
// kalau session belum/tidak tersinkron.
$topbarPhoto = null;
if (session()->get('logged_in')) {
    $topbarUser = \Config\Database::connect()
        ->table('users')
        ->select('photo')
        ->where('id', session()->get('id'))
        ->get()
        ->getRow();
    $topbarPhoto = $topbarUser->photo ?? null;
}
?>
<div class="top-header w-100">
    <!-- Bagian Kiri: Judul Halaman (Di-render dari view anak) -->
    <div class="page-header-section">
        <?= $this->renderSection('page_header') ?>
    </div>

    <!-- Bagian Kanan: Profil User -->
    <div class="user-profile dropdown">
        <div class="text-end me-2 d-none d-md-block">
            <div class="fw-bold" style="font-size: 0.9rem; color: var(--primary-navy);">
                <?= esc(session()->get('fullname') ?? 'User') ?>
            </div>
            <small class="text-muted" style="font-size: 0.75rem;">
                <?= esc(ucfirst(session()->get('role') ?? 'Role')) ?>
            </small>
        </div>

        <div class="user-avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <?php if (!empty($topbarPhoto)): ?>
                <img src="<?= base_url('uploads/profile_photos/' . esc($topbarPhoto, 'url')) ?>"
                    alt="Foto Profil">
            <?php else: ?>
                <?= strtoupper(substr(session()->get('fullname') ?? 'U', 0, 1)) ?>
            <?php endif; ?>
        </div>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
            <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
            <li>
                <hr class="dropdown-divider">
            </li>
            <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
        </ul>
    </div>
</div>

<style>
    .user-avatar {
        overflow: hidden;
    }

    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .page-header-section h4 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-navy);
        margin: 0;
    }

    .page-header-section small {
        font-size: 0.8rem;
        color: #6c757d;
    }
</style>