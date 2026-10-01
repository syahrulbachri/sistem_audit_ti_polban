<div class="dropdown">
    <button class="btn btn-light position-relative border-0" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell fs-5 text-secondary"></i>
        <?php if (count($periodes) > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                <?= count($periodes) ?>
            </span>
        <?php endif; ?>
    </button>
    
    <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="notificationDropdown" style="width: 320px; max-height: 400px; overflow-y: auto;">
        <li><h6 class="dropdown-header bg-light py-2 fw-bold text-dark">Notifikasi Periode</h6></li>
        
        <?php if (count($periodes) > 0): ?>
            <?php foreach ($periodes as $p): ?>
                <li>
                    <a class="dropdown-item py-3 border-bottom" href="#" onclick="konfirmasiAktifkan(<?= $p->id ?>, '<?= esc($p->nama_periode) ?>')">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-calendar-check text-warning me-3 fs-4 mt-1"></i>
                            <div>
                                <div class="fw-bold text-dark small"><?= esc($p->nama_periode) ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock me-1"></i>Mulai: <?= date('d M Y', strtotime($p->tanggal_mulai)) ?>
                                </div>
                                <div class="mt-2">
                                    <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">Klik untuk Aktifkan</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>
                <div class="dropdown-item-text text-muted text-center py-4">
                    <i class="bi bi-check-circle fs-1 d-block mb-2 text-success opacity-50"></i>
                    <small>Tidak ada notifikasi periode</small>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</div>

<!-- Script SweetAlert2 untuk Konfirmasi (Pastikan sudah ada di main.php atau tambahkan di sini) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function konfirmasiAktifkan(id, nama) {
        Swal.fire({
            title: 'Aktifkan Periode?',
            html: `Apakah Anda yakin ingin mengaktifkan periode <strong>"${nama}"</strong>?<br><small class="text-muted">Auditor akan dapat mulai membuat rencana audit baru.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f57e20', /* Warna accent-orange Anda */
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Ya, Aktifkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Sedang mengaktifkan periode',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                // Arahkan ke route aktivasi yang sudah kita buat sebelumnya
                window.location.href = `/admin/periodes/aktifkan/${id}`;
            }
        });
    }
</script>