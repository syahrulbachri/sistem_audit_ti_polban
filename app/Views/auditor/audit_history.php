<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);"><?= $page_title ?></h4>

<!-- ========================================== -->
<!-- AREA FILTER -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body p-4">
        <form method="get" action="/auditor/history">
            <div class="row g-3">
                <!-- Pencarian -->
                <div class="col-md-4">
                    <label class="form-label fw-bold small">
                        <i class="bi bi-search me-1"></i>Cari (Judul/Auditee)
                    </label>
                    <input type="text" name="q" class="form-control" 
                           placeholder="Ketik kata kunci..." 
                           value="<?= esc($search) ?>">
                </div>

                <!-- Filter Periode -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small">
                        <i class="bi bi-calendar me-1"></i>Periode
                    </label>
                    <select name="periode" class="form-select">
                        <option value="">Semua Periode</option>
                        <?php foreach ($periodes as $p): ?>
                            <option value="<?= $p->id ?>" 
                                <?= ($fPeriode == $p->id) ? 'selected' : '' ?>>
                                <?= esc($p->nama_periode) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filter Framework -->
                <div class="col-md-3">
                    <label class="form-label fw-bold small">
                        <i class="bi bi-book me-1"></i>Framework
                    </label>
                    <select name="framework" class="form-select">
                        <option value="">Semua Framework</option>
                        <?php foreach ($frameworks as $f): ?>
                            <option value="<?= esc($f->nama) ?>" 
                                <?= ($fFramework === $f->nama) ? 'selected' : '' ?>>
                                <?= esc($f->nama) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tombol Filter & Reset -->
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="/auditor/history" class="btn btn-outline-secondary" title="Reset Filter">
                        <i class="bi bi-x-lg"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- TABEL RIWAYAT AUDIT -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm" style="border-radius: 12px;">
    <div class="card-body p-0">
        <?php if (empty($audits)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-3 mb-0">Tidak ada riwayat audit yang sesuai dengan filter.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="px-4">Judul Audit</th>
                            <th>Periode</th>
                            <th>Auditee</th>
                            <th>Framework</th>
                            <th class="text-center">Skor Akhir</th>
                            <th class="text-center">Selesai Pada</th>
                            <th class="text-center px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($audits as $a): 
                            // Tentukan tampilan skor berdasarkan framework
                            $fw = $a->framework;
                            $isBinary = in_array($fw, ['ISO 27001', 'contoh biner']);
                            $scoreDisplay = '';
                            
                            if ($isBinary) {
                                $compliance = $a->final_score ?? 0;
                                $scoreDisplay = '<strong>' . number_format($compliance, 2) . '</strong><br><small class="text-muted">(' . number_format($compliance, 3) . '% Compliance)</small>';
                            } else {
                                $maturity = $a->final_score ?? 0;
                                $levelLabel = '';
                                if ($maturity < 1) $levelLabel = 'Incomplete';
                                elseif ($maturity < 2) $levelLabel = 'Performed';
                                elseif ($maturity < 3) $levelLabel = 'Managed';
                                elseif ($maturity < 4) $levelLabel = 'Established';
                                elseif ($maturity < 5) $levelLabel = 'Predictable';
                                else $levelLabel = 'Optimizing';
                                
                                $scoreDisplay = '<strong>' . number_format($maturity, 2) . '</strong> / 5.00<br><small class="text-muted">(' . $levelLabel . ')</small>';
                            }
                            
                            // Badge framework
                            $fwBadge = match($fw) {
                                'ISO 27001' => 'bg-success',
                                'COBIT 2019' => 'bg-info text-dark',
                                default => 'bg-secondary'
                            };
                        ?>
                        <tr>
                            <td class="px-4"><strong><?= esc($a->title) ?></strong></td>
                            <td><?= esc($a->nama_periode ?? '-') ?></td>
                            <td><?= esc($a->auditee_name ?? '-') ?></td>
                            <td>
                                <span class="badge <?= $fwBadge ?> rounded-pill">
                                    <?= esc($fw) ?>
                                </span>
                            </td>
                            <td class="text-center"><?= $scoreDisplay ?></td>
                            <td class="text-center"><?= date('d M Y', strtotime($a->updated_at)) ?></td>
                            <td class="text-center px-4">
                                <a href="/audit/detail/<?= $a->id ?>" class="btn btn-sm btn-outline-primary" title="Lihat Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>