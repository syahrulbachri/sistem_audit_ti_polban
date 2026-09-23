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

        // 2. Simpan bukti (jika ada yang diupload)
        $files = $this->request->getFiles();

        if (isset($files['evidence']) && is_array($files['evidence'])) {
            foreach ($files['evidence'] as $assignmentId => $file) {

                if (
                    $file instanceof \CodeIgniter\HTTP\Files\UploadedFile &&
                    $file->isValid() &&
                    !$file->hasMoved()
                ) {

                    if ($file->getSize() > 5 * 1024 * 1024) {
                        continue; // maks 5MB
                    }

                    $allowed = [
                        'pdf',
                        'jpg',
                        'jpeg',
                        'png',
                        'doc',
                        'docx',
                        'xls',
                        'xlsx',
                        'zip'
                    ];

                    if (!in_array(strtolower($file->getClientExtension()), $allowed)) {
                        continue;
                    }

                    $db->table('audit_question_assignments')
                        ->where('id', $assignmentId)
                        ->where('audit_id', $id)
                        ->update([
                            'evidence_file' => file_get_contents($file->getTempName()),
                            'evidence_filename' => $file->getClientName(),
                            'evidence_uploaded_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }
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

        /*
         * HANYA temuan yang RTL-nya sudah di-APPROVE auditor
         * (status In_Progress) yang masuk halaman revisi.
         *
         * Temuan Open:
         * - RTL ditolak / belum direview
         * - ditangani melalui halaman RTL
         * - tidak masuk halaman revisi
         */
        $temuans = $db->table('temuans')
            ->select(
                'temuans.*, 
                 aq.clause_code, 
                 aq.question_text, 
                 aqa.id as assignment_id, 
                 aqa.answer, 
                 aqa.evidence_filename'
            )
            ->join(
                'audit_questions as aq',
                'aq.id = temuans.question_id',
                'left'
            )
            ->join(
                'audit_question_assignments as aqa',
                'aqa.audit_id = temuans.audit_id 
                 AND aqa.question_id = temuans.question_id',
                'left'
            )
            ->where('temuans.audit_id', $id)
            ->where('temuans.status', 'In_Progress')
            ->orderBy('temuans.created_at', 'ASC')
            ->get()
            ->getResult();

        return view('auditee/revisi', [
            'title' => 'Revisi Jawaban - Sistem Audit IT POLBAN',
            'page_title' => 'Revisi Jawaban',
            'audit' => $audit,
            'temuans' => $temuans
        ]);
    }


    // Simpan revisi jawaban + bukti baru
    // Kirim revisi jawaban ke Auditor
    public function saveRevisi(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('id');

        // Validasi kepemilikan audit
        $audit = $db->table('audits')
            ->where('id', $id)
            ->where('auditee_id', $userId)
            ->get()->getRow();

        if (!$audit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $answers = $this->request->getPost('answers') ?? [];
        $uploadedNames = [];

        // 1. Simpan jawaban revisi (Teks)
        foreach ($answers as $assignmentId => $answerText) {
            $db->table('audit_question_assignments')
                ->where('id', $assignmentId)
                ->where('audit_id', $id)
                ->update([
                    'answer' => $answerText
                ]);
        }

        // 2. Simpan bukti baru (jika ada) & catat nama filenya
        $files = $this->request->getFiles();

        if (isset($files['evidence']) && is_array($files['evidence'])) {

            foreach ($files['evidence'] as $assignmentId => $file) {

                if (
                    $file instanceof \CodeIgniter\HTTP\Files\UploadedFile &&
                    $file->isValid() &&
                    !$file->hasMoved()
                ) {

                    // Batas ukuran 5MB
                    if ($file->getSize() > 5 * 1024 * 1024) {
                        continue;
                    }

                    $allowed = [
                        'pdf',
                        'jpg',
                        'jpeg',
                        'png',
                        'doc',
                        'docx',
                        'xls',
                        'xlsx',
                        'zip'
                    ];

                    if (!in_array(strtolower($file->getClientExtension()), $allowed)) {
                        continue;
                    }

                    // Ambil isi file
                    $content = file_get_contents($file->getTempName());

                    // Nama file asli
                    $safeName = $file->getClientName();

                    // Simpan file ke database
                    $db->table('audit_question_assignments')
                        ->where('id', $assignmentId)
                        ->where('audit_id', $id)
                        ->update([
                            'evidence_file' => $content,
                            'evidence_filename' => $safeName,
                            'evidence_uploaded_at' => date('Y-m-d H:i:s')
                        ]);

                    /*
                     * Simpan juga file fisik ke:
                     * public/uploads/bukti_perbaikan
                     *
                     * supaya nilai temuans.bukti_perbaikan
                     * memiliki file nyata di server.
                     */
                    $folder = FCPATH . 'uploads/bukti_perbaikan';

                    if (!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    file_put_contents(
                        $folder . DIRECTORY_SEPARATOR . $safeName,
                        $content
                    );

                    // Catat nama file yang berhasil diupload
                    $uploadedNames[$assignmentId] = $safeName;
                }
            }
        }

        /*
         * 3. KIRIM KE AUDITOR
         *
         * Gabungkan ID dari jawaban teks dan upload file
         * agar tidak ada data yang terlewat.
         */
        $processedIds = array_unique(
            array_merge(
                array_keys($answers),
                array_keys($uploadedNames)
            )
        );

        foreach ($processedIds as $assignmentId) {

            $assignment = $db->table('audit_question_assignments')
                ->where('id', $assignmentId)
                ->where('audit_id', $id)
                ->get()
                ->getRow();

            if ($assignment) {

                $dataTemuan = [
                    // Revisi sudah dikirim kembali ke auditor
                    'revisi_sent_at' => date('Y-m-d H:i:s'),

                    'updated_at' => date('Y-m-d H:i:s')
                ];

                /*
                 * Jika ada bukti baru,
                 * sinkronkan nama file ke tabel temuans.
                 */
                if (isset($uploadedNames[$assignmentId])) {
                    $dataTemuan['bukti_perbaikan'] =
                        $uploadedNames[$assignmentId];
                }

                /*
                 * HANYA temuan In_Progress
                 * yang boleh dikirim melalui halaman revisi.
                 */
                $db->table('temuans')
                    ->where('audit_id', $id)
                    ->where('question_id', $assignment->question_id)
                    ->where('status', 'In_Progress')
                    ->update($dataTemuan);
            }
        }

        // LOG AKTIVITAS: revisi + bukti dikirim ke auditor
        log_aktivitas(
            'Mengirim revisi jawaban & bukti perbaikan audit "' .
            $audit->title .
            '" (menunggu verifikasi auditor)',
            'revisi'
        );

        return redirect()->to('/auditee/dashboard')
            ->with(
                'success',
                'Revisi berhasil dikirim ke auditor. Silakan tunggu verifikasi.'
            );
    }
}