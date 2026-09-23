<?php

namespace App\Controllers\Pimpinan;

use App\Controllers\BaseController;
use Config\Database;

class ActivityLogController extends BaseController
{
    public function index()
    {
        // Validasi role pimpinan
        if (!session()->get('logged_in') || session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda bukan pimpinan.');
        }

        $db = Database::connect();
        $userId = session()->get('id');

        // Ambil parameter filter
        $filterDateFrom = $this->request->getGet('date_from') ?? '';
        $filterDateTo = $this->request->getGet('date_to') ?? '';
        $filterAction = $this->request->getGet('action') ?? '';

        // Aksi yang diperbolehkan untuk pimpinan
        $allowedActions = ['LOGIN', 'LOGOUT', 'EXPORT'];

        // Build query
        $builder = $db->table('activity_logs')
            ->where('user_id', $userId)
            ->whereIn('action', $allowedActions);

        // Terapkan filter tanggal
        if (!empty($filterDateFrom)) {
            $builder->where('created_at >=', $filterDateFrom . ' 00:00:00');
        }
        if (!empty($filterDateTo)) {
            $builder->where('created_at <=', $filterDateTo . ' 23:59:59');
        }

        // Terapkan filter aksi
        if (!empty($filterAction) && in_array($filterAction, $allowedActions)) {
            $builder->where('action', $filterAction);
        }

        // Ambil data dengan pagination
        $totalLogs = $builder->countAllResults(false);

        $logs = $builder
            ->orderBy('created_at', 'DESC')
            ->limit(50)
            ->get()
            ->getResult();

        // Format data untuk view
        $formattedLogs = [];
        foreach ($logs as $log) {
            $formattedLogs[] = [
                'id' => (int) $log->id,
                'action' => $log->action,
                'table_name' => $log->table_name ?? '-',
                'description' => $log->description ?? '-',
                'ip_address' => $log->ip_address ?? '-',
                'user_agent' => $log->user_agent ?? '-',
                'created_at' => $log->created_at ? date('d M Y H:i', strtotime($log->created_at)) : '-',
            ];
        }

        return view('pimpinan/activity_log', [
            'page_title' => 'RIWAYAT AKTIVITAS',
            'page_subtitle' => 'Log Aktivitas User Pimpinan',
            'logs' => $formattedLogs,
            'totalLogs' => $totalLogs,
            'filterDateFrom' => $filterDateFrom,
            'filterDateTo' => $filterDateTo,
            'filterAction' => $filterAction,
            'allowedActions' => $allowedActions,
        ]);
    }
}
