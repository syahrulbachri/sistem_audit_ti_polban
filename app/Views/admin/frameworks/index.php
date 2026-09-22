<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <h4 class="fw-bold mb-0" style="color: var(--primary-navy);"><?= $page_title ?></h4>
    <a href="/admin/frameworks/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Tambah Framework Baru
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
                        <th width="25%">Nama Framework</th>
                        <th width="30%">Deskripsi</th>
                        <th width="15%">Tipe Penilaian</th>
                        <th width="10%">Max Skor</th>
                        <th width="10%">Status</th>
                        <th width="15%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($frameworks)): ?>
                        <?php foreach($frameworks as $fw): ?>
                        <tr>
                            <td><strong><?= $fw->id ?></strong></td>
                            <td>
                                <strong><?= esc($fw->nama) ?></strong><br>
                                <small class="text-muted">Dibuat: <?= date('d M Y', strtotime($fw->created_at)) ?></small>
                            </td>
                            <td><small class="text-muted"><?= esc($fw->deskripsi ?? '-') ?></small></td>
                                                        <td>
                                <?php if($fw->scoring_type === 'binary'): ?>
                                    <span class="badge bg-success rounded-pill">Biner (0/1)</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark rounded-pill">
                                        Skala (<?= $fw->start_score ?>-<?= $fw->max_score ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-bold"><?= $fw->max_score ?></td>
                            <td>
                                <a href="/admin/frameworks/toggle/<?= $fw->id ?>" 
                                   class="badge text-decoration-none rounded-pill <?= $fw->is_active ? 'bg-success' : 'bg-secondary' ?>"
                                   title="Klik untuk toggle status">
                                    <?= $fw->is_active ? '🟢 Aktif' : '⚫ Nonaktif' ?>
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="/admin/frameworks/edit/<?= $fw->id ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2"></i>
                                Belum ada standar framework
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>