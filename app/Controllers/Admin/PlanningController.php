<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PlanningController extends BaseController
{

    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('audits');

        // Filter/Search
        $search = $this->request->getGet('search');
        $filterFramework = $this->request->getGet('framework');
        $filterStatus = $this->request->getGet('status');
        $filterPeriode = $this->request->getGet('periode');

        if ($search) {
            $builder->groupStart()
                ->like('audits.title', $search)
                ->orLike('u_auditee.fullname', $search)
                ->orLike('u_auditor.fullname', $search)
                ->orLike('audits.framework', $search)
                ->groupEnd();
        }

        if ($filterFramework) {
            $builder->where('audits.framework', $filterFramework);
        }

        if ($filterStatus) {
            $builder->where('audits.status', $filterStatus);
        }

        if ($filterPeriode) {
            $builder->where('audits.periode_id', $filterPeriode);
        }

        // Join tabel
        $builder->select('audits.*, periodes.nama_periode, u_auditee.fullname as auditee_name, u_auditor.fullname as auditor_name')
            ->join('periodes', 'periodes.id = audits.periode_id', 'left')
            ->join('users as u_auditee', 'u_auditee.id = audits.auditee_id', 'left')
            ->join('users as u_auditor', 'u_auditor.id = audits.created_by_auditor', 'left');

        // Pagination
        $perPage = 5;
        $currentPage = (int) ($this->request->getGet('page') ?? 1);
        if ($currentPage < 1) $currentPage = 1;

        // Hitung total sebelum limit
        $total = $builder->countAllResults(false);
        $totalPages = (int) ceil($total / $perPage);
        if ($currentPage > $totalPages && $totalPages > 0) $currentPage = $totalPages;

        $offset = ($currentPage - 1) * $perPage;

        $audits = $builder->orderBy('audits.created_at', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResult();

        // Ambil data untuk filter dropdown
        $allPeriodes = $db->table('periodes')->orderBy('nama_periode', 'ASC')->get()->getResult();

        return view('admin/planning/index', [
            'title'           => 'Perencanaan Audit - Sistem Audit IT POLBAN',
            'page_title'      => 'Daftar Rencana Audit',
            'audits'          => $audits,
            'currentPage'     => $currentPage,
            'totalPages'      => $totalPages,
            'total'           => $total,
            'perPage'         => $perPage,
            'search'          => $search,
            'filterFramework' => $filterFramework,
            'filterStatus'    => $filterStatus,
            'filterPeriode'   => $filterPeriode,
            'allPeriodes'     => $allPeriodes
        ]);
    }

    public function detail(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // 1. Ambil data dasar audit
        $audit = $db->table('audits a')
            ->select('a.*, p.nama_periode, auditor.fullname as auditor_name, auditee.fullname as auditee_name')
            ->join('periodes p', 'p.id = a.periode_id', 'left')
            ->join('users auditor', 'auditor.id = a.created_by_auditor', 'left')
            ->join('users auditee', 'auditee.id = a.auditee_id', 'left')
            ->where('a.id', $id)
            ->get()->getRow();

        if (!$audit) {
            return redirect()->to('/admin/planning')->with('error', 'Data audit tidak ditemukan.');
        }

        // 2. Ambil pertanyaan yang di-assign
        $assignedQuestions = $db->table('audit_question_assignments aqa')
            ->select('aqa.*, aq.question_text, aq.clause_code, aq.is_standard')
            ->join('audit_questions aq', 'aq.id = aqa.question_id')
            ->where('aqa.audit_id', $id)
            ->orderBy('aq.clause_code', 'ASC')
            ->get()->getResult();

        // 3. Ambil Temuan & RTL (Hanya jika status selesai)
        $findings = [];
        if ($audit->status === 'selesai') {
            $findings = $db->table('temuans t')
                ->select('t.*, aq.clause_code')
                ->join('audit_questions aq', 'aq.id = t.question_id', 'left')
                ->where('t.audit_id', $id)
                ->orderBy('t.tingkat_risiko', 'DESC')
                ->get()->getResult();
        }

        // 4. Ambil info framework untuk format skor
        $fw = $db->table('frameworks')->where('nama', $audit->framework)->get()->getRow();
        $isBinary = $fw && $fw->scoring_type === 'binary';

        return view('admin/planning/detail', [
            'title'             => 'Detail Audit - Sistem Audit IT POLBAN',
            'page_title'        => 'Detail Audit: ' . $audit->title,
            'audit'             => $audit,
            'assignedQuestions' => $assignedQuestions,
            'findings'          => $findings,
            'isBinary'          => $isBinary
        ]);
    }

    public function create()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // ✅ AMBIL SEMUA FRAMEWORK YANG AKTIF dari database
        $frameworks = $db->table('frameworks')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();

        // Ambil periode yang statusnya 'open'
        $periodes = $db->table('periodes')->where('status', 'open')->orderBy('nama_periode', 'ASC')->get()->getResult();

        // Ambil semua auditee
        $auditees = $db->table('users')->where('role', 'auditee')->orderBy('fullname', 'ASC')->get()->getResult();

        // Ambil semua auditor
        $auditors = $db->table('users')->where('role', 'auditor')->orderBy('fullname', 'ASC')->get()->getResult();

        return view('admin/planning/create', [
            'title'      => 'Buat Rencana Audit - Sistem Audit IT POLBAN',
            'page_title' => 'Buat Rencana Audit Baru',
            'frameworks' => $frameworks,  // ✅ KIRIM DATA FRAMEWORK KE VIEW
            'periodes'   => $periodes,
            'auditees'   => $auditees,
            'auditors'   => $auditors
        ]);
    }

    public function store()
    {
        $db = \Config\Database::connect();

        // ✅ AMBIL SEMUA NAMA FRAMEWORK YANG AKTIF DARI DATABASE
        $activeFrameworks = $db->table('frameworks')
            ->select('nama')
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        // Convert array menjadi string untuk validasi in_list
        $frameworkList = implode(',', array_column($activeFrameworks, 'nama'));

        $rules = [
            'title'              => 'required|min_length[3]|max_length[150]',
            'periode_id'         => 'permit_empty|numeric',
            'auditee_id'         => 'required|numeric',
            'created_by_auditor' => 'required|numeric',
            'framework'          => 'required|in_list[' . $frameworkList . ']', // ✅ DINAMIS!
            'deadline'           => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();

        // Ambil data dari form
        $title = $this->request->getPost('title');
        $auditee_id = $this->request->getPost('auditee_id');
        $periode_id = $this->request->getPost('periode_id');
        $framework = $this->request->getPost('framework');
        $deadline = $this->request->getPost('deadline');
        $auditor_id = $this->request->getPost('created_by_auditor'); // ✅ AMBIL DARI DROPDOWN

        // 1. INSERT AUDIT UTAMA
        $db->table('audits')->insert([
            'title'              => $title,
            'auditee_id'         => $auditee_id,
            'periode_id'         => $periode_id,
            'framework'          => $framework,
            'deadline'           => $deadline,
            'status'             => 'aktif',
            'created_by_auditor' => $auditor_id, // ✅ SIMPAN ID AUDITOR YANG DIPILIH
            'created_at'         => date('Y-m-d H:i:s')
        ]);
        $auditId = $db->insertID();

        // ==========================================
        // 2. SIMPAN PERTANYAAN CUSTOM + AUTO-ASSIGN
        // ==========================================
        $customCodes = $this->request->getPost('custom_clause_code');
        $customTexts = $this->request->getPost('custom_question_text');

        if (!empty($customTexts)) {
            foreach ($customTexts as $index => $text) {
                if (!empty(trim($text))) {
                    // Auto-generate kode jika kosong
                    $code = !empty($customCodes[$index]) ? $customCodes[$index] : 'CUST-' . ($index + 1);

                    // Insert pertanyaan custom
                    $db->table('audit_questions')->insert([
                        'framework'     => $framework,
                        'clause_code'   => $code,
                        'question_text' => $text,
                        'is_standard'   => 0, // 0 = Custom
                        'created_at'    => date('Y-m-d H:i:s')
                    ]);
                    $questionId = $db->insertID();

                    // LANGSUNG AUTO-ASSIGN ke audit ini
                    $db->table('audit_question_assignments')->insert([
                        'audit_id'    => $auditId,
                        'question_id' => $questionId,
                        'created_at'  => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        log_activity('CREATE', 'audits', 'Membuat rencana audit: ' . $title);

        return redirect()->to('/admin/planning')->with('success', 'Rencana audit berhasil dibuat dengan pertanyaan custom yang otomatis ditugaskan!');
    }

    public function edit($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $audit = $db->table('audits')->where('id', $id)->get()->getRow();

        if (!$audit) {
            return redirect()->to('/admin/planning')->with('error', 'Rencana audit tidak ditemukan.');
        }

        // ✅ AMBIL SEMUA FRAMEWORK YANG AKTIF
        $frameworks = $db->table('frameworks')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();

        // Untuk edit, kita tampilkan SEMUA periode (termasuk closed)
        $periodes = $db->table('periodes')->orderBy('nama_periode', 'ASC')->get()->getResult();
        $auditees = $db->table('users')->where('role', 'auditee')->orderBy('fullname', 'ASC')->get()->getResult();
        $auditors = $db->table('users')->where('role', 'auditor')->orderBy('fullname', 'ASC')->get()->getResult();

        return view('admin/planning/edit', [
            'title'      => 'Edit Rencana Audit - Sistem Audit IT POLBAN',
            'page_title' => 'Edit Rencana Audit',
            'audit'      => $audit,
            'frameworks' => $frameworks, // ✅ KIRIM DATA FRAMEWORK KE VIEW
            'periodes'   => $periodes,
            'auditees'   => $auditees,
            'auditors'   => $auditors
        ]);
    }

    public function update($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Cek apakah audit tersedia
        $audit = $db->table('audits')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$audit) {
            return redirect()->to('/admin/planning')
                ->with('error', 'Rencana audit tidak ditemukan.');
        }

        // Ambil semua framework aktif
        $activeFrameworks = $db->table('frameworks')
            ->select('nama')
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        $frameworkList = implode(',', array_column($activeFrameworks, 'nama'));

        $rules = [
            'title'              => 'required|min_length[3]|max_length[150]',
            'periode_id'         => 'permit_empty|numeric',
            'auditee_id'         => 'required|numeric',
            'created_by_auditor' => 'required|numeric',
            'framework'          => 'required|in_list[' . $frameworkList . ']',
            'deadline'           => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Ambil title agar bisa digunakan untuk log
        $title = $this->request->getPost('title');

        $db->table('audits')
            ->where('id', $id)
            ->update([
                'title'              => $title,
                'periode_id'         => $this->request->getPost('periode_id') ?: null,
                'auditee_id'         => $this->request->getPost('auditee_id'),
                'created_by_auditor' => $this->request->getPost('created_by_auditor'),
                'framework'          => $this->request->getPost('framework'),
                'deadline'           => $this->request->getPost('deadline'),
                'updated_at'         => date('Y-m-d H:i:s')
            ]);

        log_activity(
            'UPDATE',
            'audits',
            'Mengubah rencana audit: ' . $title
        );

        return redirect()->to('/admin/planning')
            ->with('success', 'Rencana audit berhasil diperbarui!');
    }

    public function delete(int $id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $audit = $db->table('audits')->where('id', $id)->get()->getRow();

        if (!$audit) {
            return redirect()->to('/admin/planning')->with('error', 'Data tidak ditemukan.');
        }

        // ✅ CEK STATUS: Hanya boleh hapus jika masih 'aktif'
        if ($audit->status !== 'aktif') {
            return redirect()->to('/admin/planning')->with('error', 'Audit yang sudah berjalan atau selesai tidak dapat dihapus.');
        }

        // Hapus data terkait dulu (assignments)
        $db->table('audit_question_assignments')->where('audit_id', $id)->delete();

        // Hapus audit
        $db->table('audits')->where('id', $id)->delete();

        // Catat log
        log_activity('DELETE', 'audits', 'Menghapus rencana audit: ' . $audit->title);

        return redirect()->to('/admin/planning')->with('success', 'Rencana audit berhasil dihapus.');
    }
}
