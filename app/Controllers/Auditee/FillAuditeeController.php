<?php

namespace App\Controllers\Auditee;

use App\Controllers\BaseController;

class FillAuditeeController extends BaseController
{
    // Menampilkan halaman kuesioner
    public function fill(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $audit = $db->table('audits')
            ->where('id', $id)
            ->where('auditee_id', $userId)
            ->get()->getRow();

        if (!$audit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $questions = $db->table('audit_question_assignments as aqa')
            ->select('aqa.*, aq.question_text, aq.clause_code')
            ->join('audit_questions as aq', 'aq.id = aqa.question_id')
            ->where('aqa.audit_id', $id)
            ->orderBy('aq.clause_code', 'ASC')
            ->get()->getResult();

        return view('auditee/fillauditee', [
            'title' => 'Isi Kuesioner - Sistem Audit IT POLBAN',
            'page_title' => 'Isi Kuesioner',
            'audit' => $audit,
            'questions' => $questions,
        ]);
    }


    // Menyimpan jawaban + multi-upload bukti (dengan logika kunci)
    public function saveAnswer(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $audit = $db->table('audits')
            ->where('id', $id)
            ->where('auditee_id', $userId)
            ->get()->getRow();

        if (!$audit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (in_array($audit->status, ['menunggu_penilaian', 'selesai'])) {
            return redirect()->to('/auditee/fill/' . $id)
                ->with('error', 'Jawaban sudah dikirim ke auditor dan tidak dapat diedit lagi.');
        }

        $action = $this->request->getPost('action') ?? 'draft';
        $answers = $this->request->getPost('answers') ?? [];

        // 1. Simpan jawaban teks
        foreach ($answers as $assignmentId => $answerText) {
            $db->table('audit_question_assignments')
                ->where('id', $assignmentId)
                ->where('audit_id', $id)
                ->update(['answer' => $answerText]);
        }

        // 2. Simpan Multi-Upload Bukti Pendukung via $_FILES langsung (100% PASTI MASUK)
        $questions = $db->table('audit_question_assignments')
            ->where('audit_id', $id)
            ->get()->getResult();

        $skipped = [];

        foreach ($questions as $q) {
            $assignmentId = $q->id;

            // Ambil objek file multiple secara spesifik menggunakan helper CodeIgniter dari $_FILES['evidence'][$assignmentId]
            $fileList = $this->request->getFileMultiple('evidence.' . $assignmentId);

            if (!$fileList) {
                continue;
            }

            // Ambil data file lama dari database
            $existingAssignment = $db->table('audit_question_assignments')
                ->where('id', $assignmentId)
                ->where('audit_id', $id)
                ->get()->getRow();

            $uploadedFilesName = [];
            if (!empty($existingAssignment->evidence_filename)) {
                $decodedOld = json_decode($existingAssignment->evidence_filename, true);
                if (is_array($decodedOld)) {
                    $uploadedFilesName = $decodedOld;
                } else {
                    $uploadedFilesName = [$existingAssignment->evidence_filename];
                }
            }

            $hasNewValidFile = false;

            foreach ($fileList as $file) {
                if (!($file instanceof \CodeIgniter\HTTP\Files\UploadedFile)) {
                    continue;
                }

                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $nama = $file->getClientName();

                if (!$file->isValid()) {
                    $skipped[] = $nama . ' (Error code: ' . $file->getError() . ')';
                    continue;
                }

                $newName = $file->getRandomName();
                $folder = FCPATH . 'uploads/evidence';

                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }

                if ($file->move($folder, $newName)) {
                    $uploadedFilesName[] = $newName;
                    $hasNewValidFile = true;
                }
            }

            // Update ke database jika ada file baru yang sukses di-upload
            if ($hasNewValidFile) {
                $db->table('audit_question_assignments')
                    ->where('id', $assignmentId)
                    ->where('audit_id', $id)
                    ->update([
                        'evidence_filename' => json_encode(array_values(array_unique($uploadedFilesName))),
                        'evidence_uploaded_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        }

        if (!empty($skipped)) {
            session()->setFlashdata('warning', 'Beberapa file gagal di-upload: ' . implode('; ', $skipped));
        }

        // 3. Jika tombol "Kirim ke Auditor" ditekan
        if ($action === 'submit') {
            $total = $db->table('audit_question_assignments')
                ->where('audit_id', $id)
                ->countAllResults();

            $answered = $db->table('audit_question_assignments')
                ->where('audit_id', $id)
                ->where('answer IS NOT NULL')
                ->where('answer !=', '')
                ->countAllResults();

            if ($answered < $total) {
                return redirect()->to('/auditee/fill/' . $id)
                    ->with('error', 'Masih ada pertanyaan yang belum dijawab.');
            }

            // Update status audit jadi menunggu penilaian
            $db->table('audits')
                ->where('id', $id)
                ->update([
                    'status' => 'menunggu_penilaian',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            return redirect()->to('/auditee/dashboard')
                ->with('success', 'Jawaban dan bukti berhasil dikirim ke auditor.');
        }

        return redirect()->to('/auditee/fill/' . $id)
            ->with('success', 'Jawaban dan bukti berhasil disimpan sebagai draft.');
    }

    // Halaman daftar revisi untuk auditee
    public function revisi(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $audit = $db->table('audits')
            ->where('id', $id)
            ->where('auditee_id', $userId)
            ->get()->getRow();

        if (!$audit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $temuans = $db->table('temuans t')
            ->select('t.*, 
                    aq.clause_code, 
                    aq.question_text, 
                    aqa.id as assignment_id, 
                    aqa.answer, 
                    aqa.evidence_filename, 
                    aqa.evidence_uploaded_at,
                    aqa.evidence_file')
            ->join('audit_questions as aq', 'aq.id = t.question_id', 'left')
            ->join('audit_question_assignments as aqa', 'aqa.question_id = t.question_id AND aqa.audit_id = t.audit_id', 'left')
            ->where('t.audit_id', $id)
            ->orderBy('t.created_at', 'ASC')
            ->get()
            ->getResult();

        $today = date('Y-m-d');
        foreach ($temuans as $t) {
            if (isset($t->status_fase) && $t->status_fase === 'revisi_mandiri' && !empty($t->deadline) && $today > $t->deadline) {
                $db->table('temuans')
                    ->where('id', $t->id)
                    ->update(['status_fase' => 'rtl_approval']);

                $t->status_fase = 'rtl_approval';
            }
        }

        return view('auditee/revisi', [
            'title' => 'Revisi Jawaban - Sistem Audit IT POLBAN',
            'page_title' => 'Revisi Jawaban',
            'audit' => $audit,
            'temuans' => $temuans
        ]);
    }


    public function saveRevisi(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $audit = $db->table('audits')
            ->where('id', $id)
            ->where('auditee_id', $userId)
            ->get()->getRow();

        if (!$audit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $answers = $this->request->getPost('answers') ?? [];
        $logMessages = [];

        foreach ($answers as $assignmentId => $answerText) {
            $db->table('audit_question_assignments')
                ->where('id', $assignmentId)
                ->where('audit_id', $id)
                ->update(['answer' => $answerText]);

            $logMessages[] = "Jawaban disimpan untuk Assignment ID: $assignmentId";
        }

        $assignments = $db->table('audit_question_assignments')
            ->where('audit_id', $id)
            ->get()->getResult();

        foreach ($assignments as $assignment) {
            $assignmentId = $assignment->id;
            $fileList = $this->request->getFileMultiple('evidence');

            if (!$fileList || !array_key_exists($assignmentId, $fileList)) {
                continue;
            }

            $currentFiles = $fileList[$assignmentId];

            $uploadedFilesName = [];
            if (!empty($assignment->evidence_filename)) {
                $decodedOld = json_decode($assignment->evidence_filename, true);
                if (is_array($decodedOld)) {
                    $uploadedFilesName = $decodedOld;
                } else {
                    $uploadedFilesName = [$assignment->evidence_filename];
                }
            }

            $hasNewValidFile = false;

            if (is_array($currentFiles)) {
                foreach ($currentFiles as $file) {
                    if (!($file instanceof \CodeIgniter\HTTP\Files\UploadedFile) || $file->getError() === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $nama = $file->getClientName();
                    if ($file->getSize() > (50 * 1024 * 1024)) {
                        $logMessages[] = "File '$nama' melebihi batas ukuran";
                        continue;
                    }

                    if (!$file->isValid()) {
                        continue;
                    }

                    $safeName = $file->getRandomName();
                    $folder = FCPATH . 'uploads/evidence';

                    if (!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    if ($file->move($folder, $safeName)) {
                        $uploadedFilesName[] = $safeName;
                        $hasNewValidFile = true;
                        $logMessages[] = "File fisik disimpan sebagai: $safeName";
                    }
                }
            }

            if ($hasNewValidFile) {
                $jsonFiles = json_encode(array_values(array_unique($uploadedFilesName)));

                $db->table('audit_question_assignments')
                    ->where('id', $assignmentId)
                    ->where('audit_id', $id)
                    ->update([
                        'evidence_filename' => $jsonFiles,
                        'evidence_uploaded_at' => date('Y-m-d H:i:s')
                    ]);

                $db->table('temuans')
                    ->where('audit_id', $id)
                    ->where('question_id', $assignment->question_id)
                    ->update([
                        'bukti_perbaikan' => $jsonFiles,
                        'revisi_sent_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                $logMessages[] = "File disinkronkan ke temuans";
            }
        }

        $logFile = WRITEPATH . 'logs/revisi_upload_' . date('Y-m-d') . '.log';
        $logContent = "\n\n=== REVISI AUDIT ID: $id ===\n";
        $logContent .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "User: " . session()->get('username') . "\n";
        $logContent .= implode("\n", $logMessages) . "\n";
        file_put_contents($logFile, $logContent, FILE_APPEND);

        if (function_exists('log_aktivitas')) {
            log_aktivitas('Mengirim revisi jawaban & bukti perbaikan audit "' . $audit->title . '"', 'revisi');
        }

        return redirect()->to('/auditee/dashboard')
            ->with('success', 'Revisi berhasil dikirim ke auditor.');
    }
}