<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $title ?? 'Sistem Audit IT POLBAN' ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-navy: #273272;
            --accent-orange: #f57e20;
        }

        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* SIDEBAR */
        .sidebar {
            min-height: 100vh;
            background-color: var(--primary-navy);
            color: white;
            width: 220px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar-brand {
            font-size: 0.95rem;
            font-weight: 700;
            padding: 20px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand img {
            width: 36px;
            height: 36px;
            object-fit: contain;
            background: white;
            padding: 4px;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .sidebar-brand span {
            line-height: 1.2;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 10px 16px;
            margin: 4px 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
            margin-right: 10px;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 220px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        .top-header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 998;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-navy);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        /* RESPONSIVE MOBILE */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.3);
            }

            .main-content {
                margin-left: 0 !important;
            }

            .overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                z-index: 999;
                backdrop-filter: blur(2px);
            }

            .overlay.active {
                display: block;
            }
        }
    </style>
</head>

<body>
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <div class="sidebar" id="sidebar">
        <button class="btn btn-sm text-white d-lg-none position-absolute top-0 end-0 m-3" onclick="toggleSidebar()"
            style="z-index: 1001;">
            <i class="bi bi-arrow-left-short fs-3 lh-1"></i>
        </button>
        <div class="sidebar-brand">
            <img src="<?= base_url('assets/img/logo-polban.png') ?>" alt="Logo POLBAN">
            <span>SISTEM AUDIT IT<br>POLBAN</span>
        </div>
        <ul class="nav flex-column mt-3 px-2">
            <li class="nav-item">
                <a class="nav-link <?= uri_string() == 'auditee/dashboard' ? 'active' : '' ?>" href="/auditee/dashboard"
                    onclick="if(window.innerWidth < 992) toggleSidebar()">
                    <i class="bi bi-grid-fill"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= uri_string() == 'auditee/riwayat' ? 'active' : '' ?>" href="/auditee/riwayat"
                    onclick="if(window.innerWidth < 992) toggleSidebar()">
                    <i class="bi bi-clock-history"></i> Riwayat Audit
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= uri_string() == 'profile' ? 'active' : '' ?>" href="/profile"
                    onclick="if(window.innerWidth < 992) toggleSidebar()">
                    <i class="bi bi-person-circle"></i> Profil Saya
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= uri_string() == 'auditee/daftar-audit' ? 'active' : '' ?>"
                    href="/auditee/daftar-audit" onclick="if(window.innerWidth < 992) toggleSidebar()">
                    <i class="bi bi-list-check"></i> Daftar Audit
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/auditee/log-aktivitas">
                    <i class="bi bi-journal-text"></i> Log Aktivitas
                </a>
            </li>
            <li class="nav-item mt-5">
                <a class="nav-link" href="/logout" onclick="if(window.innerWidth < 992) toggleSidebar()">
                    <i class="bi bi-box-arrow-left"></i> Keluar
                </a>
            </li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="top-header">
            <button class="btn btn-light d-lg-none me-3" onclick="toggleSidebar()">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h5 class="fw-bold m-0 d-none d-lg-block" style="color: var(--primary-navy);">
                <?= $page_title ?? 'Dashboard' ?>
            </h5>
            <div class="flex-grow-1"></div>
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
                    <li><a class="dropdown-item" href="/profile"><i class="bi bi-person me-2"></i> Profil Saya</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>
                            Keluar</a></li>
                </ul>
            </div>
        </div>

        <div class="p-4 flex-grow-1">
            <?= $this->renderSection('content') ?>
        </div>

        <footer class="mt-auto py-3 text-center text-muted small border-top bg-white">
            &copy;
            <?= date('Y') ?> Sistem Audit IT POLBAN. All rights reserved.
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>

</html>