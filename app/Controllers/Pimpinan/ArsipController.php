<?php

namespace App\Controllers\Pimpinan;

use App\Controllers\BaseController;

class ArsipController extends BaseController
{
    /**
     * GET /pimpinan/arsip
     *
     * STATUS PENGEMBANGAN: data di bawah masih DUMMY (hardcode),
     * belum ambil dari tabel `audits`/`temuans` beneran. Nanti begitu
     * struktur DB final, tinggal ganti $arsipList dengan query asli.
     */
    public function index()
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda bukan pimpinan.');
        }

        $arsipList = [
            [
                'kode'       => 'LHA-POLBAN-2026-01',
                'periode'    => 'Semester Genap 2025/2026',
                'standar'    => 'COBIT 2019 & ISO 27001',
                'tanggal'    => '05 Agustus 2026',
                'skor'       => '3.4 / 5.0',
                'skor_label' => 'Established',
                'skor_class' => 'established',
            ],
            [
                'kode'       => 'LHA-POLBAN-2025-02',
                'periode'    => 'Semester Ganjil 2025/2026',
                'standar'    => 'ISO/IEC 27001:2013',
                'tanggal'    => '12 Januari 2026',
                'skor'       => '3.2 / 5.0',
                'skor_label' => 'Defined',
                'skor_class' => 'defined',
            ],
            [
                'kode'       => 'LHA-POLBAN-2025-01',
                'periode'    => 'Semester Genap 2024/2025',
                'standar'    => 'COBIT 2019 (Focus Group)',
                'tanggal'    => '10 Juli 2025',
                'skor'       => '3.0 / 5.0',
                'skor_label' => 'Defined',
                'skor_class' => 'defined',
            ],
        ];

        return view('pimpinan/arsip', [
            'page_title'    => 'RIWAYAT & ARSIP LAPORAN AUDIT TI (OFFICIAL ARCHIVE)',
            'page_subtitle' => 'Satuan Pengawasan Intern (SPI) & Pimpinan Politeknik Negeri Bandung',
            'arsipList'     => $arsipList,
        ]);
    }
}