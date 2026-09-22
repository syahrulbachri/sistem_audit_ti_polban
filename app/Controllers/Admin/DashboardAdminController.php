<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardAdminController extends BaseController
{

    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // ===== STATISTIK USERS =====
        $stats['total_user'] = $db->table('users')->countAllResults();
        $stats['total_auditor'] = $db->table('users')->where('role', 'auditor')->countAllResults();
        $stats['total_auditee'] = $db->table('users')->where('role', 'auditee')->countAllResults();
        $stats['total_pimpinan'] = $db->table('users')->where('role', 'pimpinan')->countAllResults();

        // ===== STATISTIK PERIODE =====
        $stats['total_periode'] = $db->table('periodes')->countAllResults();
        $stats['periode_open'] = $db->table('periodes')->where('status', 'open')->countAllResults();
        $stats['periode_closed'] = $db->table('periodes')->where('status', 'closed')->countAllResults();

        // ===== STATISTIK AUDIT =====
        $stats['total_audit'] = $db->table('audits')->countAllResults();
        $stats['audit_aktif'] = $db->table('audits')->whereIn('status', ['aktif', 'menunggu_penilaian'])->countAllResults();
        $stats['audit_selesai'] = $db->table('audits')->where('status', 'selesai')->countAllResults();

        // ===== STATISTIK TEMUAN =====
        $stats['total_temuan'] = $db->table('temuans')->countAllResults();
        $stats['temuan_open'] = $db->table('temuans')->where('status', 'Open')->countAllResults();
        $stats['temuan_closed'] = $db->table('temuans')->where('status', 'Closed')->countAllResults();

        // ===== DATA GRAFIK: Tren Temuan per Bulan (Default: 6 bulan terakhir) =====
        $trenBulanan = $db->query("
            SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as bulan,
                SUM(CASE WHEN status = 'Open' THEN 1 ELSE 0 END) as open_count,
                SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed_count
            FROM temuans
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY bulan ASC
        ")->getResult();

        // ===== DATA GRAFIK: Distribusi Status Temuan =====
        $statusTemuan = $db->query("
            SELECT status, COUNT(*) as count
            FROM temuans
            GROUP BY status
        ")->getResult();

        // ===== DATA TABEL: Periode Terbaru =====
        $periodes_terbaru = $db->table('periodes')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResult();

        // ===== DATA TABEL: Audit Terbaru =====
        $audits_terbaru = $db->table('audits')
            ->select('audits.*, users.fullname as auditee_name')
            ->join('users', 'users.id = audits.auditee_id', 'left')
            ->orderBy('audits.created_at', 'DESC')
            ->limit(5)
            ->get()
            ->getResult();

        // ===== DATA: Semua Periode untuk Dropdown Filter =====
        $allPeriodes = $db->table('periodes')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();

        return view('admin/dashboard', [
            'title'             => 'Dashboard Admin - Sistem Audit IT POLBAN',
            'page_title'        => 'Dashboard Admin',
            'stats'             => $stats,
            'trenBulanan'       => $trenBulanan,
            'statusTemuan'      => $statusTemuan,
            'periodes_terbaru'  => $periodes_terbaru,
            'audits_terbaru'    => $audits_terbaru,
            'allPeriodes'       => $allPeriodes
        ]);
    }

    // ===== AJAX ENDPOINT: Get Chart Data by Periode =====
    public function getChartData()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(403);
        }

        $db = \Config\Database::connect();
        $periodeId = $this->request->getGet('periode_id');

        try {
            // 1. Query Tren Bulanan
            $query = "SELECT DATE_FORMAT(t.created_at, '%Y-%m') as bulan,
                         SUM(CASE WHEN t.status = 'Open' THEN 1 ELSE 0 END) as open_count,
                         SUM(CASE WHEN t.status = 'Closed' THEN 1 ELSE 0 END) as closed_count
                  FROM temuans t 
                  JOIN audits a ON t.audit_id = a.id";

            $params = [];
            // Hanya tambahkan WHERE jika periode_id dipilih dan bukan 'all'
            if ($periodeId && $periodeId !== 'all') {
                $query .= " WHERE a.periode_id = ?";
                $params[] = $periodeId;
            }
            $query .= " GROUP BY DATE_FORMAT(t.created_at, '%Y-%m') ORDER BY bulan ASC";
            $trenBulanan = $db->query($query, $params)->getResult();

            // 2. Query Status Temuan
            $query2 = "SELECT t.status, COUNT(*) as count 
                   FROM temuans t 
                   JOIN audits a ON t.audit_id = a.id";

            $params2 = [];
            if ($periodeId && $periodeId !== 'all') {
                $query2 .= " WHERE a.periode_id = ?";
                $params2[] = $periodeId;
            }
            $query2 .= " GROUP BY t.status";
            $statusTemuan = $db->query($query2, $params2)->getResult();

            return $this->response->setJSON([
                'trenBulanan'  => $trenBulanan,
                'statusTemuan' => $statusTemuan,
                'success'      => true
            ]);
        } catch (\Exception $e) {
            // Jika ada error SQL (misal kolom belum ada), kirim pesan yang jelas
            return $this->response->setJSON([
                'error' => 'Database error: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
