<?php

namespace App\Controllers\Pimpinan;

use App\Controllers\BaseController;

class DashboardPimpinanController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }
        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda bukan pimpinan.');
        }

        $db = \Config\Database::connect();

        // ===== 1. CAKUPAN SISTEM TERAUDIT =====
        // Hitung total LHA di database (sistem yang sudah diaudit)
        $totalLhaQuery = $db->table('audits')->select('COUNT(*) as total')->get()->getRow();
        $sudahDiaudit = $totalLhaQuery ? (int)$totalLhaQuery->total : 0;

        // Target sistem yang harus diaudit (statis = 20)
        $targetSistem = 20;
        $persentaseCakupan = $targetSistem > 0 ? round(($sudahDiaudit / $targetSistem) * 100) : 0;

        // ===== 2. SKOR KEMATANGAN TI (Rata-rata COBIT) =====
        // Hanya hitung dari LHA yang status != 'belum'
        $skorQuery = $db->table('audits')
            ->select('final_score')
            ->where('status !=', 'belum')
            ->get()
            ->getResultArray();

        $totalSkor = 0;
        $countSkor = 0;
        foreach ($skorQuery as $row) {
            if (!empty($row['skor']) && $row['skor'] !== '-') {
                preg_match('/([\d.]+)/', $row['skor'], $matches);
                if (isset($matches[1])) {
                    $totalSkor += floatval($matches[1]);
                    $countSkor++;
                }
            }
        }
        $averageScore = $countSkor > 0 ? round($totalSkor / $countSkor, 1) : 0;

        // Tentukan level
        $level = '';
        $color = '';
        if ($averageScore >= 4.0) {
            $level = 'Optimized (Sangat Baik)';
            $color = 'success';
        } elseif ($averageScore >= 3.0) {
            $level = 'Established (Baik)';
            $color = 'primary';
        } elseif ($averageScore >= 2.0) {
            $level = 'Repeatable (Cukup)';
            $color = 'warning';
        } else {
            $level = 'Initial (Perlu Perbaikan)';
            $color = 'danger';
        }

        // ===== 3. TOTAL TEMUAN AUDIT & DISTRIBUSI RISIKO =====
        // Hitung dari kolom 'temuan' di tabel lha (format: K/T/S/R)
        // Hanya untuk LHA yang status != 'belum'
        $temuanQuery = $db->table('temuans')
            ->select('id')
            ->where('status !=', 'belum')
            ->get()
            ->getResultArray();

        $totalTemuan = 0;
        $risikoCounts = [
            'Kritis' => 0,
            'Tinggi' => 0,
            'Sedang' => 0,
            'Rendah' => 0,
        ];

        foreach ($temuanQuery as $row) {
            if (!empty($row['temuan']) && $row['temuan'] !== '-' && strpos($row['temuan'], '/') !== false) {
                $parts = array_map('intval', array_map('trim', explode('/', $row['temuan'])));
                if (count($parts) === 4) {
                    $risikoCounts['Kritis'] += $parts[0];
                    $risikoCounts['Tinggi'] += $parts[1];
                    $risikoCounts['Sedang'] += $parts[2];
                    $risikoCounts['Rendah'] += $parts[3];
                    $totalTemuan += array_sum($parts);
                }
            }
        }

        // ===== 4. TEMUAN PRIORITAS (5 LHA dengan risiko tertinggi) =====
        $prioritasQuery = $db->table('audits')
            ->select('title, periode_id, auditee_id')
            ->where('status !=', 'belum')
            ->get()
            ->getResultArray();

        $temuanPrioritas = [];
        foreach ($prioritasQuery as $row) {
            if (!empty($row['temuan']) && $row['temuan'] !== '-' && strpos($row['temuan'], '/') !== false) {
                $parts = array_map('intval', array_map('trim', explode('/', $row['temuan'])));
                if (count($parts) === 4) {
                    // Hitung skor prioritas: Kritis=4, Tinggi=3, Sedang=2, Rendah=1
                    $skorPrioritas = ($parts[0] * 4) + ($parts[1] * 3) + ($parts[2] * 2) + ($parts[3] * 1);

                    // Tentukan risiko tertinggi
                    if ($parts[0] > 0) {
                        $risiko = 'kritis';
                        $risikoLabel = 'Kritis';
                    } elseif ($parts[1] > 0) {
                        $risiko = 'tinggi';
                        $risikoLabel = 'Tinggi';
                    } elseif ($parts[2] > 0) {
                        $risiko = 'sedang';
                        $risikoLabel = 'Sedang';
                    } else {
                        $risiko = 'rendah';
                        $risikoLabel = 'Rendah';
                    }

                    $temuanPrioritas[] = [
                        'kode' => $row['kode'],
                        'unit' => $row['judul'],
                        'risiko' => $risiko,
                        'risiko_label' => $risikoLabel,
                        'skor_prioritas' => $skorPrioritas,
                        'status' => 'progress',
                        'status_label' => 'In-Progress',
                    ];
                }
            }
        }

        // Urutkan berdasarkan skor prioritas (tertinggi dulu), ambil 5 teratas
        usort($temuanPrioritas, function ($a, $b) {
            return $b['skor_prioritas'] <=> $a['skor_prioritas'];
        });
        $temuanPrioritas = array_slice($temuanPrioritas, 0, 5);

        // ===== RETURN VIEW =====
        return view('pimpinan/dashboard', [
            'page_title' => 'Dashboard Eksekutif',
            'cakupan_sistem' => $sudahDiaudit,
            'target_sistem' => $targetSistem,
            'persentase_cakupan' => $persentaseCakupan,
            'skor_kematangan' => $averageScore,
            'level_kematangan' => $level,
            'color_kematangan' => $color,
            'total_temuan' => $totalTemuan,
            'risiko_counts' => $risikoCounts,
            'temuan_prioritas' => $temuanPrioritas,
        ]);
    }
}