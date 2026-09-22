<?php

namespace App\Controllers\Auditee;

use App\Controllers\BaseController;

class TindakLanjutController extends BaseController
{
    // ===== Halaman Form Pengajuan RTL =====
    public function rtl(int $temuanId)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $temuan = $db->table('temuans')
            ->select('temuans.*, a.title as audit_title, aq.clause_code, aq.question_text')
            ->join('audits as a', 'a.id = temuans.audit_id')
            ->join('audit_questions as aq', 'aq.id = temuans.question_id', 'left')
            ->where('temuans.id', $temuanId)
            ->where('a.auditee_id', $userId)
            ->get()->getRow();

        if (!$temuan)
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if ($temuan->status !== 'Open') {
            return redirect()->to('/auditee/dashboard')->with('error', 'RTL tidak dapat diajukan untuk temuan ini.');
        }

        return view('auditee/rtl', [
            'title' => 'Ajukan RTL - Sistem Audit IT POLBAN',
            'page_title' => 'Ajukan Rencana Tindak Lanjut',
            'temuan' => $temuan
        ]);
    }

    // ===== Simpan Pengajuan RTL =====
    public function saveRtl(int $temuanId)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $temuan = $db->table('temuans')
            ->select('temuans.*, a.title as audit_title')
            ->join('audits as a', 'a.id = temuans.audit_id')
            ->where('temuans.id', $temuanId)
            ->where('a.auditee_id', $userId)
            ->get()->getRow();

        if (!$temuan || $temuan->status !== 'Open') {
            return redirect()->to('/auditee/dashboard')->with('error', 'RTL tidak dapat diajukan untuk temuan ini.');
        }

        $rules = [
            'rtl_description' => 'required|min_length[10]',
            'rtl_deadline' => 'required|valid_date',
            'rtl_anggaran' => 'permit_empty|numeric'
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db->table('temuans')->where('id', $temuanId)->update([
            'rtl_description' => $this->request->getPost('rtl_description'),
            'rtl_deadline' => $this->request->getPost('rtl_deadline'),
            'rtl_anggaran' => $this->request->getPost('rtl_anggaran') ?: 0,
            'auditor_note' => null, // bersihkan catatan penolakan lama
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        log_aktivitas('Mengajukan RTL untuk temuan audit "' . $temuan->audit_title . '" (menunggu review auditor)', 'rtl');

        return redirect()->to('/auditee/dashboard')
            ->with('success', 'RTL berhasil diajukan! Silakan tunggu review dari Auditor.');
    }

    // ===== Halaman Upload Bukti Perbaikan =====
    public function bukti(int $temuanId)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $temuan = $db->table('temuans')
            ->select('temuans.*, a.title as audit_title, aq.clause_code')
            ->join('audits as a', 'a.id = temuans.audit_id')
            ->join('audit_questions as aq', 'aq.id = temuans.question_id', 'left')
            ->where('temuans.id', $temuanId)
            ->where('a.auditee_id', $userId)
            ->get()->getRow();

        if (!$temuan)
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        if ($temuan->status !== 'In_Progress') {
            return redirect()->to('/auditee/dashboard')->with('error', 'Upload bukti belum dapat dilakukan untuk temuan ini.');
        }

        return view('auditee/bukti', [
            'title' => 'Upload Bukti Perbaikan - Sistem Audit IT POLBAN',
            'page_title' => 'Upload Bukti Perbaikan',
            'temuan' => $temuan
        ]);
    }

    // ===== Simpan Bukti Perbaikan =====
    public function saveBukti(int $temuanId)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $temuan = $db->table('temuans')
            ->select('temuans.*, a.title as audit_title')
            ->join('audits as a', 'a.id = temuans.audit_id')
            ->where('temuans.id', $temuanId)
            ->where('a.auditee_id', $userId)
            ->get()->getRow();

        if (!$temuan || $temuan->status !== 'In_Progress') {
            return redirect()->to('/auditee/dashboard')->with('error', 'Upload bukti belum dapat dilakukan untuk temuan ini.');
        }

        $file = $this->request->getFile('bukti');
        if (!$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Silakan pilih file bukti terlebih dahulu.');
        }
        if ($file->getSize() > 5 * 1024 * 1024) {
            return redirect()->back()->with('error', 'Ukuran file maksimal 5MB.');
        }
        $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
        if (!in_array(strtolower($file->getClientExtension()), $allowed)) {
            return redirect()->back()->with('error', 'Format file tidak diizinkan.');
        }

        // Simpan file ke public/uploads/bukti
        $folder = FCPATH . 'uploads/bukti';
        if (!is_dir($folder))
            mkdir($folder, 0777, true);
        $file->move($folder, $file->getClientName());

        $db->table('temuans')->where('id', $temuanId)->update([
            'bukti_perbaikan' => $file->getClientName(),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        log_aktivitas('Mengupload bukti perbaikan temuan audit "' . $temuan->audit_title . '" (menunggu verifikasi auditor)', 'bukti');

        return redirect()->to('/auditee/dashboard')
            ->with('success', 'Bukti perbaikan berhasil dikirim! Silakan tunggu verifikasi Auditor.');
    }
}
