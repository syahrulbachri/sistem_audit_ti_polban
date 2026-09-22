<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class ActivityLogController extends BaseController
{
    public function index()
    {
        // Proteksi: hanya admin
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil parameter filter
        $filterDateFrom  = $this->request->getGet('date_from') ?? '';
        $filterDateTo    = $this->request->getGet('date_to') ?? '';
        $filterUser      = $this->request->getGet('user') ?? '';
        $filterAction    = $this->request->getGet('action') ?? '';

        // Build query
        $builder = $db->table('activity_logs');
        $builder->select('activity_logs.*, users.fullname');
        $builder->join('users', 'users.id = activity_logs.user_id', 'left');
        $builder->orderBy('activity_logs.created_at', 'DESC');

        // Apply filters
        if (!empty($filterDateFrom)) {
            $builder->where('DATE(activity_logs.created_at) >=', $filterDateFrom);
        }
        if (!empty($filterDateTo)) {
            $builder->where('DATE(activity_logs.created_at) <=', $filterDateTo);
        }
        if (!empty($filterUser)) {
            $builder->like('activity_logs.username', $filterUser);
        }
        if (!empty($filterAction)) {
            $builder->where('activity_logs.action', $filterAction);
        }

        // Hitung total untuk pagination
        $total = $builder->countAllResults(false);

        //  TAMBAHKAN INI: Hitung log hari ini (TIDAK TERPENGARUH FILTER)
        $todayLogs = $db->table('activity_logs')
            ->where('DATE(created_at)', date('Y-m-d'))
            ->countAllResults();

        // Pagination
        $perPage = 20;
        $currentPage = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($currentPage - 1) * $perPage;

        $logs = $builder->limit($perPage, $offset)->get()->getResult();
        $totalPages = ceil($total / $perPage);

        // Ambil daftar user & action untuk dropdown filter
        $users = $db->table('activity_logs')
            ->select('username')
            ->distinct()
            ->where('username IS NOT NULL')
            ->orderBy('username', 'ASC')
            ->get()->getResult();

        return view('admin/activity_logs/index', [
            'title'          => 'Log Aktivitas - Sistem Audit IT POLBAN',
            'page_title'     => 'Log Aktivitas',
            'logs'           => $logs,
            'users'          => $users,
            'total'          => $total,
            'todayLogs'      => $todayLogs,  // ✅ TAMBAHKAN VARIABEL INI
            'totalPages'     => $totalPages,
            'currentPage'    => $currentPage,
            'perPage'        => $perPage,
            'filterDateFrom' => $filterDateFrom,
            'filterDateTo'   => $filterDateTo,
            'filterUser'     => $filterUser,
            'filterAction'   => $filterAction,
        ]);
    }

    public function delete($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $db->table('activity_logs')->where('id', $id)->delete();

        // Catat log penghapusan
        log_activity('DELETE', 'activity_logs', 'Menghapus log aktivitas ID: ' . $id);

        return redirect()->back()->with('success', 'Log aktivitas berhasil dihapus.');
    }

    public function clearOld()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Hapus log yang lebih dari 90 hari (jika diminta)
        $db->table('activity_logs')
            ->where('created_at <', date('Y-m-d H:i:s', strtotime('-90 days')))
            ->delete();

        log_activity('DELETE', 'activity_logs', 'Membersihkan log aktivitas lama (>90 hari)');

        return redirect()->back()->with('success', 'Log aktivitas lama berhasil dibersihkan.');
    }
}
