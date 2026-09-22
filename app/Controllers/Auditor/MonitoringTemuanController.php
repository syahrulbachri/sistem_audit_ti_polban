<?php

namespace App\Controllers\Auditor;

use App\Controllers\BaseController;

class MonitoringTemuanController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $auditorId = session()->get('id');

        // Ambil parameter filter
        $search        = $this->request->getGet('search') ?? '';
        $filterStatus  = $this->request->getGet('status') ?? '';
        $filterRisiko  = $this->request->getGet('risiko') ?? '';
        $filterPeriode = $this->request->getGet('periode') ?? '';

        // Ambil daftar periode untuk dropdown
        $periodes = $db->table('periodes')->orderBy('nama_periode', 'DESC')->get()->getResult();

        // Build query utama
        $builder = $db->table('temuans')
            ->select('temuans.*, 
                                aq.clause_code,
                                a.title as audit_title,
                                a.framework,
                                a.periode_id,
                                p.nama_periode,
                                u.fullname as auditee_name')
            ->join('audit_questions as aq', 'aq.id = temuans.question_id', 'left')
            ->join('audits as a', 'a.id = temuans.audit_id', 'left')
            ->join('periodes as p', 'p.id = a.periode_id', 'left')
            ->join('users as u', 'u.id = a.auditee_id', 'left')
            ->where('a.created_by_auditor', $auditorId);

        // Terapkan filter
        if (!empty($search)) {
            $builder->groupStart()
                ->like('temuans.deskripsi_temuan', $search)
                ->orLike('a.title', $search)
                ->orLike('u.fullname', $search)
                ->groupEnd();
        }
        if (!empty($filterStatus)) $builder->where('temuans.status', $filterStatus);
        if (!empty($filterRisiko)) $builder->where('temuans.tingkat_risiko', $filterRisiko);
        if (!empty($filterPeriode)) $builder->where('a.periode_id', $filterPeriode);

        $findings = $builder->orderBy('temuans.tingkat_risiko', 'DESC')->orderBy('temuans.created_at', 'DESC')->get()->getResult();

        // Hitung statistik
        $statsBuilder = $db->table('temuans')->join('audits as a', 'a.id = temuans.audit_id')->where('a.created_by_auditor', $auditorId);
        $stats = [
            'total'       => (clone $statsBuilder)->countAllResults(),
            'open'        => (clone $statsBuilder)->where('temuans.status', 'Open')->countAllResults(),
            'in_progress' => (clone $statsBuilder)->where('temuans.status', 'In_Progress')->countAllResults(),
            'closed'      => (clone $statsBuilder)->where('temuans.status', 'Closed')->countAllResults(),
        ];

        return view('auditor/monitoring_temuan', [
            'title' => 'Monitoring Temuan - Sistem Audit IT POLBAN',
            'page_title' => 'Monitoring Temuan',
            'findings' => $findings,
            'periodes' => $periodes,
            'search' => $search,
            'filterStatus' => $filterStatus,
            'filterRisiko' => $filterRisiko,
            'filterPeriode' => $filterPeriode,
            'stats' => $stats
        ]);
    }

    public function detail(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $auditorId = session()->get('id');

        $finding = $db->table('temuans')
            ->select('temuans.*, 
                                aq.clause_code, aq.question_text, 
                                a.title as audit_title, a.framework, 
                                p.nama_periode, 
                                u.fullname as auditee_name')
            ->join('audit_questions as aq', 'aq.id = temuans.question_id', 'left')
            ->join('audits as a', 'a.id = temuans.audit_id', 'left')
            ->join('periodes as p', 'p.id = a.periode_id', 'left')
            ->join('users as u', 'u.id = a.auditee_id', 'left')
            ->where('temuans.id', $id)
            ->where('a.created_by_auditor', $auditorId)
            ->get()
            ->getRow();

        if (!$finding) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        return view('auditor/detail_temuan', [
            'title' => 'Detail Temuan #' . $id . ' - Sistem Audit IT POLBAN',
            'page_title' => 'Detail Temuan #' . $id,
            'finding' => $finding
        ]);
    }

    // TAHAP 1: Review RTL (Untuk status Open)
    public function reviewRTL(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $action = $this->request->getPost('rtl_action'); // 'approve' atau 'reject'
        $catatan = $this->request->getPost('catatan_rtl') ?? '';

        if (empty($catatan)) {
            return redirect()->back()->withInput()->with('error', 'Catatan review wajib diisi!');
        }

        $finding = $db->table('temuans')
            ->select('temuans.*, a.created_by_auditor, a.title as audit_title')
            ->join('audits as a', 'a.id = temuans.audit_id')
            ->where('temuans.id', $id)
            ->get()
            ->getRow();

        if (!$finding) {
            return redirect()->to('/auditor/monitoring-temuan')->with('error', 'Data temuan tidak ditemukan.');
        }

        if ($finding->status !== 'Open') {
            return redirect()->to('/auditor/monitoring-temuan')->with('error', 'Temuan tidak valid untuk di-review. Status saat ini: ' . $finding->status);
        }

        $newStatus = ($action === 'approve') ? 'In_Progress' : 'Open';

        $db->table('temuans')->where('id', $id)->update([
            'status'       => $newStatus,
            'auditor_note' => $catatan
        ]);

        // TAMBAHKAN LOG DI SINI
        $logDesc = ($action === 'approve')
            ? 'Menyetujui RTL dan mengubah status temuan menjadi In_Progress pada audit "' . $finding->audit_title . '".'
            : 'Menolak RTL temuan (status tetap Open) pada audit "' . $finding->audit_title . '".';

        log_activity('UPDATE', 'temuans', $logDesc);

        $msg = ($action === 'approve') ? 'RTL disetujui. Auditee dapat mulai mengerjakan perbaikan.' : 'RTL ditolak. Silakan minta Auditee merevisi rencana.';
        return redirect()->to('/auditor/monitoring-temuan')->with('success', $msg);
    }

    // TAHAP 2: Verifikasi Bukti (Untuk status In_Progress)
    public function verifikasiBukti(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $action = $this->request->getPost('verifikasi_action'); // 'approve' atau 'reject'
        $catatan = $this->request->getPost('catatan_verifikasi') ?? '';

        if (empty($catatan)) {
            return redirect()->back()->withInput()->with('error', 'Catatan verifikasi wajib diisi!');
        }

        $finding = $db->table('temuans')
            ->select('temuans.*, a.created_by_auditor, a.title as audit_title')
            ->join('audits as a', 'a.id = temuans.audit_id')
            ->where('temuans.id', $id)
            ->get()
            ->getRow();

        if (!$finding) {
            return redirect()->to('/auditor/monitoring-temuan')->with('error', 'Data temuan tidak ditemukan.');
        }

        if ($finding->status !== 'In_Progress') {
            return redirect()->to('/auditor/monitoring-temuan')->with('error', 'Temuan tidak valid untuk diverifikasi. Status saat ini: ' . $finding->status);
        }

        $newStatus = ($action === 'approve') ? 'Closed' : 'In_Progress';

        $updateData = [
            'status'       => $newStatus,
            'auditor_note' => $catatan
        ];

        if ($action === 'approve') {
            $updateData['closed_at'] = date('Y-m-d H:i:s');
        }

        $db->table('temuans')->where('id', $id)->update($updateData);

        // TAMBAHKAN LOG DI SINI (Termasuk saat menutup temuan)
        if ($action === 'approve') {
            log_activity('UPDATE', 'temuans', 'Menyetujui bukti perbaikan dan MENUTUP temuan (Closed) pada audit "' . $finding->audit_title . '".');
        } else {
            log_activity('UPDATE', 'temuans', 'Menolak bukti perbaikan temuan (kembali ke In_Progress) pada audit "' . $finding->audit_title . '".');
        }

        $msg = ($action === 'approve') ? 'Bukti disetujui. Temuan berhasil ditutup (Closed).' : 'Bukti ditolak. Auditee harus memperbaiki kembali.';
        return redirect()->to('/auditor/monitoring-temuan')->with('success', $msg);
    }
}
