<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\Database\Exceptions\DatabaseException;

class FrameworkController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // OPTIMASI: Menggunakan JOIN dan GROUP BY untuk menghindari N+1 query
        $builder = $db->table('frameworks f');
        $builder->select('f.*, MIN(fo.score_value) as start_score');
        $builder->join('framework_options fo', 'f.id = fo.framework_id', 'left');
        $builder->groupBy('f.id');
        $builder->orderBy('f.created_at', 'DESC');

        $frameworks = $builder->get()->getResult();

        return view('admin/frameworks/index', [
            'title' => 'Kelola Standar Framework - Sistem Audit IT POLBAN',
            'page_title' => 'Manajemen Standar Framework',
            'frameworks' => $frameworks
        ]);
    }

    public function create()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        return view('admin/frameworks/create', [
            'title' => 'Tambah Framework Baru - Sistem Audit IT POLBAN',
            'page_title' => 'Tambah Standar Framework Baru'
        ]);
    }

    public function store()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $nama = $this->request->getPost('nama'); // FIX: Ambil nama dulu untuk log

        $rules = [
            'nama'         => 'required|min_length[3]|max_length[100]|is_unique[frameworks.nama]',
            'scoring_type' => 'required|in_list[binary,scale]',
            'max_score'    => 'required|numeric|greater_than_equal_to[1]',
            'start_score'  => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $scoringType = $this->request->getPost('scoring_type');
        $maxScore = (int) $this->request->getPost('max_score');
        $startScore = (int) $this->request->getPost('start_score');

        if ($scoringType === 'binary' && ($startScore !== 0 || $maxScore !== 1)) {
            return redirect()->back()->withInput()->with('error', 'Framework biner harus memiliki nilai start 0 dan max 1');
        }

        if ($scoringType === 'scale' && $maxScore > 10) {
            return redirect()->back()->withInput()->with('error', 'Nilai maksimal untuk framework skala tidak boleh lebih dari 10');
        }

        // Gunakan Transaction untuk memastikan data tersimpan dengan aman
        $db->transStart();

        $db->table('frameworks')->insert([
            'nama'         => $nama,
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'scoring_type' => $scoringType,
            'max_score'    => $maxScore,
            'is_active'    => 1,
            'created_at'   => date('Y-m-d H:i:s')
        ]);

        $frameworkId = $db->insertID();

        $options = $this->request->getPost('options') ?? [];

        for ($i = $startScore; $i <= $maxScore; $i++) {
            $label = $options[$i]['label'] ?? "Level $i";
            $desc = $options[$i]['description'] ?? null;

            $db->table('framework_options')->insert([
                'framework_id' => $frameworkId,
                'score_value'  => $i,
                'label'        => $label,
                'description'  => $desc,
                'sort_order'   => $i
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal menambahkan framework. Terjadi kesalahan database.');
        }

        // FIX: Variabel $nama sudah terdefinisi
        if (function_exists('log_activity')) {
            log_activity('CREATE', 'frameworks', 'Menambahkan framework: ' . $nama);
        }

        return redirect()->to('/admin/frameworks')->with('success', 'Framework baru berhasil ditambahkan!');
    }

    public function edit($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $framework = $db->table('frameworks')->where('id', $id)->get()->getRow();

        if (!$framework) {
            return redirect()->to('/admin/frameworks')->with('error', 'Framework tidak ditemukan.');
        }

        $options = $db->table('framework_options')
            ->where('framework_id', $id)
            ->orderBy('sort_order', 'ASC')
            ->get()
            ->getResult();

        $optionsArray = [];
        foreach ($options as $opt) {
            $optionsArray[$opt->score_value] = $opt;
        }

        return view('admin/frameworks/edit', [
            'title'         => 'Edit Framework - Sistem Audit IT POLBAN',
            'page_title'    => 'Edit Standar Framework',
            'framework'     => $framework,
            'options'       => $optionsArray
        ]);
    }

    public function update($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $nama = $this->request->getPost('nama'); // FIX: Ambil nama dulu untuk log

        $rules = [
            'nama'         => 'required|min_length[3]|max_length[100]|is_unique[frameworks.nama,id,' . $id . ']',
            'scoring_type' => 'required|in_list[binary,scale]',
            'max_score'    => 'required|numeric|greater_than_equal_to[1]',
            'start_score'  => 'required|numeric'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $scoringType = $this->request->getPost('scoring_type');
        $maxScore = (int) $this->request->getPost('max_score');
        $startScore = (int) $this->request->getPost('start_score');

        $db->transStart();

        $db->table('frameworks')->where('id', $id)->update([
            'nama'         => $nama,
            'deskripsi'    => $this->request->getPost('deskripsi'),
            'scoring_type' => $scoringType,
            'max_score'    => $maxScore
        ]);

        // Hapus options lama
        $db->table('framework_options')->where('framework_id', $id)->delete();

        // Insert options baru
        $options = $this->request->getPost('options') ?? [];
        for ($i = $startScore; $i <= $maxScore; $i++) {
            $label = $options[$i]['label'] ?? "Level $i";
            $desc = $options[$i]['description'] ?? null;

            $db->table('framework_options')->insert([
                'framework_id' => $id,
                'score_value'  => $i,
                'label'        => $label,
                'description'  => $desc,
                'sort_order'   => $i
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memperbarui framework. Terjadi kesalahan database.');
        }

        if (function_exists('log_activity')) {
            log_activity('UPDATE', 'frameworks', 'Mengubah framework: ' . $nama);
        }

        return redirect()->to('/admin/frameworks')->with('success', 'Framework berhasil diperbarui!');
    }

    public function toggle($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $fw = $db->table('frameworks')->where('id', $id)->get()->getRow();

        if (!$fw) return redirect()->to('/admin/frameworks')->with('error', 'Framework tidak ditemukan.');

        // Ubah status: 1 jadi 0, atau 0 jadi 1
        $newStatus = ($fw->is_active == 1) ? 0 : 1;
        $db->table('frameworks')->where('id', $id)->update(['is_active' => $newStatus]);

        $msg = $newStatus ? 'Framework diaktifkan.' : 'Framework dinonaktifkan (hanya untuk arsip).';

        // ✅ CATAT LOG AKTIVITAS (Opsional, tapi bagus untuk audit trail)
        if (function_exists('log_activity')) {
            $action = $newStatus ? 'UPDATE' : 'UPDATE';
            $desc = $newStatus ? 'Mengaktifkan framework: ' . $fw->nama : 'Menonaktifkan framework: ' . $fw->nama;
            log_activity($action, 'frameworks', $desc);
        }

        return redirect()->to('/admin/frameworks')->with('success', $msg);
    }
}
