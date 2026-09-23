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
        $filterAuditee   = (string) ($this->request->getGet('auditee') ?? '');

        $currentPage = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;
        if ($currentPage < 1) $currentPage = 1;

        // Ambil nama periode untuk label card
        $selectedPeriodeName = '';
        if (!empty($filterPeriode)) {
            $pRow = $db->table('periodes')->select('nama_periode')->where('id', $filterPeriode)->get()->getRow();
            $selectedPeriodeName = $pRow ? (string) $pRow->nama_periode : '';
        }

        // Ambil nama auditee untuk label card (BARU)
        $selectedAuditeeName = '';
        if (!empty($filterAuditee)) {
            $aRow = $db->table('users')->select('fullname')->where('id', $filterAuditee)->get()->getRow();
            $selectedAuditeeName = $aRow ? (string) $aRow->fullname : '';
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

        // Card 1: Total Audit
        $q1 = clone $baseBuilder;
        $totalAudit = (int) $q1->countAllResults();

        // Card 2: Periode
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

        // Card 3: Auditee (DINAMIS - Sama seperti Periode & Framework)
        if (!empty($filterAuditee)) {
            // Jika difilter: Hitung total audit untuk auditee tersebut
            $q3 = clone $baseBuilder;
            $countAuditee = (int) $q3->countAllResults();
            $labelAuditee = 'Unit Teraudit: ' . $selectedAuditeeName;
            $noteAuditee  = 'Audit untuk unit ini';
        } else {
            // Jika TIDAK difilter: Hitung jumlah unit unik yang diaudit
            $q3 = clone $baseBuilder;
            $row3 = $q3->select('COUNT(DISTINCT auditee_id) as total', false)->get()->getRow();
            $countAuditee = $row3 ? (int) $row3->total : 0;
            $labelAuditee = 'Total Unit Teraudit';
            $noteAuditee  = 'Unit kerja unik diaudit';
        }

        // Card 4: Framework
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

        // ==========================================================
        // AMBIL DATA DROPDOWN DARI AUDIT YANG SUDAH SELESAI (DINAMIS)
        // ==========================================================

        // 1. Framework: Ambil DISTINCT framework dari audit yang status='selesai'
        $frameworks = $db->table('audits')
            ->distinct()                                    // ✅ Method terpisah
            ->select('framework')                           // ✅ Tanpa kata DISTINCT
            ->where('status', 'selesai')
            ->where('framework IS NOT NULL')
            ->where('framework !=', '')
            ->orderBy('framework', 'ASC')
            ->get()
            ->getResult();

        // 2. Periode: Ambil DISTINCT periode dari audit yang status='selesai'
        $periodes = $db->table('audits')
            ->distinct()                                    // ✅ Method terpisah
            ->select('periodes.id, periodes.nama_periode', false)  // ✅ false = jangan escape
            ->join('periodes', 'periodes.id = audits.periode_id', 'left')
            ->where('audits.status', 'selesai')
            ->where('audits.periode_id IS NOT NULL')
            ->orderBy('periodes.nama_periode', 'DESC')
            ->get()
            ->getResult();

        // 3. Unit/Auditee: Ambil DISTINCT auditee dari audit yang status='selesai'
        $auditees = $db->table('audits')
            ->distinct()                                    // ✅ Method terpisah
            ->select('users.id, users.fullname', false)     // ✅ false = jangan escape
            ->join('users', 'users.id = audits.auditee_id', 'left')
            ->where('audits.status', 'selesai')
            ->where('audits.auditee_id IS NOT NULL')
            ->orderBy('users.fullname', 'ASC')
            ->get()
            ->getResult();

        // Kirim Data ke View
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
            'frameworks'      => $frameworks,
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

        // 1. Ambil data audit dengan semua relasi
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
            ->getRow();

        if (!$audit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 2. Ambil daftar pertanyaan yang di-assign ke audit ini
        $assignedQuestions = $db->table('audit_question_assignments as aqa')
            ->select('aqa.*, aq.clause_code, aq.question_text, aq.framework')
            ->join('audit_questions as aq', 'aq.id = aqa.question_id', 'left')
            ->where('aqa.audit_id', $id)
            ->orderBy('aq.clause_code', 'ASC')
            ->get()
            ->getResult();

        // 3. Hitung skor akhir (Compliance Rate)
        $totalQuestions = count($assignedQuestions);
        $totalScore = 0;
        $answeredCount = 0;

        foreach ($assignedQuestions as $q) {
            if ($q->score !== null) {
                $totalScore += (float) $q->score;
                $answeredCount++;
            }
        }

        // Tentukan max score berdasarkan framework
        $fw = $db->table('frameworks')->where('nama', $audit->framework)->get()->getRow();
        $isBinary = $fw && isset($fw->scoring_type) && $fw->scoring_type === 'binary';
        $maxScore = $fw ? (int) ($fw->max_score ?? 1) : 1;

        $finalScorePercent = 0;
        if ($totalQuestions > 0) {
            if ($isBinary) {
                $finalScorePercent = $answeredCount > 0 ? round(($totalScore / $answeredCount) * 100) : 0;
            } else {
                $finalScorePercent = $answeredCount > 0 ? round(($totalScore / ($answeredCount * $maxScore)) * 100, 2) : 0;
            }
        }

        // 4. Ambil temuan untuk audit ini
        $findings = $db->table('temuans')
            ->select('temuans.*, aq.clause_code')
            ->join('audit_questions as aq', 'aq.id = temuans.question_id', 'left')
            ->where('temuans.audit_id', $id)
            ->orderBy('temuans.tingkat_risiko', 'DESC')
            ->get()
            ->getResult();

        // 5. Kirim data ke view
        return view('pimpinan/daftar_lha/lha_detail', [
            'page_title'    => 'DETAIL LAPORAN HASIL AUDIT (LHA)',
            'page_subtitle' => (string) ($audit->title ?? 'Detail Audit')
                . ' — '
                . (string) ($audit->auditee_name ?? ''),
            'audit'         => $audit,
            'assignedQuestions' => $assignedQuestions,
            'totalQuestions'    => $totalQuestions,
            'finalScorePercent' => $finalScorePercent,
            'isBinary'          => $isBinary,
            'maxScore'          => $maxScore,
            'findings'          => $findings,
        ]);
    }
}
