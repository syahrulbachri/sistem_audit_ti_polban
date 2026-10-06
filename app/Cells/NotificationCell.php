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
        // 1. INISIALISASI SEMUA VARIABEL
        // ==========================================

        // Notifikasi umum
        $periodeDraft = [];
        $auditOverdue = [];
        $auditH3 = [];
        $auditBaru = [];
        $menungguPenilaian = [];
        $rtlPerluReview = [];
        $auditSelesai = [];
        $lhaSelesai = [];

        // Khusus Pimpinan
        $temuanBaru = [];
        $rtlMenunggu = [];
        $temuanTerverifikasi = [];

        // ==========================================
        // 2. LOGIKA NOTIFIKASI BERDASARKAN ROLE
        // ==========================================

        // =====================================================
        // ADMIN
        // =====================================================
        if ($role === 'admin') {

            // 1. Periode yang siap diaktifkan
            $periodeDraft = $db->table('periodes')
                ->where('tanggal_mulai <=', $today)
                ->where('status', 'draft')
                ->orderBy('tanggal_mulai', 'ASC')
                ->get()
                ->getResult();

            // 2. Audit Melebihi Deadline (Overdue)
            $auditOverdue = $db->table('audits a')
                ->select('a.id, a.title, a.deadline, auditee.fullname as auditee_name')
                ->join(
                    'users auditee',
                    'auditee.id = a.auditee_id',
                    'left'
                )
                ->where('a.deadline <', $today)
                ->whereNotIn('a.status', ['selesai'])
                ->orderBy('a.deadline', 'ASC')
                ->get()
                ->getResult();

            // 3. Audit H-3 Warning
            $auditH3 = $db->table('audits a')
                ->select('a.id, a.title, a.deadline, auditee.fullname as auditee_name')
                ->join(
                    'users auditee',
                    'auditee.id = a.auditee_id',
                    'left'
                )
                ->where('a.deadline >=', $today)
                ->where('a.deadline <=', $h3Date)
                ->whereNotIn('a.status', ['selesai'])
                ->orderBy('a.deadline', 'ASC')
                ->get()
                ->getResult();

            // 4. Audit Baru Selesai
            // Hanya menampilkan audit selesai yang belum dilihat Admin
            $auditSelesai = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode, a.updated_at')
                ->join(
                    'periodes p',
                    'p.id = a.periode_id',
                    'left'
                )
                ->where('a.status', 'selesai')
                ->where('a.admin_viewed_at', null)
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();
        }

        // =====================================================
        // AUDITOR
        // =====================================================
        elseif ($role === 'auditor') {

            // 1. Audit Baru Ditugaskan / Dibuat
            $auditBaru = $db->table('audits a')
                ->select('a.id, a.title, p.nama_periode')
                ->join(
                    'periodes p',
                    'p.id = a.periode_id',
                    'left'
                )
                ->where('a.created_by_auditor', $userId)
                ->where('a.status', 'aktif')
                ->orderBy('a.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();

            // 2. Audit Menunggu Penilaian
            // Auditee sudah mengirim jawaban
            $menungguPenilaian = $db->table('audits a')
                ->select(
                    'a.id, a.title, p.nama_periode, auditee.fullname as auditee_name'
                )
                ->join(
                    'periodes p',
                    'p.id = a.periode_id',
                    'left'
                )
                ->join(
                    'users auditee',
                    'auditee.id = a.auditee_id',
                    'left'
                )
                ->where('a.created_by_auditor', $userId)
                ->where('a.status', 'menunggu_penilaian')
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();

            // 3. RTL / Bukti Perbaikan Perlu Diverifikasi
            $rtlPerluReview = $db->table('temuans t')
                ->select(
                    't.id, q.clause_code, a.title as audit_title, auditee.fullname as auditee_name'
                )
                ->join(
                    'audits a',
                    'a.id = t.audit_id',
                    'left'
                )
                ->join(
                    'users auditee',
                    'auditee.id = a.auditee_id',
                    'left'
                )
                ->join(
                    'audit_questions q',
                    'q.id = t.question_id',
                    'left'
                )
                ->where('a.created_by_auditor', $userId)
                ->where('t.status', 'In_Progress')
                ->orderBy('t.updated_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();

            // 4. Audit Selesai yang Ditugaskan ke Auditor Ini
            // Hanya audit yang selesai dalam 24 jam terakhir
            $auditSelesai = $db->table('audits a')
                ->select(
                    'a.id, a.title, p.nama_periode, a.updated_at'
                )
                ->join(
                    'periodes p',
                    'p.id = a.periode_id',
                    'left'
                )
                ->where('a.created_by_auditor', $userId)
                ->where('a.status', 'selesai')
                ->where('a.updated_at >=', $yesterday)
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();
        }

        // =====================================================
        // AUDITEE
        // =====================================================
        elseif ($role === 'auditee') {

            // 1. Audit Baru Ditugaskan
            $auditBaru = $db->table('audits a')
                ->select(
                    'a.id, a.title, p.nama_periode, a.deadline'
                )
                ->join(
                    'periodes p',
                    'p.id = a.periode_id',
                    'left'
                )
                ->where('a.auditee_id', $userId)
                ->whereIn(
                    'a.status',
                    ['aktif', 'menunggu_jawaban']
                )
                ->orderBy('a.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();
        }

        // =====================================================
        // PIMPINAN
        // =====================================================
        elseif ($role === 'pimpinan') {

            $h7Date = date(
                'Y-m-d H:i:s',
                strtotime('-7 days')
            );

            // 1. LHA Selesai
            // Audit yang baru selesai dalam 7 hari terakhir
            $lhaSelesai = $db->table('audits a')
                ->select(
                    'a.id, a.title, p.nama_periode, a.updated_at'
                )
                ->join(
                    'periodes p',
                    'p.id = a.periode_id',
                    'left'
                )
                ->where('a.status', 'selesai')
                ->where(
                    'a.updated_at >=',
                    date(
                        'Y-m-d',
                        strtotime('-7 days')
                    )
                )
                ->orderBy('a.updated_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();

            // 2. Temuan Baru
            // Temuan yang baru dicatat auditor dalam 7 hari terakhir
            $temuanBaru = $db->table('temuans t')
                ->select(
                    't.id, t.tingkat_risiko, a.title as audit_title, auditee.fullname as auditee_name, t.created_at'
                )
                ->join(
                    'audits a',
                    'a.id = t.audit_id',
                    'left'
                )
                ->join(
                    'users auditee',
                    'auditee.id = a.auditee_id',
                    'left'
                )
                ->where('t.created_at >=', $h7Date)
                ->orderBy('t.created_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();

            // 3. RTL Menunggu Persetujuan
            // Auditee sudah mengajukan rencana dan
            // masih menunggu proses review
            $rtlMenunggu = $db->table('temuans t')
                ->select(
                    't.id, a.title as audit_title, auditee.fullname as auditee_name, t.updated_at'
                )
                ->join(
                    'audits a',
                    'a.id = t.audit_id',
                    'left'
                )
                ->join(
                    'users auditee',
                    'auditee.id = a.auditee_id',
                    'left'
                )
                ->where('t.status', 'Open')
                ->where('t.rtl_description IS NOT NULL')
                ->where('t.updated_at >=', $h7Date)
                ->orderBy('t.updated_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();

            // 4. RTL Terverifikasi / Closed
            // RTL yang baru ditutup dalam 7 hari terakhir
            $temuanTerverifikasi = $db->table('temuans t')
                ->select(
                    't.id, a.title as audit_title, auditee.fullname as auditee_name, t.closed_at'
                )
                ->join(
                    'audits a',
                    'a.id = t.audit_id',
                    'left'
                )
                ->join(
                    'users auditee',
                    'auditee.id = a.auditee_id',
                    'left'
                )
                ->where('t.status', 'Closed')
                ->where('t.closed_at >=', $h7Date)
                ->orderBy('t.closed_at', 'DESC')
                ->limit(5)
                ->get()
                ->getResult();
        }

        // ==========================================
        // 3. HITUNG TOTAL NOTIFIKASI
        // ==========================================

        $totalNotif =
            count($periodeDraft)
            + count($auditOverdue)
            + count($auditH3)
            + count($auditSelesai)
            + count($auditBaru)
            + count($menungguPenilaian)
            + count($rtlPerluReview)
            + count($lhaSelesai)
            + count($temuanBaru)
            + count($rtlMenunggu)
            + count($temuanTerverifikasi);

        // ==========================================
        // 4. DATA FILTER NOTIFIKASI
        // ==========================================

        $filterData = [
            'periode' => count($periodeDraft),

            'overdue' => count($auditOverdue),

            'h3' => count($auditH3),

            'selesai' => count($auditSelesai)
                + count($lhaSelesai),

            'audit_baru' => count($auditBaru),

            'menunggu_penilaian' => count($menungguPenilaian),

            'rtl' => count($rtlPerluReview)
                + count($rtlMenunggu),

            'temuan_baru' => count($temuanBaru),

            'rtl_closed' => count($temuanTerverifikasi),
        ];

        // ==========================================
        // 5. KIRIM DATA KE VIEW
        // ==========================================

        return view(
            'layouts/partials/notification_dropdown',
            [
                'role' => $role,

                'totalNotif' => $totalNotif,

                'periodeDraft' => $periodeDraft,

                'auditOverdue' => $auditOverdue,

                'auditH3' => $auditH3,

                'auditBaru' => $auditBaru,

                'menungguPenilaian' => $menungguPenilaian,

                'rtlPerluReview' => $rtlPerluReview,

                'auditSelesai' => $auditSelesai,

                'lhaSelesai' => $lhaSelesai,

                'temuanBaru' => $temuanBaru,

                'rtlMenunggu' => $rtlMenunggu,

                'temuanTerverifikasi' => $temuanTerverifikasi,

                'filterData' => $filterData,
            ]
        );
    }
}