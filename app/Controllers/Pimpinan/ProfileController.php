<?php
namespace App\Controllers\pimpinan;
use App\Controllers\BaseController;

class ProfileController extends BaseController
{

    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $user = $db->table('users')->where('id', $userId)->get()->getRow();

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('profile/index', [
            'title' => 'Profil Saya - Sistem Audit IT POLBAN',
            'page_title' => 'Profil Saya',
            'user' => $user
        ]);
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $userId = session()->get('id');

        $rules = [
            'fullname' => 'required|min_length[3]|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $data = [
            'fullname' => $this->request->getPost('fullname'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Jika user mengisi password baru, update juga
        $newPassword = $this->request->getPost('new_password');
        if (!empty($newPassword)) {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $db->table('users')->where('id', $userId)->update($data);

        // Update session fullname jika berubah
        session()->set('fullname', $data['fullname']);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }
}