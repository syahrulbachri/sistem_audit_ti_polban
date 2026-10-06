<?php

namespace App\Cells;

class NotificationCell
{
    public function render()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        $h3Date = date('Y-m-d', strtotime('+3 days'));
        $yesterday = date('Y-m-d', strtotime('-1 day')); // Untuk audit yang selesai dalam 24 jam

        $role = session()->get('role');
        $userId = session()->get('id');

        // Inisialisasi variabel
        $periodeDraft = [];
        $auditOverdue = [];
        $auditH3 = [];
        $auditBaru = [];
        $rtlPerluReview = [];
        $auditSelesai = [];
        $lhaSelesai = [];
        // Khusus pimpinan (read-only, 7 hari terakhir)
        $auditBaruPimpinan = [];
        $temuanBaru = [];
        $rtlMenunggu = [];
        $temuanTerverifikasi = [];

        // ==========================================
        // LOGIKA NOTIFIKASI BERDASARKAN ROLE
        // ==========================================

        if ($role === 'admin') {
            // 1. Periode yang siap diaktifkan
            $periodeDraft = $db->table('periodes')
                ->where('tanggal_mulai <=', $today)
                ->where('status', 'draft')
                ->orderBy('tanggal_mulai', 'ASC')
                ->get()->getResult();

            // 2. Audit Melebihi Deadline (Overdue)
            $auditOverdue = $db->table('audits a')
                ->select('a.id, a.title, a.deadline, auditee.fullname as auditee_name')
                ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
                ->where('a.deadline <', $today)
                ->whereNotIn('a.status', ['selesai'])
                ->orderBy('a.deadline', 'ASC')
                ->get()->getResult();

            // 3. Audit H-3 Warning
            $auditH3 = $db->table('audits a')
                ->select('a.id, a.title, a.deadline, auditee.fullname as auditee_name')
                ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
                ->where('a.deadline >=', $today)
                ->where('a.deadline <=', $h3Date)
                ->whereNotIn('a.status', ['selesai'])
                ->orderBy('a.deadline', 'ASC')
                ->get()->getResult();

            // 4. Audit Baru Selesai (dalam 24 jam terakhir) - BARU!
            $auditSelesai = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, a.updated_at')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->where('a.status', 'selesai')
                ->where('a.updated_at >=', $yesterday)
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()->getResult();
        } elseif ($role === 'auditor') {
            // 1. Audit Baru Ditugaskan (Status Aktif)
            $auditBaru = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->where('a.created_by_auditor', $userId)
                ->where('a.status', 'aktif')
                ->orderBy('a.created_at', 'DESC')
                ->limit(5)
                ->get()->getResult();

            // 2. RTL / Bukti Perbaikan Perlu Diverifikasi
            $rtlPerluReview = $db->table('temuans t')
                ->select('t.id, q.clause_code, a.title as audit_title, auditee.fullname as auditee_name')
                ->join('audits a', 'a.id = t.audit_id', 'left')
                ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
                ->join('audit_questions q', 'q.id = t.question_id', 'left')
                ->where('a.created_by_auditor', $userId)
                ->where('t.status', 'In_Progress')
                ->orderBy('t.updated_at', 'DESC')
                ->limit(5)
                ->get()->getResult();

            // 3. Audit Selesai yang Ditugaskan ke Auditor Ini
            $auditSelesai = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, a.updated_at')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->where('a.created_by_auditor', $userId)
                ->where('a.status', 'selesai')
                ->where('a.updated_at >=', $yesterday)
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()->getResult();
        } elseif ($role === 'auditee') {
            // 1. Audit Baru Ditugaskan
            $auditBaru = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, a.deadline')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->where('a.auditee_id', $userId)
                ->whereIn('a.status', ['aktif', 'menunggu_jawaban'])
                ->orderBy('a.created_at', 'DESC')
                ->limit(5)
                ->get()->getResult();
        } elseif ($role === 'pimpinan') {
            $h7Date = date('Y-m-d H:i:s', strtotime('-7 days'));

            // 1. LHA Selesai (Audit baru selesai dalam 7 hari terakhir)
            $lhaSelesai = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, a.updated_at')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->where('a.status', 'selesai')
                ->where('a.updated_at >=', date('Y-m-d', strtotime('-7 days')))
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()->getResult();

            // 2. Audit Baru Masuk (audit baru dibuat/berjalan, dalam 7 hari terakhir)
            $auditBaruPimpinan = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, auditee.fullname as auditee_name, a.created_at')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
                ->where('a.created_at >=', $h7Date)
                ->orderBy('a.created_at', 'DESC')
                ->limit(5)
                ->get()->getResult();

            // 3. Temuan Baru (temuan audit baru dicatat auditor, dalam 7 hari terakhir)
            $temuanBaru = $db->table('temuans t')
                ->select('t.id, t.tingkat_risiko, a.title as audit_title, auditee.fullname as auditee_name, t.created_at')
                ->join('audits a', 'a.id = t.audit_id', 'left')
                ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
                ->where('t.created_at >=', $h7Date)
                ->orderBy('t.created_at', 'DESC')
                ->limit(5)
                ->get()->getResult();

            // 4. RTL Menunggu Persetujuan (auditee sudah mengajukan rencana, menunggu direview auditor)
            $rtlMenunggu = $db->table('temuans t')
                ->select('t.id, a.title as audit_title, auditee.fullname as auditee_name, t.updated_at')
                ->join('audits a', 'a.id = t.audit_id', 'left')
                ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
                ->where('t.status', 'Open')
                ->where('t.rtl_description IS NOT NULL')
                ->where('t.updated_at >=', $h7Date)
                ->orderBy('t.updated_at', 'DESC')
                ->limit(5)
                ->get()->getResult();

            // 5. RTL Terverifikasi/Closed (update status terbaru, dalam 7 hari terakhir)
            $temuanTerverifikasi = $db->table('temuans t')
                ->select('t.id, a.title as audit_title, auditee.fullname as auditee_name, t.closed_at')
                ->join('audits a', 'a.id = t.audit_id', 'left')
                ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
                ->where('t.status', 'Closed')
                ->where('t.closed_at >=', $h7Date)
                ->orderBy('t.closed_at', 'DESC')
                ->limit(5)
                ->get()->getResult();
        }

