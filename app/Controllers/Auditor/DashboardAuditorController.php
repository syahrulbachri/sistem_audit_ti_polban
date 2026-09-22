<?php

namespace App\Controllers\Auditor;

use App\Controllers\BaseController; // <--- PERBAIKAN DI SINI
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class DashboardAuditorController extends BaseController
{

    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // TAMBAHKAN INI: Ambil ID auditor yang login
        $auditorId = session()->get('id');  // <-- TAMBAHKAN BARIS INI

        // Statistik - TAMBAHKAN FILTER created_by_auditor
        $stats['aktif']    = $db->table('audits')->where('status', 'aktif')->where('created_by_auditor', $auditorId)->countAllResults();  // <-- TAMBAHKAN ->where('created_by_auditor', $auditorId)
        $stats['menunggu'] = $db->table('audits')->where('status', 'menunggu_penilaian')->where('created_by_auditor', $auditorId)->countAllResults();  // <-- TAMBAHKAN ->where('created_by_auditor', $auditorId)
        $stats['selesai']  = $db->table('audits')->where('status', 'selesai')->where('created_by_auditor', $auditorId)->countAllResults();  // <-- TAMBAHKAN ->where('created_by_auditor', $auditorId)
        $stats['total']    = $db->table('audits')->where('created_by_auditor', $auditorId)->countAllResults();  // <-- TAMBAHKAN ->where('created_by_auditor', $auditorId)

        // QUERY YANG DIPERBAIKI - JOIN 2 TABEL USERS
        $audits = $db->table('audits')
            ->select('audits.*, 
                           auditee.fullname as auditee_name, 
                           auditor.fullname as auditor_name')
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->join('users as auditor', 'auditor.id = audits.created_by_auditor', 'left')
            ->where('audits.created_by_auditor', $auditorId)  // <-- TAMBAHKAN BARIS INI
            ->orderBy('audits.created_at', 'DESC')
            ->limit(6)
            ->get()
            ->getResult();

        return view('auditor/dashboard', [
            'title'      => 'Dashboard Auditor - Sistem Audit IT POLBAN',
            'page_title' => 'Dashboard Auditor',
            'stats'      => $stats,
            'audits'     => $audits
        ]);
    }

    // Di dalam class Dashboard, tambahkan method ini:

    public function detail(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $auditorId = session()->get('id');

        // 1. Ambil data audit TANPA JOIN yang bisa menyebabkan NULL
        $audit = $db->table('audits')
            ->select('audits.*, 
                     periodes.nama_periode, 
                     auditee.fullname as auditee_name, 
                     auditor.fullname as auditor_name')
            ->join('periodes', 'periodes.id = audits.periode_id', 'left')  // LEFT JOIN
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->join('users as auditor', 'auditor.id = audits.created_by_auditor', 'left')
            ->where('audits.id', $id)
            ->where('audits.created_by_auditor', $auditorId)
            ->get()
            ->getRow();

        if (!$audit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Debug: Cek data audit
        log_message('debug', '=== DETAIL AUDIT DEBUG ===');
        log_message('debug', 'Audit ID: ' . $audit->id);
        log_message('debug', 'Audit Title: ' . $audit->title);
        log_message('debug', 'Audit Framework: [' . $audit->framework . ']');
        log_message('debug', 'Audit Status: ' . $audit->status);

        // 2. Logika Data Berdasarkan Status
        $customQuestions = [];
        $standardQuestions = [];
        $assignedQuestions = [];
        $assignedIds = [];
        $selectedCount = 0;
        $findings = [];

        // ✅ PASTE KODE BARU INI:
        // 2. Logika Data Berdasarkan Status
        $customQuestions = [];
        $standardQuestions = [];
        $assignedQuestions = [];
        $assignedIds = [];
        $selectedCount = 0;
        $findings = [];
        $lockedQuestions = [];
        $isLocked = false;

        if ($audit->status === 'aktif') {
            // CEK: Apakah auditor SUDAH PERNAH memilih pertanyaan standar?
            // Hanya hitung pertanyaan standar (is_standard = 1), jangan hitung custom
            $hasAssignments = $db->table('audit_question_assignments as aqa')
                ->join('audit_questions as aq', 'aq.id = aqa.question_id')
                ->where('aqa.audit_id', $id)
                ->where('aq.is_standard', 1)  // Hanya pertanyaan standar
                ->countAllResults() > 0;

            if ($hasAssignments) {
                // === MODE TERKUNCI (READ-ONLY) ===
                $lockedQuestions = $db->table('audit_questions as aq')
                    ->select('aq.id, aq.clause_code, aq.question_text, aq.is_standard')
                    ->join('audit_question_assignments as aqa', 'aqa.question_id = aq.id')
                    ->where('aqa.audit_id', $id)
                    ->orderBy('aq.clause_code', 'ASC')
                    ->get()
                    ->getResult();

                $selectedCount = count($lockedQuestions);
                $isLocked = true;
            } else {
                // === MODE PILIH PERTANYAAN (BELUM PERNAH DISIMPAN) ===
                $customQuestions = $db->table('audit_questions as aq')
                    ->select('aq.id, aq.clause_code, aq.question_text, aq.is_standard')
                    ->join('audit_question_assignments as aqa', 'aqa.question_id = aq.id')
                    ->where('aqa.audit_id', $id)
                    ->where('aq.is_standard', 0)
                    ->orderBy('aq.clause_code', 'ASC')
                    ->get()
                    ->getResult();

                $standardQuestions = $db->table('audit_questions')
                    ->select('id, framework, clause_code, question_text, is_standard')
                    ->where('framework', $audit->framework)
                    ->where('is_standard', 1)
                    ->whereNotIn('id', function ($subQuery) use ($db, $id) {
                        $subQuery->select('question_id')
                            ->from('audit_question_assignments')
                            ->where('audit_id', $id);
                    })
                    ->orderBy('clause_code', 'ASC')
                    ->get()
                    ->getResult();

                $selectedCount = count($customQuestions);
                $isLocked = false;
            }
        } else {
            // STATUS MENUNGGU/SELESAI
            $assignedQuestions = $db->table('audit_question_assignments as aqa')
                ->select('aq.id, aq.clause_code, aq.question_text, aq.is_standard, aqa.score, aqa.note, aqa.answer')
                ->join('audit_questions as aq', 'aq.id = aqa.question_id')
                ->where('aqa.audit_id', $id)
                ->orderBy('aq.clause_code', 'ASC')
                ->get()
                ->getResult();

            $findings = $db->table('temuans')
                ->select('temuans.*, aq.clause_code')
                ->join('audit_questions as aq', 'aq.id = temuans.question_id', 'left')
                ->where('temuans.audit_id', $id)
                ->orderBy('temuans.tingkat_risiko', 'DESC')
                ->get()
                ->getResult();
        }

        // Ambil info framework
        $fw = $db->table('frameworks')->where('nama', $audit->framework)->get()->getRow();
        $isBinary = $fw && $fw->scoring_type === 'binary';
        $maxScore = $fw ? (int) $fw->max_score : 1;

        return view('auditor/detail_audit', [
            'title'               => 'Detail Audit - Sistem Audit IT POLBAN',
            'page_title'          => 'Detail Audit',
            'audit'               => $audit,
            'customQuestions'     => $customQuestions,
            'standardQuestions'   => $standardQuestions,
            'lockedQuestions'     => $lockedQuestions,
            'isLocked'            => $isLocked,
            'assignedQuestions'   => $assignedQuestions,
            'assignedIds'         => $assignedIds,
            'selectedCount'       => $selectedCount,
            'findings'            => $findings,
            'isBinary'            => $isBinary,
            'maxScore'            => $maxScore
        ]);
    }

    // Di dalam class Dashboard, tambahkan dua method ini:

    public function edit(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil data audit
        $audit = $db->table('audits')->where('id', $id)->get()->getRow();
        if (!$audit) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        // Ambil list auditee untuk dropdown
        $auditees = $db->table('users')->where('role', 'auditee')->orderBy('fullname', 'ASC')->get()->getResult();

        // Ambil SEMUA pertanyaan (untuk difilter di frontend)
        $allQuestions = $db->table('audit_questions')
            ->orderBy('framework', 'ASC')
            ->orderBy('clause_code', 'ASC')
            ->get()->getResult();

        // Ambil pertanyaan yang SUDAH di-assign (untuk pre-check)
        $assignedIds = $db->table('audit_question_assignments')
            ->where('audit_id', $id)
            ->select('question_id')
            ->get()->getResultArray();
        $assignedIds = array_column($assignedIds, 'question_id');

        return view('auditor/edit_audit', [
            'title'         => 'Edit Audit - Sistem Audit IT POLBAN',
            'page_title'    => 'Edit Audit',
            'audit'         => $audit,
            'auditees'      => $auditees,
            'allQuestions'  => $allQuestions,
            'assignedIds'   => $assignedIds
        ]);
    }

    public function update(int $id)
    {
        $rules = [
            'title'       => 'required|min_length[5]|max_length[150]',
            'auditee_id'  => 'required|is_natural_no_zero',
            'framework'   => 'required|in_list[ISO 27001,COBIT 2019]',
            'deadline'    => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();

        // 1. Update data audit
        $db->table('audits')->where('id', $id)->update([
            'title'      => $this->request->getPost('title'),
            'auditee_id' => $this->request->getPost('auditee_id'),
            'framework'  => $this->request->getPost('framework'),
            'deadline'   => $this->request->getPost('deadline'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 2. Update assignment pertanyaan (reset & insert baru)
        $selectedQuestions = $this->request->getPost('question_ids') ?? [];

        // Hapus assignment lama
        $db->table('audit_question_assignments')->where('audit_id', $id)->delete();

        // Insert assignment baru
        foreach ($selectedQuestions as $qId) {
            $db->table('audit_question_assignments')->insert([
                'audit_id'    => $id,
                'question_id' => $qId,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        return redirect()->to('/audit/detail/' . $id)
            ->with('success', 'Audit berhasil diperbarui dengan ' . count($selectedQuestions) . ' pertanyaan!');
    }

    // Method untuk menampilkan form assign pertanyaan di halaman detail
    public function assignQuestions(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil data audit
        $audit = $db->table('audits')->where('id', $id)->get()->getRow();
        if (!$audit) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        // Ambil SEMUA pertanyaan berdasarkan framework audit ini
        $allQuestions = $db->table('audit_questions')
            ->where('framework', $audit->framework)
            ->orderBy('clause_code', 'ASC')
            ->get()->getResult();

        // Ambil pertanyaan yang SUDAH di-assign ke audit ini
        $assignedIds = $db->table('audit_question_assignments')
            ->where('audit_id', $id)
            ->select('question_id')
            ->get()->getResultArray();
        $assignedIds = array_column($assignedIds, 'question_id');

        return view('auditor/assign_questions', [
            'title'         => 'Assign Pertanyaan - Sistem Audit IT POLBAN',
            'page_title'    => 'Pilih Pertanyaan',
            'audit'         => $audit,
            'allQuestions'  => $allQuestions,
            'assignedIds'   => $assignedIds
        ]);
    }

    // Method untuk menyimpan assignment
    // Method untuk menyimpan assignment
    public function saveAssignments(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $selectedQuestions = $this->request->getPost('question_ids') ?? [];

        // Validasi: minimal harus pilih 1 pertanyaan
        if (empty($selectedQuestions)) {
            return redirect()->back()->with('error', 'Anda harus memilih minimal 1 pertanyaan untuk audit ini!');
        }

        $db = \Config\Database::connect();

        // 1. Hapus assignment lama (reset)
        $db->table('audit_question_assignments')->where('audit_id', $id)->delete();

        // 2. Insert assignment baru
        foreach ($selectedQuestions as $qId) {
            $db->table('audit_question_assignments')->insert([
                'audit_id'    => $id,
                'question_id' => $qId,
                'created_at'  => date('Y-m-d H:i:s')
            ]);
        }

        // ✅ TAMBAHKAN 3 BARIS INI: Ubah status audit menjadi 'menunggu_jawaban'
        $db->table('audits')->where('id', $id)->update([
            'status' => 'menunggu_jawaban',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Setelah berhasil update status ke 'menunggu_jawaban'
        $audit = $db->table('audits')->where('id', $id)->get()->getRow();
        log_activity('UPDATE', 'audits', 'Mengonfigurasi dan mengirimkan ' . count($selectedQuestions) . ' pertanyaan untuk audit "' . $audit->title . '" ke Auditee.');

        return redirect()->to('/audit/detail/' . $id)
            ->with('success', count($selectedQuestions) . ' pertanyaan berhasil di-assign! Audit kini menunggu jawaban Auditee.');
    }

    // Method menampilkan form penilaian
    public function nilai(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $auditorId = session()->get('id');

        // Ambil data audit
        $audit = $db->table('audits')
            ->select('audits.*, users.fullname as auditee_name')
            ->join('users', 'users.id = audits.auditee_id', 'left')
            ->where('audits.id', $id)
            ->where('audits.created_by_auditor', $auditorId)
            ->get()->getRow();

        if (!$audit || $audit->status !== 'menunggu_penilaian') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Ambil pertanyaan yang sudah di-assign + jawaban auditee + bukti
        $items = $db->table('audit_question_assignments as aqa')
            ->select('aqa.id as assignment_id, 
                          aqa.answer, 
                          aqa.score, 
                          aqa.note, 
                          aqa.evidence_file, 
                          aqa.evidence_filename, 
                          aqa.evidence_uploaded_at,
                          aq.id as question_id, 
                          aq.question_text, 
                          aq.clause_code, 
                          aq.framework')
            ->join('audit_questions as aq', 'aq.id = aqa.question_id')
            ->where('aqa.audit_id', $id)
            ->orderBy('aq.clause_code', 'ASC')
            ->get()->getResult();

        // Cast score ke float untuk konsistensi
        foreach ($items as &$item) {
            if ($item->score !== null) {
                $item->score = (float) $item->score;
            }
        }

        // Ambil temuan yang sudah ada untuk audit ini
        $existingFindings = $db->table('temuans')
            ->where('audit_id', $id)
            ->get()
            ->getResult();

        // Mapping question_id yang sudah ada temuannya
        $findingQuestionIds = [];
        foreach ($existingFindings as $f) {
            if ($f->question_id) {
                $findingQuestionIds[$f->question_id] = $f;
            }
        }

        // Ambil framework options untuk dropdown penilaian
        $frameworkOptions = $db->table('framework_options')
            ->select('score_value, label, description')
            ->where('framework_id', function ($subQuery) use ($db, $audit) {
                $subQuery->select('id')
                    ->from('frameworks')
                    ->where('nama', $audit->framework);
            })
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResult();

        // Convert ke array untuk mudah diakses
        $optionsArray = [];
        foreach ($frameworkOptions as $opt) {
            $optionsArray[$opt->score_value] = $opt;
        }

        return view('auditor/penilaian', [
            'title'              => 'Penilaian Audit - Sistem Audit IT POLBAN',
            'page_title'         => 'Input Nilai',
            'audit'              => $audit,
            'items'              => $items,
            'findingQuestionIds' => $findingQuestionIds,
            'frameworkOptions'   => $optionsArray  // ✅ TAMBAHKAN BARIS INI
        ]);
    }

    // Method menyimpan nilai (Draft atau Final)
    // Method menyimpan nilai (Draft atau Final)
    public function simpanNilai(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditor') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $isFinal = ($this->request->getPost('submit_action') === 'final');

        $scores = $this->request->getPost('scores') ?? [];
        $notes  = $this->request->getPost('notes') ?? [];
        $findings = $this->request->getPost('findings') ?? [];

        // Ambil data audit di awal agar bisa dipakai untuk deskripsi log
        $audit = $db->table('audits')->where('id', $id)->get()->getRow();
        $auditTitle = $audit ? $audit->title : 'Audit ID ' . $id;

        // ==========================================
        // VALIDASI BARU: Cek jawaban auditee
        // ==========================================
        if ($isFinal) {
            $unansweredQuestions = $db->table('audit_question_assignments')
                ->where('audit_id', $id)
                ->groupStart()
                ->where('answer IS NULL')
                ->orWhere('answer', '')
                ->groupEnd()
                ->countAllResults();

            if ($unansweredQuestions > 0) {
                return redirect()->back()->with('error', "⚠️ Tidak dapat finalisasi! Masih ada <strong>{$unansweredQuestions}</strong> pertanyaan yang belum dijawab oleh auditee.");
            }

            $totalAssigned = $db->table('audit_question_assignments')->where('audit_id', $id)->countAllResults();
            $totalScored = count(array_filter($scores, function ($s) {
                return $s !== '' && $s !== null;
            }));

            if ($totalScored < $totalAssigned) {
                return redirect()->back()->with('error', "Anda belum menilai semua soal! ($totalScored dari $totalAssigned soal terisi).");
            }
        }

        // ==========================================
        // 1. LOG: Memberikan Nilai/Skor
        // ==========================================
        if (!empty($scores)) {
            $scoredCount = count(array_filter($scores, function ($s) {
                return $s !== '' && $s !== null;
            }));
            if ($scoredCount > 0) {
                log_activity('UPDATE', 'audit_question_assignments', 'Memberikan penilaian/skor pada ' . $scoredCount . ' jawaban auditee di audit "' . $auditTitle . '".');
            }
        }

        // 2. Simpan Nilai ke Database
        foreach ($scores as $assignmentId => $score) {
            if ($score !== '' && is_numeric($score)) {
                $db->table('audit_question_assignments')->where('id', $assignmentId)->update([
                    'score' => (float) $score,
                    'note'  => $notes[$assignmentId] ?? null
                ]);
            }
        }

        // ==========================================
        // 3. LOG: Membuat Temuan Baru & Simpan Temuan
        // ==========================================
        $findingsCreatedCount = 0;
        foreach ($findings as $assignmentId => $finding) {
            if (!empty($finding['deskripsi_temuan'])) {
                $existing = $db->table('temuans')->where('audit_id', $id)->where('question_id', $finding['question_id'])->get()->getRow();
                $dataTemuan = [
                    'audit_id' => $id,
                    'question_id' => $finding['question_id'],
                    'deskripsi_temuan' => $finding['deskripsi_temuan'],
                    'tingkat_risiko' => $finding['tingkat_risiko'] ?? 'Sedang',
                    'rekomendasi' => $finding['rekomendasi']
                ];

                if ($existing) {
                    $db->table('temuans')->where('id', $existing->id)->update($dataTemuan);
                } else {
                    $db->table('temuans')->insert($dataTemuan);
                    $findingsCreatedCount++;
                }
            }
        }

        if ($findingsCreatedCount > 0) {
            log_activity('CREATE', 'temuans', 'Membuat ' . $findingsCreatedCount . ' temuan ketidaksesuaian baru pada audit "' . $auditTitle . '".');
        }

        // ==========================================
        // 4. Update Status Audit & LOG Finalisasi
        // ==========================================
        if ($isFinal) {
            $framework = $db->table('frameworks')->where('nama', $audit->framework)->get()->getRow();
            $maxScore = $framework ? (int) $framework->max_score : 1;
            $scoringType = $framework ? $framework->scoring_type : 'binary';

            $allScores = $db->table('audit_question_assignments')->select('score')->where('audit_id', $id)->get()->getResult();
            $total = 0;
            $count = 0;
            foreach ($allScores as $s) {
                if ($s->score !== null) {
                    $total += $s->score;
                    $count++;
                }
            }

            $finalScore = 0;
            if ($scoringType === 'binary') {
                $finalScore = $count > 0 ? round(($total / $count) * 100) : 0;
            } else {
                $finalScore = $count > 0 ? round(($total / $count), 2) : 0;
            }

            $db->table('audits')->where('id', $id)->update([
                'status' => 'selesai',
                'final_score' => $finalScore,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // LOG: Memfinalisasi Audit
            log_activity('UPDATE', 'audits', 'Memfinalisasi dan menutup audit "' . $auditTitle . '" (Status berubah menjadi Selesai).');

            return redirect()->to('/dashboard')->with('success', 'Penilaian Final berhasil disimpan!');
        } else {
            // Draft
            $db->table('audits')->where('id', $id)->update(['updated_at' => date('Y-m-d H:i:s')]);
            log_activity('UPDATE', 'audits', 'Menyimpan draft penilaian untuk audit "' . $auditTitle . '".');

            return redirect()->back()->with('success', 'Draft berhasil disimpan!');
        }
    }

    // Method untuk melihat/mendownload bukti
    public function viewEvidence(int $assignmentId)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        $assignment = $db->table('audit_question_assignments')
            ->select('evidence_file, evidence_filename')
            ->where('id', $assignmentId)
            ->get()
            ->getRow();

        if (!$assignment || empty($assignment->evidence_file)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('File tidak ditemukan');
        }

        // Deteksi tipe file dari extension
        $ext = strtolower(pathinfo($assignment->evidence_filename, PATHINFO_EXTENSION));

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

        $this->response->setHeader('Content-Type', $mimeType);

        // Image & PDF tampil inline (preview), lainnya download
        if (strpos($mimeType, 'image/') === 0 || $mimeType === 'application/pdf') {
            $this->response->setHeader('Content-Disposition', 'inline; filename="' . $assignment->evidence_filename . '"');
        } else {
            $this->response->setHeader('Content-Disposition', 'attachment; filename="' . $assignment->evidence_filename . '"');
        }

        $this->response->setHeader('Content-Length', strlen($assignment->evidence_file));

        return $this->response->setBody($assignment->evidence_file);
    }
}
