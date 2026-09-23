<div class="sidebar" id="sidebar">
    <!-- Tombol Close Khusus Mobile -->
    <button class="btn btn-sm text-white d-lg-none position-absolute top-0 end-0 m-3" onclick="toggleSidebar()"
        style="z-index: 1001;">
        <i class="bi bi-x-lg fs-5"></i>
    </button>

    <!-- 1. HEADER (Logo) - TETAP DI ATAS -->
    <div class="sidebar-brand">
        <img src="<?= base_url('assets/img/logo-polban.png') ?>" alt="Logo POLBAN">
        <span>SISTEM AUDIT IT<br>POLBAN</span>
    </div>

    <!-- 2. MENU - BISA DI-SCROLL -->
    <div class="sidebar-menu-container">
        <ul class="nav flex-column mt-3 px-2">
            <?php
            $currentUri = uri_string();
            $isActive = function ($path) use ($currentUri) {
                return $currentUri === $path || strpos($currentUri, $path . '/') === 0;
            };
            ?>

            <?php if (session()->get('role') === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/dashboard') ? 'active' : '' ?>" href="/admin/dashboard">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/users') ? 'active' : '' ?>" href="/admin/users">
                        <i class="bi bi-people-fill"></i> Manajemen Akun
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/questions') ? 'active' : '' ?>" href="/admin/questions">
                        <i class="bi bi-question-circle-fill"></i> Template Pertanyaan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/frameworks') ? 'active' : '' ?>" href="/admin/frameworks">
                        <i class="bi bi-book-fill"></i> Manajemen Framework
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/periodes') ? 'active' : '' ?>" href="/admin/periodes">
                        <i class="bi bi-calendar-event-fill"></i> Manajemen Periode
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/planning') ? 'active' : '' ?>" href="/admin/planning">
                        <i class="bi bi-calendar-check-fill"></i> Perencanaan Audit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/findings') ? 'active' : '' ?>" href="/admin/findings">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Monitoring Temuan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/completed-audits') ? 'active' : '' ?>"
                        href="/admin/completed-audits">
                        <i class="bi bi-clock-history me-2"></i> Riwayat Audit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('admin/activity-logs') ? 'active' : '' ?>" href="/admin/activity-logs">
                        <i class="bi bi-journal-text me-2"></i> Log Aktivitas
                    </a>
                </li>

            <?php elseif (session()->get('role') === 'auditor'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('dashboard') ? 'active' : '' ?>" href="/dashboard">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('auditor/list') ? 'active' : '' ?>" href="/auditor/list">
                        <i class="bi bi-folder"></i> Daftar Audit Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('auditor/monitoring-temuan') ? 'active' : '' ?>"
                        href="/auditor/monitoring-temuan">
                        <i class="bi bi-exclamation-triangle me-2"></i> Monitoring Temuan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('auditor/history') ? 'active' : '' ?>" href="/auditor/history">
                        <i class="bi bi-clock-history"></i> Riwayat Audit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('auditor/activity-logs') ? 'active' : '' ?>"
                        href="/auditor/activity-logs">
                        <i class="bi bi-journal-text me-2"></i> Log Aktivitas
                    </a>
                </li>

            <?php elseif (session()->get('role') === 'auditee'): ?>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('auditee/dashboard') ? 'active' : '' ?>" href="/auditee/dashboard"
                        onclick="if(window.innerWidth < 992) toggleSidebar()">
                        <i class="bi bi-grid-fill"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('auditee/riwayat') ? 'active' : '' ?>" href="/auditee/riwayat"
                        onclick="if(window.innerWidth < 992) toggleSidebar()">
                        <i class="bi bi-clock-history"></i> Riwayat Audit
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentUri === 'profile' ? 'active' : '' ?>" href="/profile"
                        onclick="if(window.innerWidth < 992) toggleSidebar()">
                        <i class="bi bi-person-circle"></i> Profil Saya
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $isActive('auditee/log-aktivitas') ? 'active' : '' ?>"
                        href="/auditee/log-aktivitas">
                        <i class="bi bi-journal-text"></i> Log Aktivitas
                    </a>
                </li>

            <?php elseif (session()->get('role') === 'pimpinan'): ?>
                <!-- MENU PIMPINAN -->
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() == 'pimpinan/dashboard' ? 'active' : '' ?>"
                        href="/pimpinan/dashboard" onclick="if(window.innerWidth < 992) toggleSidebar()">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() == 'pimpinan/daftar_lha' ? 'active' : '' ?>" href="/pimpinan/lha"
                        onclick="if(window.innerWidth < 992) toggleSidebar()">
                        <i class="bi bi-file-earmark-check-fill"></i> Daftar LHA
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() == 'pimpinan/monitoring_rtl' ? 'active' : '' ?>"
                        href="/pimpinan/rtl" onclick="if(window.innerWidth < 992) toggleSidebar()">
                        <i class="bi bi-search-heart"></i> Monitoring Temuan
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() == 'pimpinan/arsip' ? 'active' : '' ?>" href="/pimpinan/arsip"
                        onclick="if(window.innerWidth < 992) toggleSidebar()">
                        <i class="bi bi-archive-fill"></i> Arsip Laporan Audit
                    </a>
                </li>
                <!-- Menu Log Aktivitas -->
                <li class="nav-item">
                    <a class="nav-link" href="/pimpinan/activity-logs">
                        <i class="bi bi-journal-text"></i>
                        <span>Log Aktivitas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= uri_string() == 'pimpinan/profile' ? 'active' : '' ?>" href="/pimpinan/profile"
                        onclick="if(window.innerWidth < 992) toggleSidebar()">
                        <i class="bi bi-gear-fill"></i> Pengaturan Profil
                    </a>
                </li>
            <?php endif; ?>


            <!-- Tombol Keluar (Semua Role) -->
            <li class="nav-item mt-5">
                <a class="nav-link" href="/logout" onclick="if(window.innerWidth < 992) toggleSidebar()">
                    <i class="bi bi-box-arrow-left"></i> Keluar
                </a>
            </li>
        </ul>
    </div>
</div>