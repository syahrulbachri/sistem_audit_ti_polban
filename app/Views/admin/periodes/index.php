<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .badge-status {
        cursor: pointer;
        transition: all 0.2s;
        padding: 6px 14px;
        font-size: 0.85rem;
    }
    .badge-status:hover {
        transform: scale(1.05);
        opacity: 0.9;
    }
    .pagination .page-link {
        color: var(--primary-navy);
        border-radius: 6px !important;
        margin: 0 2px;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--primary-navy);
        border-color: var(--primary-navy);
        color: white;
    }
    .pagination .page-link:hover {
        background-color: var(--primary-navy);
        border-color: var(--primary-navy);
        color: white;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h4 class="fw-bold mb-0" style="color: var(--primary-navy);"><?= $page_title ?></h4>
    <a href="/admin/periodes/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Tambah Periode
    </a>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">ID</th>
                        <th>Nama Periode</th>
                        <th width="10%">Tahun</th>
                        <th width="15%">Tanggal Mulai</th>
                        <th width="15%">Tanggal Selesai</th>
                        <th width="12%">Status</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($periodes)): ?>
                        <?php foreach($periodes as $p): ?>
                        <tr>
                            <td><strong><?= $p->id ?></strong></td>
                            <td>
                                <strong><?= esc($p->nama_periode) ?></strong><br>
                                <small class="text-muted">
                                    <?= date('d M Y', strtotime($p->tanggal_mulai)) ?> - <?= date('d M Y', strtotime($p->tanggal_selesai)) ?>
                                </small>
                            </td>
                            <td><?= $p->tahun ?></td>
                            <td><?= date('d M Y', strtotime($p->tanggal_mulai)) ?></td>
                            <td><?= date('d M Y', strtotime($p->tanggal_selesai)) ?></td>
                            <td>
                                <a href="/admin/periodes/toggle/<?= $p->id ?>" 
                                   class="badge badge-status text-decoration-none rounded-pill <?= $p->status === 'open' ? 'bg-success' : 'bg-secondary' ?>"
                                   title="Klik untuk toggle status">
                                    <?= $p->status === 'open' ? '🟢 Open' : '⚫ Closed' ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="/admin/periodes/edit/<?= $p->id ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/periodes/delete/<?= $p->id ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Yakin ingin menghapus periode ini?')" 
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                                Belum ada periode audit
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <div class="card-footer bg-white border-0 p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <small class="text-muted">
                Menampilkan <?= count($periodes) ?> dari <?= $total ?> periode
            </small>
            
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $baseUrl = '/admin/periodes';
                    
                    // Previous
                    if($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage - 1 ?>">
                                <i class="bi bi-chevron-left"></i> Prev
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="bi bi-chevron-left"></i> Prev</span>
                        </li>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);
                    
                    if($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl ?>?page=1">1</a>
                        </li>
                        <?php if($startPage > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl ?>?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if($endPage < $totalPages): ?>
                        <?php if($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl ?>?page=<?= $totalPages ?>"><?= $totalPages ?></a>
                        </li>
                    <?php endif; ?>

                    <?php if($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= $baseUrl ?>?page=<?= $currentPage + 1 ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Next <i class="bi bi-chevron-right"></i></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>