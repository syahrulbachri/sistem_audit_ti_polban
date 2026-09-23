<?php

namespace App\Controllers\Pimpinan;

use App\Controllers\BaseController;

class PimpinanProfileController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }
        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('id');

        $user = $db->table('users')->where('id', $userId)->get()->getRow();

        if (!$user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('profile/index', [
            'title'      => 'Profil Saya - Sistem Audit IT POLBAN',
            'page_title' => 'Profil Saya',
            'user'       => $user
        ]);
    }

    public function edit()
    {
        return $this->index();
    }

    public function updateIdentity()
    {
        return $this->update();
    }

    public function update()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }
        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $userId = session()->get('id');
        $db = \Config\Database::connect();

        $rules = [
            'fullname' => 'required|min_length[3]|max_length[100]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $currentUser = $db->table('users')->where('id', $userId)->get()->getRow();

        $data = [
            'fullname'   => $this->request->getPost('fullname'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $newPassword = $this->request->getPost('new_password');
        if (!empty($newPassword)) {
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $folder = FCPATH . 'uploads/profile_photos';

        if ($this->request->getPost('remove_photo') === '1') {
            if (!empty($currentUser->photo) && is_file($folder . DIRECTORY_SEPARATOR . $currentUser->photo)) {
                @unlink($folder . DIRECTORY_SEPARATOR . $currentUser->photo);
            }
            $data['photo'] = null;
        } else {
            $file = $this->request->getFile('photo');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower($file->getClientExtension());

                if ($file->getSize() > 2 * 1024 * 1024) {
                    return redirect()->back()->withInput()->with('error', 'Ukuran foto maksimal 2MB.');
                }
                if (!in_array($ext, $allowed)) {
                    return redirect()->back()->withInput()->with('error', 'Format foto harus JPG, PNG, atau WEBP.');
                }

                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }

                $newName = 'user_' . $userId . '_' . time() . '.' . $ext;
                $file->move($folder, $newName);

                if (!empty($currentUser->photo) && is_file($folder . DIRECTORY_SEPARATOR . $currentUser->photo)) {
                    @unlink($folder . DIRECTORY_SEPARATOR . $currentUser->photo);
                }

                $data['photo'] = $newName;
            }
        }

        $db->table('users')->where('id', $userId)->update($data);

        session()->set('fullname', $data['fullname']);
        if (array_key_exists('photo', $data)) {
            if ($data['photo'] === null) {
                session()->remove('photo');
            } else {
                session()->set('photo', $data['photo']);
            }
        }

        return redirect()->to('/pimpinan/profile')->with('success', 'Profil berhasil diperbarui!');
    }
}
