<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-4" style="color: var(--primary-navy);">Riwayat Audit Selesai</h5>

        <?php if (!empty($audits)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Judul Audit</th>
                            <th width="15%">Framework</th>
                            <th width="15%">Auditor</th>
                            <th width="15%">Nilai Akhir</th>
                            <th width="15%">Tanggal Selesai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audits as $i => $audit): ?>
                            <tr>
                                <td class="fw-bold text-muted">
                                    <?= $i + 1 ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <?= esc($audit->title) ?>
                                    </div>
                                    <small class="text-muted">ID: #
                                        <?= str_pad($audit->id, 4, '0', STR_PAD_LEFT) ?>
                                    </small>
                                </td>
                                <td><span class="badge bg-secondary rounded-pill">
                                        <?= esc($audit->framework) ?>
                                    </span></td>
                                <td>
                                    <?= esc($audit->auditor_name ?? '-') ?>
                                </td>
                                <td>
                                    <?php if ($audit->final_score !== null): ?>
                                        <?php
                                        $scoreClass = $audit->final_score >= 80 ? 'bg-success' :
                                            ($audit->final_score >= 60 ? 'bg-warning text-dark' : 'bg-danger');
                                        ?>
                                        <span class="badge <?= $scoreClass ?> rounded-pill px-3 py-2">
                                            <?= number_format($audit->final_score, 1) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $audit->updated_at ? date('d M Y', strtotime($audit->updated_at)) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <p class="mb-0">Belum ada audit yang selesai.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>