<?php $today = date('Y-m-d'); ?>

<style>
    /* Panel Notifikasi ala YouTube */
    .notif-panel {
        width: 420px;
        max-height: 75vh;
        min-height: 350px;
        overflow-y: auto;
        padding: 0 !important;
        border-radius: 12px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15) !important;
    }
    .notif-panel::-webkit-scrollbar { width: 6px; }
    .notif-panel::-webkit-scrollbar-thumb { background: #ccc; border-radius: 3px; }
    
    .notif-header {
        padding: 12px 20px;
        border-bottom: 1px solid #e5e5e5;
        position: sticky; top: 0; background: white; z-index: 20;
    }
    
    /* Filter Menu - Menggunakan Toggle Manual (Bukan Bootstrap Dropdown) */
    .notif-filter-container { margin-top: 8px; position: relative; }
    .notif-filter-btn {
        width: 100%; padding: 6px 12px; border: 1px solid #dadce0; border-radius: 8px;
        background: white; color: #3c4043; font-size: 0.85rem; font-weight: 500;
        cursor: pointer; display: flex; justify-content: space-between; align-items: center; transition: all 0.2s;
    }
    .notif-filter-btn:hover { background: #f8f9fa; border-color: #065fd4; }
    .notif-filter-btn.active { border-color: #065fd4; background: #e8f0fe; }
    
    .notif-filter-menu {
        display: none; /* Hidden by default */
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 4px;
        border: 1px solid #dadce0;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-height: 250px;
        overflow-y: auto;
        background: white;
        z-index: 30;
    }
    .notif-filter-menu.show {
        display: block; /* Show when active */
    }
    
    .notif-filter-item {
        padding: 8px 16px; cursor: pointer; display: flex; justify-content: space-between;
        align-items: center; transition: background 0.2s;
    }
    .notif-filter-item:hover { background: #f8f9fa; }
    .notif-filter-item.active { background: #e8f0fe; color: #065fd4; font-weight: 600; }
    .notif-filter-count {
        background: #e8eaed; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem;
    }
    .notif-filter-item.active .notif-filter-count { background: #065fd4; color: white; }
    
    .notif-section-title {
        padding: 10px 20px 6px; font-size: 0.75rem; font-weight: 700; color: #606060;
        text-transform: uppercase; letter-spacing: 0.5px; background: #f9f9f9; border-bottom: 1px solid #f0f0f0;
    }
    .notif-item { padding: 14px 20px; border-bottom: 1px solid #f0f0f0; transition: background 0.2s; }
    .notif-item:hover { background: #f8f9fa; }
    .notif-item:last-child { border-bottom: none; }
    
    .notif-icon {
        width: 36px; height: 36px; border-radius: 50%; display: flex;
        align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;
    }
    .notif-content { flex-grow: 1; min-width: 0; }
    .notif-title { font-weight: 600; font-size: 0.85rem; color: #0f0f0f; margin-bottom: 4px; line-height: 1.3; }
    .notif-subtitle { font-size: 0.75rem; color: #606060; line-height: 1.3; }
    .notif-actions { display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap; }
    .notif-btn {
        font-size: 0.7rem; padding: 4px 10px; border-radius: 20px; border: 1px solid #dadce0;
        background: white; color: #065fd4; font-weight: 500; cursor: pointer; transition: all 0.2s;
        text-decoration: none; display: inline-flex; align-items: center;
    }
    .notif-btn:hover { background: #065fd4; color: white; border-color: #065fd4; }
    .notif-btn-danger { color: #c00; border-color: #c00; }
    .notif-btn-danger:hover { background: #c00; color: white; }
    
    .notif-empty { padding: 40px 20px; text-align: center; color: #606060; }
    .notif-empty i { font-size: 2.5rem; color: #ccc; margin-bottom: 12px; }
    
    .notif-item[data-category] { display: block; }
    .notif-item.hidden { display: none !important; }
    .notif-section-title.hidden { display: none !important; }
    
    .filter-empty-msg {
        display: none;
        padding: 40px 20px;
        text-align: center;
        color: #606060;
    }
    .filter-empty-msg.show {
        display: block;
    }
</style>

<div class="dropdown">
    <button class="btn btn-light position-relative border-0" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell fs-5 text-secondary"></i>
        <?php if ($totalNotif > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                <?= $totalNotif ?>
            </span>
        <?php endif; ?>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end notif-panel" id="notifPanel" aria-labelledby="notificationDropdown">
        <li class="notif-header">
            <h6 class="fw-bold mb-2">Notifikasi</h6>
            
            <!-- Filter Container - Menggunakan Toggle Manual -->
            <div class="notif-filter-container">
                <button class="notif-filter-btn" type="button" id="filterToggleBtn">
                    <span id="filterLabel"><i class="bi bi-funnel me-2"></i>Filter: Semua</span>
                    <i class="bi bi-chevron-down small" id="filterChevron"></i>
                </button>
                <div class="notif-filter-menu" id="filterMenu">
                    <div class="notif-filter-item active" data-filter="all" onclick="setFilter('all', 'Semua', event)">
                        <span>Semua Notifikasi</span><span class="notif-filter-count"><?= $totalNotif ?></span>
                    </div>
                    
                    <?php if ($role === 'admin'): ?>
                        <?php if ($filterData['periode'] > 0): ?>
                        <div class="notif-filter-item" data-filter="periode" onclick="setFilter('periode', 'Periode', event)">
                            <span>Periode Audit</span><span class="notif-filter-count"><?= $filterData['periode'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($filterData['overdue'] > 0): ?>
                        <div class="notif-filter-item" data-filter="overdue" onclick="setFilter('overdue', 'Overdue', event)">
                            <span>Melebihi Deadline</span><span class="notif-filter-count"><?= $filterData['overdue'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($filterData['h3'] > 0): ?>
                        <div class="notif-filter-item" data-filter="h3" onclick="setFilter('h3', 'H-3 Warning', event)">
                            <span>Mendekati Deadline</span><span class="notif-filter-count"><?= $filterData['h3'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($filterData['selesai'] > 0): ?>
                        <div class="notif-filter-item" data-filter="selesai" onclick="setFilter('selesai', 'Audit Selesai', event)">
                            <span>Audit Baru Selesai</span><span class="notif-filter-count"><?= $filterData['selesai'] ?></span>
                        </div>
                        <?php endif; ?>
                        
                    <?php elseif ($role === 'auditor'): ?>
                        <?php if ($filterData['audit_baru'] > 0): ?>
                        <div class="notif-filter-item" data-filter="audit_baru" onclick="setFilter('audit_baru', 'Audit Baru', event)">
                            <span>Audit Baru</span><span class="notif-filter-count"><?= $filterData['audit_baru'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($filterData['rtl'] > 0): ?>
                        <div class="notif-filter-item" data-filter="rtl" onclick="setFilter('rtl', 'RTL Review', event)">
                            <span>RTL Perlu Review</span><span class="notif-filter-count"><?= $filterData['rtl'] ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($filterData['selesai'] > 0): ?>
                        <div class="notif-filter-item" data-filter="selesai" onclick="setFilter('selesai', 'Audit Selesai', event)">
                            <span>Audit Selesai</span><span class="notif-filter-count"><?= $filterData['selesai'] ?></span>
                        </div>
                        <?php endif; ?>
                        
                    <?php elseif ($role === 'auditee'): ?>
                        <?php if ($filterData['audit_baru'] > 0): ?>
                        <div class="notif-filter-item" data-filter="audit_baru" onclick="setFilter('audit_baru', 'Tugas Baru', event)">
                            <span>Tugas Audit Baru</span><span class="notif-filter-count"><?= $filterData['audit_baru'] ?></span>
                        </div>
                        <?php endif; ?>
                        
                    <?php elseif ($role === 'pimpinan'): ?>
                        <?php if ($filterData['selesai'] > 0): ?>
                        <div class="notif-filter-item" data-filter="selesai" onclick="setFilter('selesai', 'LHA Baru', event)">
                            <span>Laporan Hasil Audit</span><span class="notif-filter-count"><?= $filterData['selesai'] ?></span>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </li>
        
        <li class="filter-empty-msg" id="filterEmptyMsg">
            <i class="bi bi-search d-block mb-2" style="font-size: 2rem; color: #ccc;"></i>
            <small>Tidak ada notifikasi untuk filter ini</small>
        </li>

        <?php if ($totalNotif == 0): ?>
            <li>
                <div class="notif-empty">
                    <i class="bi bi-bell-slash d-block"></i>
                    <small>Tidak ada notifikasi baru</small>
                </div>
            </li>
        <?php else: ?>
            
            <!-- === SECTION KHUSUS ADMIN === -->
            <?php if ($role === 'admin'): ?>
                <?php if (!empty($periodeDraft)): ?>
                    <li class="notif-section-title" data-category="periode">Periode Audit</li>
                    <?php foreach ($periodeDraft as $p): ?>
                        <li class="notif-item" data-category="periode">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #fff3cd; color: #856404;"><i class="bi bi-calendar-check"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($p->nama_periode) ?></div>
                                    <div class="notif-subtitle"><i class="bi bi-clock me-1"></i>Mulai: <?= date('d M Y', strtotime($p->tanggal_mulai)) ?></div>
                                    <div class="notif-actions">
                                        <button class="notif-btn" onclick="konfirmasiAktifkan(<?= $p->id ?>, '<?= esc($p->nama_periode) ?>')">
                                            <i class="bi bi-power me-1"></i>Aktifkan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($auditOverdue)): ?>
                    <li class="notif-section-title" data-category="overdue">Audit Melebihi Deadline</li>
                    <?php foreach ($auditOverdue as $a): 
                        $selisih = (strtotime($today) - strtotime($a->deadline)) / (60*60*24);
                        $hariText = $selisih == 1 ? '1 hari' : floor($selisih) . ' hari';
                    ?>
                        <li class="notif-item" data-category="overdue">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #f8d7da; color: #721c24;"><i class="bi bi-exclamation-triangle"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($a->title) ?></div>
                                    <div class="notif-subtitle"><i class="bi bi-building me-1"></i><?= esc($a->auditee_name ?? 'Auditee') ?> • Melebihi <?= $hariText ?></div>
                                    <div class="notif-actions">
                                        <a href="/admin/planning/detail/<?= $a->id ?>" class="notif-btn"><i class="bi bi-eye me-1"></i>Detail</a>
                                        <button class="notif-btn notif-btn-danger" onclick="perpanjangDeadline(<?= $a->id ?>, '<?= esc($a->title) ?>', '<?= $a->deadline ?>')">
                                            <i class="bi bi-clock-history me-1"></i>Perpanjang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($auditH3)): ?>
                    <li class="notif-section-title" data-category="h3">Audit Mendekati Deadline (H-3)</li>
                    <?php foreach ($auditH3 as $a): 
                        $selisih = (strtotime($a->deadline) - strtotime($today)) / (60*60*24);
                        $hariText = $selisih == 1 ? '1 hari lagi' : ceil($selisih) . ' hari lagi';
                    ?>
                        <li class="notif-item" data-category="h3">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #fff3cd; color: #856404;"><i class="bi bi-hourglass-split"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($a->title) ?></div>
                                    <div class="notif-subtitle"><i class="bi bi-building me-1"></i><?= esc($a->auditee_name ?? 'Auditee') ?> • Deadline <?= $hariText ?></div>
                                    <div class="notif-actions">
                                        <a href="/admin/planning/detail/<?= $a->id ?>" class="notif-btn"><i class="bi bi-eye me-1"></i>Detail</a>
                                        <button class="notif-btn notif-btn-danger" onclick="perpanjangDeadline(<?= $a->id ?>, '<?= esc($a->title) ?>', '<?= $a->deadline ?>')">
                                            <i class="bi bi-clock-history me-1"></i>Perpanjang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (!empty($auditSelesai)): ?>
                    <li class="notif-section-title" data-category="selesai">Audit Baru Selesai</li>
                    <?php foreach ($auditSelesai as $a): ?>
                        <li class="notif-item" data-category="selesai">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #d4edda; color: #155724;"><i class="bi bi-file-earmark-check"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($a->title) ?></div>
                                    <div class="notif-subtitle">Periode: <?= esc($a->nama_periode) ?> • Selesai: <?= date('d M Y', strtotime($a->updated_at)) ?></div>
                                    <div class="notif-actions">
                                        <a href="/admin/completed-audits/detail/<?= $a->id ?>" class="notif-btn"><i class="bi bi-eye me-1"></i>Tinjau LHA</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

            <!-- === SECTION KHUSUS AUDITOR === -->
            <?php if ($role === 'auditor'): ?>
                <?php if (!empty($auditBaru)): ?>
                    <li class="notif-section-title" data-category="audit_baru">Audit Baru Ditugaskan</li>
                    <?php foreach ($auditBaru as $a): ?>
                        <li class="notif-item" data-category="audit_baru">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #d1ecf1; color: #0c5460;"><i class="bi bi-clipboard-check"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($a->title) ?></div>
                                    <div class="notif-subtitle">Periode: <?= esc($a->nama_periode) ?></div>
                                    <div class="notif-actions">
                                        <a href="/auditor/list" class="notif-btn"><i class="bi bi-eye me-1"></i>Lihat Daftar</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (!empty($rtlPerluReview)): ?>
                    <li class="notif-section-title" data-category="rtl">RTL Perlu Diverifikasi</li>
                    <?php foreach ($rtlPerluReview as $t): ?>
                        <li class="notif-item" data-category="rtl">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #fff3cd; color: #856404;"><i class="bi bi-arrow-repeat"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($t->audit_title) ?></div>
                                    <div class="notif-subtitle">Klausul: <?= esc($t->clause_code) ?> • Auditee: <?= esc($t->auditee_name) ?></div>
                                    <div class="notif-actions">
                                        <a href="/auditor/monitoring-temuan" class="notif-btn"><i class="bi bi-search me-1"></i>Verifikasi</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (!empty($auditSelesai)): ?>
                    <li class="notif-section-title" data-category="selesai">Audit Selesai</li>
                    <?php foreach ($auditSelesai as $a): ?>
                        <li class="notif-item" data-category="selesai">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #d4edda; color: #155724;"><i class="bi bi-check-circle"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($a->title) ?></div>
                                    <div class="notif-subtitle">Periode: <?= esc($a->nama_periode) ?> • Selesai: <?= date('d M Y', strtotime($a->updated_at)) ?></div>
                                    <div class="notif-actions">
                                        <a href="/auditor/riwayat" class="notif-btn"><i class="bi bi-eye me-1"></i>Lihat Laporan</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

            <!-- === SECTION KHUSUS AUDITEE === -->
            <?php if ($role === 'auditee'): ?>
                <?php if (!empty($auditBaru)): ?>
                    <li class="notif-section-title" data-category="audit_baru">Tugas Audit Baru</li>
                    <?php foreach ($auditBaru as $a): ?>
                        <li class="notif-item" data-category="audit_baru">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #d1ecf1; color: #0c5460;"><i class="bi bi-inbox"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($a->title) ?></div>
                                    <div class="notif-subtitle">Periode: <?= esc($a->nama_periode) ?> • Deadline: <?= date('d M Y', strtotime($a->deadline)) ?></div>
                                    <div class="notif-actions">
                                        <a href="/auditee/daftar-audit" class="notif-btn"><i class="bi bi-pencil me-1"></i>Kerjakan</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

            <!-- === SECTION KHUSUS PIMPINAN === -->
            <?php if ($role === 'pimpinan'): ?>
                <?php if (!empty($lhaSelesai)): ?>
                    <li class="notif-section-title" data-category="selesai">Laporan Hasil Audit (LHA) Baru</li>
                    <?php foreach ($lhaSelesai as $a): ?>
                        <li class="notif-item" data-category="selesai">
                            <div class="d-flex align-items-start gap-3">
                                <div class="notif-icon" style="background: #d4edda; color: #155724;"><i class="bi bi-file-earmark-check"></i></div>
                                <div class="notif-content">
                                    <div class="notif-title"><?= esc($a->title) ?></div>
                                    <div class="notif-subtitle">Periode: <?= esc($a->nama_periode) ?> • Selesai: <?= date('d M Y', strtotime($a->updated_at)) ?></div>
                                    <div class="notif-actions">
                                        <a href="/pimpinan/lha" class="notif-btn"><i class="bi bi-eye me-1"></i>Tinjau LHA</a>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>

        <?php endif; ?>
    </ul>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Toggle Filter Menu (Manual, tanpa Bootstrap Dropdown)
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const filterMenu = document.getElementById('filterMenu');
    const filterChevron = document.getElementById('filterChevron');
    
    filterToggleBtn.addEventListener('click', function(event) {
        event.stopPropagation(); // Mencegah event merambat ke parent dropdown
        filterMenu.classList.toggle('show');
        filterToggleBtn.classList.toggle('active');
        filterChevron.style.transform = filterMenu.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0)';
    });
    
    // Tutup filter menu ketika klik di luar
    document.addEventListener('click', function(event) {
        if (!filterToggleBtn.contains(event.target) && !filterMenu.contains(event.target)) {
            filterMenu.classList.remove('show');
            filterToggleBtn.classList.remove('active');
            filterChevron.style.transform = 'rotate(0)';
        }
    });

    // Mencegah popup notifikasi menutup saat diklik di area panel
    document.getElementById('notifPanel').addEventListener('click', function(event) {
        event.stopPropagation();
    });

    // Fungsi Filter
    let currentFilter = 'all';
    
    function setFilter(filterType, filterName, event) {
        event.stopPropagation();
        
        currentFilter = filterType;
        document.getElementById('filterLabel').innerHTML = `<i class="bi bi-funnel me-2"></i>Filter: ${filterName}`;
        
        // Tutup filter menu setelah memilih
        filterMenu.classList.remove('show');
        filterToggleBtn.classList.remove('active');
        filterChevron.style.transform = 'rotate(0)';
        
        // Update active state di filter menu
        document.querySelectorAll('.notif-filter-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.filter === filterType) {
                item.classList.add('active');
            }
        });
        
        let visibleItemsCount = 0;

        // Filter notifikasi
        document.querySelectorAll('.notif-item').forEach(item => {
            if (filterType === 'all') {
                item.classList.remove('hidden');
                visibleItemsCount++;
            } else {
                if (item.dataset.category === filterType) {
                    item.classList.remove('hidden');
                    visibleItemsCount++;
                } else {
                    item.classList.add('hidden');
                }
            }
        });
        
        // Sembunyikan section title yang kosong
        document.querySelectorAll('.notif-section-title').forEach(title => {
            const nextItems = [];
            let next = title.nextElementSibling;
            while (next && !next.classList.contains('notif-section-title') && !next.classList.contains('filter-empty-msg')) {
                if (next.classList.contains('notif-item') && !next.classList.contains('hidden')) {
                    nextItems.push(next);
                }
                next = next.nextElementSibling;
            }
            if (nextItems.length === 0) {
                title.classList.add('hidden');
            } else {
                title.classList.remove('hidden');
            }
        });

        // Tampilkan pesan "Kosong" jika filter tidak menemukan hasil
        const emptyMsg = document.getElementById('filterEmptyMsg');
        if (visibleItemsCount === 0 && filterType !== 'all') {
            emptyMsg.classList.add('show');
        } else {
            emptyMsg.classList.remove('show');
        }
    }
    
    // Konfirmasi aktivasi periode
    function konfirmasiAktifkan(id, nama) {
        Swal.fire({
            title: 'Aktifkan Periode?',
            html: `Apakah Anda yakin ingin mengaktifkan periode <strong>"${nama}"</strong>?<br><small class="text-muted">Auditor dapat mulai membuat rencana audit baru.</small>`,
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#f57e20', cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Ya, Aktifkan!',
            cancelButtonText: 'Batal', reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                window.location.href = `/admin/periodes/aktifkan/${id}`;
            }
        });
    }
    
    // Perpanjang deadline audit
    function perpanjangDeadline(id, judul, deadlineLama) {
        Swal.fire({
            title: 'Perpanjang Deadline Audit',
            html: `
                <p class="mb-2">Audit: <strong>${judul}</strong></p>
                <p class="mb-3 text-muted" style="font-size: 0.9rem;">Deadline lama: ${deadlineLama}</p>
                <label class="form-label text-start d-block fw-bold">Tambah hari:</label>
                <select id="tambahHari" class="form-select">
                    <option value="3">+3 Hari</option>
                    <option value="7" selected>+7 Hari</option>
                    <option value="14">+14 Hari</option>
                    <option value="30">+30 Hari</option>
                </select>
            `,
            icon: 'info', showCancelButton: true,
            confirmButtonColor: '#065fd4', cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-clock-history me-1"></i> Perpanjang',
            cancelButtonText: 'Batal', reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const tambahHari = document.getElementById('tambahHari').value;
                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                
                fetch('/admin/planning/perpanjang-deadline', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ audit_id: id, tambah_hari: parseInt(tambahHari) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false })
                        .then(() => { location.reload(); });
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Terjadi kesalahan sistem', 'error');
                });
            }
        });
    }
</script>