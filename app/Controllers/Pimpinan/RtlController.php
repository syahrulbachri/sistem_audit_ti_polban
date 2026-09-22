<?php

namespace App\Controllers\Pimpinan;

use App\Controllers\BaseController;

class RtlController extends BaseController
{
    /**
     * Data ringkas untuk halaman daftar (/pimpinan/rtl).
     * STATUS PENGEMBANGAN: masih DUMMY, belum ambil dari tabel `temuans`.
     */
    private function getRtlList(): array
    {
        return [
            [
                'id'             => 'RTL-2026-001',
                'unit'           => 'UPA TIK',
                'sistem'         => 'SIAKAD',
                'deskripsi'      => 'Belum menerapkan Multi-Factor Authentication (MFA) pada login Admin SIAKAD',
                'risiko'         => 'kritis',
                'risiko_label'   => 'Kritis',
                'deadline'       => '15 Agu 2026',
                'deadline_note'  => '(Sisa 9 Hari)',
                'progress'       => 80,
                'progress_color' => 'orange',
                'status'         => 'inprogress',
                'status_icon'    => '⏳',
                'status_label'   => 'In-Progress',
            ],
            [
                'id'             => 'RTL-2026-002',
                'unit'           => 'UPA TIK',
                'sistem'         => 'Server Pusat',
                'deskripsi'      => 'Prosedur Disaster Recovery Backup belum pernah diuji coba secara berkala',
                'risiko'         => 'tinggi',
                'risiko_label'   => 'Tinggi',
                'deadline'       => '01 Jul 2026',
                'deadline_note'  => '(Lewat 36 Hari)',
                'progress'       => 60,
                'progress_color' => 'red',
                'status'         => 'overdue',
                'status_icon'    => '⚠️',
                'status_label'   => 'OVERDUE',
            ],
            [
                'id'             => 'RTL-2026-003',
                'unit'           => 'Bagian Umum',
                'sistem'         => 'Gedung TIK',
                'deskripsi'      => 'Log akses masuk fisik ruang server masih manual menggunakan buku tamu',
                'risiko'         => 'tinggi',
                'risiko_label'   => 'Tinggi',
                'deadline'       => '30 Agu 2026',
                'deadline_note'  => '(Sisa 24 Hari)',
                'progress'       => 20,
                'progress_color' => 'light',
                'status'         => 'inprogress',
                'status_icon'    => '⏳',
                'status_label'   => 'In-Progress',
            ],
            [
                'id'             => 'RTL-2026-004',
                'unit'           => 'Keuangan',
                'sistem'         => 'SI-Keuangan',
                'deskripsi'      => 'Server SPP belum terintegrasi otomatis dengan Sistem Keuangan Pusat Polban',
                'risiko'         => 'sedang',
                'risiko_label'   => 'Sedang',
                'deadline'       => '10 Agu 2026',
                'deadline_note'  => '(Sisa 4 Hari)',
                'progress'       => 0,
                'progress_color' => 'empty',
                'status'         => 'open',
                'status_icon'    => '🔴',
                'status_label'   => 'OPEN (Belum RTL)',
            ],
            [
                'id'             => 'RTL-2026-005',
                'unit'           => 'Perpustakaan',
                'sistem'         => 'OPAC Library',
                'deskripsi'      => 'Kategori hak akses pustakawan terlalu luas (potensi over-privilege)',
                'risiko'         => 'rendah',
                'risiko_label'   => 'Rendah',
                'deadline'       => '01 Agu 2026',
                'deadline_note'  => '(Tepat Waktu)',
                'progress'       => 100,
                'progress_color' => 'green',
                'status'         => 'closed',
                'status_icon'    => '✅',
                'status_label'   => 'CLOSED (Verified)',
            ],
        ];
    }

