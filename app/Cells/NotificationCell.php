<?php

namespace App\Cells;

class NotificationCell
{
    public function render()
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        $h3Date = date('Y-m-d', strtotime('+3 days'));
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        $role = session()->get('role');
        $userId = session()->get('id');

        // ==========================================
        // 1. INISIALISASI SEMUA VARIABEL DI SINI (PENTING!)
        // ==========================================
        $periodeDraft = [];
        $auditOverdue = [];
        $auditH3 = [];
        $auditBaru = [];
        $menungguPenilaian = []; // <-- INI YANG SEBELUMNYA KURANG
        $rtlPerluReview = [];
        $auditSelesai = [];
        $lhaSelesai = [];

        // ==========================================
        // 2. LOGIKA NOTIFIKASI BERDASARKAN ROLE
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

                        // 4. Audit Baru Selesai (HANYA yang BELUM dilihat Admin)
            $auditSelesai = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, a.updated_at')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->where('a.status', 'selesai')
                ->where('a.admin_viewed_at', null) // <-- TAMBAHKAN BARIS INI
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

            // 2. Audit Menunggu Penilaian (Auditee sudah submit jawaban)
            $menungguPenilaian = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, auditee.fullname as auditee_name')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
                ->where('a.created_by_auditor', $userId)
                ->where('a.status', 'menunggu_penilaian')
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()->getResult();

            // 3. RTL / Bukti Perbaikan Perlu Diverifikasi
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
            // 1. LHA Selesai (Audit baru selesai dalam 7 hari terakhir)
            $lhaSelesai = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, a.updated_at')
                ->join('periodes p', 'p.id = a.periode_id', 'left')
                ->where('a.status', 'selesai')
                ->where('a.updated_at >=', date('Y-m-d', strtotime('-7 days')))
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()->getResult();
        }

        // ==========================================
        // 3. HITUNG TOTAL & SIAPKAN DATA FILTER
        // (Sekarang aman, karena semua variabel sudah diinisialisasi di atas)
        // ==========================================
        $totalNotif = count($periodeDraft) + count($auditOverdue) + count($auditH3) + count($auditSelesai) + 
                      count($auditBaru) + count($menungguPenilaian) + count($rtlPerluReview) + count($lhaSelesai);

        $filterData = [
            'periode' => count($periodeDraft),
            'overdue' => count($auditOverdue),
            'h3' => count($auditH3),
            'selesai' => count($auditSelesai) + count($lhaSelesai),
            'audit_baru' => count($auditBaru),
            'menunggu_penilaian' => count($menungguPenilaian),
            'rtl' => count($rtlPerluReview),
        ];

        return view('layouts/partials/notification_dropdown', [
            'role'             => $role,
            'totalNotif'       => $totalNotif,
            'periodeDraft'     => $periodeDraft,
            'auditOverdue'     => $auditOverdue,
            'auditH3'          => $auditH3,
            'auditBaru'        => $auditBaru,
            'menungguPenilaian'=> $menungguPenilaian,
            'rtlPerluReview'   => $rtlPerluReview,
            'auditSelesai'     => $auditSelesai,
            'lhaSelesai'       => $lhaSelesai,
            'filterData'       => $filterData
        ]);
    }
}