<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class UserController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil semua user kecuali admin yang sedang login
        $currentUserId = session()->get('id');

        $users = $db->table('users')
            ->where('id !=', $currentUserId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResult();

        return view('admin/users/index', [
            'title'      => 'Manajemen Akun - Sistem Audit IT POLBAN',
            'page_title' => 'Manajemen Akun Pengguna',
            'users'      => $users
        ]);
    }

    public function create()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        return view('admin/users/create', [
            'title'      => 'Tambah User - Sistem Audit IT POLBAN',
            'page_title' => 'Tambah Pengguna Baru'
        ]);
    }

    public function store()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'fullname' => 'required|min_length[3]|max_length[100]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[auditor,auditee,pimpinan]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Ambil data terlebih dahulu agar bisa digunakan di log_activity()
        $username = $this->request->getPost('username');
        $fullname = $this->request->getPost('fullname');
        $role     = $this->request->getPost('role');

        $db = \Config\Database::connect();

        $db->table('users')->insert([
            'username'   => $username,
            'fullname'   => $fullname,
            'password'   => password_hash(
                $this->request->getPost('password'),
                PASSWORD_DEFAULT
            ),
            'role'       => $role,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        log_activity(
            'CREATE',
            'users',
            'Menambahkan user baru: ' . $username . ' (' . $role . ')'
        );

        return redirect()->to('/admin/users')
            ->with('success', 'Pengguna berhasil ditambahkan!');
    }

    public function edit($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        $user = $db->table('users')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$user) {
            return redirect()->to('/admin/users')
                ->with('error', 'User tidak ditemukan.');
        }

        return view('admin/users/edit', [
            'title'      => 'Edit User - Sistem Audit IT POLBAN',
            'page_title' => 'Edit Pengguna',
            'user'       => $user
        ]);
    }

    public function update($id)
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,' . $id . ']',
            'fullname' => 'required|min_length[3]|max_length[100]',
            'role'     => 'required|in_list[auditor,auditee,pimpinan,admin]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Ambil username baru
        $username = $this->request->getPost('username');

        $db = \Config\Database::connect();

        $data = [
            'username'   => $username,
            'fullname'   => $this->request->getPost('fullname'),
            'role'       => $this->request->getPost('role'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Hanya update password jika diisi
        $newPassword = $this->request->getPost('password');

        if (!empty($newPassword)) {
            $data['password'] = password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );
        }

        $db->table('users')
            ->where('id', $id)
            ->update($data);

        log_activity(
            'UPDATE',
            'users',
            'Mengubah data user: ' . $username
        );

        return redirect()->to('/admin/users')
            ->with('success', 'Data pengguna berhasil diperbarui!');
    }

    public function delete($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        // Cegah admin menghapus akunnya sendiri
        if ($id == session()->get('id')) {
            return redirect()->to('/admin/users')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $db = \Config\Database::connect();

        // Ambil data user sebelum dihapus
        $user = $db->table('users')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$user) {
            return redirect()->to('/admin/users')
                ->with('error', 'User tidak ditemukan.');
        }

        // Hapus user
        $db->table('users')
            ->where('id', $id)
            ->delete();

        // Catat aktivitas
        log_activity(
            'DELETE',
            'users',
            'Menghapus user: ' . $user->username
        );

        return redirect()->to('/admin/users')
            ->with('success', 'Pengguna berhasil dihapus!');
    }
}