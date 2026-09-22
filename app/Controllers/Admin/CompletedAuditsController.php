<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class CompletedAuditsController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        $filterPeriode   = $this->request->getGet('periode') ?? '';
        $filterAuditor   = $this->request->getGet('auditor') ?? '';
        $filterAuditee   = $this->request->getGet('auditee') ?? '';
        $filterFramework = $this->request->getGet('framework') ?? '';

        $periodes   = $db->table('periodes')->orderBy('nama_periode', 'DESC')->get()->getResult();
        $frameworks = $db->table('frameworks')->where('is_active', 1)->orderBy('nama', 'ASC')->get()->getResult();

        $builder = $db->table('audits a')
            ->select('a.*, p.nama_periode, auditor.fullname as auditor_name, auditee.fullname as auditee_name')
            ->join('periodes p', 'p.id = a.periode_id', 'left')
            ->join('users auditor', 'auditor.id = a.created_by_auditor', 'left')
            ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
            ->where('a.status', 'selesai');

        if (!empty($filterPeriode)) $builder->where('a.periode_id', $filterPeriode);
        if (!empty($filterAuditor)) $builder->like('auditor.fullname', $filterAuditor);
        if (!empty($filterAuditee)) $builder->like('auditee.fullname', $filterAuditee);
        if (!empty($filterFramework)) $builder->where('a.framework', $filterFramework);

        $audits = $builder->orderBy('a.updated_at', 'DESC')->get()->getResult();

        return view('admin/completed_audits/index', [
            'title'           => 'Riwayat Audit - Sistem Audit IT POLBAN',
            'page_title'      => 'Riwayat Audit Selesai',
            'audits'          => $audits,
            'periodes'        => $periodes,
            'frameworks'      => $frameworks,
            'filterPeriode'   => $filterPeriode,
            'filterAuditor'   => $filterAuditor,
            'filterAuditee'   => $filterAuditee,
            'filterFramework' => $filterFramework
        ]);
    }

    public function detail(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        $audit = $db->table('audits a')
            ->select('a.*, p.nama_periode, auditor.fullname as auditor_name, auditee.fullname as auditee_name')
            ->join('periodes p', 'p.id = a.periode_id', 'left')
            ->join('users auditor', 'auditor.id = a.created_by_auditor', 'left')
            ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
            ->where('a.id', $id)
            ->where('a.status', 'selesai')
            ->get()->getRow();

        if (!$audit) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $assignedQuestions = $db->table('audit_question_assignments aqa')
            ->select('aqa.*, aq.question_text, aq.clause_code')
            ->join('audit_questions aq', 'aq.id = aqa.question_id')
            ->where('aqa.audit_id', $id)
            ->orderBy('aq.clause_code', 'ASC')->get()->getResult();

        $findings = $db->table('temuans t')
            ->select('t.*, aq.clause_code')
            ->join('audit_questions aq', 'aq.id = t.question_id', 'left')
            ->where('t.audit_id', $id)
            ->orderBy('t.tingkat_risiko', 'DESC')->get()->getResult();

        $fw = $db->table('frameworks')->where('nama', $audit->framework)->get()->getRow();
        $isBinary = $fw && $fw->scoring_type === 'binary';

        return view('admin/completed_audits/detail', [
            'title'             => 'Detail Audit - Sistem Audit IT POLBAN',
            'page_title'        => 'Detail Audit: ' . $audit->title,
            'audit'             => $audit,
            'assignedQuestions' => $assignedQuestions,
            'findings'          => $findings,
            'isBinary'          => $isBinary
        ]);
    }

        // Method khusus untuk mencatat log sebelum cetak
    public function logExport(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak.'])->setStatusCode(403);
        }

        $db = \Config\Database::connect();
        $audit = $db->table('audits')->where('id', $id)->get()->getRow();

        if ($audit) {
            // Catat ke activity_logs
            log_activity('EXPORT', 'audits', 'Mencetak laporan PDF audit "' . $audit->title . '" (Status: Selesai)');
            
            return $this->response->setJSON(['status' => 'success']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak ditemukan.'])->setStatusCode(404);
    }
}