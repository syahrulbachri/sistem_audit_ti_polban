<div class="top-header">
    <!-- Tombol Toggle Mobile -->
    <button class="btn btn-light d-lg-none me-3" onclick="toggleSidebar()">
        <i class="bi bi-list fs-4"></i>
    </button>
    
    <!-- Judul Halaman -->
    <h5 class="fw-bold m-0 d-none d-lg-block" style="color: var(--primary-navy);">
        <?= $page_title ?? 'Dashboard' ?>
    </h5>
    
    <!-- Spacer agar profil terdorong ke kanan -->
    <div class="flex-grow-1"></div> 

    <!-- User Profile Section -->
    <div class="dropdown">
        <div class="user-profile dropdown-toggle" data-bs-toggle="dropdown">
            <div class="text-end d-none d-sm-block">
                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                    <?= session()->get('fullname') ?? 'User' ?>
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">
                    <?= ucfirst(session()->get('role')) ?? 'Role' ?>
                </div>
            </div>
            <div class="user-avatar">
                <?= substr(session()->get('fullname') ?? 'U', 0, 1) ?>
            </div>
        </div>
        <ul class="dropdown-menu dropdown-menu-end shadow">
              <li>
        <a class="dropdown-item" href="/profile">
            <i class="bi bi-person me-2"></i> Profil Saya
        </a>
    </li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i> Keluar</a></li>
        </ul>
    </div>
</div>