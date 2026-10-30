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

        // KUNCI: tidak bisa edit jika sudah dikirim ke auditor
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
                ->update([
                    'answer' => $answerText
                ]);
        }

        // 2. Simpan Multi-Upload Bukti Pendukung
        $skipped = [];
        $files = $this->request->getFileMultiple('evidence');

        if ($files && is_array($files)) {
            foreach ($files as $assignmentId => $fileList) {
                // Ambil data assignment lama untuk mengecek file sebelumnya (jika ada)
                $existingAssignment = $db->table('audit_question_assignments')
                    ->where('id', $assignmentId)
                    ->where('audit_id', $id)
                    ->get()->getRow();

                if (!$existingAssignment) {
                    continue;
                }

                // Decode file lama yang sudah ada di DB (format JSON)
                $uploadedFilesName = [];
                if (!empty($existingAssignment->evidence_filename)) {
                    $decodedOld = json_decode($existingAssignment->evidence_filename, true);
                    if (is_array($decodedOld)) {
                        $uploadedFilesName = $decodedOld;
                    } else {
                        // Fallback jika dulunya string tunggal
                        $uploadedFilesName = [$existingAssignment->evidence_filename];
                    }
                }

                $hasNewValidFile = false;

                foreach ($fileList as $file) {
                    if (!($file instanceof \CodeIgniter\HTTP\Files\UploadedFile)) {
                        continue;
                    }

                    // Input kosong (user tidak memilih file di input ini)
                    if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $nama = $file->getClientName();
                    $ukuranMB = round($file->getSize() / 1024 / 1024, 1);

                    // Batas maksimal 5 MB per file
                    if ($file->getSize() > (5 * 1024 * 1024)) {
                        $skipped[] = $nama . ' (' . $ukuranMB . ' MB) — melebihi batas maksimal 5 MB';
                        continue;
                    }

                    // Error upload level server
                    if (!$file->isValid() || $file->hasMoved()) {
                        $skipped[] = $nama . ' — gagal diupload (error server/limit PHP)';
                        continue;
                    }

                    // Validasi Ekstensi
                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
                    $ext = strtolower($file->getClientExtension());
                    if (!in_array($ext, $allowed)) {
                        $skipped[] = $nama . ' — format .' . $ext . ' tidak didukung';
                        continue;
                    }

                    // Buat nama file unik agar tidak bentrok
                    $newName = $file->getRandomName();

                    // Pindahkan ke folder public/uploads/evidence
                    $folder = FCPATH . 'uploads/evidence';
                    if (!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    if ($file->move($folder, $newName)) {
                        $uploadedFilesName[] = $newName;
                        $hasNewValidFile = true;
                    }
                }

                // Jika ada file baru yang valid di-upload, update ke database
                if ($hasNewValidFile) {
                    $db->table('audit_question_assignments')
                        ->where('id', $assignmentId)
                        ->where('audit_id', $id)
                        ->update([
                            'evidence_filename' => json_encode($uploadedFilesName),
                            'evidence_uploaded_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }
        }

        // Peringatan kuning tampil jika ada file yang ditolak ukurannya
        if (!empty($skipped)) {
            session()->setFlashdata(
                'warning',
                'PERHATIAN: Berkas berikut TIDAK ikut tersimpan → ' . implode('; ', $skipped) .
                '. Silakan kompres atau bungkus menjadi ZIP (maks 5 MB) lalu unggah ulang.'
            );
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
                    ->with(
                        'error',
                        'Masih ada ' . ($total - $answered) . ' pertanyaan yang belum dijawab.'
                    );
            }

            // Kunci jawaban & kirim ke auditor
            $db->table('audits')
                ->where('id', $id)
                ->update([
                    'status' => 'menunggu_penilaian',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            log_aktivitas(
                'Mengirim jawaban audit "' . $audit->title . '" ke auditor (menunggu penilaian)',
                'jawaban'
            );

            return redirect()->to('/auditee/dashboard')
                ->with('success', 'Jawaban berhasil dikirim ke auditor.');
        }

        log_aktivitas(
            'Menyimpan draft jawaban audit "' . $audit->title . '"',
            'jawaban'
        );

        return redirect()->to('/auditee/fill/' . $id)
            ->with('success', 'Jawaban berhasil disimpan.');
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
            ->where('t.status', 'In_Progress')
            ->orderBy('t.created_at', 'ASC')
            ->get()
            ->getResult();

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

        // 1. Simpan jawaban revisi (Teks)
        foreach ($answers as $assignmentId => $answerText) {
            $db->table('audit_question_assignments')
                ->where('id', $assignmentId)
                ->where('audit_id', $id)
                ->update(['answer' => $answerText]);

            $logMessages[] = "Jawaban disimpan untuk Assignment ID: $assignmentId";
        }

        // 2. Proses Multi-Upload File Revisi
        $files = $this->request->getFileMultiple('evidence');

        if ($files && is_array($files)) {
            foreach ($files as $assignmentId => $fileList) {

                $assignment = $db->table('audit_question_assignments')
                    ->where('id', $assignmentId)
                    ->where('audit_id', $id)
                    ->get()->getRow();

                if (!$assignment) {
                    continue;
                }

                // Ambil file lama jika ada
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

                foreach ($fileList as $file) {
                    if (!($file instanceof \CodeIgniter\HTTP\Files\UploadedFile)) {
                        continue;
                    }

                    if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }

                    $nama = $file->getClientName();
                    $ukuranMB = round($file->getSize() / 1024 / 1024, 1);

                    if ($file->getSize() > (5 * 1024 * 1024)) {
                        $logMessages[] = "File '$nama' ($ukuranMB MB) melebihi 5 MB";
                        continue;
                    }

                    $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
                    $ext = strtolower($file->getClientExtension());
                    if (!in_array($ext, $allowed)) {
                        $logMessages[] = "Format file '$nama' tidak didukung";
                        continue;
                    }

                    $safeName = $file->getRandomName();
                    $folder = FCPATH . 'uploads/bukti_perbaikan';

                    if (!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    if ($file->move($folder, $safeName)) {
                        $uploadedFilesName[] = $safeName;
                        $hasNewValidFile = true;
                        $logMessages[] = "File fisik disimpan sebagai: $safeName";
                    } else {
                        $logMessages[] = "GAGAL menyimpan file fisik: " . $file->getErrorString();
                    }
                }

                if ($hasNewValidFile) {
                    $jsonFiles = json_encode($uploadedFilesName);

                    // Update assignment
                    $db->table('audit_question_assignments')
                        ->where('id', $assignmentId)
                        ->where('audit_id', $id)
                        ->update([
                            'evidence_filename' => $jsonFiles,
                            'evidence_uploaded_at' => date('Y-m-d H:i:s')
                        ]);

                    // Sinkronisasi ke tabel temuans
                    $db->table('temuans')
                        ->where('audit_id', $id)
                        ->where('question_id', $assignment->question_id)
                        ->update([
                            'bukti_perbaikan' => $jsonFiles,
                            'revisi_sent_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                    $logMessages[] = "File disinkronkan ke tabel temuans (Multi-upload JSON)";
                }
            }
        }

        // Tulis log debug
        $logFile = WRITEPATH . 'logs/revisi_upload_' . date('Y-m-d') . '.log';
        $logContent = "\n\n=== REVISI AUDIT ID: $id ===\n";
        $logContent .= "Time: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "User: " . session()->get('username') . "\n";
        $logContent .= implode("\n", $logMessages) . "\n";
        file_put_contents($logFile, $logContent, FILE_APPEND);

        log_aktivitas('Mengirim revisi jawaban & bukti perbaikan audit "' . $audit->title . '"', 'revisi');

        return redirect()->to('/auditee/dashboard')
            ->with('success', 'Revisi berhasil dikirim ke auditor.');
    }
}