<?php

namespace App\Controllers\Auditor;

use App\Controllers\BaseController;

class ActivityLogController extends BaseController
{
    public function index()
    {
        // Proteksi: hanya auditor
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $auditorId = session()->get('id');

        // Ambil parameter filter (hanya tanggal dan aksi, tidak perlu filter user karena hanya milik sendiri)
        $filterDateFrom = $this->request->getGet('date_from') ?? '';
        $filterDateTo   = $this->request->getGet('date_to') ?? '';
        $filterAction   = $this->request->getGet('action') ?? '';

        // Build query: HANYA ambil log milik auditor yang sedang login
        $builder = $db->table('activity_logs');
        $builder->where('user_id', $auditorId);
        $builder->orderBy('created_at', 'DESC');

        // Apply filters
        if (!empty($filterDateFrom)) {
            $builder->where('DATE(created_at) >=', $filterDateFrom);
        }
        if (!empty($filterDateTo)) {
            $builder->where('DATE(created_at) <=', $filterDateTo);
        }
        if (!empty($filterAction)) {
            $builder->where('action', $filterAction);
        }

        // Hitung total untuk pagination
        $total = $builder->countAllResults(false);

        // Pagination
        $perPage = 20;
        $currentPage = (int) ($this->request->getGet('page') ?? 1);
        $offset = ($currentPage - 1) * $perPage;

        $logs = $builder->limit($perPage, $offset)->get()->getResult();
        $totalPages = ceil($total / $perPage);

        return view('auditor/activity_logs/index', [
            'title'          => 'Riwayat Aktivitas Saya - Sistem Audit IT POLBAN',
            'page_title'     => 'Riwayat Aktivitas Saya',
            'logs'           => $logs,
            'total'          => $total,
            'totalPages'     => $totalPages,
            'currentPage'    => $currentPage,
            'perPage'        => $perPage,
            'filterDateFrom' => $filterDateFrom,
            'filterDateTo'   => $filterDateTo,
            'filterAction'   => $filterAction
        ]);
    }
}