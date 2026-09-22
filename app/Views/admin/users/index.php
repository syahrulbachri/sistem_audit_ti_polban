<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0" style="color: var(--primary-navy);"><?= $page_title ?></h4>
    <a href="/admin/users/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-2"></i>Tambah User Baru
    </a>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th>Role</th>
                        <th>Dibuat Pada</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($users)): ?>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><?= $u->id ?></td>
                            <td><strong><?= esc($u->username) ?></strong></td>
                            <td><?= esc($u->fullname) ?></td>
                            <td>
                                <?php 
                                $roleBadge = match($u->role) {
                                    'admin' => 'bg-dark',
                                    'auditor' => 'bg-primary',
                                    'auditee' => 'bg-info text-dark',
                                    'pimpinan' => 'bg-warning text-dark',
                                    default => 'bg-secondary'
                                };
                                ?>
                                <span class="badge <?= $roleBadge ?> rounded-pill px-3"><?= ucfirst($u->role) ?></span>
                            </td>
                            <td><?= date('d M Y', strtotime($u->created_at)) ?></td>
                            <td class="text-center">
                                <a href="/admin/users/edit/<?= $u->id ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="/admin/users/delete/<?= $u->id ?>" class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Yakin ingin menghapus user ini?')" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>Belum ada data pengguna
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>