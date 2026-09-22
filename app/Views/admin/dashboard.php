<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
    .stat-card {
        border-radius: 12px;
        padding: 24px;
        color: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .card-purple {
        background: #667eea;
    }

    .card-pink {
        background: #f5576c;
    }

    .card-blue {
        background: #4facfe;
    }

    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }

    .filter-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        color: #273272;
        background: white;
        cursor: pointer;
        transition: all 0.3s;
    }

    .filter-select:hover {
        border-color: #667eea;
    }

    .filter-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .badge-navy {
        background-color: var(--primary-navy) !important;
        color: white !important;
    }
</style>

<h4 class="fw-bold mb-4" style="color: var(--primary-navy);">Dashboard Admin</h4>

<!-- ===== CARDS: MANAJEMEN PENGGUNA ===== -->
<h6 class="fw-bold mb-3 text-muted">Manajemen Pengguna</h6>
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background: var(--primary-navy);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-2 fw-bold"><?= $stats['total_user'] ?? 0 ?></div>
                    <div class="small opacity-75 mt-2 text-uppercase">Total User</div>
                </div>
                <i class="bi bi-people fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background: #17a2b8;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-2 fw-bold"><?= $stats['total_auditor'] ?? 0 ?></div>
                    <div class="small opacity-75 mt-2 text-uppercase">Auditor</div>
                </div>
                <i class="bi bi-person-check fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background: #ffc107;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-2 fw-bold" style="color: #000;"><?= $stats['total_auditee'] ?? 0 ?></div>
                    <div class="small opacity-75 mt-2 text-uppercase" style="color: #000;">Auditee</div>
                </div>
                <i class="bi bi-briefcase fs-1 opacity-50" style="color: #000;"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card" style="background: #6c757d;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-2 fw-bold"><?= $stats['total_pimpinan'] ?? 0 ?></div>
                    <div class="small opacity-75 mt-2 text-uppercase">Pimpinan</div>
                </div>
                <i class="bi bi-person-badge fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- ===== CARDS: PERIODE & AUDIT (WARNA SOLID) ===== -->
