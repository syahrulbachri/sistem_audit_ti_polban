<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<style>
    /* ===== WRAPPER - FULL WIDTH ===== */
    .review-wrap {
        max-width: 100%;
        width: 100%;
        margin: 0;
        padding: 0 32px 32px 32px;
    }

    /* ===== BACK LINK ===== */
    .back-link {
        color: #64748B;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 20px;
    }

    .back-link:hover {
        color: #1E293B;
    }

    /* ===== TOOLBAR ===== */
    .review-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-bottom: 24px;
    }

    .btn-outline-soft {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        color: #1E293B;
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 8px;
        padding: 12px 20px;
    }

    .btn-outline-danger-soft {
        background: #FFFFFF;
        border: 1px solid #FCA5A5;
        color: #DC2626;
        font-weight: 600;
        font-size: 0.9rem;
        border-radius: 8px;
        padding: 12px 20px;
    }

    .btn-approve-green {
        background: #16A34A;
        border: none;
        color: #FFFFFF;
        font-weight: 700;
        font-size: 0.9rem;
        border-radius: 8px;
        padding: 12px 22px;
    }

    .btn-approve-green:hover {
        background: #15803D;
        color: #FFFFFF;
    }

    /* ===== CARD PANEL ===== */
    .card-panel {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        padding: 28px 32px;
        margin-bottom: 24px;
    }

    /* ===== STATUS PILL ===== */
    .status-pill-top {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 8px 18px;
        margin-bottom: 18px;
    }

    .status-menunggu-top {
        background: #FEF3C7;
        color: #92400E;
    }

    .status-disetujui-top {
        background: #DCFCE7;
        color: #16A34A;
    }

    .status-revisi-top {
        background: #FEE2E2;
        color: #DC2626;
    }

    .status-belum-top {
        background: #F1F5F9;
        color: #64748B;
    }

    /* ===== DOC INFO ===== */
    .doc-title {
        color: #1E2A5E;
        font-weight: 800;
        font-size: 1.6rem;
        margin-bottom: 10px;
    }

    .doc-scope {
        color: #64748B;
        font-size: 1rem;
        margin-bottom: 16px;
    }

    .doc-meta {
        font-size: 0.9rem;
        color: #475569;
    }

    .doc-meta b {
        color: #1E293B;
    }

    .doc-meta .sep {
        margin: 0 10px;
        color: #CBD5E1;
    }

    /* ===== TABS ===== */
    .review-tabs {
        display: flex;
        gap: 36px;
        border-bottom: 2px solid #E2E8F0;
        margin: 28px 0 32px 0;
    }

    .review-tab {
        background: none;
        border: none;
        padding: 14px 4px;
        font-size: 0.95rem;
        font-weight: 600;
        color: #94A3B8;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        margin-bottom: -2px;
    }

    .review-tab.active {
        color: #1E2A5E;
        border-bottom-color: #1E2A5E;
    }

    /* ===== MINI STAT ===== */
    .mini-stat {
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 28px 20px;
        text-align: center;
    }

    .mini-stat .num {
        font-size: 2.2rem;
        font-weight: 800;
    }

    .mini-stat .lbl {
        font-size: 0.85rem;
        color: #64748B;
        margin-top: 8px;
        font-weight: 600;
    }

    .mini-num-navy {
        color: #1E293B;
    }

    .mini-num-red {
        color: #DC2626;
    }

    .mini-num-orange {
        color: #D97706;
    }

    /* ===== CATATAN EKSEKUTIF ===== */
    .catatan-eksekutif {
        font-size: 0.95rem;
        color: #334155;
        margin-top: 24px;
        line-height: 1.7;
        padding: 20px;
        background: #F8FAFC;
        border-radius: 10px;
        border-left: 4px solid #3B82F6;
    }

    /* ===== TEMUAN BLOCK ===== */
    .temuan-block {
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 28px;
        margin-bottom: 28px;
        background: #FFFFFF;
    }

    .temuan-block:last-child {
        margin-bottom: 0;
    }

    .temuan-kode {
        color: #2563EB;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.3px;
        margin-bottom: 8px;
    }

    .temuan-judul {
        font-weight: 700;
        color: #0F172A;
        font-size: 1.15rem;
        margin: 8px 0 20px 0;
    }

    /* ===== RISK PILL ===== */
    .risk-pill {
        font-size: 0.8rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 6px 16px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .risk-pill.kritis {
        background: #FEE2E2;
        color: #DC2626;
    }

    .risk-pill.tinggi {
        background: #FFEDD5;
        color: #C2410C;
    }

    .risk-pill.sedang {
        background: #FEF3C7;
        color: #92400E;
    }

    .risk-pill.rendah {
        background: #DCFCE7;
        color: #16A34A;
    }

    /* ===== C5 GRID ===== */
    .c5-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px 32px;
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border-radius: 10px;
        padding: 24px;
        margin-bottom: 20px;
    }

    .c5-grid .c5-label {
        font-size: 0.78rem;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        margin-bottom: 8px;
        letter-spacing: 0.3px;
    }

    .c5-grid .c5-text {
        font-size: 0.92rem;
        color: #1E293B;
        line-height: 1.6;
    }

    /* ===== REKOMENDASI ===== */
    .rekomendasi-label {
        font-weight: 700;
        font-size: 0.95rem;
        color: #1E293B;
        margin-top: 16px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .rekomendasi-text {
        font-size: 0.92rem;
        color: #334155;
        margin-top: 10px;
        line-height: 1.6;
        padding: 18px;
        background: #EFF6FF;
        border-radius: 10px;
        border-left: 3px solid #3B82F6;
    }

    /* ===== PANEL PENGESAHAN ===== */
    .panel-pengesahan h6 {
        font-weight: 700;
        color: #1E293B;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }

    .panel-pengesahan textarea {
        font-size: 0.92rem;
        line-height: 1.6;
    }

    /* ===== PIN BOX ===== */
    .pin-box-wrap {
        border: 2px dashed #93C5FD;
        background: linear-gradient(135deg, #F0F7FF 0%, #E0F2FE 100%);
        border-radius: 12px;
        padding: 22px;
        margin: 24px 0;
    }

    .pin-box-wrap label {
        font-size: 0.9rem;
        font-weight: 700;
        color: #1E3A8A;
        margin-bottom: 12px;
        display: block;
    }

    .pin-box-wrap input {
        letter-spacing: 12px;
        font-weight: 700;
        text-align: center;
        font-size: 1.2rem;
    }

    .pin-box-wrap .hint {
        font-size: 0.8rem;
        color: #64748B;
        margin-top: 10px;
    }

    /* ===== SERTIFIKAT NOTE ===== */
    .sertifikat-note {
        font-size: 0.78rem;
        color: #94A3B8;
        text-align: center;
        margin-top: 20px;
        line-height: 1.6;
        padding: 14px;
        background: #F8FAFC;
        border-radius: 8px;
    }

    /* ===== DECIDED BOX ===== */
    .decided-box {
        border-radius: 12px;
        padding: 22px;
        font-size: 0.92rem;
        margin-bottom: 24px;
        line-height: 1.6;
    }

    .decided-box.approved {
        background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%);
        color: #15803D;
        border: 2px solid #86EFAC;
    }

    .decided-box.revised {
        background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
        color: #B91C1C;
        border: 2px solid #FCA5A5;
    }

    .decided-box.pending-doc {
        background: #F1F5F9;
        color: #64748B;
        border: 2px solid #CBD5E1;
        text-align: center;
    }

    /* ===== ARAHAN ===== */
    .arahan-readonly {
        background: linear-gradient(135deg, #F8FAFC 0%, #F1F5F9 100%);
        border: 2px solid #E2E8F0;
        border-radius: 10px;
        padding: 20px;
        font-size: 0.92rem;
        color: #1E293B;
        line-height: 1.7;
        min-height: 120px;
    }

    .arahan-meta {
        font-size: 0.85rem;
        color: #64748B;
        margin-top: 16px;
        line-height: 1.8;
    }

    .arahan-meta b {
        color: #1E293B;
    }

    .badge-pin-verified {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
        color: #1D4ED8;
        font-size: 0.78rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 6px 14px;
        margin-top: 12px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .review-wrap {
            padding: 0 16px 16px 16px;
        }

        .card-panel {
            padding: 20px;
        }

        .c5-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="review-wrap">
    <a href="/pimpinan/lha" class="back-link">← Kembali ke Daftar LHA</a>

    <div class="review-toolbar">
        <button type="button" class="btn-outline-soft">⬇️ Unduh PDF LHA</button>
        <button type="button" class="btn-outline-danger-soft">💬 Minta Revisi</button>
        <button type="button" class="btn-approve-green">✅ Sahkan & Approve LHA</button>
    </div>
    <div class="card-panel">
        <span class="status-pill-top status-<?= esc($lha['status']) ?>-top">
            <?= $lha['status_icon'] ?> <?= esc($lha['status_label']) ?>
        </span>
        <div class="doc-title"><?= esc($lha['judul']) ?></div>
        <div class="doc-scope">Ruang Lingkup: <?= esc($lha['ruang_lingkup']) ?></div>
        <div class="doc-meta">
            <b>No. Dokumen:</b> <?= esc($lha['no_dokumen']) ?>
            <span class="sep">•</span>
            <b>Periode Audit:</b> <?= esc($lha['periode']) ?>
            <span class="sep">•</span>
            <b>Lead Auditor:</b> <?= esc($lha['lead_auditor']) ?>
            <span class="sep">•</span>
            <b>Auditee:</b> <?= esc($lha['auditee']) ?>
        </div>
    </div>

    <div class="review-tabs">
        <button type="button" class="review-tab active" data-tab="ringkasan"> Ringkasan & Temuan Audit (5C)</button>
        <button type="button" class="review-tab" data-tab="rtl">📁 Rencana Tindak Lanjut (RTL)</button>
        <button type="button" class="review-tab" data-tab="riwayat">📜 Riwayat Verifikasi & Tim Auditor</button>
    </div>

    <div class="row g-4" id="tab-ringkasan">
        <div class="col-lg-8">
            <div class="card-panel">
                <h6 class="fw-bold mb-4" style="font-size: 1.15rem;">📊 Ringkasan Eksekutif & Tingkat Kematangan</h6>
                <div class="row g-3 mb-4">
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="num mini-num-navy"><?= esc($lha['skor_kematangan']) ?></div>
                            <div class="lbl">Skor Kematangan COBIT</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="num mini-num-red"><?= esc($lha['total_temuan']) ?></div>
                            <div class="lbl">Total Temuan Audit</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="mini-stat">
                            <div class="num mini-num-orange"><?= esc($lha['kesiapan_rtl']) ?>%</div>
                            <div class="lbl">Kesiapan RTL Auditee</div>
                        </div>
                    </div>
                </div>
                <div class="catatan-eksekutif">
                    <b>Catatan Eksekutif Auditor:</b> <?= $lha['catatan_eksekutif'] ?>
                </div>
            </div>

            <div class="card-panel">
                <h6 class="fw-bold mb-4" style="font-size: 1.15rem;">🔍 Rincian Temuan Audit Utama (Sesuai Metode 5C)</h6>
                <?php foreach ($lha['temuan'] as $t): ?>
                    <div class="temuan-block">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="temuan-kode">KODE TEMUAN: <?= esc($t['kode']) ?> | <?= esc($t['standar']) ?></div>
                                <div class="temuan-judul"><?= esc($t['judul']) ?></div>
                            </div>
                            <span class="risk-pill <?= esc($t['risiko']) ?>">🔴 <?= esc($t['risiko_label']) ?></span>
                        </div>
                        <div class="c5-grid">
                            <div>
                                <div class="c5-label">1. Condition (Kondisi Saat Ini)</div>
                                <div class="c5-text"><?= esc($t['condition']) ?></div>
                            </div>
                            <div>
                                <div class="c5-label">2. Criteria (Standar / Kriteria)</div>
                                <div class="c5-text"><?= esc($t['criteria']) ?></div>
                            </div>
                            <div>
                                <div class="c5-label">3. Cause (Sebab Utama)</div>
                                <div class="c5-text"><?= esc($t['cause']) ?></div>
                            </div>
                            <div>
                                <div class="c5-label">4. Effect (Dampak Risiko)</div>
                                <div class="c5-text"><?= esc($t['effect']) ?></div>
                            </div>
                        </div>
                        <div class="rekomendasi-label">💡 Rekomendasi Auditor (Recommendation):</div>
                        <div class="rekomendasi-text"><?= esc($t['rekomendasi']) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-panel panel-pengesahan">
                <h6 class="mb-4">🖋️ <?= $lha['status'] === 'menunggu' ? 'Panel Pengesahan Direktur' : 'Hasil Pengesahan Direktur' ?></h6>

                <?php if ($lha['status'] === 'menunggu'): ?>
                    <!-- FORM UNTUK STATUS MENUNGGU -->
                    <form action="/pimpinan/lha/decide/<?= esc($lha['kode']) ?>" method="post">
                        <?= csrf_field() ?>

                        <label class="form-label small fw-semibold">Arahan / Catatan Strategis Pimpinan:</label>
                        <textarea name="catatan_pimpinan" rows="6" class="form-control" placeholder="Tuliskan catatan atau instruksi khusus untuk <?= esc($lha['auditee']) ?> / Tim Auditor di sini..."></textarea>

                        <div class="pin-box-wrap mt-3">
                            <label class="form-label small fw-semibold">Masukkan PIN Otorisasi (6 Digit):</label>
                            <input type="password" name="pin" maxlength="6" class="form-control" placeholder="••••••" required>
                            <div class="hint">PIN ini menggantikan tanda tangan basah resmi.</div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" name="aksi" value="setujui" class="btn btn-approve-green w-100 mb-2">✓ Setujui & Sahkan LHA Ini</button>
                            <button type="submit" name="aksi" value="revisi" class="btn btn-outline-danger-soft w-100">💬 Kembalikan untuk Revisi</button>
                        </div>
                    </form>

                    <div class="sertifikat-note mt-3">
                        Pengesahan ini menggunakan sertifikat digital terenkripsi SHA-256 Polban Internal Security.
                    </div>

                <?php elseif ($lha['status'] === 'disetujui' || $lha['status'] === 'revisi'): ?>
                    <!-- TAMPILAN HASIL KEPUTUSAN -->
                    <?php if ($lha['status'] === 'disetujui'): ?>
                        <div class="decided-box approved">
                            ✅ LHA ini sudah <b>disahkan & disetujui</b> oleh Pimpinan.
                        </div>
                    <?php else: ?>
                        <div class="decided-box revised">
                            💬 LHA ini <b>dikembalikan untuk revisi</b> ke Auditor/SPI.
                        </div>
                    <?php endif; ?>

                    <label class="form-label small fw-semibold">Arahan / Catatan Strategis Pimpinan:</label>
                    <div class="arahan-readonly">
                        <?= nl2br(esc($lha['catatan_pimpinan'] ?? '(Tidak ada catatan yang dituliskan pimpinan.)')) ?>
                    </div>

                    <div class="arahan-meta">
                        <?= $lha['status'] === 'disetujui' ? 'Disahkan' : 'Dikembalikan' ?> oleh <b><?= esc($lha['pimpinan_nama'] ?? 'Pimpinan') ?></b><br>
                        pada <b><?= esc($lha['tanggal_keputusan'] ?? '-') ?></b>
                        <div class="badge-pin-verified">✓ Terverifikasi PIN Otorisasi</div>
                    </div>

                    <!-- TAMPILKAN TANDA TANGAN DIGITAL -->
                    <?php if (!empty($lha['signature_path'])): ?>
                        <div class="mt-4 pt-3" style="border-top: 1px solid #E2E8F0;">
                            <label class="form-label small fw-semibold">Tanda Tangan Digital Pimpinan:</label>
                            <div class="text-center p-3" style="background: #F8FAFC; border-radius: 8px; border: 2px dashed #CBD5E1;">
                                <img src="<?= base_url($lha['signature_path']) ?>"
                                    alt="Tanda Tangan Digital"
                                    style="max-height: 100px; max-width: 250px;">
                                <div class="mt-2">
                                    <div class="badge bg-success mb-1" style="font-size: 0.7rem;">✓ SIGNED & VERIFIED</div>
                                    <div class="fw-bold small"><?= esc($lha['pimpinan_nama'] ?? 'Pimpinan') ?></div>
                                    <div class="text-muted small">Direktur Polban</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="sertifikat-note mt-3">
                        Pengesahan ini menggunakan sertifikat digital terenkripsi SHA-256 Polban Internal Security.
                    </div>

                <?php else: ?>
                    <!-- STATUS BELUM DISETORKAN -->
                    <div class="decided-box pending-doc" style="text-align:center;">
                        📄 Dokumen LHA ini <b>belum disetorkan</b> oleh Auditor.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-panel" id="tab-rtl" style="display:none;">
        <h6 class="fw-bold mb-4" style="font-size: 1.15rem;">📁 Rencana Tindak Lanjut (RTL)</h6>
        <div class="alert alert-info mb-4">
            <strong>Info:</strong> RTL akan tersedia setelah LHA disahkan oleh Pimpinan.
        </div>
        <div class="row g-3">
            <div class="col-md-4">
                <div class="p-4 border rounded bg-light">
                    <div class="text-muted small">Total Temuan</div>
                    <div class="fw-bold fs-3"><?= esc($lha['total_temuan']) ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded bg-light">
                    <div class="text-muted small">RTL Selesai</div>
                    <div class="fw-bold fs-3">0</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4 border rounded bg-light">
                    <div class="text-muted small">Dalam Proses</div>
                    <div class="fw-bold fs-3">0</div>
                </div>
            </div>
        </div>
        <p class="text-muted mb-0 mt-4">Rencana Tindak Lanjut akan ditampilkan setelah dokumen LHA disahkan.</p>
    </div>

    <div class="card-panel" id="tab-riwayat" style="display:none;">
        <h6 class="fw-bold mb-4" style="font-size: 1.15rem;">📜 Riwayat Verifikasi & Tim Auditor</h6>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Aktivitas</th>
                        <th>Oleh</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= esc($lha['tanggal'] ?? '-') ?></td>
                        <td>Dokumen LHA Diajukan</td>
                        <td><?= esc($lha['lead_auditor']) ?></td>
                        <td>Dokumen awal diajukan untuk review</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.review-tab');
        const panels = {
            ringkasan: document.getElementById('tab-ringkasan'),
            rtl: document.getElementById('tab-rtl'),
            riwayat: document.getElementById('tab-riwayat'),
        };

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                Object.keys(panels).forEach(function(key) {
                    panels[key].style.display = (key === tab.dataset.tab) ? '' : 'none';
                });
            });
        });
    });
</script>
<?= $this->endSection() ?>