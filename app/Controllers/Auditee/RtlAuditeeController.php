<?php

namespace App\Controllers\Auditee;

use App\Controllers\BaseController;

class RtlAuditeeController extends BaseController
{
    // Halaman daftar temuan yang perlu diisi RTL (per audit)
    public function index(int $auditId)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $audit = $db->table('audits')
            ->where('id', $auditId)
            ->where('auditee_id', $userId)
            ->get()->getRow();
        if (!$audit)
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        // Ambil semua temuan berstatus Open (yang butuh RTL / menunggu review / ditolak)
        $temuans = $db->table('temuans')
            ->select('temuans.*, aq.clause_code, aq.question_text')
            ->join('audit_questions as aq', 'aq.id = temuans.question_id', 'left')
            ->where('temuans.audit_id', $auditId)
            ->where('temuans.status', 'Open')
            ->orderBy('temuans.tingkat_risiko', 'DESC')
            ->get()->getResult();

        return view('auditee/rtl', [
            'title' => 'Rencana Tindak Lanjut - Sistem Audit IT POLBAN',
            'page_title' => 'Rencana Tindak Lanjut (RTL)',
            'audit' => $audit,
            'temuans' => $temuans
        ]);
    }

       // Auditee mengirim/merevisi RTL ke auditor
    public function submit(int $temuanId)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $temuan = $db->table('temuans')->where('id', $temuanId)->get()->getRow();
        if (!$temuan) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Pastikan temuan milik auditee ini & masih Open (atau In_Progress jika revisi bukti)
        $audit = $db->table('audits')
            ->where('id', $temuan->audit_id)
            ->where('auditee_id', $userId)
            ->get()->getRow();
            
        if (!$audit || !in_array($temuan->status, ['Open', 'In_Progress'])) {
            return redirect()->to('/auditee/dashboard')->with('error', 'Temuan tidak valid untuk diisi/direvisi.');
        }

        $desc = $this->request->getPost('rtl_description') ?? '';
        $anggaran = $this->request->getPost('rtl_anggaran');
        $deadline = $this->request->getPost('rtl_deadline') ?? '';

        if (empty($desc) || empty($deadline)) {
            return redirect()->back()->withInput()->with('error', 'Rencana dan estimasi waktu wajib diisi!');
        }

        // Data dasar yang akan diupdate
        $updateData = [
            'rtl_description' => $desc,
            'rtl_anggaran' => ($anggaran === '' || $anggaran === null) ? 0 : (float) $anggaran,
            'rtl_deadline' => $deadline,
            'auditor_note' => null, // bersihkan catatan reject lama
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // ✅ PERBAIKAN UTAMA: Tambahkan logika upload file jika ada
        $file = $this->request->getFile('bukti');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validasi ukuran (Maksimal 5MB)
            if ($file->getSize() > 5 * 1024 * 1024) {
                return redirect()->back()->withInput()->with('error', 'Ukuran file bukti maksimal 5MB.');
            }
            
            // Validasi ekstensi
            $allowed = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
            if (!in_array(strtolower($file->getClientExtension()), $allowed)) {
                return redirect()->back()->withInput()->with('error', 'Format file tidak diizinkan (Gunakan: pdf, jpg, png, doc, docx, xls, xlsx, zip).');
            }

            // Simpan file
            $folder = FCPATH . 'uploads/bukti_perbaikan';
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            
            // PERBAIKAN BEST PRACTICE: Gunakan getRandomName() agar nama file unik 
            // dan tidak tertimpa/eror karena spasi atau karakter khusus di nama file asli.
            $newFileName = $file->getRandomName();
            $file->move($folder, $newFileName);

            // Masukkan nama file baru ke data yang akan diupdate
            $updateData['bukti_perbaikan'] = $newFileName;
        }

        // Eksekusi update ke database
        $db->table('temuans')->where('id', $temuanId)->update($updateData);

        // Log aktivitas
        $actionText = ($temuan->status === 'Open') ? 'Mengajukan revisi RTL' : 'Mengupload revisi bukti perbaikan';
        log_aktivitas($actionText . ' untuk temuan audit "' . $audit->title . '"', 'rtl');

        return redirect()->to('/auditee/rtl/' . $audit->id)
            ->with('success', 'Data revisi berhasil dikirim ke auditor!');
    }
}
