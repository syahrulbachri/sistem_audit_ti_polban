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

    // Auditee mengirim RTL ke auditor
    public function submit(int $temuanId)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'auditee') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $temuan = $db->table('temuans')->where('id', $temuanId)->get()->getRow();
        if (!$temuan)
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        // Pastikan temuan milik auditee ini & masih Open
        $audit = $db->table('audits')
            ->where('id', $temuan->audit_id)
            ->where('auditee_id', $userId)
            ->get()->getRow();
        if (!$audit || $temuan->status !== 'Open') {
            return redirect()->to('/auditee/dashboard')->with('error', 'Temuan tidak valid untuk diisi RTL.');
        }

        $desc = $this->request->getPost('rtl_description') ?? '';
        $anggaran = $this->request->getPost('rtl_anggaran');
        $deadline = $this->request->getPost('rtl_deadline') ?? '';

        if (empty($desc) || empty($deadline)) {
            return redirect()->back()->withInput()->with('error', 'Rencana dan estimasi waktu wajib diisi!');
        }

        $db->table('temuans')->where('id', $temuanId)->update([
            'rtl_description' => $desc,
            'rtl_anggaran' => ($anggaran === '' || $anggaran === null) ? 0 : (float) $anggaran,
            'rtl_deadline' => $deadline,
            'auditor_note' => null, // bersihkan catatan reject lama
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // ✅ LOG AKTIVITAS: auditee submit RTL
        log_aktivitas('Mengajukan RTL untuk temuan audit "' . $audit->title . '" (menunggu review auditor)', 'rtl');

        return redirect()->to('/auditee/rtl/' . $audit->id)
            ->with('success', 'RTL berhasil dikirim ke auditor untuk direview.');
    }
}
