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


    // Menyimpan jawaban + bukti (dengan logika kunci)
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

        // 1. Simpan jawaban
        foreach ($answers as $assignmentId => $answerText) {
            $db->table('audit_question_assignments')
                ->where('id', $assignmentId)
                ->where('audit_id', $id)
                ->update([
                    'answer' => $answerText
                ]);
        }

        // 2. Simpan bukti (jika ada yang diupload) + catat berkas yang ditolak
        $skipped = [];
        $files = $this->request->getFiles();

        if (isset($files['evidence']) && is_array($files['evidence'])) {
            foreach ($files['evidence'] as $assignmentId => $file) {

                if (!($file instanceof \CodeIgniter\HTTP\Files\UploadedFile)) {
                    continue;
                }

                // Input kosong (user tidak memilih file) → lewati tanpa error
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                $nama = $file->getClientName();
                $ukuranMB = round($file->getSize() / 1024 / 1024, 1);

                // ✅ BATAS KERAS 5 MB — file TIDAK akan disimpan sama sekali
                if ($file->getSize() > 5 * 1024 * 1024) {
                    $skipped[] = $nama . ' (' . $ukuranMB . ' MB) — melebihi batas maksimal 5 MB';
                    continue;
                }

                // Error upload level server (mis. melebihi post_max_size php.ini)
                if (!$file->isValid() || $file->hasMoved()) {
                    $skipped[] = $nama . ' — gagal diupload (error server/limit PHP)';
                    continue;
                }

                $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
                if (!in_array(strtolower($file->getClientExtension()), $allowed)) {
                    $skipped[] = $nama . ' — format .' . strtolower($file->getClientExtension()) . ' tidak didukung';
                    continue;
                }

                $db->table('audit_question_assignments')
                    ->where('id', $assignmentId)
                    ->where('audit_id', $id)
                    ->update([
                        'evidence_file' => file_get_contents($file->getTempName()),
                        'evidence_filename' => $nama,
                        'evidence_uploaded_at' => date('Y-m-d H:i:s'),
                    ]);
            }
        }

        // ✅ Peringatan kuning tampil setelah redirect
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

            // LOG AKTIVITAS: jawaban dikirim ke auditor
            log_aktivitas(
                'Mengirim jawaban audit "' . $audit->title . '" ke auditor (menunggu penilaian)',
                'jawaban'
            );

            return redirect()->to('/auditee/dashboard')
                ->with('success', 'Jawaban berhasil dikirim ke auditor.');
        }

        // LOG AKTIVITAS: jawaban disimpan sebagai draft
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

        // PERBAIKAN QUERY: Ambil data langsung dari audit_question_assignments
        // dengan kondisi yang lebih spesifik
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

        // HAPUS KODE DEBUG (echo dan die) yang sebelumnya ditambahkan
        // return view langsung
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

        // 2. Proses File Upload
        $files = $this->request->getFiles();
        
        if (isset($files['evidence']) && is_array($files['evidence'])) {
            foreach ($files['evidence'] as $assignmentId => $file) {
                
                if (!($file instanceof \CodeIgniter\HTTP\Files\UploadedFile)) {
                    $logMessages[] = "File untuk ID $assignmentId bukan instance UploadedFile";
                    continue;
                }
                
                if ($file->getError() === UPLOAD_ERR_NO_FILE) {
                    $logMessages[] = "Tidak ada file yang dipilih untuk ID $assignmentId";
                    continue;
                }

                $nama = $file->getClientName();
                $ukuranMB = round($file->getSize() / 1024 / 1024, 1);

                if ($file->getSize() > 5 * 1024 * 1024) {
                    $logMessages[] = "File '$nama' ($ukuranMB MB) melebihi 5 MB";
                    continue;
                }

                $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
                if (!in_array(strtolower($file->getClientExtension()), $allowed)) {
                    $logMessages[] = "Format file '$nama' tidak didukung";
                    continue;
                }

                // Baca konten file untuk disimpan ke database (BLOB)
                $content = file_get_contents($file->getTempName());
                
                // Update database
                $db->table('audit_question_assignments')
                    ->where('id', $assignmentId)
                    ->where('audit_id', $id)
                    ->update([
                        'evidence_file' => $content,
                        'evidence_filename' => $nama,
                        'evidence_uploaded_at' => date('Y-m-d H:i:s')
                    ]);
                
                $affectedRows = $db->affectedRows();
                
                if ($affectedRows > 0) {
                    $logMessages[] = "BERHASIL: File '$nama' disimpan ke DB. Baris terupdate: $affectedRows";
                    
                    // ✅ PERBAIKAN KRUSIAL: JANGAN GUNAKAN getRandomName()
                    // Buat nama file secara manual agar tidak memicu error finfo_file
                    $originalName = pathinfo($nama, PATHINFO_FILENAME);
                    $ext = $file->getClientExtension();
                    $timestamp = time();
                    
                    // Sanitasi nama file: ganti spasi & karakter khusus dengan underscore
                    $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName) . '_' . $timestamp . '.' . $ext;
                    
                    // Simpan ke folder fisik
                    $folder = FCPATH . 'uploads/bukti_perbaikan';
                    if (!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }
                    
                    // Pindahkan file LANGSUNG dengan nama yang sudah kita tentukan
                    if ($file->move($folder, $safeName)) {
                        $logMessages[] = "File fisik disimpan sebagai: $safeName";
                    } else {
                        $logMessages[] = "GAGAL menyimpan file fisik. Error: " . $file->getErrorString();
                    }
                    
                    // Update juga tabel temuans agar sinkron dengan nama file yang sebenarnya ada di folder
                    $assignment = $db->table('audit_question_assignments')
                        ->where('id', $assignmentId)
                        ->get()->getRow();
                    
                    if ($assignment) {
                        $db->table('temuans')
                            ->where('audit_id', $id)
                            ->where('question_id', $assignment->question_id)
                            ->update([
                                'bukti_perbaikan' => $safeName,
                                'revisi_sent_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                        $logMessages[] = "File disinkronkan ke tabel temuans";
                    }
                } else {
                    $logMessages[] = "GAGAL: Query UPDATE tidak menemukan data! Assignment ID: $assignmentId, Audit ID: $id";
                }
            }
        } else {
            $logMessages[] = "Variabel 'evidence' tidak ditemukan di request";
        }

        // TULIS LOG KE FILE (Untuk debugging jika masih ada masalah)
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