<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<style>
    .arsip-wrap {
        max-width: 1180px;
        margin: 0 auto;
    }

    .filter-box {
        background: #EEF2FA;
        border: 1px solid #E0E7F5;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 20px;
    }

    .filter-box .filter-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: nowrap;
    }

    .filter-box .filter-label {
        font-weight: 700;
        font-size: 0.85rem;
        color: #1E293B;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .filter-box input[type="text"] {
        flex: 1 1 auto;
        width: auto;
        min-width: 180px;
    }

    .filter-box select {
        flex: 0 0 auto;
        width: auto;
        min-width: 170px;
    }

    @media (max-width: 991.98px) {
        .filter-box .filter-bar {
            flex-wrap: wrap;
        }

        .filter-box input[type="text"],
        .filter-box select {
            flex: 1 1 100%;
            width: 100%;
        }
    }

    .card-panel {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .arsip-table thead th {
        background-color: #111827;
        color: #FFFFFF;
        font-size: 0.74rem;
        text-transform: none;
        font-weight: 700;
        border: none;
        padding: 12px 16px;
        vertical-align: middle;
    }

    .arsip-table td {
        font-size: 0.85rem;
        vertical-align: middle;
        padding: 14px 16px;
    }

    .arsip-table .kode-link {
        color: #2563EB;
        font-weight: 700;
        text-decoration: none;
    }

    .arsip-table .periode {
        font-weight: 700;
        color: #0F172A;
    }

    .skor-value {
        font-weight: 700;
    }

    .skor-value.established {
        color: #16A34A;
    }

    .skor-value.defined {
        color: #1E293B;
    }

    .skor-note {
        font-size: 0.82rem;
        color: #64748B;
    }

    .status-pill-archive {
        font-size: 0.76rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 5px 12px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #DCFCE7;
        color: #16A34A;
        white-space: nowrap;
    }

    .btn-cetak-lha {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        color: #1E293B;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 14px;
        white-space: nowrap;
    }

    .btn-cetak-lha:hover {
        background: #F8FAFC;
    }
</style>

<div class="arsip-wrap">

    <div class="filter-box">
        <div class="filter-bar">
            <span class="filter-label">🔍 Cari Arsip Laporan:</span>
            <input type="text" class="form-control" placeholder="Ketik Kode Dokumen, Tahun, atau Judul Audit...">
            <select class="form-select">
                <option>Tahun Audit: Semua Tahun</option>
            </select>
            <select class="form-select">
                <option>Standar: COBIT 2019 & ISO 27001</option>
            </select>
            <select class="form-select">
                <option>Status: Signed & Final</option>
            </select>
        </div>
    </div>

    <div class="card-panel p-3 pb-4">
        <h6 class="fw-bold mb-3" style="color:#1E293B;">Daftar Dokumen Laporan Hasil Audit (LHA) Resmi</h6>

        <div class="table-responsive">
            <table class="table arsip-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode Dokumen LHA</th>
                        <th>Periode Audit</th>
                        <th>Kerangka Kerja / Standar</th>
                        <th>Tanggal Pengesahan</th>
                        <th>Skor Kematangan</th>
                        <th>Status Dokumen</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($arsipList as $i => $a): ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td><a href="#" class="kode-link"><?= esc($a['kode']) ?></a></td>
                            <td class="periode"><?= esc($a['periode']) ?></td>
                            <td class="text-muted"><?= esc($a['standar']) ?></td>
                            <td class="text-muted"><?= esc($a['tanggal']) ?></td>
                            <td>
                                <span class="skor-value <?= esc($a['skor_class']) ?>"><?= esc($a['skor']) ?></span>
                                <span class="skor-note">(<?= esc($a['skor_label']) ?>)</span>
                            </td>
                            <td>
                                <span class="status-pill-archive">✅ Signed &amp; Final</span>
                            </td>
                            <td>
                                <button type="button" class="btn-cetak-lha">🖨️ Cetak LHA</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?= $this->endSection() ?>