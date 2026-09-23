<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div>
    <h4 class="mb-0" style="font-size: 1.1rem; font-weight: 700; color: var(--primary-navy); margin: 0;">
        DETAIL RENCANA TINDAK LANJUT (RTL)
    </h4>
    <small style="font-size: 0.8rem; color: #6c757d;">
        Monitoring dan Evaluasi Perbaikan Temuan Audit
    </small>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    :root {
        --primary-navy: #273272;
        --secondary-blue: #3B82F6;
        --success-green: #10B981;
        --warning-orange: #F59E0B;
        --danger-red: #EF4444;
        --info-cyan: #06B6D4;
        --gray-50: #F9FAFB;
        --gray-100: #F3F4F6;
        --gray-200: #E5E7EB;
        --gray-300: #D1D5DB;
        --gray-600: #4B5563;
        --gray-700: #374151;
        --gray-900: #111827;
    }

    /* ===== FIX FOOTER OVERLAP ===== */
    html,
    body {
        height: 100%;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .main-wrapper,
    .wrapper,
    #wrapper {
        flex: 1 0 auto;
        display: flex;
        flex-direction: column;
    }

    .content-wrapper {
        flex: 1 0 auto;
    }

    footer {
        margin-top: auto !important;
        flex-shrink: 0;
    }

    /* Container utama dengan padding bawah yang cukup */
    .rtl-detail-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        padding-bottom: 60px;
    }

    @media (max-width: 768px) {
        .rtl-detail-container {
            padding: 12px;
            padding-bottom: 80px;
        }
    }

    /* Back Button */
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--gray-600);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        padding: 8px 12px;
        border-radius: 8px;
        transition: all 0.2s;
        margin-bottom: 16px;
    }

    .back-button:hover {
        background: var(--gray-100);
        color: var(--primary-navy);
    }

    /* Status Alert Banner */
    .status-alert-banner {
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 600;
    }

    .status-alert-banner.open {
        background: linear-gradient(135deg, #FEF2F2, #FEE2E2);
        border: 2px solid #FECACA;
        color: #991B1B;
    }

    .status-alert-banner.inprogress {
        background: linear-gradient(135deg, #FFFBEB, #FEF3C7);
        border: 2px solid #FDE68A;
        color: #92400E;
    }

    .status-alert-banner.overdue {
        background: linear-gradient(135deg, #FEF2F2, #FEE2E2);
        border: 2px solid #FCA5A5;
        color: #991B1B;
    }

    .status-alert-banner.closed {
        background: linear-gradient(135deg, #F0FDF4, #DCFCE7);
        border: 2px solid #86EFAC;
        color: #166534;
    }

    .status-alert-icon {
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .status-alert-content {
        flex: 1;
    }

    .status-alert-title {
        font-size: 0.85rem;
    }

    /* Main Header Card - 2 Columns */
    .main-header-card {
        background: white;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--gray-200);
        display: grid;
        grid-template-columns: 1fr 180px;
        gap: 16px;
        align-items: center;
    }

    @media (max-width: 968px) {
        .main-header-card {
            grid-template-columns: 1fr;
        }
    }

    .header-badges {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-risk {
        border: 1px solid;
    }

    .badge-risk.rendah {
        background: #DCFCE7;
        color: #166534;
        border-color: #86EFAC;
    }

    .badge-risk.sedang {
        background: #FEF3C7;
        color: #92400E;
        border-color: #FCD34D;
    }

    .badge-risk.tinggi {
        background: #FFEDD5;
        color: #C2410C;
        border-color: #FDBA74;
    }

    .badge-risk.kritis {
        background: #FEE2E2;
        color: #991B1B;
        border-color: #FCA5A5;
    }

    .header-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 6px 0;
        line-height: 1.3;
    }

    .header-subtitle {
        color: var(--gray-600);
        font-size: 0.825rem;
    }

    .header-subtitle strong {
        color: var(--primary-navy);
    }

    /* Status Display Card */
    .status-display-card {
        border-radius: 10px;
        padding: 14px;
        text-align: center;
        border: 2px solid;
        transition: all 0.3s;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 6px;
        min-height: 100px;
    }

    .status-display-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .status-display-card.open {
        background: linear-gradient(135deg, #FEE2E2, #FECACA);
        border-color: #FCA5A5;
        color: #991B1B;
    }

    .status-display-card.inprogress {
        background: linear-gradient(135deg, #FEF3C7, #FDE68A);
        border-color: #FCD34D;
        color: #92400E;
    }

    .status-display-card.overdue {
        background: linear-gradient(135deg, #FEE2E2, #FCA5A5);
        border-color: #F87171;
        color: #991B1B;
    }

    .status-display-card.closed {
        background: linear-gradient(135deg, #DCFCE7, #86EFAC);
        border-color: #4ADE80;
        color: #166534;
    }

    .status-display-icon {
        font-size: 1.8rem;
        line-height: 1;
    }

    .status-display-label {
        font-size: 0.95rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        line-height: 1.1;
    }

    .status-display-desc {
        font-size: 0.7rem;
        font-weight: 500;
        opacity: 0.9;
        line-height: 1.2;
    }

    .status-display-deadline {
        font-size: 0.65rem;
        font-weight: 600;
        margin-top: 4px;
        padding: 3px 6px;
        border-radius: 5px;
        background: rgba(255, 255, 255, 0.6);
    }

    /* Status Info Card */
    .status-info-card {
        background: white;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--gray-200);
    }

    .status-info-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .status-timeline {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .status-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px;
        border-radius: 8px;
        background: var(--gray-50);
        border-left: 4px solid var(--gray-300);
    }

    .status-item.completed {
        background: #F0FDF4;
        border-left-color: var(--success-green);
    }

    .status-item.warning {
        background: #FFFBEB;
        border-left-color: var(--warning-orange);
    }

    .status-icon {
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .status-content {
        flex: 1;
    }

    .status-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--gray-900);
        margin-bottom: 3px;
    }

    .status-desc {
        font-size: 0.725rem;
        color: var(--gray-600);
        line-height: 1.4;
    }

    /* Content Grid - 2 Columns (7:5) */
    .content-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 16px;
    }

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Info Card */
    .info-card {
        background: white;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border: 1px solid var(--gray-200);
        margin-bottom: 16px;
        transition: all 0.3s;
    }

    .info-card:last-child {
        margin-bottom: 0;
    }

    .info-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .card-section-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--gray-900);
        margin: 0 0 12px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid var(--gray-100);
    }

    .card-section-title .icon {
        font-size: 1rem;
    }

    /* Data Rows */
    .data-row {
        margin-bottom: 10px;
    }

    .data-label {
        font-size: 0.7rem;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .data-value {
        font-size: 0.85rem;
        color: var(--gray-900);
        font-weight: 500;
        line-height: 1.4;
    }

    .data-value.danger {
        color: var(--danger-red);
    }

    .data-value.success {
        color: var(--success-green);
    }

    .data-value.warning {
        color: var(--warning-orange);
    }

    /* Highlight Boxes */
    .highlight-box {
        border-radius: 8px;
        padding: 10px;
        margin-top: 10px;
        border-left: 4px solid;
    }

    .highlight-box.question {
        background: #EFF6FF;
        border-left-color: var(--secondary-blue);
    }

    .highlight-box.recommendation {
        background: #DBEAFE;
        border-left-color: #3B82F6;
    }

    .highlight-box.budget-none {
        background: #F0FDF4;
        border-left-color: var(--success-green);
    }

    .highlight-box.budget-amount {
        background: #FEF3C7;
        border-left-color: var(--warning-orange);
    }

    .highlight-box.no-rtl {
        background: #FFFBEB;
        border-left-color: var(--warning-orange);
    }

    .highlight-box.has-rtl {
        background: #F0F9FF;
        border-left-color: var(--info-cyan);
    }

    .highlight-title {
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .highlight-content {
        font-size: 0.8rem;
        line-height: 1.5;
        color: var(--gray-700);
    }

    /* File Box */
    .file-box {
        background: var(--gray-50);
        border: 2px dashed var(--gray-300);
        border-radius: 8px;
        padding: 14px;
        text-align: center;
    }

    .file-icon {
        font-size: 1.8rem;
        margin-bottom: 6px;
        opacity: 0.5;
    }

    .file-text {
        font-size: 0.775rem;
        color: var(--gray-600);
    }

    .file-item {
        background: #F0FDF4;
        border: 2px solid #86EFAC;
        border-radius: 8px;
        padding: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .file-item-icon {
        font-size: 1.3rem;
    }

    .file-item-info {
        flex: 1;
    }

    .file-item-name {
        font-weight: 600;
        color: var(--gray-900);
        font-size: 0.8rem;
        margin-bottom: 2px;
    }

    .file-item-desc {
        font-size: 0.675rem;
        color: var(--gray-600);
    }

    .file-actions {
        display: flex;
        gap: 5px;
        flex-direction: column;
    }

    .btn-file {
        background: var(--primary-navy);
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 0.7rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }

    .btn-file:hover {
        background: #1e2860;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(39, 50, 114, 0.3);
    }

    .btn-file-outline {
        background: white;
        color: var(--primary-navy);
        border: 1px solid var(--primary-navy);
    }

    .btn-file-outline:hover {
        background: var(--primary-navy);
        color: white;
    }

    /* Verification Box */
    .verification-box {
        background: linear-gradient(135deg, #F0F9FF, #FFFFFF);
        border: 2px solid #BAE6FD;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 16px;
    }

    .verification-label {
        font-size: 0.675rem;
        color: var(--gray-600);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .verification-status {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        color: var(--primary-navy);
        margin-bottom: 6px;
    }

    .verification-note {
        font-size: 0.725rem;
        color: var(--gray-600);
        line-height: 1.4;
    }

    /* Tombol Detail Audit */
    .btn-detail-audit {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: var(--primary-navy);
        color: white;
        text-decoration: none;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        transition: all 0.2s;
        border: 1px solid var(--primary-navy);
    }

    .btn-detail-audit:hover {
        background: #1e2860;
        color: white;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(39, 50, 114, 0.3);
    }

    .btn-detail-audit:active {
        transform: translateY(0);
    }

    @media (max-width: 768px) {
        .header-subtitle {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .btn-detail-audit {
            margin-top: 8px;
        }
    }

    /* Animations */
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }
    }

    .status-alert-banner.open .status-alert-icon,
    .status-alert-banner.overdue .status-alert-icon {
        animation: pulse 2s infinite;
    }
</style>

<div class="rtl-detail-container">
    <!-- Back Button -->
    <a href="/pimpinan/rtl" class="back-button">
        ← Kembali ke Monitoring Temuan
    </a>

    <!-- Status Alert Banner -->
    <?php
    $alertClass = $rtl['status'];
    $alertIcon = match ($rtl['status']) {
        'open' => '⏳',
        'inprogress' => '🔄',
        'overdue' => '⚠️',
        'closed' => '✅',
        default => 'ℹ️'
    };
    $alertMessage = match ($rtl['status']) {
        'open' => 'Menunggu Auditee membuat Rencana Tindak Lanjut (RTL).',
        'inprogress' => 'RTL telah disetujui. Auditee sedang mengerjakan perbaikan.',
        'overdue' => 'Perbaikan melewati target deadline! Perlu perhatian khusus.',
        'closed' => 'Temuan telah selesai dan diverifikasi oleh Auditor.',
        default => 'Status tidak diketahui.'
    };
    ?>
    <div class="status-alert-banner <?= $alertClass ?>">
        <div class="status-alert-icon"><?= $alertIcon ?></div>
        <div class="status-alert-content">
            <div class="status-alert-title"><?= $alertMessage ?></div>
        </div>
    </div>

    <!-- Main Header Card (2 Kolom: Info Kiri + Status Card Kanan) -->
    <div class="main-header-card">
        <!-- Kolom Kiri: Info Temuan -->
        <div>
            <div class="header-badges">
                <span class="badge badge-risk <?= esc($rtl['risiko']) ?>">
                    <?= $rtl['risiko'] === 'kritis' ? '🔴' : ($rtl['risiko'] === 'tinggi' ? '🟠' : ($rtl['risiko'] === 'sedang' ? '🟡' : '🟢')) ?>
                    Risiko <?= esc($rtl['risiko_label']) ?>
                </span>
            </div>

            <h2 class="header-title"><?= esc($rtl['judul']) ?></h2>
            <div class="header-subtitle" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <span>
                    Berdasarkan Temuan: <strong><?= esc($rtl['lha_nama']) ?></strong>
                </span>
                <a href="/pimpinan/lha/detail/<?= (int) $rtl['audit_id'] ?>"
                    class="btn-detail-audit"
                    title="Lihat Detail Audit">
                    👁️ Lihat Detail Audit
                </a>
            </div>
        </div>

        <!-- Kolom Kanan: Status Card Kecil -->
        <?php
        $statusDisplayConfig = match ($rtl['status']) {
            'open' => [
                'class' => 'open',
                'icon' => '⏳',
                'label' => 'OPEN',
                'desc' => 'Menunggu RTL',
            ],
            'inprogress' => [
                'class' => 'inprogress',
                'icon' => '🔄',
                'label' => 'IN PROGRESS',
                'desc' => 'Sedang diperbaiki',
            ],
            'overdue' => [
                'class' => 'overdue',
                'icon' => '⚠️',
                'label' => 'OVERDUE',
                'desc' => 'Melebihi deadline!',
            ],
            'closed' => [
                'class' => 'closed',
                'icon' => '✅',
                'label' => 'CLOSED',
                'desc' => 'Selesai & terverifikasi',
            ],
            default => [
                'class' => 'inprogress',
                'icon' => 'ℹ️',
                'label' => 'UNKNOWN',
                'desc' => 'Status tidak diketahui',
            ]
        };
        ?>
        <div class="status-display-card <?= $statusDisplayConfig['class'] ?>">
            <div class="status-display-icon"><?= $statusDisplayConfig['icon'] ?></div>
            <div class="status-display-label"><?= $statusDisplayConfig['label'] ?></div>
            <div class="status-display-desc"><?= $statusDisplayConfig['desc'] ?></div>
            <?php if ($rtl['rtl_deadline'] !== '-' && in_array($rtl['status'], ['inprogress', 'overdue', 'open'])): ?>
                <div class="status-display-deadline">
                    📅 <?= esc($rtl['rtl_deadline']) ?>
                </div>
            <?php endif; ?>
            <?php if ($rtl['status'] === 'closed' && !empty($rtl['closed_at'])): ?>
                <div class="status-display-deadline">
                    ✅ <?= esc($rtl['closed_at']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Info Card (Timeline) -->
    <div class="status-info-card">
        <div class="status-info-title">
            📊 Status & Timeline Penyelesaian
        </div>
        <div class="status-timeline">
            <!-- Step 1: Temuan Dibuat -->
            <div class="status-item completed">
                <div class="status-icon">✅</div>
                <div class="status-content">
                    <div class="status-label">Temuan Dibuat</div>
                    <div class="status-desc">
                        <?= esc($rtl['created_at']) ?> oleh <?= esc($rtl['auditor_name']) ?>
                    </div>
                </div>
            </div>

            <!-- Step 2: RTL Disetujui (jika In_Progress atau Closed) -->
            <?php if (in_array($rtl['status'], ['inprogress', 'closed'])): ?>
                <div class="status-item completed">
                    <div class="status-icon">✅</div>
                    <div class="status-content">
                        <div class="status-label">RTL Disetujui</div>
                        <div class="status-desc">
                            Auditor menyetujui RTL yang diajukan. Status berubah menjadi In_Progress.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="status-item">
                    <div class="status-icon">⏳</div>
                    <div class="status-content">
                        <div class="status-label">Menunggu Persetujuan RTL</div>
                        <div class="status-desc">
                            Menunggu auditor menyetujui RTL yang diajukan auditee.
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Step 3: Closed / Overdue / In Progress -->
            <?php if ($rtl['status'] === 'closed'): ?>
                <div class="status-item completed">
                    <div class="status-icon">✅</div>
                    <div class="status-content">
                        <div class="status-label">Bukti Perbaikan Diverifikasi</div>
                        <div class="status-desc">
                            <?= esc($rtl['closed_at']) ?> - Bukti perbaikan telah diverifikasi dan temuan dinyatakan Closed.
                        </div>
                    </div>
                </div>
            <?php elseif ($rtl['status'] === 'overdue'): ?>
                <div class="status-item warning">
                    <div class="status-icon">⚠️</div>
                    <div class="status-content">
                        <div class="status-label">Melebihi Deadline</div>
                        <div class="status-desc">
                            Target deadline: <?= esc($rtl['rtl_deadline']) ?> - Perlu perhatian khusus!
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="status-item">
                    <div class="status-icon">🔄</div>
                    <div class="status-content">
                        <div class="status-label">Dalam Proses Perbaikan</div>
                        <div class="status-desc">
                            Target deadline: <?= esc($rtl['rtl_deadline']) ?>
                            <?php if (!empty($rtl['rtl_deadline_status'])): ?>
                                <span class="<?= esc($rtl['rtl_deadline_class']) ?>"> - <?= esc($rtl['rtl_deadline_status']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Content Grid - 2 Columns -->
    <div class="content-grid">
        <!-- Left Column: Ringkasan + Komitmen -->
        <div>
            <!-- Ringkasan Temuan Audit -->
            <div class="info-card">
                <h3 class="card-section-title">
                    <span class="icon">📌</span>
                    Ringkasan Temuan Audit
                </h3>

                <div class="highlight-box question">
                    <div class="highlight-title">
                        ❓ Pertanyaan (<?= esc($rtl['clause_code']) ?>)
                    </div>
                    <div class="highlight-content">
                        <?= nl2br(esc($rtl['question_text'])) ?>
                    </div>
                </div>

                <div class="data-row" style="margin-top: 12px;">
                    <div class="data-label">Deskripsi Masalah</div>
                    <div class="data-value"><?= nl2br(esc($rtl['deskripsi'])) ?></div>
                </div>

                <div class="highlight-box recommendation">
                    <div class="highlight-title">
                        💡 Rekomendasi Auditor
                    </div>
                    <div class="highlight-content">
                        <?= nl2br(esc($rtl['rekomendasi'])) ?>
                    </div>
                </div>
            </div>

            <!-- Komitmen & Target -->
            <div class="info-card">
                <h3 class="card-section-title">
                    <span class="icon">🎯</span>
                    Komitmen & Target
                </h3>

                <div class="data-row">
                    <div class="data-label">Unit Kerja PIC</div>
                    <div class="data-value"><?= esc($rtl['unit']) ?></div>
                </div>

                <div class="data-row">
                    <div class="data-label">Framework</div>
                    <div class="data-value"><?= esc($rtl['sistem']) ?></div>
                </div>

                <div class="data-row">
                    <div class="data-label">Target Deadline</div>
                    <div class="data-value <?= $rtl['status'] === 'overdue' ? 'danger' : ($rtl['status'] === 'closed' ? 'success' : 'warning') ?>">
                        <?= esc($rtl['rtl_deadline']) ?>
                    </div>
                    <?php if (!empty($rtl['rtl_deadline_status']) && $rtl['rtl_deadline'] !== '-'): ?>
                        <small class="<?= esc($rtl['rtl_deadline_class']) ?>" style="font-size: 0.7rem; display: block; margin-top: 4px;">
                            ⏰ <?= esc($rtl['rtl_deadline_status']) ?>
                        </small>
                    <?php endif; ?>
                </div>

                <div class="highlight-box <?= empty($rtl['rtl_anggaran']) || $rtl['rtl_anggaran'] == 0 ? 'budget-none' : 'budget-amount' ?>">
                    <div class="highlight-title">
                        💰 Estimasi Anggaran
                    </div>
                    <div class="highlight-content">
                        <?php if (empty($rtl['rtl_anggaran']) || $rtl['rtl_anggaran'] == 0): ?>
                            <em>Tidak memerlukan anggaran</em>
                        <?php else: ?>
                            <strong>Rp <?= number_format($rtl['rtl_anggaran'], 0, ',', '.') ?></strong>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($rtl['rtl_description'])): ?>
                    <div class="highlight-box has-rtl" style="margin-top: 10px;">
                        <div class="highlight-title">
                            📝 Rencana Aksi (RTL)
                        </div>
                        <div class="highlight-content">
                            <?= nl2br(esc($rtl['rtl_description'])) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="highlight-box no-rtl" style="margin-top: 10px;">
                        <div class="highlight-title">
                            ⚠️ Belum Ada RTL
                        </div>
                        <div class="highlight-content">
                            Auditee belum membuat RTL.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Bukti + Verifikasi + Info Audit -->
        <div>
            <!-- Berkas Bukti Perbaikan -->
            <div class="info-card">
                <h3 class="card-section-title">
                    <span class="icon">📎</span>
                    Berkas Bukti
                </h3>

                <?php if (!empty($rtl['bukti_perbaikan'])): ?>
                    <div class="file-item">
                        <div class="file-item-icon">📄</div>
                        <div class="file-item-info">
                            <div class="file-item-name"><?= esc($rtl['bukti_perbaikan']) ?></div>
                            <div class="file-item-desc">Bukti perbaikan telah diunggah</div>
                        </div>
                        <div class="file-actions">
                            <!-- Tombol Lihat: controller mengirim file dengan Content-Disposition: inline -->
                            <a href="<?= esc($rtl['bukti_url']) ?>" target="_blank" rel="noopener"
                                class="btn-file btn-file-outline">
                                👁️ Lihat
                            </a>
                            <!-- Tombol Download: controller mengirim header attachment -->
                            <a href="<?= esc($rtl['bukti_download_url']) ?>" class="btn-file">
                                ⬇️ Download
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="file-box">
                        <div class="file-icon">📁</div>
                        <div class="file-text">
                            <?php if ($rtl['status'] === 'closed'): ?>
                                Tidak ada berkas bukti
                            <?php else: ?>
                                Belum ada bukti perbaikan
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status Verifikasi -->
            <div class="verification-box">
                <div class="verification-label">Status Verifikasi SPI</div>
                <div class="verification-status">
                    🔍 <?= esc($rtl['status_verifikasi']) ?>
                </div>
                <div class="verification-note">
                    <?= $rtl['status_verifikasi_note'] ?>
                </div>
            </div>

            <!-- Informasi Audit -->
            <div class="info-card">
                <h3 class="card-section-title">
                    <span class="icon">ℹ️</span>
                    Informasi Audit
                </h3>

                <div class="data-row">
                    <div class="data-label">Tanggal Temuan</div>
                    <div class="data-value">📅 <?= esc($rtl['created_at']) ?></div>
                </div>

                <?php if (!empty($rtl['closed_at'])): ?>
                    <div class="data-row">
                        <div class="data-label">Tanggal Ditutup</div>
                        <div class="data-value success">✅ <?= esc($rtl['closed_at']) ?></div>
                    </div>
                <?php endif; ?>

                <div class="data-row">
                    <div class="data-label">Auditor</div>
                    <div class="data-value">👤 <?= esc($rtl['auditor_name']) ?></div>
                </div>

                <div class="data-row">
                    <div class="data-label">Periode</div>
                    <div class="data-value">📆 <?= esc($rtl['periode']) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>