    /**
     * GET /pimpinan/rtl
     */
    public function index()
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }
        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda bukan pimpinan.');
        }

        $summary = [
            'open'         => 2,
            'in_progress'  => 4,
            'overdue'      => 1,
            'closed'       => 10,
            'overall_pct'  => 70.5,
            'total_temuan' => 17,
        ];

        $pagination = [
            'current' => 1,
            'total'   => 4,
        ];

        return view('pimpinan/monitoring_rtl', [
            'page_title'    => 'PELACAKAN TINDAK LANJUT / REMEDIASI TEMUAN (RTL TRACKER)',
            'page_subtitle' => 'Sistem Monitoring Audit TI - Politeknik Negeri Bandung | Role: Pimpinan / SPI',
            'rtlList'       => $this->getRtlList(),
            'summary'       => $summary,
            'pagination'    => $pagination,
        ]);
    }

    /**
     * GET /pimpinan/rtl/detail/{id}
     *
     * STATUS PENGEMBANGAN: detail lengkap (ringkasan, komitmen, riwayat,
     * berkas) baru tersedia dummy untuk RTL-2026-001 (persis isi Figma).
     * ID lain pakai data ringkas dari daftar + riwayat placeholder,
     * biar halaman tetap kebuka tanpa error.
     */
    public function detail(string $id)
    {
        if (! session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }
        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda bukan pimpinan.');
        }

        $list = $this->getRtlList();
        $row  = null;
        foreach ($list as $r) {
            if ($r['id'] === $id) {
                $row = $r;
                break;
            }
        }

        if (! $row) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $statusVerifMap = [
            'inprogress' => 'Menunggu Verifikasi Dokumen Akhir',
            'overdue'    => 'Menunggu Tindak Lanjut Auditee (Terlambat)',
            'open'       => 'Belum Ada Aksi dari Auditee',
            'closed'     => 'Terverifikasi & Ditutup oleh SPI',
        ];

        if ($id === 'RTL-2026-001') {
            // Data lengkap persis sesuai Figma
            $rtl = [
                'id'                     => 'RTL-2026-001',
                'risiko'                 => 'kritis',
                'risiko_label'           => 'Kritis',
                'status'                 => 'inprogress',
                'status_icon'            => '⏳',
                'status_label'           => 'In Progress',
                'progress'               => 75,
                'judul'                  => 'Penerapan Multi-Factor Authentication (MFA) pada Akun Admin SIAKAD',
                'lha_kode'               => 'LHA/TI/2026/004',
                'lha_nama'               => 'SIAKAD Polban',
                'deskripsi_masalah'      => 'Akun superadmin SIAKAD saat ini hanya dilindungi oleh password statis tanpa otentikasi ganda, berpotensi memicu akses tidak sah (unauthorized access) yang mengancam integritas data nilai mahasiswa.',
                'standar_acuan'          => 'ISO 27001 Klausul A.9.4.2 & COBIT 2019 DSS05.04',
                'pic_unit'               => 'UPA TIK Polban',
                'pic_lapangan'           => 'Budi Santoso, S.ST. (Aplikasi & Data)',
                'target_deadline'        => '28 Februari 2026',
                'target_evidence'        => 'SOP Otentikasi MFA & Screenshot Sistem Active',
                'rencana_aksi'           => [
                    'Mengintegrasikan library TOTP Google Authenticator ke portal Login Admin.',
                    'Menerbitkan SOP wajib MFA untuk seluruh Staf Admin SIAKAD.',
                    'Melakukan pengujian terbatas (UAT) dan sosialisasi penggunaan.',
                ],
                'riwayat'                => [
                    [
                        'tanggal'   => '10 Februari 2026',
                        'oleh'      => 'UPA TIK (Auditee)',
                        'judul'     => 'Update Progres 75%',
                        'isi'       => 'Modul MFA telah selesai dikembangkan di environment Staging dan lolos pengujian UAT internal. Saat ini sedang dalam tahap sosialisasi ke staf akademik sebelum di-deploy ke Server Production.',
                        'highlight' => false,
                    ],
                    [
                        'tanggal'   => '02 Februari 2026',
                        'oleh'      => 'Tim Auditor SPI',
                        'judul'     => 'Catatan Verifikasi Auditor',
                        'isi'       => 'Bukti skrip integrasi dan screenshot login staging telah diperiksa. Mohon lampirkan SOP Resmi yang telah ditandatangani Kepala UPA TIK untuk penutupan status Closed.',
                        'highlight' => true,
                    ],
                    [
                        'tanggal'   => '25 Januari 2026',
                        'oleh'      => 'UPA TIK (Auditee)',
                        'judul'     => 'Inisiasi RTL (25%)',
                        'isi'       => 'Penyiapan environment repositori dan pemilihan skema OTP (Google Authenticator).',
                        'highlight' => false,
                    ],
                ],
                'berkas'                 => [
                    [
                        'icon'    => '📄',
                        'nama'    => 'Laporan_UAT_MFA_v1.pdf',
                        'ukuran'  => '2.4 MB',
                        'tanggal' => '10 Feb 2026',
                    ],
                    [
                        'icon'    => '🖼️',
                        'nama'    => 'Screenshot_Login_MFA.png',
                        'ukuran'  => '1.1 MB',
                        'tanggal' => '10 Feb 2026',
                    ],
                ],
                'status_verifikasi'      => $statusVerifMap['inprogress'],
                'status_verifikasi_note' => 'RTL akan dinyatakan <b>Closed (Selesai)</b> setelah seluruh bukti fisik diverifikasi oleh Tim Auditor Satuan Pengawas Internal (SPI).',
            ];
        } else {
            // Fallback ringkas untuk ID RTL lain
            $rtl = [
                'id'                     => $row['id'],
                'risiko'                 => $row['risiko'],
                'risiko_label'           => $row['risiko_label'],
                'status'                 => $row['status'],
                'status_icon'            => $row['status_icon'],
                'status_label'           => $row['status_label'],
                'progress'               => $row['progress'],
                'judul'                  => $row['deskripsi'],
                'lha_kode'               => '-',
                'lha_nama'               => $row['unit'] . ' - ' . $row['sistem'],
                'deskripsi_masalah'      => $row['deskripsi'],
                'standar_acuan'          => 'Data rinci belum tersedia (mode pengembangan).',
                'pic_unit'               => $row['unit'],
                'pic_lapangan'           => 'Data belum tersedia',
                'target_deadline'        => $row['deadline'] . ' ' . $row['deadline_note'],
                'target_evidence'        => 'Data rinci belum tersedia (mode pengembangan).',
                'rencana_aksi'           => [
                    'Rincian rencana aksi belum tersedia (mode pengembangan).',
                ],
                'riwayat'                => [
                    [
                        'tanggal'   => $row['deadline'],
                        'oleh'      => $row['unit'] . ' (Auditee)',
                        'judul'     => 'Belum ada riwayat',
                        'isi'       => 'Riwayat perkembangan untuk RTL ini belum tersedia (mode pengembangan).',
                        'highlight' => false,
                    ],
                ],
                'berkas'                 => [],
                'status_verifikasi'      => $statusVerifMap[$row['status']] ?? 'Belum diketahui',
                'status_verifikasi_note' => 'RTL akan dinyatakan <b>Closed (Selesai)</b> setelah seluruh bukti fisik diverifikasi oleh Tim Auditor Satuan Pengawas Internal (SPI).',
            ];
        }

        return view('pimpinan/monitoring_rtl/rtl_detail', [
            'page_title'    => 'DETAIL RENCANA TINDAK LANJUT (RTL)',
            'page_subtitle' => $rtl['id'] . ' — ' . $rtl['judul'],
            'rtl'           => $rtl,
        ]);
    }
}