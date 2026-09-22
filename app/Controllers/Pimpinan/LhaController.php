<?php

namespace App\Controllers\Pimpinan;

use App\Controllers\BaseController;
use Config\Database;

class LhaController extends BaseController
{
    public function index()
    {
        // 1. Validasi Login & Role
        if (!session()->get('logged_in') || session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = Database::connect();

        // 2. Ambil Parameter Filter
        $search          = (string) ($this->request->getGet('search') ?? '');
        $filterFramework = (string) ($this->request->getGet('framework') ?? '');
        $filterPeriode   = (string) ($this->request->getGet('periode') ?? '');
        $filterAuditee   = (string) ($this->request->getGet('auditee') ?? ''); // BARU

        $currentPage = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;
        if ($currentPage < 1) $currentPage = 1;

        // Ambil nama periode untuk label card
        $selectedPeriodeName = '';
        if (!empty($filterPeriode)) {
            $pRow = $db->table('periodes')->select('nama_periode')->where('id', $filterPeriode)->get()->getRow();
            $selectedPeriodeName = $pRow ? (string) $pRow->nama_periode : '';
        }

        // ==========================================================
        // BASE BUILDER (Hanya untuk Card & Filter Dropdown - TIDAK menerima $search)
        // ==========================================================
        $baseBuilder = $db->table('audits')
            ->select('audits.*, periodes.nama_periode, auditee.fullname as auditee_name')
            ->join('periodes', 'periodes.id = audits.periode_id', 'left')
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->where('audits.status', 'selesai');

        if (!empty($filterPeriode)) {
            $baseBuilder->where('audits.periode_id', $filterPeriode);
        }
        if (!empty($filterFramework)) {
            $baseBuilder->where('audits.framework', $filterFramework);
        }
        if (!empty($filterAuditee)) {
            $baseBuilder->where('audits.auditee_id', $filterAuditee);
        }

        // ==========================================================
        // HITUNG STATISTIK CARD
        // ==========================================================
        $q1 = clone $baseBuilder;
        $totalAudit = (int) $q1->countAllResults();

        if (!empty($filterPeriode)) {
            $q2 = clone $baseBuilder;
            $countPeriode = (int) $q2->countAllResults();
            $labelPeriode = 'Periode: ' . $selectedPeriodeName;
            $notePeriode  = 'Audit pada periode ini';
        } else {
            $q2 = clone $baseBuilder;
            $row2 = $q2->select('COUNT(DISTINCT periode_id) as total', false)->get()->getRow();
            $countPeriode = $row2 ? (int) $row2->total : 0;
            $labelPeriode = 'Total Periode';
            $notePeriode  = 'Periode dengan audit selesai';
        }

        $q3 = clone $baseBuilder;
        $row3 = $q3->select('COUNT(DISTINCT auditee_id) as total', false)->get()->getRow();
        $countAuditee = $row3 ? (int) $row3->total : 0;
        $labelAuditee = 'Total Unit Teraudit';
        $noteAuditee  = 'Unit kerja unik diaudit';

        if (!empty($filterFramework)) {
            $q4 = clone $baseBuilder;
            $countFramework = (int) $q4->countAllResults();
            $labelFramework = 'Framework: ' . $filterFramework;
            $noteFramework  = 'Audit menggunakan framework ini';
        } else {
            $q4 = clone $baseBuilder;
            $row4 = $q4->select('COUNT(DISTINCT framework) as total', false)->get()->getRow();
            $countFramework = $row4 ? (int) $row4->total : 0;
            $labelFramework = 'Total Framework';
            $noteFramework  = 'Framework digunakan dalam audit';
        }

        // ==========================================================
        // TABEL (Menerima Search + Filter Dropdown)
        // ==========================================================
        $tableBuilder = clone $baseBuilder;

        if (!empty($search)) {
            $tableBuilder->groupStart()
                ->like('audits.title', $search)
                ->orLike('auditee.fullname', $search)
                ->groupEnd();
        }

        $total = (int) $tableBuilder->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        if ($totalPages < 1) $totalPages = 1;
        if ($currentPage > $totalPages) $currentPage = $totalPages;

        $audits = $tableBuilder
            ->orderBy('audits.created_at', 'DESC')
            ->limit($perPage, ($currentPage - 1) * $perPage)
            ->get()
            ->getResult();

        // Ambil daftar periode & auditee untuk dropdown
        $periodes = $db->table('periodes')->orderBy('nama_periode', 'DESC')->get()->getResult();
        $auditees = $db->table('users')
            ->select('id, fullname')
            ->where('role', 'auditee')
            ->orderBy('fullname', 'ASC')
            ->get()->getResult();

        return view('pimpinan/daftar_lha', [
            'page_title'      => 'DAFTAR LAPORAN HASIL AUDIT (LHA) - POLBAN',
            'page_subtitle'   => 'Portal Pimpinan (Monitoring Laporan Akhir Audit)',
            'audits'          => $audits,
            'search'          => $search,
            'filterFramework' => $filterFramework,
            'filterPeriode'   => $filterPeriode,
            'filterAuditee'   => $filterAuditee,
            'periodes'        => $periodes,
            'auditees'        => $auditees,
            'currentPage'     => $currentPage,
            'totalPages'      => $totalPages,
            'total'           => $total,
            'perPage'         => $perPage,
            'summary' => [
                'total_audit'     => (int) $totalAudit,
                'count_periode'   => (int) $countPeriode,
                'label_periode'   => (string) $labelPeriode,
                'note_periode'    => (string) $notePeriode,
                'count_auditee'   => (int) $countAuditee,
                'label_auditee'   => (string) $labelAuditee,
                'note_auditee'    => (string) $noteAuditee,
                'count_framework' => (int) $countFramework,
                'label_framework' => (string) $labelFramework,
                'note_framework'  => (string) $noteFramework,
            ]
        ]);
    }

    public function detail(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = Database::connect();

        $audit = $db->table('audits')
            ->select('audits.*, 
                      periodes.nama_periode,
                      auditee.fullname as auditee_name,
                      auditor.fullname as auditor_name')
            ->join('periodes', 'periodes.id = audits.periode_id', 'left')
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->join('users as auditor', 'auditor.id = audits.created_by_auditor', 'left')
            ->where('audits.id', $id)
            ->get()
            ->getRowArray();

        if (!$audit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('pimpinan/daftar_lha/lha_detail', [
            'page_title'    => 'DETAIL AUDIT',
            'page_subtitle' => (string) ($audit['title'] ?? 'Detail Audit'),
            'audit'         => $audit,
        ]);
    }
}