        // Hitung total notifikasi untuk badge merah
        $totalNotif = count($periodeDraft) + count($auditOverdue) + count($auditH3) +
            count($auditBaru) + count($rtlPerluReview) + count($auditSelesai) + count($lhaSelesai) +
            count($auditBaruPimpinan) + count($temuanBaru) + count($rtlMenunggu) + count($temuanTerverifikasi);

        // Siapkan data filter untuk JavaScript
        $filterData = [
            'periode' => count($periodeDraft),
            'overdue' => count($auditOverdue),
            'h3' => count($auditH3),
            'selesai' => count($auditSelesai) + count($lhaSelesai),
            'audit_baru' => count($auditBaru) + count($auditBaruPimpinan),
            'rtl' => count($rtlPerluReview) + count($rtlMenunggu),
            'temuan_baru' => count($temuanBaru),
            'rtl_closed' => count($temuanTerverifikasi),
        ];

        return view('layouts/partials/notification_dropdown', [
            'role'             => $role,
            'totalNotif'       => $totalNotif,
            'periodeDraft'     => $periodeDraft,
            'auditOverdue'     => $auditOverdue,
            'auditH3'          => $auditH3,
            'auditBaru'        => $auditBaru,
            'rtlPerluReview'   => $rtlPerluReview,
            'auditSelesai'     => $auditSelesai,
            'lhaSelesai'       => $lhaSelesai,
            'auditBaruPimpinan'    => $auditBaruPimpinan,
            'temuanBaru'           => $temuanBaru,
            'rtlMenunggu'          => $rtlMenunggu,
            'temuanTerverifikasi'  => $temuanTerverifikasi,
            'filterData'       => $filterData
        ]);
    }
}
