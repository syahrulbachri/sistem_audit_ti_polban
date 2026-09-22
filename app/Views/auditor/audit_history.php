<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Judul Audit</th>
                        <th>Periode</th>
                        <th>Auditee</th>
                        <th>Framework</th>
                        <th>Skor Akhir</th>
                        <th>Selesai Pada</th>
                        <th class="text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
    <?php if(!empty($audits)): ?>
        <?php foreach($audits as $a): 
            // Tentukan warna badge berdasarkan framework
            $fwClass = ($a->framework === 'ISO 27001') ? 'bg-success' : 'bg-info text-dark';
        ?>
        <tr>
            <td><strong><?= esc($a->title) ?></strong></td>
            <td><?= esc($a->nama_periode ?? '-') ?></td>
            <td><?= esc($a->auditee_name ?? '-') ?></td>
            <td>
                <span class="badge <?= $fwClass ?> rounded-pill">
                    <?= esc($a->framework) ?>
                </span>
            </td>
            <td>
                <?php if($a->framework === 'ISO 27001'): ?>
                    <strong><?= number_format($a->final_score ?? 0, 2) ?></strong>
                    <br><small class="text-muted">(<?= number_format(($a->final_score ?? 0) * 100, 0) ?>% Compliance)</small>
                <?php else: ?>
                    <strong><?= number_format($a->final_score ?? 0, 2) ?></strong>
                    <small class="text-muted">/ 5.00</small>
                    <br><small class="text-muted">
                        <?php 
                        $level = $a->final_score ?? 0;
                        $label = match(true) {
                            $level >= 4.5 => 'Optimal',
                            $level >= 3.5 => 'Predictable',
                            $level >= 2.5 => 'Established',
                            $level >= 1.5 => 'Managed',
                            $level >= 0.5 => 'Performed',
                            default => 'Incomplete'
                        };
                        ?>
                        (<?= $label ?>)
                    </small>
                <?php endif; ?>
            </td>
            <td>
                <?php if(!empty($a->updated_at) && strtotime($a->updated_at) > 0): ?>
                    <?= date('d M Y', strtotime($a->updated_at)) ?>
                <?php else: ?>
                    <span class="text-muted">-</span>
                <?php endif; ?>
            </td>
            <td class="text-center">
                <a href="/audit/detail/<?= $a->id ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                    <i class="bi bi-eye"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                Belum ada riwayat audit selesai
            </td>
        </tr>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>