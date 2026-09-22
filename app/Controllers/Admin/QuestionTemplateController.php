<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class QuestionTemplateController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('audit_questions');

        // Ambil semua framework aktif untuk dropdown
        $frameworks = $db->table('frameworks')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();

        // Filter framework
        $frameworkFilter = $this->request->getGet('framework');

        if ($frameworkFilter) {
            $builder->where('framework', $frameworkFilter);
        }

        // Search
        $search = $this->request->getGet('search');

        if ($search) {
            $builder->groupStart()
                ->like('question_text', $search)
                ->orLike('clause_code', $search)
                ->groupEnd();
        }

        // Pagination
        $perPage = 10;

        $currentPage = (int) ($this->request->getGet('page') ?? 1);

        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $offset = ($currentPage - 1) * $perPage;

        // Total data
        $total = $builder->countAllResults(false);

        // Ambil data
        $questions = $builder
            ->select('audit_questions.*, frameworks.scoring_type')
            ->join(
                'frameworks',
                'frameworks.nama = audit_questions.framework',
                'left'
            )
            ->orderBy('audit_questions.framework', 'ASC')
            ->orderBy('audit_questions.clause_code', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResult();

        // Pagination
        $pager = \Config\Services::pager();
        $pager->setPath('/admin/questions');

        return view('admin/questions/index', [
            'title'       => 'Template Pertanyaan - Sistem Audit IT POLBAN',
            'page_title'  => 'Manajemen Template Pertanyaan',
            'questions'   => $questions,
            'frameworks'  => $frameworks,
            'filter'      => $frameworkFilter,
            'search'      => $search,
            'pager'       => $pager,
            'total'       => $total,
            'perPage'     => $perPage,
            'currentPage' => $currentPage
        ]);
    }

    public function create()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        $frameworks = $db->table('frameworks')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();

        return view('admin/questions/create', [
            'title'      => 'Tambah Pertanyaan - Sistem Audit IT POLBAN',
            'page_title' => 'Tambah Pertanyaan Baru',
            'frameworks' => $frameworks
        ]);
    }

    public function store()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil data form
        $framework = trim($this->request->getPost('framework'));
        $clauseCode = trim($this->request->getPost('clause_code'));
        $questionText = trim($this->request->getPost('question_text'));

        // Validasi
        if (empty($framework)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Framework harus dipilih!');
        }

        if (empty($questionText) || strlen($questionText) < 10) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Pertanyaan minimal 10 karakter!');
        }

        // Cek framework
        $fwCheck = $db->table('frameworks')
            ->where('nama', $framework)
            ->where('is_active', 1)
            ->countAllResults();

        if ($fwCheck === 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Framework tidak valid!');
        }

        // Data insert
        $insertData = [
            'framework'     => $framework,
            'clause_code'   => $clauseCode,
            'question_text' => $questionText,
            'is_standard'   => 1,
            'created_at'    => date('Y-m-d H:i:s')
        ];

        $db->table('audit_questions')->insert($insertData);

        // FIX:
        // sebelumnya menggunakan $question_text
        // sekarang menggunakan $questionText
        log_activity(
            'CREATE',
            'audit_questions',
            'Menambahkan pertanyaan template: '
            . substr($questionText, 0, 50)
            . '...'
        );

        return redirect()->to('/admin/questions')
            ->with('success', 'Pertanyaan berhasil ditambahkan!');
    }

    public function edit($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        $question = $db->table('audit_questions')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$question) {
            return redirect()->to('/admin/questions')
                ->with('error', 'Pertanyaan tidak ditemukan.');
        }

        $frameworks = $db->table('frameworks')
            ->where('is_active', 1)
            ->orderBy('nama', 'ASC')
            ->get()
            ->getResult();

        return view('admin/questions/edit', [
            'title'      => 'Edit Pertanyaan - Sistem Audit IT POLBAN',
            'page_title' => 'Edit Pertanyaan',
            'question'   => $question,
            'frameworks' => $frameworks
        ]);
    }

    public function update($id)
    {
        // Cek akses admin
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil framework aktif
        $activeFrameworks = $db->table('frameworks')
            ->select('nama')
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        $frameworkList = implode(
            ',',
            array_column($activeFrameworks, 'nama')
        );

        $rules = [
            'framework'     => 'required|in_list[' . $frameworkList . ']',
            'clause_code'   => 'permit_empty|max_length[20]',
            'question_text' => 'required|min_length[10]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $question = $db->table('audit_questions')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$question) {
            return redirect()->to('/admin/questions')
                ->with('error', 'Pertanyaan tidak ditemukan.');
        }

        $db->table('audit_questions')
            ->where('id', $id)
            ->update([
                'framework'     => $this->request->getPost('framework'),
                'clause_code'   => $this->request->getPost('clause_code'),
                'question_text' => $this->request->getPost('question_text'),
            ]);

        log_activity(
            'UPDATE',
            'audit_questions',
            'Mengubah pertanyaan template ID: ' . $id
        );

        return redirect()->to('/admin/questions')
            ->with('success', 'Pertanyaan berhasil diperbarui!');
    }

    public function delete($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil data sebelum dihapus
        $question = $db->table('audit_questions')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$question) {
            return redirect()->to('/admin/questions')
                ->with('error', 'Pertanyaan tidak ditemukan.');
        }

        // Hapus
        $db->table('audit_questions')
            ->where('id', $id)
            ->delete();

        log_activity(
            'DELETE',
            'audit_questions',
            'Menghapus pertanyaan template ID: ' . $id
        );

        return redirect()->to('/admin/questions')
            ->with('success', 'Pertanyaan berhasil dihapus!');
    }
}