<?= $this->extend('layouts/main') ?>

<!-- BARU: Section untuk Judul di Topbar -->
<?= $this->section('page_header') ?>
<div>
    <h4 class="mb-0" style="font-size: 1.1rem; font-weight: 700; color: var(--primary-navy); margin: 0;">
        MONITORING TEMUAN AUDIT (PEMANTAUAN RTL)
    </h4>
    <small style="font-size: 0.8rem; color: #6c757d;">
        Monitoring Status Tindak Lanjut Temuan Audit
    </small>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    /* ============ CARD STATISTIK ============ */
    .stats-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 18px 22px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }

    .stats-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .stats-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .stats-label.open {
        color: #DC2626;
    }

    .stats-label.inprogress {
        color: #D97706;
    }

    .stats-label.closed {
        color: #16A34A;
    }

    .stats-label.overdue {
        color: #DC2626;
    }

    .stats-number {
        font-size: 2.2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .stats-number.open {
        color: #DC2626;
    }

    .stats-number.inprogress {
        color: #D97706;
    }

    .stats-number.closed {
        color: #16A34A;
    }

    .stats-number.overdue {
        color: #DC2626;
    }

    .stats-sub {
        font-size: 0.78rem;
        color: #64748B;
        font-weight: 500;
    }

    /* ============ FILTER BAR ============ */
    .filter-bar {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
        display: block;
    }

    .filter-input {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.82rem;
        width: 100%;
        transition: all 0.2s ease;
        background: #FFFFFF;
    }

    .filter-input:focus {
        outline: none;
        border-color: #273272;
        box-shadow: 0 0 0 3px rgba(39, 50, 114, 0.1);
    }

    .filter-select {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.82rem;
        background: #FFFFFF;
        cursor: pointer;
        transition: all 0.2s ease;
        width: 100%;
    }

    .filter-select:focus {
        outline: none;
        border-color: #273272;
        box-shadow: 0 0 0 3px rgba(39, 50, 114, 0.1);
    }

    .btn-reset-filter {
        background: #F1F5F9;
        color: #475569;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
        width: 100%;
        height: 38px;
    }

    .btn-reset-filter:hover {
        background: #E2E8F0;
        color: #475569;
        text-decoration: none;
    }

    .btn-reset-filter.active {
        background: #FED7AA;
        color: #C2410C;
        border-color: #FDBA74;
    }

    .btn-reset-filter.active:hover {
        background: #FDBA74;
    }

    /* ============ TABLE CARD ============ */
    .table-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #E2E8F0;
    }

    .table-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1E293B;
        margin: 0;
    }

    .btn-print {
        background: #273272;
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 0.82rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-print:hover {
        background: #1e2860;
        box-shadow: 0 2px 6px rgba(39, 50, 114, 0.3);
        transform: translateY(-1px);
    }

    /* ============ TABLE ============ */
    .rtl-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }

    .rtl-table thead th {
        background-color: #273272;
        color: #FFFFFF;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-weight: 700;
        border: none;
        padding: 12px 14px;
        vertical-align: middle;
        white-space: nowrap;
    }

    .rtl-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        border-bottom: 1px solid #F1F5F9;
        color: #334155;
        white-space: nowrap;
    }

    .rtl-table tbody tr:hover {
        background-color: #F8FAFC;
    }

    .rtl-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ============ BADGES ============ */
    .badge-clause {
        font-size: 0.75rem;
        font-weight: 600;
        color: #475569;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        padding: 4px 8px;
        font-family: 'Courier New', monospace;
        display: inline-block;
    }

    .audit-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1E293B;
        line-height: 1.3;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
        cursor: help;
    }

    .badge-framework {
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 3px 8px;
        display: inline-block;
    }

    .badge-framework.framework-iso {
        background: #DCFCE7;
        color: #16A34A;
    }

    .badge-framework.framework-cobit {
        background: #DBEAFE;
        color: #2563EB;
    }

    .badge-framework.framework-default {
        background: #F1F5F9;
        color: #64748B;
    }

    .badge-risk {
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 10px;
        display: inline-block;
    }

    .badge-risk.risk-rendah {
        background: #DCFCE7;
        color: #16A34A;
    }

    .badge-risk.risk-sedang {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-risk.risk-tinggi {
        background: #FFEDD5;
        color: #C2410C;
    }

    .badge-risk.risk-kritis {
        background: #FEE2E2;
        color: #DC2626;
    }

    .badge-status {
        font-size: 0.72rem;
        font-weight: 700;
        border-radius: 999px;
        padding: 4px 10px;
        display: inline-block;
    }

    .badge-status.status-open {
        background: #FEE2E2;
        color: #DC2626;
    }

    .badge-status.status-inprogress {
        background: #FEF3C7;
        color: #92400E;
    }

    .badge-status.status-overdue {
        background: #FEE2E2;
        color: #DC2626;
        font-weight: 800;
    }

    .badge-status.status-closed {
        background: #DCFCE7;
        color: #16A34A;
    }

    .btn-detail-rtl {
        background-color: #273272;
        color: #FFFFFF;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 8px;
        padding: 6px 12px;
        border: none;
        white-space: nowrap;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s ease;
    }

    .btn-detail-rtl:hover {
        background-color: #1e2860;
        color: #FFFFFF;
        text-decoration: none;
        box-shadow: 0 2px 6px rgba(39, 50, 114, 0.3);
        transform: translateY(-1px);
    }

    .text-date {
        font-size: 0.78rem;
        color: #64748B;
    }

    .deadline-text {
        font-weight: 600;
        font-size: 0.82rem;
    }

    .deadline-note {
        font-size: 0.72rem;
        font-weight: 600;
    }

    .auditee-text {
        font-size: 0.82rem;
        color: #334155;
        line-height: 1.4;
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: block;
    }

    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #94A3B8;
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    /* ============ PAGINATION ============ */
    .pagination-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 20px;
        border-top: 1px solid #E2E8F0;
        background: #F8FAFC;
    }

    .pagination-info {
        font-size: 0.82rem;
        color: #64748B;
    }

    .pagination-btns {
        display: flex;
        gap: 6px;
    }

    .pagination-btns button {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .pagination-btns button:hover:not(:disabled) {
        background: #273272;
        color: #FFFFFF;
        border-color: #273272;
    }

    .pagination-btns button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .pagination-btns button.active {
        background: #273272;
        color: #FFFFFF;
        border-color: #273272;
    }
</style>

<div class="container-fluid py-4">
    <!-- ============ STATISTIK CARD ============ -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-label open">🔴 OPEN (BELUM RTL)</div>
                <div class="stats-number open"><?= (int) ($summary['open'] ?? 0) ?></div>
                <div class="stats-sub">Temuan · Butuh Aksi Rencana Perbaikan Auditee</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-label inprogress">⏳ IN-PROGRESS</div>
                <div class="stats-number inprogress"><?= (int) ($summary['in_progress'] ?? 0) ?></div>
                <div class="stats-sub">Temuan · Sedang Dikerjakan oleh Unit Kerja</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-label closed">✅ VERIFIED & CLOSED</div>
                <div class="stats-number closed"><?= (int) ($summary['closed'] ?? 0) ?></div>
                <div class="stats-sub">Temuan · Perbaikan Disetujui Auditor</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-label overdue">️ OVERDUE (TERLAMBAT)</div>
                <div class="stats-number overdue"><?= (int) ($summary['overdue'] ?? 0) ?></div>
                <div class="stats-sub">Temuan · Lewat Target Deadline Selesai!</div>
            </div>
        </div>
    </div>

    <!-- ============ FILTER BAR (1 BARIS - COMPACT) ============ -->
    <div class="filter-bar">
        <form method="GET" action="/pimpinan/rtl" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search (Compact) -->
                <div class="col-lg-3">
                    <label class="filter-label mb-1">🔍 Pencarian:</label>
                    <input type="text" name="search" class="filter-input" placeholder="Cari..." value="<?= esc((string) ($search ?? '')) ?>" id="searchInput">
                </div>

                <!-- Framework -->
                <div class="col-lg-2">
                    <label class="filter-label mb-1">Framework:</label>
                    <select name="framework" class="filter-select" id="filterFramework">
                        <option value="">Semua Framework</option>
                        <?php foreach ($frameworks as $fw): ?>
                            <option value="<?= esc((string) ($fw->framework ?? '')) ?>" <?= ($filterFramework ?? '') === $fw->framework ? 'selected' : '' ?>>
                                <?= esc((string) ($fw->framework ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Periode -->
                <div class="col-lg-2">
                    <label class="filter-label mb-1">Periode:</label>
                    <select name="periode" class="filter-select" id="filterPeriode">
                        <option value="">Semua Periode</option>
                        <?php foreach ($periodes as $p): ?>
                            <option value="<?= (int) ($p->id ?? 0) ?>" <?= ($filterPeriode ?? '') == $p->id ? 'selected' : '' ?>>
                                <?= esc((string) ($p->nama_periode ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Auditee/Unit -->
                <div class="col-lg-2">
                    <label class="filter-label mb-1">Unit:</label>
                    <select name="auditee" class="filter-select" id="filterAuditee">
                        <option value="">Semua Unit</option>
                        <?php foreach ($auditees as $aud): ?>
                            <option value="<?= (int) ($aud->id ?? 0) ?>" <?= ($filterAuditee ?? '') == $aud->id ? 'selected' : '' ?>>
                                <?= esc((string) ($aud->fullname ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Risiko -->
                <div class="col-lg-1">
                    <label class="filter-label mb-1">Risiko:</label>
                    <select name="risiko" class="filter-select" id="filterRisiko">
                        <option value="">Semua</option>
                        <option value="Rendah" <?= ($filterRisiko ?? '') === 'Rendah' ? 'selected' : '' ?>>Rendah</option>
                        <option value="Sedang" <?= ($filterRisiko ?? '') === 'Sedang' ? 'selected' : '' ?>>Sedang</option>
                        <option value="Tinggi" <?= ($filterRisiko ?? '') === 'Tinggi' ? 'selected' : '' ?>>Tinggi</option>
                        <option value="Kritis" <?= ($filterRisiko ?? '') === 'Kritis' ? 'selected' : '' ?>>Kritis</option>
                    </select>
                </div>

                <!-- Status -->
                <div class="col-lg-1">
                    <label class="filter-label mb-1">Status:</label>
                    <select name="status" class="filter-select" id="filterStatus">
                        <option value="">Semua</option>
                        <option value="Open" <?= ($filterStatus ?? '') === 'Open' ? 'selected' : '' ?>>Open</option>
                        <option value="In_Progress" <?= ($filterStatus ?? '') === 'In_Progress' ? 'selected' : '' ?>>In Progress</option>
                        <option value="Closed" <?= ($filterStatus ?? '') === 'Closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="overdue" <?= ($filterStatus ?? '') === 'overdue' ? 'selected' : '' ?>>Overdue</option>
                    </select>
                </div>

                <!-- Reset Button -->
                <div class="col-lg-1">
                    <label class="filter-label mb-1" style="visibility: hidden;">Aksi</label>
                    <a href="/pimpinan/rtl" class="btn-reset-filter <?= (!empty($search) || !empty($filterFramework) || !empty($filterPeriode) || !empty($filterAuditee) || !empty($filterRisiko) || !empty($filterStatus)) ? 'active' : '' ?>" style="width: 100%; padding: 8px 6px; font-size: 0.75rem;">
                        ✓ Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- ============ TABEL ============ -->
    <div class="table-card">
        <div class="table-header">
            <h5 class="table-title">Matriks Pelacakan Tindak Lanjut (RTL Tracker Detail) — Semua Status</h5>
            <button type="button" class="btn-print">🖨️ Cetak Rekap RTL</button>
        </div>

        <div class="table-responsive">
            <table class="rtl-table" id="rtlTable">
                <thead>
                    <tr>
                        <th style="width: 8%;">KODE KLAUSUL</th>
                        <th style="width: 18%;">JUDUL AUDIT</th>
                        <th style="width: 10%;">FRAMEWORK</th>
                        <th style="width: 12%;">PERIODE</th>
                        <th style="width: 9%;">TANGGAL TEMUAN</th>
                        <th style="width: 11%;">DEADLINE RTL</th>
                        <th style="width: 7%;">RISIKO</th>
                        <th style="width: 8%;">STATUS</th>
                        <th style="width: 14%;">AUDITEE (UNIT)</th>
                        <th style="width: 3%;">AKSI</th>
                    </tr>
                </thead>
                <tbody id="rtlTableBody">
                    <!-- Data di-render via JavaScript -->
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-wrap" id="paginationWrap">
            <div class="pagination-info" id="paginationInfo"></div>
            <div class="pagination-btns" id="paginationBtns"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== DATA DARI SERVER =====
        const rtlData = <?= json_encode($rtlList ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const perPage = 10;
        let currentPage = 1;
        let filtered = [...rtlData];

        // ===== DOM ELEMENTS =====
        const rtlTableBody = document.getElementById('rtlTableBody');
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationBtns = document.getElementById('paginationBtns');
        const searchInput = document.getElementById('searchInput');
        const filterFramework = document.getElementById('filterFramework');
        const filterPeriode = document.getElementById('filterPeriode');
        const filterRisiko = document.getElementById('filterRisiko');
        const filterStatus = document.getElementById('filterStatus');
        const filterAuditee = document.getElementById('filterAuditee'); // BARU

        // ===== RENDER TABEL =====
        function renderTable() {
            if (!rtlTableBody) return;

            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const pageData = filtered.slice(start, end);

            rtlTableBody.innerHTML = '';

            if (pageData.length === 0) {
                rtlTableBody.innerHTML = `
                <tr>
                    <td colspan="10" class="empty-state">
                        <i class="bi bi-inbox d-block"></i>
                        <div>Belum ada data temuan yang sesuai filter</div>
                    </td>
                </tr>
            `;
                if (paginationInfo) paginationInfo.textContent = '';
                if (paginationBtns) paginationBtns.innerHTML = '';
                return;
            }

            pageData.forEach(r => {
                const fwClass = r.framework === 'ISO 27001' ? 'framework-iso' :
                    r.framework === 'COBIT 2019' ? 'framework-cobit' : 'framework-default';
                const statusClass = r.status === 'closed' ? 'status-closed' :
                    r.status === 'overdue' ? 'status-overdue' :
                    r.status === 'inprogress' ? 'status-inprogress' : 'status-open';
                const riskClass = r.risiko === 'rendah' ? 'risk-rendah' :
                    r.risiko === 'sedang' ? 'risk-sedang' :
                    r.risiko === 'tinggi' ? 'risk-tinggi' : 'risk-kritis';

                const deadlineColor = r.is_overdue ? '#DC2626' : '#1E293B';
                const noteColor = r.is_overdue ? '#DC2626' : (r.status === 'closed' ? '#16A34A' : '#D97706');

                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td><span class="badge-clause">${r.clause_code || '-'}</span></td>
                <td><div class="audit-title" title="${r.audit_title || ''}">${r.audit_title || '-'}</div></td>
                <td><span class="badge-framework ${fwClass}">${r.framework || '-'}</span></td>
                <td>${r.periode || '-'}</td>
                <td><span class="text-date">${r.tanggal_temuan || '-'}</span></td>
                <td>
                    <div class="deadline-text" style="color: ${deadlineColor}">${r.rtl_deadline || '-'}</div>
                    ${r.deadline_note ? `<div class="deadline-note" style="color: ${noteColor}">${r.deadline_note}</div>` : ''}
                </td>
                <td><span class="badge-risk ${riskClass}">${r.risiko_label || '-'}</span></td>
                <td><span class="badge-status ${statusClass}">${r.status_label || '-'}</span></td>
                <td><span class="auditee-text" title="${r.auditee_name || ''}">${r.auditee_name || '-'}</span></td>
                <td class="text-center">
                    <a href="/pimpinan/rtl/detail/${r.id}" class="btn-detail-rtl">Detail →</a>
                </td>
            `;
                rtlTableBody.appendChild(tr);
            });

            renderPagination();
        }

        // ===== RENDER PAGINATION =====
        function renderPagination() {
            if (!paginationInfo || !paginationBtns) return;

            const totalPages = Math.ceil(filtered.length / perPage);
            const start = (currentPage - 1) * perPage + 1;
            const end = Math.min(currentPage * perPage, filtered.length);

            paginationInfo.textContent = `Menampilkan ${start}-${end} dari ${filtered.length} data`;
            paginationBtns.innerHTML = '';

            // Prev button
            const prevBtn = document.createElement('button');
            prevBtn.textContent = '← Prev';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                }
            };
            paginationBtns.appendChild(prevBtn);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.className = i === currentPage ? 'active' : '';
                btn.onclick = () => {
                    currentPage = i;
                    renderTable();
                };
                paginationBtns.appendChild(btn);
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Next →';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            };
            paginationBtns.appendChild(nextBtn);
        }

        // ===== SEARCH =====
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const keyword = this.value.toLowerCase().trim();
                    if (keyword === '') {
                        filtered = [...rtlData];
                    } else {
                        filtered = rtlData.filter(r => {
                            return (r.clause_code || '').toLowerCase().includes(keyword) ||
                                (r.audit_title || '').toLowerCase().includes(keyword) ||
                                (r.framework || '').toLowerCase().includes(keyword) ||
                                (r.periode || '').toLowerCase().includes(keyword) ||
                                (r.risiko_label || '').toLowerCase().includes(keyword) ||
                                (r.status_label || '').toLowerCase().includes(keyword) ||
                                (r.auditee_name || '').toLowerCase().includes(keyword) ||
                                (r.id || '').toLowerCase().includes(keyword);
                        });
                    }
                    currentPage = 1;
                    renderTable();
                }, 150);
            });
        }

        // ===== FILTER DROPDOWNS =====
        if (filterFramework) {
            filterFramework.addEventListener('change', function() {
                this.form.submit();
            });
        }
        if (filterPeriode) {
            filterPeriode.addEventListener('change', function() {
                this.form.submit();
            });
        }
        if (filterRisiko) {
            filterRisiko.addEventListener('change', function() {
                this.form.submit();
            });
        }
        if (filterStatus) {
            filterStatus.addEventListener('change', function() {
                this.form.submit();
            });
        }
        // BARU: Event listener untuk filter Auditee
        if (filterAuditee) {
            filterAuditee.addEventListener('change', function() {
                this.form.submit();
            });
        }

        // ===== INITIAL RENDER =====
        renderTable();
    });
</script>

<?= $this->endSection() ?>