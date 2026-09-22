<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Audit IT POLBAN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-navy: #273272;
            --accent-orange: #f57e20;
        }

        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
            min-height: 550px;
        }

        /* Sisi Kiri: Branding */
        .login-brand {
            background: var(--primary-navy);
            color: white;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .login-brand::before {
            content: '';
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .login-brand h2 {
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .login-brand p {
            opacity: 0.8;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* Sisi Kanan: Form */
        .login-form-wrapper {
            flex: 1;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
        }

        .form-control:focus {
            border-color: var(--primary-navy);
            box-shadow: 0 0 0 3px rgba(39, 50, 114, 0.1);
            background-color: white;
        }

        .btn-login {
            background-color: var(--primary-navy);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #1a2250;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(39, 50, 114, 0.3);
        }

        /* Responsif untuk Mobile */
        @media (max-width: 768px) {
            .login-card {
                flex-direction: column;
                min-height: auto;
            }

            .login-brand {
                padding: 30px;
            }

            .login-form-wrapper {
                padding: 30px;
            }
        }
    </style>
</head>

<body>

    <div class="login-card">
        <!-- Bagian Kiri: Visual Branding -->
        <div class="login-brand d-none d-md-flex">
            <img src="<?= base_url('assets/img/polban.png') ?>" alt="Logo POLBAN" class="mb-3" style="max-width: 120px; height: auto; border-radius: 25px;">
            <h2>SISTEM AUDIT IT<br>POLBAN</h2>
            <p class="mt-3">Platform terintegrasi untuk manajemen audit teknologi informasi, penilaian kepatuhan, dan pelaporan standar ISO 27001 & COBIT 2019.</p>
        </div>

        <!-- Bagian Kanan: Form Login -->
        <div class="login-form-wrapper">
            <div class="mb-4">
                <h3 class="fw-bold" style="color: var(--primary-navy);">Selamat Datang</h3>
                <p class="text-muted small">Silakan masuk menggunakan akun auditor atau auditee Anda.</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show small">
                    <i class="bi bi-exclamation-circle me-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="/auth/process" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold small text-muted">USERNAME</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                        <input type="text" name="username" class="form-control border-start-0 ps-0" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold small text-muted">PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Masukkan password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-login text-white py-3">
                    MASUK KE SISTEM <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">&copy; 2026 Politeknik Negeri Bandung</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>