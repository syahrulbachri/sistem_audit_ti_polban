<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Sistem Audit IT POLBAN' ?></title>
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
            height: 100vh;
            background-color: var(--primary-navy);
            color: white;
            width: 220px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-brand {
            font-size: 0.95rem;
            font-weight: 700;
            padding: 20px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .sidebar-brand img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            border-radius: 10px;
            flex-shrink: 0;
        }

        .sidebar-menu-container {
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-bottom: 10px;
        }

        .sidebar-menu-container::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu-container::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar-menu-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar-menu-container::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .sidebar-footer {
            flex-shrink: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 16px;
            margin-top: auto;
        }

        .sidebar-footer .nav-link {
            margin: 0 !important;
            border-radius: 8px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7) !important;
            padding: 10px 16px;
            margin: 4px 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            font-weight: 600 !important;
            border-left: 3px solid var(--accent-orange) !important;
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

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                padding-top: 10px;
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

    <!-- Overlay untuk Mobile -->
    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- Panggil Sidebar -->
    <?= $this->include('layouts/partials/sidebar') ?>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- Panggil Topbar (Di sinilah judul halaman akan muncul) -->
        <?= $this->include('layouts/partials/topbar') ?>

        <!-- Konten Dinamis dari View Anak -->
        <div class="p-4 flex-grow-1">
            <?= $this->renderSection('content') ?>
        </div>

        <!-- Panggil Footer -->
        <?= $this->include('layouts/partials/footer') ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (sidebar) sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        }
    </script>
</body>

</html>