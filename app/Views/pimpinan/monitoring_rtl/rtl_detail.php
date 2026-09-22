<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .rtl-detail-wrap {
        max-width: 1180px;
        margin: 0 auto;
    }

    .back-link {
        color: #64748B;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .back-link:hover {
        color: #1E293B;
    }

    .toolbar-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .btn-outline-soft {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        color: #1E293B;
        font-weight: 600;
        font-size: 0.85rem;
        border-radius: 8px;
        padding: 8px 16px;
    }

    .card-panel {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    /* Header */
    .header-badges {
        display: flex;
        gap: 8px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }

    .badge-id {
        font-size: 0.78rem;
        font-weight: 700;
        color: #2563EB;
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 999px;
        padding: 4px 12px;
    }

    .badge-risk {
        font-size: 0.78rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-risk.kritis {
        background: #FEE2E2;
        color: #DC2626;
    }

    .badge-risk.tinggi {
        background: #FFEDD5;
        color: #C2410C;
    }

    .badge-risk.sedang {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-risk.rendah {
        background: #DCFCE7;
        color: #16A34A;
    }

    .badge-status {
        font-size: 0.78rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 12px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-status.inprogress {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-status.overdue {
        background: #FEE2E2;
        color: #DC2626;
    }

    .badge-status.open {
        background: #FEE2E2;
        color: #DC2626;
    }

    .badge-status.closed {
        background: #DCFCE7;
        color: #16A34A;
    }

    .header-title {
        color: #1E2A5E;
        font-weight: 800;
        font-size: 1.25rem;
        margin-bottom: 6px;
    }

    .header-sub {
        color: #64748B;
        font-size: 0.87rem;
    }

    .header-sub b {
        color: #1E293B;
    }

    .progress-box {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 14px 18px;
        text-align: center;
        min-width: 220px;
    }

    .progress-box .lbl {
        font-size: 0.78rem;
        color: #64748B;
        margin-bottom: 4px;
    }

    .progress-box .pct {
        font-size: 1.8rem;
        font-weight: 800;
        color: #D97706;
        line-height: 1.1;
    }

    .progress-box .track {
        width: 100%;
        height: 8px;
        background: #E2E8F0;
        border-radius: 6px;
        margin-top: 10px;
        overflow: hidden;
    }

    .progress-box .fill {
        height: 100%;
        background: #F59E0B;
        border-radius: 6px;
    }

    /* Section titles */
    .section-title {
        font-weight: 700;
        color: #1E293B;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 6px;
        padding-bottom: 10px;
        border-bottom: 1px solid #F1F5F9;
        margin-bottom: 14px;
    }

    .field-label {
        font-size: 0.76rem;
        color: #94A3B8;
        margin-bottom: 3px;
    }

    .field-value {
        font-size: 0.87rem;
        color: #1E293B;
        font-weight: 600;
    }

    .field-value.danger {
        color: #DC2626;
    }

    .rencana-aksi-box {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 14px 16px;
        margin-top: 16px;
    }

    .rencana-aksi-box .title-mini {
        font-weight: 700;
        font-size: 0.83rem;
        color: #1E293B;
        margin-bottom: 8px;
    }

    .rencana-aksi-box ol {
        margin: 0;
        padding-left: 18px;
        font-size: 0.85rem;
        color: #334155;
    }

    .rencana-aksi-box li {
        margin-bottom: 4px;
    }

    /* Timeline */
    .timeline-item {
        position: relative;
        padding-left: 22px;
        padding-bottom: 20px;
        border-left: 2px solid #E2E8F0;
        margin-left: 4px;
    }

    .timeline-item:last-child {
        border-left: 2px solid transparent;
        padding-bottom: 0;
    }

    .timeline-item .dot {
        position: absolute;
        left: -6px;
        top: 2px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #2563EB;
    }

    .timeline-item .meta {
        font-size: 0.76rem;
        color: #94A3B8;
        margin-bottom: 6px;
    }

    .timeline-item .content-box {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 0.84rem;
        color: #334155;
    }

    .timeline-item .content-box.highlight {
        background: #EFF6FF;
        border-color: #BFDBFE;
    }

    .timeline-item .content-box b {
        color: #1E293B;
    }

    /* Berkas bukti */
    .berkas-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #F1F5F9;
    }

    .berkas-item:last-of-type {
        border-bottom: none;
    }

    .berkas-icon {
        font-size: 1.2rem;
    }

    .berkas-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1E293B;
    }

    .berkas-meta {
        font-size: 0.74rem;
        color: #94A3B8;
    }

    .berkas-link {
        margin-left: auto;
        color: #2563EB;
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
    }

    .btn-upload-soft {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        color: #334155;
        font-weight: 600;
        font-size: 0.83rem;
        border-radius: 8px;
        padding: 9px 14px;
        width: 100%;
        margin-top: 8px;
    }

    /* Verifikasi SPI */
    .verif-label {
        font-size: 0.8rem;
        color: #64748B;
        margin-bottom: 4px;
    }

    .verif-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: #B45309;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 10px;
    }

    .verif-note {
        font-size: 0.78rem;
        color: #64748B;
        line-height: 1.6;
    }
</style>

<div class="rtl-detail-wrap">

    <div class="toolbar-top">
        <a href="/pimpinan/rtl" class="back-link">← Kembali ke Pelacakan RTL</a>
        <button type="button" class="btn-outline-soft">⬇️ Cetak Laporan Perbaikan</button>
    </div>

    <div class="card-panel p-4 mb-3">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="header-badges">
                    <span class="badge-id"><?= esc($rtl['id']) ?></span>
                    <span class="badge-risk <?= esc($rtl['risiko']) ?>">🔴 Risiko <?= esc($rtl['risiko_label']) ?></span>
                    <span class="badge-status <?= esc($rtl['status']) ?>"><?= $rtl['status_icon'] ?> Status: <?= esc($rtl['status_label']) ?> (<?= esc($rtl['progress']) ?>%)</span>
                </div>
                <div class="header-title"><?= esc($rtl['judul']) ?></div>
                <div class="header-sub">
                    Berdasarkan Temuan LHA: <b><?= esc($rtl['lha_kode']) ?></b> (<?= esc($rtl['lha_nama']) ?>)
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="progress-box ms-lg-auto">
                    <div class="lbl">Progres Penyelesaian RTL</div>
                    <div class="pct"><?= esc($rtl['progress']) ?>%</div>
                    <div class="track">
                        <div class="fill" style="width: <?= esc($rtl['progress']) ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card-panel p-4 mb-3">
                <div class="section-title">📌 Ringkasan Temuan Audit Basis</div>
                <div class="mb-3">
                    <div class="field-label">Deskripsi Masalah:</div>
                    <div class="field-value" style="font-weight:400;"><?= esc($rtl['deskripsi_masalah']) ?></div>
                </div>
                <div>
                    <div class="field-label">Standar Acuan:</div>
                    <div class="field-value" style="font-weight:400;"><?= esc($rtl['standar_acuan']) ?></div>
                </div>
            </div>

            <div class="card-panel p-4 mb-3">
                <div class="section-title">📝 Komitmen &amp; Target Tindak Lanjut</div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="field-label">Unit Kerja Penanggung Jawab (PIC)</div>
                        <div class="field-value"><?= esc($rtl['pic_unit']) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Penanggung Jawab Lapangan</div>
                        <div class="field-value"><?= esc($rtl['pic_lapangan']) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Target Tanggal Selesai (Deadline)</div>
                        <div class="field-value danger"><?= esc($rtl['target_deadline']) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="field-label">Target Luaran / Evidence</div>
                        <div class="field-value"><?= esc($rtl['target_evidence']) ?></div>
                    </div>
                </div>

                <div class="rencana-aksi-box">
                    <div class="title-mini">Rencana Aksi Teknis:</div>
                    <ol>
                        <?php foreach ($rtl['rencana_aksi'] as $aksi): ?>
                            <li><?= esc($aksi) ?></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>

            <div class="card-panel p-4">
                <div class="section-title">🕐 Riwayat Perkembangan &amp; Catatan SPI</div>

                <?php foreach ($rtl['riwayat'] as $h): ?>
                    <div class="timeline-item">
                        <span class="dot"></span>
                        <div class="meta"><?= esc($h['tanggal']) ?> - <?= esc($h['oleh']) ?></div>
                        <div class="content-box <?= $h['highlight'] ? 'highlight' : '' ?>">
                            <b><?= esc($h['judul']) ?>:</b> <?= esc($h['isi']) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-panel p-4 mb-3">
                <div class="section-title">📎 Berkas Bukti Perbaikan</div>
                <?php if (!empty($rtl['berkas'])): ?>
                    <?php foreach ($rtl['berkas'] as $b): ?>
                        <div class="berkas-item">
                            <span class="berkas-icon"><?= $b['icon'] ?></span>
                            <div>
                                <div class="berkas-name"><?= esc($b['nama']) ?></div>
                                <div class="berkas-meta"><?= esc($b['ukuran']) ?> • Diunggah <?= esc($b['tanggal']) ?></div>
                            </div>
                            <a href="#" class="berkas-link">Lihat</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-muted text-center py-3">
                        <i class="bi bi-folder2-open" style="font-size: 1.5rem; opacity: 0.4;"></i>
                        <p class="mb-0 small mt-2">Belum ada berkas bukti yang diunggah</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card-panel p-4">
                <div class="section-title">🛡️ Status Verifikasi SPI</div>
                <div class="verif-label">Status Verifikasi Saat Ini:</div>
                <div class="verif-value">🔍 <?= esc($rtl['status_verifikasi']) ?></div>
                <div class="verif-note"><?= $rtl['status_verifikasi_note'] ?></div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>