<h6 class="fw-bold mb-3 text-muted">Periode & Audit</h6>
<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="stat-card card-purple">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="display-4 fw-bold"><?= $stats['total_periode'] ?? 0 ?></div>
                    <div class="small opacity-75">Total Periode</div>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                    <i class="bi bi-calendar-event fs-3"></i>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3 pt-3 border-top border-white border-opacity-25">
                <div><i class="bi bi-circle-fill text-success me-2"></i>Open: <?= $stats['periode_open'] ?? 0 ?></div>
                <div><i class="bi bi-circle-fill text-danger me-2"></i>Closed: <?= $stats['periode_closed'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="stat-card card-pink">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="display-4 fw-bold"><?= $stats['total_audit'] ?? 0 ?></div>
                    <div class="small opacity-75">Total Audit</div>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                    <i class="bi bi-clipboard-check fs-3"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                <div><i class="bi bi-circle-fill text-warning me-2"></i>Aktif: <?= $stats['audit_aktif'] ?? 0 ?></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="stat-card card-blue">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="display-4 fw-bold"><?= $stats['audit_selesai'] ?? 0 ?></div>
                    <div class="small opacity-75">Audit Selesai</div>
                </div>
                <div class="bg-white bg-opacity-25 rounded-circle p-3">
                    <i class="bi bi-check-circle fs-3"></i>
                </div>
            </div>
            <div class="mt-3 pt-3 border-top border-white border-opacity-25">
                <div><i class="bi bi-check-circle-fill text-success me-2"></i>Completion Rate</div>
            </div>
        </div>
    </div>
</div>

<!-- ===== GRAFIK DENGAN FILTER ===== -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-header bg-white border-0 pt-4 px-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="fw-bold mb-1" style="color: var(--primary-navy);">
                    <i class="bi bi-graph-up me-2"></i>Analisis Temuan Audit
                </h5>
                <small class="text-muted">Tren dan distribusi temuan berdasarkan periode</small>
            </div>
            <div>
                <label class="small text-muted fw-bold mb-1 d-block">Filter Periode:</label>
                <select id="filterPeriode" class="filter-select">
                    <option value="all">Semua Periode</option>
                    <?php foreach ($allPeriodes as $periode): ?>
                        <option value="<?= $periode->id ?>">
                            <?= esc($periode->nama_periode) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="card-body px-4 pb-4">
        <div class="row g-4">
            <!-- GRAFIK 1: Tren Temuan per Bulan -->
            <div class="col-lg-6">
                <h6 class="fw-bold mb-3" style="color: var(--primary-navy);">
                    <i class="bi bi-bar-chart-line me-2"></i>Tren Temuan per Bulan
                </h6>
                <div class="chart-container">
                    <canvas id="trenChart"></canvas>
                </div>
            </div>

            <!-- GRAFIK 2: Status Temuan -->
            <div class="col-lg-6">
                <h6 class="fw-bold mb-3" style="color: var(--primary-navy);">
                    <i class="bi bi-pie-chart me-2"></i>Status Temuan
                </h6>
                <div class="chart-container">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== TABEL: PERIODE & AUDIT TERBARU ===== -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0" style="color: var(--primary-navy);">
                    <i class="bi bi-calendar3 me-2"></i>Periode Audit Terbaru
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if (!empty($periodes_terbaru)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Periode</th>
                                    <th>Tahun</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($periodes_terbaru as $periode): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($periode->nama_periode) ?></strong><br>
                                            <small class="text-muted">
                                                <?= date('d M', strtotime($periode->tanggal_mulai)) ?> - <?= date('d M Y', strtotime($periode->tanggal_selesai)) ?>
                                            </small>
                                        </td>
                                        <td><?= $periode->tahun ?></td>
                                        <td>
                                            <?php if ($periode->status === 'open'): ?>
                                                <span class="badge bg-success rounded-pill px-3">Open</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary rounded-pill px-3">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>Belum ada periode audit
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h5 class="fw-bold mb-0" style="color: var(--primary-navy);">
                    <i class="bi bi-clipboard-data me-2"></i>Audit Terbaru
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                <?php if (!empty($audits_terbaru)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Judul Audit</th>
                                    <th>Auditee</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($audits_terbaru as $audit): ?>
                                    <tr>
                                        <td>
                                            <strong><?= esc($audit->title) ?></strong><br>
                                            <small class="text-muted"><?= esc($audit->framework) ?></small>
                                        </td>
                                        <td><?= esc($audit->auditee_name ?? 'N/A') ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = match ($audit->status) {
                                                'menunggu_jawaban' => 'bg-info text-dark',  // Biru muda
                                                'aktif' => 'badge-navy',                    // Biru navy
                                                'menunggu_penilaian' => 'bg-warning text-dark', // Kuning
                                                'selesai' => 'bg-success',                  // Hijau
                                                default => 'bg-secondary'
                                            };
                                            ?>
                                            <span class="badge <?= $badgeClass ?> rounded-pill px-3">
                                                <?= ucfirst(str_replace('_', ' ', $audit->status)) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>Belum ada data audit
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ===== CHART.JS SCRIPT ===== -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ===== DATA AWAL DARI PHP =====
    const initialTren = <?= json_encode($trenBulanan ?? []) ?>;
    const initialStatus = <?= json_encode($statusTemuan ?? []) ?>;

    let trenChart, statusChart;

    // ===== INISIALISASI GRAFIK =====
    function initCharts(trenData, statusData) {
        if (trenChart) trenChart.destroy();
        if (statusChart) statusChart.destroy();

        // 1. GRAFIK TREN
        const bulanLabels = trenData.map(item => {
            const bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const date = new Date(item.bulan + '-01');
            return bulan[date.getMonth()] + ' ' + date.getFullYear().toString().substr(-2);
        });

        const openData = trenData.map(item => parseInt(item.open_count) || 0);
        const closedData = trenData.map(item => parseInt(item.closed_count) || 0);
        const hasTrenData = trenData.length > 0 && (openData.reduce((a, b) => a + b, 0) > 0 || closedData.reduce((a, b) => a + b, 0) > 0);

        trenChart = new Chart(document.getElementById('trenChart'), {
            type: 'bar',
            data: {
                labels: hasTrenData ? bulanLabels : ['Belum ada data'],
                datasets: hasTrenData ? [{
                        label: 'Open',
                        data: openData,
                        backgroundColor: '#f5576c',
                        borderRadius: 6
                    },
                    {
                        label: 'Closed',
                        data: closedData,
                        backgroundColor: '#10b981',
                        borderRadius: 6
                    }
                ] : [{
                    label: 'Data',
                    data: [0],
                    backgroundColor: '#e5e7eb',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: hasTrenData,
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        display: hasTrenData
                    },
                    x: {
                        display: hasTrenData
                    }
                }
            }
        });

        // 2. GRAFIK DONUT
        const statusLabels = statusData.map(item => item.status);
        const statusDataValues = statusData.map(item => parseInt(item.count));
        const totalStatus = statusDataValues.reduce((a, b) => a + b, 0);
        const statusColors = {
            'Open': '#f97316',
            'Closed': '#10b981'
        };
        const hasStatusData = statusData.length > 0 && totalStatus > 0;

        statusChart = new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: hasStatusData ? statusLabels : ['Belum ada data'],
                datasets: [{
                    data: hasStatusData ? statusDataValues : [1],
                    backgroundColor: hasStatusData ? statusLabels.map(s => statusColors[s] || '#6c757d') : ['#e5e7eb'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: hasStatusData,
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            padding: 15,
                            font: {
                                size: 12,
                                weight: '500'
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                if (total === 0) return [{
                                    text: 'Belum ada data',
                                    fillStyle: '#e5e7eb',
                                    hidden: false,
                                    index: 0
                                }];
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label}: ${value} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                        }
                    },
                    tooltip: {
                        enabled: hasStatusData
                    }
                }
            }
        });
    }

    // Inisialisasi awal
    initCharts(initialTren, initialStatus);

    // ===== FILTER PERIODE (AJAX) =====
    document.getElementById('filterPeriode').addEventListener('change', function() {
        const periodeId = this.value;
        const trenContainer = document.getElementById('trenChart').parentElement;
        const statusContainer = document.getElementById('statusChart').parentElement;

        // Efek loading
        trenContainer.style.opacity = '0.5';
        statusContainer.style.opacity = '0.5';

        fetch(`/admin/dashboard/chart-data?periode_id=${periodeId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Server Error:', data.error);
                    initCharts([], []); // Fallback ke tampilan kosong yang rapi
                } else {
                    initCharts(data.trenBulanan || [], data.statusTemuan || []);
                }
                // Hilangkan loading
                trenContainer.style.opacity = '1';
                statusContainer.style.opacity = '1';
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                initCharts([], []);
                trenContainer.style.opacity = '1';
                statusContainer.style.opacity = '1';
            });
    });
</script>
<?= $this->endSection() ?>