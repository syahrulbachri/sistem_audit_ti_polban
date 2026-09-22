<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Auth extends Controller
{
    public function index()
    {
        if (session()->get('logged_in')) {
            $role = session()->get('role');
            if ($role === 'admin')
                return redirect()->to('/admin/dashboard');
            if ($role === 'auditor')
                return redirect()->to('/dashboard');
            if ($role === 'auditee')
                return redirect()->to('/auditee/dashboard');
            if ($role === 'pimpinan')
                return redirect()->to('/pimpinan/dashboard');
        }
        return view('auth/login');
    }

    public function process()
    {
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $db = \Config\Database::connect();

        $user = $db->table('users')->where('username', $username)->get()->getRow();

        if ($user && password_verify($password, $user->password)) {
            session()->set([
                'id' => $user->id,
                'username' => $user->username,
                'fullname' => $user->fullname,
                'role' => $user->role,
                'logged_in' => TRUE
            ]);

            log_activity('LOGIN', 'system', 'User login: ' . $username);

            // Redirect berdasarkan role
            if ($user->role === 'admin')
                return redirect()->to('/admin/dashboard');
            if ($user->role === 'pimpinan')
                return redirect()->to('/pimpinan/dashboard');
            if ($user->role === 'auditor')
                return redirect()->to('/dashboard');
            if ($user->role === 'auditee')
                return redirect()->to('/auditee/dashboard');

            return redirect()->to('/')->with('error', 'Role tidak dikenali.');
        } else {
            session()->setFlashdata('error', 'Username atau Password salah!');
            return redirect()->back();
        }
    }

    public function logout()
    {
        log_activity('LOGOUT', 'system', 'User logout: ' . session()->get('username'));

        session()->destroy();
        return redirect()->to('/');
    }
}
