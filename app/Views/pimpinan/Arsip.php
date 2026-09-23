<?= $this->extend('layouts/main') ?>

<?= $this->section('page_header') ?>
<div>
    <h4 class="mb-0" style="font-size: 1.1rem; font-weight: 700; color: var(--primary-navy); margin: 0;">
        <?= esc((string) $page_title) ?>
    </h4>
    <small style="font-size: 0.8rem; color: #6c757d;">
        <?= esc((string) $page_subtitle) ?>
    </small>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .stats-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        transition: all 0.2s ease;
    }

    .stats-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateY(-2px);
    }

    .stats-label {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748B;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        color: #1E293B;
        margin-bottom: 4px;
    }

    .stats-sub {
        font-size: 0.78rem;
        color: #64748B;
        font-weight: 500;
    }

    .filter-bar {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .filter-label {
        font-size: 0.82rem;
        font-weight: 700;
        color: #1E293B;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .filter-input {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 0.82rem;
        width: 100%;
        transition: all 0.2s ease;
    }

    .filter-input:focus {
        outline: none;
        border-color: #273272;
        box-shadow: 0 0 0 3px rgba(39, 50, 114, 0.1);
    }

    .filter-select {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 0.82rem;
        width: 100%;
        background: #FFFFFF;
        cursor: pointer;
        transition: all 0.2s ease;
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
        display: block;
        text-align: center;
        text-decoration: none;
    }

    .btn-reset-filter:hover {
        background: #E2E8F0;
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

    .arsip-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .arsip-table thead th {
        background-color: #273272;
        color: #FFFFFF;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        font-weight: 700;
        border: none;
        padding: 14px 12px;
        vertical-align: middle;
        white-space: nowrap;
        text-align: left;
    }

    .arsip-table thead th:last-child {
        text-align: center;
    }

    .arsip-table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
        border-bottom: 1px solid #F1F5F9;
        color: #334155;
    }

    .arsip-table tbody tr:hover {
        background-color: #F8FAFC;
    }

    .arsip-table tbody tr:last-child td {
        border-bottom: none;
    }

    .col-no {
        width: 5%;
        text-align: center;
        font-weight: 600;
        color: #64748B;
    }

    .col-judul {
        width: 22%;
        font-weight: 700;
        color: #1E293B;
    }

    .col-periode {
        width: 14%;
        color: #475569;
    }

    .col-auditee {
        width: 18%;
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .col-framework {
        width: 10%;
        text-align: center;
    }

    .col-skor {
        width: 11%;
        text-align: center;
    }

    .col-selesai {
        width: 12%;
        color: #475569;
        font-size: 0.83rem;
    }

    .col-aksi {
        width: 8%;
        text-align: center;
        white-space: nowrap;
    }

    .badge-framework {
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 6px;
        padding: 4px 10px;
        display: inline-block;
        background: #F1F5F9;
        color: #475569;
        white-space: nowrap;
    }

    .btn-action {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 6px;
        padding: 6px 10px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
        text-decoration: none;
        color: inherit;
    }

    .btn-action:hover {
        background: #F1F5F9;
        border-color: #CBD5E1;
        transform: scale(1.05);
    }

    .action-group {
        display: flex;
        gap: 6px;
        justify-content: center;
        align-items: center;
    }

    .skor-value {
        font-weight: 700;
        font-size: 0.9rem;
    }

    .skor-value.high {
        color: #16A34A;
    }

    .skor-value.medium {
        color: #D97706;
    }

    .skor-value.low {
        color: #DC2626;
    }

    .skor-label {
        font-size: 0.72rem;
        color: #64748B;
        margin-top: 2px;
    }

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

    .btn-pagination {
        background: #FFFFFF;
        border: 1px solid #CBD5E1;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-pagination:hover:not(:disabled) {
        background: #273272;
        color: #FFFFFF;
        border-color: #273272;
    }

    .btn-pagination.active {
        background: #273272;
        color: #FFFFFF;
        border-color: #273272;
    }

    .btn-pagination:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state-icon {
        font-size: 4rem;
        opacity: 0.3;
        margin-bottom: 16px;
    }

    .empty-state-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1E293B;
        margin-bottom: 8px;
    }

    .empty-state-desc {
        font-size: 0.9rem;
        color: #64748B;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .empty-state-criteria {
        background: #EFF6FF;
        border: 1px solid #BFDBFE;
        border-radius: 8px;
        padding: 16px 20px;
        margin-top: 20px;
        text-align: left;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
    }

    .empty-state-criteria strong {
        color: #1E40AF;
    }

    .empty-state-criteria ol {
        margin: 8px 0 0 0;
        padding-left: 20px;
        color: #1E40AF;
    }

    .empty-state-criteria li {
        margin-bottom: 4px;
    }
</style>

<div class="container-fluid py-4">
    <!-- Statistik Card (3 Cards) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">
                    <span style="font-size: 1.2rem;">🗂️</span>
                    Arsip Periode Saat Ini
                </div>
                <div class="stats-number"><?= (int) $card1Total ?></div>
                <div class="stats-sub"><?= esc((string) $periodeAktifNama) ?></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">
                    <span style="font-size: 1.2rem;">️</span>
                    Total Arsip
                </div>
                <div class="stats-number"><?= (int) $card3Total ?></div>
                <div class="stats-sub">Semua periode tercatat</div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card">
                <div class="stats-label">
                    <span style="font-size: 1.2rem;">📅</span>
                    Total Periode
                </div>
                <div class="stats-number"><?= (int) $card4Total ?></div>
                <div class="stats-sub">Periode sejak sistem digunakan</div>
            </div>
        </div>
    </div>

    <!-- Filter Bar (Search + 3 Dropdown + Reset) -->
    <div class="filter-bar mb-4">
        <form method="GET" action="/pimpinan/arsip" id="filterForm" class="row g-2 align-items-end">
            <div class="col-lg-3">
                <label class="filter-label mb-1">🔍 Cari Arsip:</label>
                <input type="text" name="search" class="filter-input" placeholder="Ketik judul, auditee, framework, periode, atau skor..." id="searchInput">
            </div>
            <div class="col-lg-2">
                <label class="filter-label mb-1">Periode:</label>
                <select name="periode" class="filter-select" id="filterPeriode">
                    <option value="">Semua Periode</option>
                    <?php foreach ($periodes as $p): ?>
                        <option value="<?= (int) $p->id ?>" <?= (string) $filterPeriode === (string) $p->id ? 'selected' : '' ?>>
                            <?= esc((string) $p->nama_periode) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="filter-label mb-1">Framework:</label>
                <select name="framework" class="filter-select" id="filterFramework">
                    <option value="">Semua Framework</option>
                    <?php foreach ($frameworks as $fw): ?>
                        <option value="<?= esc((string) $fw->framework) ?>" <?= (string) $filterFramework === (string) $fw->framework ? 'selected' : '' ?>>
                            <?= esc((string) $fw->framework) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2">
                <label class="filter-label mb-1">Auditee:</label>
                <select name="auditee" class="filter-select" id="filterAuditee">
                    <option value="">Semua Unit</option>
                    <?php foreach ($auditees as $aud): ?>
                        <option value="<?= (int) $aud->id ?>" <?= (string) $filterAuditee === (string) $aud->id ? 'selected' : '' ?>>
                            <?= esc((string) $aud->fullname) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-3">
                <a href="/pimpinan/arsip" class="btn-reset-filter <?= (!empty($filterPeriode) || !empty($filterFramework) || !empty($filterAuditee)) ? 'active' : '' ?>">
                    🔄 Reset Filter
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Arsip -->
    <div class="table-card">
        <div class="table-header">
            <h5 class="table-title">Daftar Dokumen Laporan Hasil Audit (LHA) Resmi</h5>
            <button type="button" class="btn-print" onclick="window.print()">🖨️ Cetak Rekap</button>
        </div>

        <div class="table-responsive">
            <table class="arsip-table">
                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th class="col-judul">Judul Audit</th>
                        <th class="col-periode">Periode</th>
                        <th class="col-auditee">Auditee</th>
                        <th class="col-framework">Framework</th>
                        <th class="col-skor">Skor Akhir</th>
                        <th class="col-selesai">Selesai Pada</th>
                        <th class="col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody id="arsipTableBody">
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

    <!-- Info Banner -->
    <div class="alert alert-info mt-3" role="alert" style="background: #EFF6FF; border: 1px solid #BFDBFE; color: #1E40AF; border-radius: 8px; padding: 12px 16px;">
        <strong>ℹ️ Kriteria Arsip:</strong> Halaman ini hanya menampilkan audit yang:
        <ol class="mb-0 mt-2" style="padding-left: 20px;">
            <li>Status audit sudah <strong>Selesai</strong></li>
            <li><strong>Semua temuan</strong> dalam audit tersebut berstatus <strong>Closed</strong> (100% selesai)</li>
            <li><strong>Atau audit tidak memiliki temuan</strong> (langsung masuk arsip karena tidak ada yang perlu diperbaiki)</li>
        </ol>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data dari server
        const arsipData = <?= json_encode($arsipList, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        // Pagination settings
        const perPage = 10;
        let currentPage = 1;
        let filteredData = [...arsipData];

        // DOM Elements
        const tableBody = document.getElementById('arsipTableBody');
        const paginationInfo = document.getElementById('paginationInfo');
        const paginationBtns = document.getElementById('paginationBtns');
        const searchInput = document.getElementById('searchInput');
        const filterPeriode = document.getElementById('filterPeriode');
        const filterFramework = document.getElementById('filterFramework');
        const filterAuditee = document.getElementById('filterAuditee');

        // Render tabel
        function renderTable() {
            const start = (currentPage - 1) * perPage;
            const end = start + perPage;
            const pageData = filteredData.slice(start, end);

            tableBody.innerHTML = '';

            if (pageData.length === 0) {
                // Tampilkan pesan empty state yang informatif
                tableBody.innerHTML = `
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon">📭</div>
                            <div class="empty-state-title">Tidak Ada Data Arsip yang Sesuai</div>
                            <div class="empty-state-desc">
                                Data audit yang Anda cari tidak tersedia di halaman arsip karena belum memenuhi kriteria dokumen resmi.
                            </div>
                            <div class="empty-state-criteria">
                                <strong> Kriteria Dokumen Arsip:</strong>
                                <ol>
                                    <li>Status audit sudah <strong>Selesai</strong></li>
                                    <li><strong>Semua temuan</strong> dalam audit tersebut berstatus <strong>Closed</strong> (100% selesai)</li>
                                    <li><strong>Atau audit tidak memiliki temuan</strong> (langsung masuk arsip karena tidak ada yang perlu diperbaiki)</li>
                                </ol>
                                <p class="mb-0 mt-2"><small><em>💡 Audit yang masih dalam proses atau memiliki temuan yang belum closed akan muncul di halaman Monitoring Temuan.</em></small></p>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
                paginationInfo.textContent = '';
                paginationBtns.innerHTML = '';
                return;
            }

            pageData.forEach((arsip, index) => {
                const skorClass = arsip.skor >= 3.0 ? 'high' : (arsip.skor >= 1.5 ? 'medium' : 'low');
                const row = document.createElement('tr');

                // Gunakan template literal JavaScript untuk variabel arsip
                row.innerHTML = `
                <td class="col-no">${start + index + 1}</td>
                <td class="col-judul">${arsip.judul}</td>
                <td class="col-periode">${arsip.periode}</td>
                <td class="col-auditee" title="${arsip.auditee}">${arsip.auditee}</td>
                <td class="col-framework"><span class="badge-framework">${arsip.framework}</span></td>
                <td class="col-skor">
                    <div class="skor-value ${skorClass}">${arsip.skor.toFixed(2)} / 5.0</div>
                    <div class="skor-label">${arsip.skor_label}</div>
                </td>
                <td class="col-selesai">${arsip.tanggal}</td>
                <td class="col-aksi">
                    <div class="action-group">
                        <a href="/pimpinan/lha/detail/${arsip.id}" class="btn-action" title="Lihat Detail Audit">👁️</a>
                        <button class="btn-action" title="Cetak LHA" onclick="window.print()">🖨️</button>
                    </div>
                </td>
            `;
                tableBody.appendChild(row);
            });

            renderPagination();
        }

        // Render pagination
        function renderPagination() {
            const totalPages = Math.ceil(filteredData.length / perPage);
            const start = (currentPage - 1) * perPage + 1;
            const end = Math.min(currentPage * perPage, filteredData.length);

            paginationInfo.textContent = `Menampilkan ${start}-${end} dari ${filteredData.length} arsip`;
            paginationBtns.innerHTML = '';

            // Prev button
            const prevBtn = document.createElement('button');
            prevBtn.textContent = '← Prev';
            prevBtn.className = 'btn-pagination';
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
                btn.className = 'btn-pagination' + (i === currentPage ? ' active' : '');
                btn.onclick = () => {
                    currentPage = i;
                    renderTable();
                };
                paginationBtns.appendChild(btn);
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.textContent = 'Next →';
            nextBtn.className = 'btn-pagination';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                }
            };
            paginationBtns.appendChild(nextBtn);
        }

        // Apply filters dengan search di semua kolom
        function applyFilters() {
            const searchKeyword = searchInput.value.toLowerCase().trim();
            const periodeValue = filterPeriode.value;
            const frameworkValue = filterFramework.value;
            const auditeeValue = filterAuditee.value;

            filteredData = arsipData.filter(arsip => {
                // Search filter (client-side) - SEARCH DI SEMUA KOLOM
                const matchSearch = searchKeyword === '' ||
                    arsip.judul.toLowerCase().includes(searchKeyword) ||
                    arsip.auditee.toLowerCase().includes(searchKeyword) ||
                    arsip.framework.toLowerCase().includes(searchKeyword) ||
                    arsip.periode.toLowerCase().includes(searchKeyword) ||
                    arsip.skor.toFixed(2).includes(searchKeyword) ||
                    arsip.skor_label.toLowerCase().includes(searchKeyword) ||
                    arsip.tanggal.toLowerCase().includes(searchKeyword);

                // Dropdown filters (server-side applied, tapi kita filter lagi untuk consistency)
                const matchPeriode = periodeValue === '' || arsip.periode_id == periodeValue;
                const matchFramework = frameworkValue === '' || arsip.framework === frameworkValue;
                const matchAuditee = auditeeValue === '' || arsip.auditee_id == auditeeValue;

                return matchSearch && matchPeriode && matchFramework && matchAuditee;
            });

            currentPage = 1;
            renderTable();
        }

        // Search input (client-side instant dengan debounce)
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });

        // Dropdown filters (server-side - reload page)
        filterPeriode.addEventListener('change', function() {
            this.form.submit();
        });

        filterFramework.addEventListener('change', function() {
            this.form.submit();
        });

        filterAuditee.addEventListener('change', function() {
            this.form.submit();
        });

        // Initial render
        renderTable();
    });
</script>

<?= $this->endSection() ?>