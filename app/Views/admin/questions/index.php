<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
    .pagination .page-link {
        color: var(--primary-navy);
        border-radius: 6px !important;
        margin: 0 2px;
    }

    .pagination .page-item.active .page-link {
        background-color: var(--primary-navy);
        border-color: var(--primary-navy);
    }

    .pagination .page-link:hover {
        background-color: var(--primary-navy);
        border-color: var(--primary-navy);
        color: white;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h4 class="fw-bold mb-0" style="color: var(--primary-navy);"><?= $page_title ?></h4>
    <a href="/admin/questions/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Tambah Pertanyaan
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= session()->getFlashdata('success') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Filter & Search -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-3">
        <form method="get" action="/admin/questions" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold small">Filter Framework</label>
                <select name="framework" class="form-select">
                    <option value="">Semua Framework</option>
                    <?php if (!empty($frameworks)): ?>
                        <?php foreach ($frameworks as $fw): ?>
                            <option value="<?= esc($fw->nama) ?>"
                                <?= ($filter == $fw->nama) ? 'selected' : '' ?>>
                                <?= esc($fw->nama) ?>
                                (<?= $fw->scoring_type === 'binary' ? 'Biner' : 'Skala 0-' . $fw->max_score ?>)
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold small">Cari Pertanyaan / Kode</label>
                <input type="text" name="search" class="form-control" value="<?= esc($search) ?>" placeholder="Ketik kata kunci...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100"><i class="bi bi-search"></i> Cari</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabel -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">Framework</th>
                        <th width="15%">Kode Klausul</th>
                        <th>Pertanyaan</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($questions)): ?>
                        <?php foreach ($questions as $q):
                            // Tentukan warna badge berdasarkan scoring_type
                            $badgeColor = 'bg-secondary'; // Default jika tidak terdeteksi
                            if (isset($q->scoring_type)) {
                                if ($q->scoring_type === 'binary') {
                                    $badgeColor = 'bg-success'; // HIJAU untuk Biner (seperti ISO)
                                } else {
                                    $badgeColor = 'bg-primary'; // BIRU untuk Skala (seperti COBIT)
                                }
                            }
                        ?>
                            <tr>
                                <td>
                                    <!-- Badge Dinamis -->
                                    <span class="badge <?= $badgeColor ?> rounded-pill">
                                        <?= esc($q->framework) ?>
                                    </span>
                                </td>
                                <td><code><?= esc($q->clause_code ?? '-') ?></code></td>
                                <td><?= esc($q->question_text) ?></td>
                                <td>
                                    <a href="/admin/questions/edit/<?= $q->id ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="/admin/questions/delete/<?= $q->id ?>" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Yakin ingin menghapus pertanyaan ini?')" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Tidak ada data pertanyaan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Pagination -->
<?php if ($total > $perPage): ?>
    <div class="card-footer bg-white border-0 p-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <small class="text-muted">
                Menampilkan <?= count($questions) ?> dari <?= $total ?> pertanyaan
            </small>

            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $totalPages = ceil($total / $perPage);

                    // Previous button
                    if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage - 1])) ?>">
                                <i class="bi bi-chevron-left"></i> Prev
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="bi bi-chevron-left"></i> Prev</span>
                        </li>
                    <?php endif; ?>

                    <!-- Page numbers -->
                    <?php
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPages, $currentPage + 2);

                    if ($startPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">1</a>
                        </li>
                        <?php if ($startPage > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $totalPages])) ?>">
                                <?= $totalPages ?>
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Next button -->
                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $currentPage + 1])) ?>">
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
<?= $this->endSection() ?>

<script>
    function filterQuestions() {
        const framework = document.querySelector('select[name="framework"]').value;
        const search = document.getElementById('searchInput').value;

        let url = '/admin/questions?';
        if (framework && framework !== 'all') url += 'framework=' + encodeURIComponent(framework) + '&';
        if (search) url += 'search=' + encodeURIComponent(search);

        window.location.href = url;
    }
</script>