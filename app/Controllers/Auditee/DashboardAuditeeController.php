<?php
namespace App\Controllers\Auditee;
use App\Controllers\BaseController;

class DashboardAuditeeController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        // 1. Tugas aktif (aktif / menunggu_penilaian)
        $audits = $db->table('audits')
            ->select('audits.*, users.fullname as auditor_name, COUNT(aqa.id) as total_questions,
      SUM(CASE WHEN aqa.answer IS NOT NULL AND aqa.answer != "" THEN 1 ELSE 0 END) as answered_count')
            ->join('users', 'users.id = audits.created_by_auditor', 'left')
            ->join('audit_question_assignments as aqa', 'aqa.audit_id = audits.id', 'left')
            ->where('audits.auditee_id', $userId)
            ->where('audits.status !=', 'selesai')
            ->groupBy('audits.id')
            ->orderBy('audits.deadline', 'ASC')
            ->get()->getResult();

        // 2. Cari semua temuan aktif milik auditee
        $temuanRows = $db->table('temuans')
            ->select('temuans.audit_id, temuans.status as t_status, temuans.rtl_description, temuans.revisi_sent_at, audits.title')
            ->join('audits', 'audits.id = temuans.audit_id')
            ->where('audits.auditee_id', $userId)
            ->whereIn('temuans.status', ['Open', 'In_Progress'])
            ->get()->getResult();

        $rtlMap = [];
        $revisiMap = [];
        $waitingMap = [];

        foreach ($temuanRows as $t) {
            // Open = urusan RTL
            if ($t->t_status === 'Open') {
                if (!isset($rtlMap[$t->audit_id])) {
                    $rtlMap[$t->audit_id] = (object) ['id' => $t->audit_id, 'title' => $t->title, 'belum_rtl' => 0];
                }
                if (empty($t->rtl_description)) {
                    $rtlMap[$t->audit_id]->belum_rtl++;
                }
            }
            // In_Progress = RTL udah di-approve
            elseif ($t->t_status === 'In_Progress') {
                if (empty($t->revisi_sent_at)) {
                    // Belum kirim revisi → PERLU REVISI
                    if (!isset($revisiMap[$t->audit_id])) {
                        $revisiMap[$t->audit_id] = (object) ['id' => $t->audit_id, 'title' => $t->title, 'jumlah_revisi' => 0];
                    }
                    $revisiMap[$t->audit_id]->jumlah_revisi++;
                } else {
                    // Udah kirim revisi → MENUNGGU VERIFIKASI
                    if (!isset($waitingMap[$t->audit_id])) {
                        $waitingMap[$t->audit_id] = (object) ['id' => $t->audit_id, 'title' => $t->title, 'jumlah_menunggu' => 0];
                    }
                    $waitingMap[$t->audit_id]->jumlah_menunggu++;
                }
            }
        }

        return view('auditee/dashboard', [
            'title' => 'Dashboard Auditee - Sistem Audit IT POLBAN',
            'page_title' => 'Tugas Audit Saya',
            'audits' => $audits,
            'rtlAudits' => array_values($rtlMap),
            'revisiAudits' => array_values($revisiMap),
            'waitingAudits' => array_values($waitingMap)
        ]);
    }

    // ===== HALAMAN DAFTAR AUDIT (BARU) =====
    public function daftarAudit()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $search  = trim((string) $this->request->getGet('q'));
        $fStatus = $this->request->getGet('status') ?? 'semua';

        $query = $db->table('audits')
            ->select('audits.*, users.fullname as auditor_name,
                COUNT(aqa.id) as total_questions,
                SUM(CASE WHEN aqa.answer IS NOT NULL AND aqa.answer != "" THEN 1 ELSE 0 END) as answered_count')
            ->join('users', 'users.id = audits.created_by_auditor', 'left')
            ->join('audit_question_assignments as aqa', 'aqa.audit_id = audits.id', 'left')
            ->where('audits.auditee_id', $userId)
            ->groupBy('audits.id')
            ->orderBy('audits.deadline', 'ASC');

        if ($search !== '')  $query->like('audits.title', $search);
        if ($fStatus !== 'semua') $query->where('audits.status', $fStatus);

        $audits = $query->get()->getResult();

        // Hitung kewajiban per audit (RTL / Revisi / Menunggu) + progres
        foreach ($audits as $a) {
            $temuans = $db->table('temuans')->where('audit_id', $a->id)->get()->getResult();
            $rtl = 0; $revisi = 0; $menunggu = 0;
            foreach ($temuans as $x) {
                if ($x->status === 'Open') $rtl++;
                elseif ($x->status === 'In_Progress') {
                    if (empty($x->revisi_sent_at)) $revisi++; else $menunggu++;
                }
            }
            $a->need_rtl    = $rtl;
            $a->need_revisi = $revisi;
            $a->waiting     = $menunggu;
            $a->progress    = ((int) $a->total_questions > 0)
                ? (int) round(((int) $a->answered_count / (int) $a->total_questions) * 100)
                : 0;
        }

        return view('auditee/daftar_audit', [
            'title'      => 'Daftar Audit Saya - Sistem Audit IT POLBAN',
            'page_title' => 'Daftar Audit Saya',
            'audits'     => $audits,
            'search'     => $search,
            'fStatus'    => $fStatus,
        ]);
    }

    public function riwayat()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        // SEMUA audit selesai masuk riwayat
        $riwayat = $db->table('audits')
            ->select('audits.*, users.fullname as auditor_name')
            ->join('users', 'users.id = audits.created_by_auditor', 'left')
            ->where('audits.auditee_id', $userId)
            ->where('audits.status', 'selesai')
            ->orderBy('audits.updated_at', 'DESC')
            ->get()->getResult();

        return view('auditee/riwayat', [
            'title' => 'Riwayat Audit - Sistem Audit IT POLBAN',
            'page_title' => 'Riwayat Audit',
            'audits' => $riwayat
        ]);
    }

    // Halaman Log Aktivitas Auditee (baca dari activity_logs, HANYA milik user login)
    public function logAktivitas()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        // Ambil filter dari URL
        $dari = $this->request->getGet('dari');
        $sampai = $this->request->getGet('sampai');
        $jenis = $this->request->getGet('jenis');

        // Total aktivitas → HANYA milik auditee yang login
        $totalAktivitas = $db->table('activity_logs')
            ->where('user_id', $userId)
            ->where('role', 'auditee')
            ->countAllResults();

        // Query dengan filter → HANYA milik auditee yang login
        $query = $db->table('activity_logs')
            ->where('user_id', $userId)
            ->where('role', 'auditee');

        if (!empty($dari))
            $query = $query->where('DATE(created_at) >=', $dari);
        if (!empty($sampai))
            $query = $query->where('DATE(created_at) <=', $sampai);
        if (!empty($jenis) && $jenis !== 'semua')
            $query = $query->where('action', $jenis);

        $logs = $query->orderBy('created_at', 'DESC')->limit(100)->get()->getResult();

        // Parse user agent mentah jadi "Chrome - Windows 10"
        foreach ($logs as $log) {
            $log->browser = parse_user_agent($log->user_agent ?? '');
        }

        return view('auditee/log_aktivitas', [
            'title' => 'Riwayat Aktivitas Saya - Sistem Audit IT POLBAN',
            'page_title' => 'Riwayat Aktivitas Saya',
            'logs' => $logs,
            'totalAktivitas' => $totalAktivitas,
            'filterDari' => $dari,
            'filterSampai' => $sampai,
            'filterJenis' => $jenis,
        ]);
    }
}