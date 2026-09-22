<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class PeriodeController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Pagination manual
        $perPage = 10;
        $currentPage = (int) ($this->request->getGet('page') ?? 1);

        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $offset = ($currentPage - 1) * $perPage;

        // Total records
        $total = $db->table('periodes')->countAllResults();

        $totalPages = (int) ceil($total / $perPage);

        if ($currentPage > $totalPages && $totalPages > 0) {
            $currentPage = $totalPages;
        }

        // Ambil data dengan pagination
        $periodes = $db->table('periodes')
            ->orderBy('created_at', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResult();

        return view('admin/periodes/index', [
            'title'       => 'Manajemen Periode - Sistem Audit IT POLBAN',
            'page_title'  => 'Manajemen Periode Audit',
            'periodes'    => $periodes,
            'currentPage' => $currentPage,
            'totalPages'  => $totalPages,
            'total'       => $total,
            'perPage'     => $perPage
        ]);
    }

    public function create()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        return view('admin/periodes/create', [
            'title'      => 'Tambah Periode - Sistem Audit IT POLBAN',
            'page_title' => 'Tambah Periode Audit Baru'
        ]);
    }

    public function store()
    {
        $rules = [
            'nama_periode'    => 'required|min_length[3]|max_length[100]',
            'tahun'           => 'required|numeric|exact_length[4]',
            'tanggal_mulai'   => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $namaPeriode = $this->request->getPost('nama_periode');
        $tahun = $this->request->getPost('tahun');
        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');

        // Validasi tanggal
        if (strtotime($tanggalMulai) >= strtotime($tanggalSelesai)) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Tanggal Mulai harus lebih kecil dari Tanggal Selesai.'
                );
        }

        $db = \Config\Database::connect();

        // Cek overlap
        $overlap = $db->table('periodes')
            ->where('tanggal_mulai <=', $tanggalSelesai)
            ->where('tanggal_selesai >=', $tanggalMulai)
            ->countAllResults();

        if ($overlap > 0) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Periode ini bertabrakan (overlap) dengan periode yang sudah ada. Silakan pilih rentang tanggal yang berbeda.'
                );
        }

        $db->table('periodes')->insert([
            'nama_periode'    => $namaPeriode,
            'tahun'           => $tahun,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'status'          => 'closed',
            'created_at'      => date('Y-m-d H:i:s')
        ]);

        log_activity('CREATE', 'periodes', 'Menambahkan periode baru: ' . $namaPeriode);

        return redirect()->to('/admin/periodes')
            ->with('success', 'Periode berhasil ditambahkan!');
    }

    public function edit($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        $periode = $db->table('periodes')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$periode) {
            return redirect()->to('/admin/periodes')
                ->with('error', 'Periode tidak ditemukan.');
        }

        return view('admin/periodes/edit', [
            'title'      => 'Edit Periode - Sistem Audit IT POLBAN',
            'page_title' => 'Edit Periode Audit',
            'periode'    => $periode
        ]);
    }

    public function update($id)
    {
        $rules = [
            'nama_periode'    => 'required|min_length[3]|max_length[100]',
            'tahun'           => 'required|numeric|exact_length[4]',
            'tanggal_mulai'   => 'required|valid_date',
            'tanggal_selesai' => 'required|valid_date'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $namaPeriode = $this->request->getPost('nama_periode');
        $tahun = $this->request->getPost('tahun');
        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');

        // Validasi tanggal
        if (strtotime($tanggalMulai) >= strtotime($tanggalSelesai)) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Tanggal Mulai harus lebih kecil dari Tanggal Selesai.'
                );
        }

        $db = \Config\Database::connect();

        // Cek overlap kecuali periode yang sedang diedit
        $overlap = $db->table('periodes')
            ->where('id !=', $id)
            ->where('tanggal_mulai <=', $tanggalSelesai)
            ->where('tanggal_selesai >=', $tanggalMulai)
            ->countAllResults();

        if ($overlap > 0) {
            return redirect()->back()
                ->withInput()
                ->with(
                    'error',
                    'Periode ini bertabrakan (overlap) dengan periode yang sudah ada.'
                );
        }

        $db->table('periodes')
            ->where('id', $id)
            ->update([
                'nama_periode'    => $namaPeriode,
                'tahun'           => $tahun,
                'tanggal_mulai'   => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
            ]);

        // FIX: gunakan $namaPeriode
        log_activity('UPDATE', 'periodes', 'Mengubah data periode: ' . $namaPeriode);

        return redirect()->to('/admin/periodes')
            ->with('success', 'Periode berhasil diperbarui!');
    }

    public function toggle($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        $periode = $db->table('periodes')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$periode) {
            return redirect()->to('/admin/periodes')
                ->with('error', 'Periode tidak ditemukan.');
        }

        $newStatus = ($periode->status === 'open') ? 'closed' : 'open';

        // Jika ingin menutup periode, cek audit yang masih berjalan
        if ($newStatus === 'closed') {
            $auditBerjalan = $db->table('audits')
                ->where('periode_id', $id)
                ->whereIn('status', ['aktif', 'menunggu_penilaian'])
                ->countAllResults();

            if ($auditBerjalan > 0) {
                return redirect()->to('/admin/periodes')
                    ->with('error', "Tidak dapat menutup periode ini. Masih ada <strong>{$auditBerjalan}</strong> audit yang berstatus 'Aktif' atau 'Menunggu Penilaian'. Selesaikan atau hapus audit tersebut terlebih dahulu.");
            }
        }

        // 1. Update status di database
        $db->table('periodes')
            ->where('id', $id)
            ->update([
                'status' => $newStatus
            ]);

        // 2. ✅ PERBAIKAN: Definisikan variabel teks status secara eksplisit di sini
        $statusText = ($newStatus === 'open') ? 'Aktif (Open)' : 'Nonaktif (Closed)';

        // 3. Catat ke log aktivitas
        log_activity('UPDATE', 'periodes', 'Mengubah status periode "' . $periode->nama_periode . '" menjadi ' . $statusText);

        return redirect()->to('/admin/periodes')
            ->with('success', 'Status periode berhasil diubah menjadi ' . ucfirst($newStatus) . '!');
    }

    public function delete($id)
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'admin') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = \Config\Database::connect();

        // Ambil periode sebelum dihapus
        $periode = $db->table('periodes')
            ->where('id', $id)
            ->get()
            ->getRow();

        if (!$periode) {
            return redirect()->to('/admin/periodes')
                ->with('error', 'Periode tidak ditemukan.');
        }

        // Cek apakah ada audit yang menggunakan periode ini
        $auditCount = $db->table('audits')
            ->where('periode_id', $id)
            ->countAllResults();

        if ($auditCount > 0) {
            return redirect()->to('/admin/periodes')
                ->with(
                    'error',
                    "Tidak dapat menghapus periode ini karena masih memiliki {$auditCount} audit yang terkait. Hapus atau pindahkan audit terlebih dahulu."
                );
        }

        // Hapus periode
        $db->table('periodes')
            ->where('id', $id)
            ->delete();

        // Catat aktivitas sebelum redirect
        log_activity('DELETE', 'periodes', 'Menghapus periode: ' . $periode->nama_periode);

        return redirect()->to('/admin/periodes')
            ->with('success', 'Periode berhasil dihapus!');
    }
}
