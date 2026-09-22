<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
/**
 * @var string $page_title
 * @var int $cakupan_sistem
 * @var int $target_sistem
 * @var int $persentase_cakupan
 * @var float $skor_kematangan
 * @var string $level_kematangan
 * @var string $color_kematangan
 * @var int $total_temuan
 * @var array $risiko_counts
 * @var array $temuan_prioritas
 */
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<style>
    /* ===== WRAPPER UTAMA - FULL WIDTH ===== */
    .dashboard-wrapper {
        width: 100%;
        max-width: 100%;
        margin: 0;
        padding: 0 24px 24px 24px;
    }

    /* ===== STAT CARDS ===== */
    .card-stat-item {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 24px 28px;
        height: 100%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card-stat-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .card-stat-item .title {
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-stat-item .value {
        font-size: 2.6rem;
        font-weight: 800;
        color: #0F172A;
        line-height: 1.1;
    }

    .card-stat-item .value small {
        font-size: 1.1rem;
        font-weight: 500;
        color: #94A3B8;
    }

    /* ===== CARD PANEL ===== */
    .card-panel {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        padding: 24px 28px;
        height: 100%;
    }

    .card-panel h6 {
        color: #1E293B;
        font-weight: 700;
        margin-bottom: 20px;
        font-size: 1rem;
    }

    /* ===== BAR KEMATANGAN COBIT ===== */
    .maturity-row {
        display: flex;
        align-items: center;
        margin-bottom: 24px;
    }

    .maturity-row:last-child {
        margin-bottom: 8px;
    }

    .maturity-label {
        width: 160px;
        flex-shrink: 0;
        font-size: 0.85rem;
        color: #334155;
        font-weight: 600;
    }

    .maturity-track {
        position: relative;
        flex-grow: 1;
        height: 12px;
        background: #EEF1F6;
        border-radius: 6px;
        margin-right: 14px;
    }

    .maturity-fill {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        border-radius: 6px;
        background: #3B5BFD;
        z-index: 1;
    }

    .maturity-value {
        width: 32px;
        flex-shrink: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: #1E293B;
        text-align: right;
    }

    .maturity-target-line {
        position: absolute;
        top: -24px;
        bottom: -6px;
        width: 0;
        border-left: 2px dashed #F57E20;
        z-index: 2;
        pointer-events: none;
    }

    .maturity-target-label {
        position: absolute;
        top: -22px;
        font-size: 0.7rem;
        font-weight: 600;
        color: #F57E20;
        white-space: nowrap;
        transform: translateX(-50%);
    }

    .maturity-chart-area {
        position: relative;
        padding-top: 24px;
    }

    /* ===== BADGE RISIKO ===== */
    .risk-badge {
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .risk-badge .dot {
        width: 10px;
        height: 10px;
        border-radius: 2px;
        display: inline-block;
    }

    .risk-kritis .dot {
        background: #EF4444;
    }

    .risk-kritis {
        color: #EF4444;
    }

    .risk-tinggi .dot {
        background: #F97316;
    }

    .risk-tinggi {
        color: #F97316;
    }

    .risk-sedang .dot {
        background: #EAB308;
    }

    .risk-sedang {
        color: #B45309;
    }

    .risk-rendah .dot {
        background: #22C55E;
    }

    .risk-rendah {
        color: #16A34A;
    }

    /* ===== BADGE STATUS ===== */
    .status-badge {
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .status-badge .dot {
        width: 10px;
        height: 10px;
        border-radius: 2px;
        display: inline-block;
    }

    .status-progress .dot {
        background: #F59E0B;
    }

    .status-progress {
        color: #B45309;
    }

    .status-closed .dot {
        background: #22C55E;
    }

    .status-closed {
        color: #16A34A;
    }

    /* ===== TABLE ===== */
    .table th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748B;
        font-weight: 700;
        background-color: #F8FAFC;
        border-bottom: 1px solid #E2E8F0;
        white-space: nowrap;
    }

    .table td {
        font-size: 0.85rem;
        vertical-align: middle;
    }

    /* ===== DONUT LEGEND ===== */
    .donut-legend-item {
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
    }

    .donut-legend-item:last-child {
        margin-bottom: 0;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 0 16px 16px 16px;
        }

        .card-stat-item {
            padding: 20px;
        }

        .card-stat-item .value {
            font-size: 2.2rem;
        }

        .card-panel {
            padding: 20px;
        }

        .maturity-label {
            width: 130px;
            font-size: 0.8rem;
        }
    }
</style>

<div class="dashboard-wrapper">
    <!-- ===== STAT CARDS ROW ===== -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card-stat-item">
                <div class="title mb-2">Skor Kematangan TI</div>
                <div class="value"><?= number_format((float) $skor_kematangan, 1) ?> <small>/ 5.0</small></div>
                <div class="text-<?= esc((string) $color_kematangan) ?> small mt-2 fw-semibold">Level:
                    <?= esc((string) $level_kematangan) ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card-stat-item">
                <div class="title mb-2">Total Temuan Audit</div>
                <div class="value"><?= (int) $total_temuan ?> <small>Isu Risiko</small></div>
                <div class="text-muted small mt-2">
                    <span class="risk-badge risk-kritis"><span class="dot"></span><?= (int) $risiko_counts['Kritis'] ?>
                        Kritis</span>
                    &nbsp;&nbsp;
                    <span class="risk-badge risk-tinggi"><span class="dot"></span><?= (int) $risiko_counts['Tinggi'] ?>
                        Tinggi</span>
                    &nbsp;&nbsp;
                    <?= ((int) $risiko_counts['Sedang'] + (int) $risiko_counts['Rendah']) ?> Lainnya
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card-stat-item">
                <div class="title mb-2">Cakupan Sistem Teraudit</div>
                <div class="value"><?= (int) $cakupan_sistem ?> / <?= (int) $target_sistem ?> <small>Unit TI</small>
                </div>
                <div class="text-primary small mt-2 fw-semibold"><?= (int) $persentase_cakupan ?>% dari seluruh aset TI
                    Polban</div>
            </div>
        </div>
    </div>

    <!-- ===== CHARTS ROW ===== -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-7">
            <div class="card-panel">
                <h6>Tingkat Kematangan Tata Kelola TI per Domain COBIT 2019</h6>
                <div class="maturity-chart-area">
                    <div class="maturity-target-line" style="left: calc(160px + (100% - 160px - 44px) * 0.8);">
                        <span class="maturity-target-label">Target Institusi (4.0)</span>
                    </div>
                    <?php
                    $domains = [
                        ['label' => 'EDM (Tata Kelola)', 'value' => 3.8],
                        ['label' => 'APO (Perencanaan)', 'value' => 3.2],
                        ['label' => 'BAI (Pengembangan)', 'value' => 3.0],
                        ['label' => 'DSS (Operasional)', 'value' => 3.5],
                        ['label' => 'MEA (Pengawasan)', 'value' => 3.6],
                    ];
                    foreach ($domains as $d):
                        $percent = ($d['value'] / 5) * 100;
                        ?>
                        <div class="maturity-row">
                            <div class="maturity-label"><?= esc($d['label']) ?></div>
                            <div class="maturity-track">
                                <div class="maturity-fill" style="width: <?= $percent ?>%;"></div>
                            </div>
                            <div class="maturity-value"><?= number_format($d['value'], 1) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-5">
            <div class="card-panel">
                <h6>Distribusi Tingkat Risiko Temuan Audit</h6>
                <div class="d-flex align-items-center justify-content-center">
                    <div style="height: 240px; width: 240px; position: relative;" class="flex-shrink-0">
                        <canvas id="riskDonutChart"></canvas>
                    </div>
                    <div class="ms-4 d-flex flex-column">
                        <div class="donut-legend-item risk-badge risk-kritis"><span class="dot"></span> Kritis
                            (<?= (int) $risiko_counts['Kritis'] ?>)</div>
                        <div class="donut-legend-item risk-badge risk-tinggi"><span class="dot"></span> Tinggi
                            (<?= (int) $risiko_counts['Tinggi'] ?>)</div>
                        <div class="donut-legend-item risk-badge risk-sedang"><span class="dot"></span> Sedang
                            (<?= (int) $risiko_counts['Sedang'] ?>)</div>
                        <div class="donut-legend-item risk-badge risk-rendah"><span class="dot"></span> Rendah
                            (<?= (int) $risiko_counts['Rendah'] ?>)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== TABLE ROW ===== -->
    <div class="card-panel p-0 mb-4">
        <div class="p-4 border-bottom">
            <h6 class="mb-0">Daftar Temuan Prioritas</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Kode / Area Audit</th>
                        <th>Deskripsi Temuan Audit</th>
                        <th>Tingkat Risiko</th>
                        <th>PIC Unit Kerja</th>
                        <th>Status RTL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($temuan_prioritas)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada temuan prioritas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($temuan_prioritas as $i => $t): ?>
                            <tr>
                                <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                                <td class="fw-semibold text-dark"><?= esc($t['kode']) ?> / <?= esc($t['unit']) ?></td>
                                <td class="text-muted">Temuan audit dengan risiko <?= strtolower($t['risiko_label']) ?></td>
                                <td>
                                    <span class="risk-badge risk-<?= $t['risiko'] ?>">
                                        <span class="dot"></span><?= esc($t['risiko_label']) ?>
                                    </span>
                                </td>
                                <td>-</td>
                                <td>
                                    <span class="status-badge status-<?= $t['status'] ?>">
                                        <span class="dot"></span><?= esc($t['status_label']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('riskDonutChart').getContext('2d');

        // Data dinamis dari PHP
        const dataKritis = <?= (int) $risiko_counts['Kritis'] ?>;
        const dataTinggi = <?= (int) $risiko_counts['Tinggi'] ?>;
        const dataSedang = <?= (int) $risiko_counts['Sedang'] ?>;
        const dataRendah = <?= (int) $risiko_counts['Rendah'] ?>;
        const totalTemuan = dataKritis + dataTinggi + dataSedang + dataRendah;

        const centerTextPlugin = {
            id: 'centerText',
            afterDraw(chart) {
                const {
                    ctx,
                    chartArea: {
                        top,
                        bottom,
                        left,
                        right
                    }
                } = chart;
                const centerX = (left + right) / 2;
                const centerY = (top + bottom) / 2;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = '700 28px sans-serif';
                ctx.fillStyle = '#0F172A';
                ctx.fillText(totalTemuan.toString(), centerX, centerY - 8);
                ctx.font = '600 11px sans-serif';
                ctx.fillStyle = '#64748B';
                ctx.fillText('Total Temuan', centerX, centerY + 14);
                ctx.restore();
            }
        };

        const chartColors = ['#EF4444', '#F97316', '#EAB308', '#22C55E'];
        const chartData = [dataKritis, dataTinggi, dataSedang, dataRendah];
        const hasData = totalTemuan > 0;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Kritis', 'Tinggi', 'Sedang', 'Rendah'],
                datasets: [{
                    data: hasData ? chartData : [1],
                    backgroundColor: hasData ? chartColors : ['#E2E8F0'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                if (!hasData) return 'Belum ada data';
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? Math.round((context.raw / total) * 100) : 0;
                                return `${context.label}: ${context.raw} (${pct}%)`;
                            }
                        }
                    },
                    datalabels: {
                        anchor: 'center',
                        align: 'center',
                        color: '#FFFFFF',
                        font: {
                            weight: '700',
                            size: 12
                        },
                        display: hasData,
                        formatter: function (value, context) {
                            if (!hasData) return '';
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = total > 0 ? Math.round((value / total) * 100) : 0;
                            return pct + '%';
                        }
                    }
                }
            },
            plugins: [centerTextPlugin, ChartDataLabels]
        });
    });
</script>
<?= $this->endSection() ?>