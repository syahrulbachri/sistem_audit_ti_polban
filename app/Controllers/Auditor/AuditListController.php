<?php

namespace App\Controllers\Auditor;

use App\Controllers\BaseController;

class AuditListController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $auditorId = session()->get('id');

        // Ambil parameter filter dari URL
        $search          = $this->request->getGet('search') ?? '';
        $filterStatus    = $this->request->getGet('status') ?? '';
        $filterFramework = $this->request->getGet('framework') ?? '';
        $filterPeriode   = $this->request->getGet('periode') ?? '';
        $currentPage     = (int) ($this->request->getGet('page') ?? 1);
        $perPage         = 10; // Pagination 10 data per halaman

        if ($currentPage < 1) $currentPage = 1;

        // Ambil daftar periode untuk dropdown filter
        $periodes = $db->table('periodes')
            ->orderBy('nama_periode', 'DESC')
            ->get()
            ->getResult();

        // Build query utama
        $builder = $db->table('audits')
            ->select('audits.*, 
                                periodes.nama_periode,
                                auditee.fullname as auditee_name, 
                                auditor.fullname as auditor_name')
            ->join('periodes', 'periodes.id = audits.periode_id', 'left')
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->join('users as auditor', 'auditor.id = audits.created_by_auditor', 'left')
            ->where('audits.created_by_auditor', $auditorId);

        // Terapkan filter
        if (!empty($search)) {
            $builder->groupStart()
                ->like('audits.title', $search)
                ->orLike('auditee.fullname', $search)
                ->groupEnd();
        }
        if (!empty($filterStatus)) {
            $builder->where('audits.status', $filterStatus);
        }
        if (!empty($filterFramework)) {
            $builder->where('audits.framework', $filterFramework);
        }
        if (!empty($filterPeriode)) {
            $builder->where('audits.periode_id', $filterPeriode);
        }

        // Hitung total data untuk pagination (tanpa reset query)
        $total = $builder->countAllResults(false);
        $totalPages = ceil($total / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        if ($currentPage > $totalPages) $currentPage = $totalPages;

        // Ambil data dengan limit & offset
        $audits = $builder
            ->orderBy('audits.created_at', 'DESC')
            ->limit($perPage, ($currentPage - 1) * $perPage)
            ->get()
            ->getResult();

        return view('auditor/audit_list', [
            'title'           => 'Daftar Audit Saya - Sistem Audit IT POLBAN',
            'page_title'      => 'Daftar Audit Saya',
            'audits'          => $audits,
            'search'          => $search,
            'filterStatus'    => $filterStatus,
            'filterFramework' => $filterFramework,
            'filterPeriode'   => $filterPeriode,
            'periodes'        => $periodes,
            'currentPage'     => $currentPage,
            'totalPages'      => $totalPages,
            'total'           => $total,
            'perPage'         => $perPage
        ]);
    }

        public function history()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $auditorId = session()->get('id');

        // Ambil parameter filter
        $search  = trim((string) $this->request->getGet('q'));
        $fPeriode = $this->request->getGet('periode') ?? '';
        $fFramework = $this->request->getGet('framework') ?? '';

        // Ambil daftar periode dan framework untuk dropdown
        $periodes = $db->table('periodes')->orderBy('nama_periode', 'DESC')->get()->getResult();
        $frameworks = $db->table('frameworks')->where('is_active', 1)->orderBy('nama', 'ASC')->get()->getResult();

        // Build query utama - HANYA audit yang berstatus 'selesai'
        $query = $db->table('audits a')
            ->select('a.*, p.nama_periode, u.fullname as auditee_name, f.nama as framework_nama')
            ->join('periodes p', 'p.id = a.periode_id', 'left')
            ->join('users u', 'u.id = a.auditee_id', 'left')
            ->join('frameworks f', 'f.nama = a.framework', 'left')
            ->where('a.created_by_auditor', $auditorId)
            ->where('a.status', 'selesai');

        // Terapkan filter
        if ($search !== '') {
            $query->groupStart()
                ->like('a.title', $search)
                ->orLike('u.fullname', $search)
                ->groupEnd();
        }
        if ($fPeriode !== '') {
            $query->where('a.periode_id', $fPeriode);
        }
        if ($fFramework !== '') {
            $query->where('a.framework', $fFramework);
        }

        $audits = $query->orderBy('a.updated_at', 'DESC')->get()->getResult();

        return view('auditor/audit_history', [
            'title' => 'Riwayat Audit - Sistem Audit IT POLBAN',
            'page_title' => 'Riwayat Audit',
            'audits' => $audits,
            'periodes' => $periodes,
            'frameworks' => $frameworks,
            'search' => $search,
            'fPeriode' => $fPeriode,
            'fFramework' => $fFramework
        ]);
    }
}
