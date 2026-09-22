<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class FindingsController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil parameter filter
        $filterPeriode   = $this->request->getGet('periode') ?? '';
        $filterAuditor   = $this->request->getGet('auditor') ?? '';
        $filterAuditee   = $this->request->getGet('auditee') ?? '';
        $filterFramework = $this->request->getGet('framework') ?? '';
        $filterStatus    = $this->request->getGet('status') ?? '';
        $filterRisiko    = $this->request->getGet('risiko') ?? '';

        // Ambil data untuk dropdown
        $periodes   = $db->table('periodes')->orderBy('nama_periode', 'DESC')->get()->getResult();
        $frameworks = $db->table('frameworks')->orderBy('nama', 'ASC')->get()->getResult();

        // Build query utama: ambil audit yang memiliki temuan
        $builder = $db->table('audits a')
            ->select('a.*, 
                     p.nama_periode,
                     auditor.fullname as auditor_name,
                     auditee.fullname as auditee_name,
                     COUNT(t.id) as total_findings')
            ->join('periodes p', 'p.id = a.periode_id', 'left')
            ->join('users auditor', 'auditor.id = a.created_by_auditor', 'left')
            ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
            ->join('temuans t', 't.audit_id = a.id', 'left')
            ->groupBy('a.id')
            ->having('COUNT(t.id) >', 0);

        // Apply filters
        if (!empty($filterPeriode)) {
            $builder->where('a.periode_id', $filterPeriode);
        }
        if (!empty($filterAuditor)) {
            $builder->like('auditor.fullname', $filterAuditor);
        }
        if (!empty($filterAuditee)) {
            $builder->like('auditee.fullname', $filterAuditee);
        }
        if (!empty($filterFramework)) {
            $builder->where('a.framework', $filterFramework);
        }
        if (!empty($filterStatus)) {
            // Filter berdasarkan status temuan
            $builder->join('temuans t2', 't2.audit_id = a.id', 'left')
                ->where('t2.status', $filterStatus);
        }
        if (!empty($filterRisiko)) {
            // Filter berdasarkan tingkat risiko temuan
            $builder->join('temuans t3', 't3.audit_id = a.id', 'left')
                ->where('t3.tingkat_risiko', $filterRisiko);
        }

        $audits = $builder->orderBy('a.updated_at', 'DESC')->get()->getResult();

        return view('admin/findings/index', [
            'title'           => 'Monitoring Temuan - Sistem Audit IT POLBAN',
            'page_title'      => 'Monitoring Temuan Audit',
            'audits'          => $audits,
            'periodes'        => $periodes,
            'frameworks'      => $frameworks,
            'filterPeriode'   => $filterPeriode,
            'filterAuditor'   => $filterAuditor,
            'filterAuditee'   => $filterAuditee,
            'filterFramework' => $filterFramework,
            'filterStatus'    => $filterStatus,
            'filterRisiko'    => $filterRisiko
        ]);
    }

    public function detail(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil data audit
        $audit = $db->table('audits a')
            ->select('a.*, p.nama_periode, auditor.fullname as auditor_name, auditee.fullname as auditee_name')
            ->join('periodes p', 'p.id = a.periode_id', 'left')
            ->join('users auditor', 'auditor.id = a.created_by_auditor', 'left')
            ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
            ->where('a.id', $id)
            ->get()->getRow();

        if (!$audit) {
            return redirect()->to('/admin/findings')->with('error', 'Data audit tidak ditemukan.');
        }

        // Ambil semua temuan dalam audit ini
        $findings = $db->table('temuans t')
            ->select('t.*, aq.clause_code, aq.question_text')
            ->join('audit_questions aq', 'aq.id = t.question_id', 'left')
            ->where('t.audit_id', $id)
            ->orderBy('t.tingkat_risiko', 'DESC')
            ->get()->getResult();

        return view('admin/findings/detail', [
            'title'      => 'Detail Temuan Audit - Sistem Audit IT POLBAN',
            'page_title' => 'Detail Temuan: ' . $audit->title,
            'audit'      => $audit,
            'findings'   => $findings
        ]);
    }
}
