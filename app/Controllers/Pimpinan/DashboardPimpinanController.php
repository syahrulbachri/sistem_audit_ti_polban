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

        // ==========================================================
        // 1. CAKUPAN SISTEM TERAUDIT
        // Pembilang : jumlah unit (auditee) yang sudah punya audit selesai
        // Penyebut  : jumlah seluruh unit/auditee terdaftar
        // ==========================================================
        $rowCakupan = $db->table('audits')
            ->select('COUNT(DISTINCT auditee_id) as total', false)
            ->where('status', 'selesai')
            ->get()
            ->getRow();
        $unitTeraudit = $rowCakupan ? (int) $rowCakupan->total : 0;

        $rowTargetUnit = $db->table('users')
            ->select('COUNT(*) as total', false)
            ->where('role', 'auditee')
            ->get()
            ->getRow();
        $targetSistem = $rowTargetUnit ? (int) $rowTargetUnit->total : 0;

        // Target tidak boleh lebih kecil dari yang sudah diaudit
        $targetSistem = max($targetSistem, $unitTeraudit, 1);

        $persentaseCakupan = (int) round(($unitTeraudit / $targetSistem) * 100);

        // ==========================================================
        // 2. SKOR KEMATANGAN TI (dinormalisasi ke skala 0 - 5)
        // final_score bisa berupa persentase (framework binary)
        // atau skor skala (framework scale, mis. 0-5 / 0-3),
        // jadi semuanya dikonversi dulu ke skala 5.
        // ==========================================================
        $frameworkRows = $db->table('frameworks')
            ->select('nama, scoring_type, max_score')
            ->get()
            ->getResultArray();

        $frameworkMap = [];
        foreach ($frameworkRows as $fw) {
            $frameworkMap[$fw['nama']] = [
                'scoring_type' => $fw['scoring_type'] ?? 'binary',
                'max_score'    => max(1, (int) ($fw['max_score'] ?? 1)),
            ];
        }

        $auditSelesai = $db->table('audits')
            ->select('framework, final_score')
            ->where('status', 'selesai')
            ->where('final_score IS NOT NULL')
            ->get()
            ->getResultArray();

        $totalSkor = 0.0;
        $countSkor = 0;

        foreach ($auditSelesai as $row) {
            $score = (float) $row['final_score'];
            $fw    = $frameworkMap[$row['framework']] ?? ['scoring_type' => 'binary', 'max_score' => 1];

            if ($fw['scoring_type'] === 'scale') {
                // Skor skala: 0..max_score  ->  0..5
                $normalized = ($score / $fw['max_score']) * 5;
            } else {
                // Framework binary: nilai bisa tersimpan sebagai persen (0..100)
                // atau sebagai rasio (0..1) pada data lama.
                $normalized = $score <= 1 ? $score * 5 : ($score / 100) * 5;
            }

            $totalSkor += max(0.0, min(5.0, $normalized));
            $countSkor++;
        }

        $averageScore = $countSkor > 0 ? round($totalSkor / $countSkor, 1) : 0.0;

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

        // ==========================================================
        // 3. TOTAL TEMUAN & DISTRIBUSI RISIKO (dari tabel temuans)
        // ==========================================================
        $risikoCounts = [
            'Kritis' => 0,
            'Tinggi' => 0,
            'Sedang' => 0,
            'Rendah' => 0,
        ];

        $risikoRows = $db->table('temuans')
            ->select('tingkat_risiko, COUNT(*) as jumlah', false)
            ->groupBy('tingkat_risiko')
            ->get()
            ->getResultArray();

        $totalTemuan = 0;
        foreach ($risikoRows as $row) {
            $key = (string) ($row['tingkat_risiko'] ?? '');
            if (array_key_exists($key, $risikoCounts)) {
                $risikoCounts[$key] = (int) $row['jumlah'];
            }
            $totalTemuan += (int) $row['jumlah'];
        }

        // ==========================================================
        // 4. TEMUAN PRIORITAS (5 temuan belum selesai, risiko tertinggi)
        // ==========================================================
        $prioritasRows = $db->table('temuans')
            ->select('temuans.id, temuans.tingkat_risiko, temuans.status,
                      temuans.rtl_deadline, audits.title as audit_title,
                      auditee.fullname as auditee_name')
            ->join('audits', 'audits.id = temuans.audit_id', 'left')
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->whereIn('temuans.status', ['Open', 'In_Progress'])
            ->get()
            ->getResultArray();

        $bobotRisiko = ['Kritis' => 4, 'Tinggi' => 3, 'Sedang' => 2, 'Rendah' => 1];

        $temuanPrioritas = [];
        foreach ($prioritasRows as $row) {
            $risikoLabel = (string) ($row['tingkat_risiko'] ?? 'Sedang');
            $bobot       = $bobotRisiko[$risikoLabel] ?? 2;

            // Temuan yang sudah melewati deadline RTL dinaikkan prioritasnya
            $isOverdue = !empty($row['rtl_deadline']) && strtotime($row['rtl_deadline']) < strtotime(date('Y-m-d'));

            $temuanPrioritas[] = [
                'kode'           => 'RTL-' . str_pad((string) $row['id'], 4, '0', STR_PAD_LEFT),
                'unit'           => (string) ($row['auditee_name'] ?? ($row['audit_title'] ?? '-')),
                'risiko'         => strtolower($risikoLabel),
                'risiko_label'   => $risikoLabel,
                'skor_prioritas' => $bobot * 10 + ($isOverdue ? 5 : 0),
                'status'         => $row['status'] === 'Closed' ? 'closed' : 'progress',
                'status_label'   => $row['status'] === 'In_Progress' ? 'In-Progress' : 'Open',
            ];
        }

        usort($temuanPrioritas, static function ($a, $b) {
            return $b['skor_prioritas'] <=> $a['skor_prioritas'];
        });
        $temuanPrioritas = array_slice($temuanPrioritas, 0, 5);

        // ===== RETURN VIEW =====
        return view('pimpinan/dashboard', [
            'page_title'         => 'Dashboard Eksekutif',
            'caupan_sistem'      => (int) $unitTeraudit,
            'target_sistem'      => (int) $targetSistem,
            'persentase_cakupan' => (int) $persentaseCakupan,
            'skor_kematangan'    => (float) $averageScore,
            'level_kematangan'   => (string) $level,
            'color_kematangan'   => (string) $color,
            'total_temuan'       => (int) $totalTemuan,
            'risiko_counts'      => $risikoCounts,
            'temuan_prioritas'   => $temuanPrioritas,
        ]);
    }
}
