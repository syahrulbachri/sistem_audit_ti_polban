<?php

namespace App\Controllers\Pimpinan;

use App\Controllers\BaseController;
use Config\Database;

class RtlController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }

        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak. Anda bukan pimpinan.');
        }

        $db = Database::connect();

        // Ambil parameter filter
        $search = (string) ($this->request->getGet('search') ?? '');
        $filterFramework = (string) ($this->request->getGet('framework') ?? '');
        $filterPeriode = (string) ($this->request->getGet('periode') ?? '');
        $filterRisiko = (string) ($this->request->getGet('risiko') ?? '');
        $filterStatus = (string) ($this->request->getGet('status') ?? '');
        $filterAuditee = (string) ($this->request->getGet('auditee') ?? '');

        $currentPage = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;
        if ($currentPage < 1) $currentPage = 1;

        // Ambil daftar periode untuk dropdown
        $periodes = $db->table('periodes')
            ->select('id, nama_periode')
            ->orderBy('nama_periode', 'DESC')
            ->get()
            ->getResult();

        // Ambil SEMUA framework yang ada di database (dinamis)
        $frameworks = $db->table('audits')
            ->distinct()
            ->select('framework')
            ->where('framework IS NOT NULL')
            ->where('framework !=', '')
            ->orderBy('framework', 'ASC')
            ->get()
            ->getResult();

        // BARU: Ambil daftar auditee untuk filter dropdown
        $auditees = $db->table('audits')
            ->distinct()
            ->select('users.id, users.fullname')
            ->join('users', 'users.id = audits.auditee_id', 'left')
            ->where('audits.status', 'selesai')
            ->orderBy('users.fullname', 'ASC')
            ->get()
            ->getResult();

        // ==========================================================
        // STATISTIK CARD
        // ==========================================================
        $totalTemuan = (int) $db->table('temuans')->countAllResults();
        $open = (int) $db->table('temuans')->where('status', 'Open')->countAllResults();
        $inProgress = (int) $db->table('temuans')->where('status', 'In_Progress')->countAllResults();
        $closed = (int) $db->table('temuans')->where('status', 'Closed')->countAllResults();

        // Hitung overdue (In_Progress tapi deadline sudah lewat)
        $overdue = (int) $db->table('temuans')
            ->where('status', 'In_Progress')
            ->where('rtl_deadline <', date('Y-m-d'))
            ->countAllResults();

        $overallPct = $totalTemuan > 0 ? round(($closed / $totalTemuan) * 100, 1) : 0;

        $summary = [
            'open'          => $open,
            'in_progress'   => $inProgress,
            'overdue'       => $overdue,
            'closed'        => $closed,
            'overall_pct'   => $overallPct,
            'total_temuan'  => $totalTemuan,
        ];

        // ==========================================================
        // BUILD QUERY DENGAN FILTER
        // ==========================================================
        $builder = $db->table('temuans')
            ->select('temuans.*, 
                      audits.title as audit_title,
                      audits.framework,
                      audits.periode_id,
                      periodes.nama_periode,
                      auditee.fullname as auditee_name,
                      audit_questions.clause_code')
            ->join('audits', 'audits.id = temuans.audit_id', 'left')
            ->join('periodes', 'periodes.id = audits.periode_id', 'left')
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->join('audit_questions', 'audit_questions.id = temuans.question_id', 'left');

        // Terapkan filter
        if (!empty($filterFramework)) {
            $builder->where('audits.framework', $filterFramework);
        }

        if (!empty($filterPeriode)) {
            $builder->where('audits.periode_id', $filterPeriode);
        }

        // BARU: Terapkan filter Auditee
        if (!empty($filterAuditee)) {
            $builder->where('audits.auditee_id', $filterAuditee);
        }

        if (!empty($filterRisiko)) {
            $builder->where('temuans.tingkat_risiko', $filterRisiko);
        }

        if (!empty($filterStatus)) {
            if ($filterStatus === 'overdue') {
                $builder->where('temuans.status', 'In_Progress');
                $builder->where('temuans.rtl_deadline <', date('Y-m-d'));
            } else {
                $builder->where('temuans.status', ucfirst(str_replace('_', ' ', $filterStatus)));
            }
        }

        // Hapus pagination di server - kirim SEMUA data ke client
        $rtlList = $builder
            ->orderBy('temuans.created_at', 'DESC')
            ->get()
            ->getResult();

        // Format data untuk view
        $formattedList = [];
        foreach ($rtlList as $r) {
            $deadlineNote = '';
            $isOverdue = false;
            if ($r->rtl_deadline) {
                $deadlineDate = strtotime($r->rtl_deadline);
                $today = strtotime(date('Y-m-d'));
                $diff = ceil(($deadlineDate - $today) / (60 * 60 * 24));

                if ($diff < 0 && $r->status !== 'Closed') {
                    $isOverdue = true;
                    $deadlineNote = '(Lewat ' . abs($diff) . ' Hari)';
                } elseif ($diff > 0 && $r->status === 'In_Progress') {
                    $deadlineNote = '(Sisa ' . $diff . ' Hari)';
                } elseif ($diff === 0 && $r->status === 'In_Progress') {
                    $deadlineNote = '(Hari Ini)';
                }
            }

            $progress = (int) ($r->progress ?? 0);
            if ($r->status === 'Closed') {
                $progress = 100;
            } elseif ($r->status === 'Open') {
                $progress = 0;
            }

            $formattedList[] = [
                'id'            => 'RTL-' . str_pad($r->id, 4, '0', STR_PAD_LEFT),
                'clause_code'   => (string) ($r->clause_code ?? '-'),
                'audit_title'   => (string) ($r->audit_title ?? '-'),
                'framework'     => (string) ($r->framework ?? '-'),
                'periode'       => (string) ($r->nama_periode ?? '-'),
                'risiko'        => strtolower((string) ($r->tingkat_risiko ?? 'sedang')),
                'risiko_label'  => ucfirst((string) ($r->tingkat_risiko ?? 'Sedang')),
                'status'        => $isOverdue ? 'overdue' : strtolower(str_replace('_', '', (string) ($r->status ?? 'open'))),
                'status_label'  => $isOverdue ? 'Overdue' : str_replace('_', ' ', ucfirst((string) ($r->status ?? 'Open'))),
                'auditee_name'  => (string) ($r->auditee_name ?? '-'),
                'deskripsi'     => (string) ($r->deskripsi_temuan ?? '-'),
                'rtl_deadline'  => $r->rtl_deadline ? date('d M Y', strtotime($r->rtl_deadline)) : '-',
                'deadline_note' => $deadlineNote,
                'is_overdue'    => $isOverdue,
                'progress'      => $progress,
                'progress_color' => $this->getProgressColor($r->status, $isOverdue),
                'temuan_id'     => (int) $r->id,
                'tanggal_temuan' => $r->created_at ? date('d M Y', strtotime($r->created_at)) : '-',
            ];
        }

        return view('pimpinan/monitoring_rtl', [
            'page_title'      => 'PELACAKAN TINDAK LANJUT / REMEDIASI TEMUAN (RTL TRACKER)',
            'page_subtitle'   => 'Sistem Monitoring Audit TI - Politeknik Negeri Bandung | Role: Pimpinan / SPI',
            'rtlList'         => $formattedList,
            'summary'         => $summary,
            'periodes'        => $periodes,
            'frameworks'      => $frameworks,
            'auditees'        => $auditees,
            'search'          => $search,
            'filterFramework' => $filterFramework,
            'filterPeriode'   => $filterPeriode,
            'filterRisiko'    => $filterRisiko,
            'filterStatus'    => $filterStatus,
            'filterAuditee'   => $filterAuditee,
            'totalData'       => count($formattedList),
        ]);
    }

    private function getProgressColor(string $status, bool $isOverdue): string
    {
        if ($status === 'Closed') return 'green';
        if ($isOverdue) return 'red';
        if ($status === 'In_Progress') return 'orange';
        return 'empty';
    }

    public function detail(string $id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }
        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $numericId = (int) preg_replace('/[^0-9]/', '', $id);
        $db = Database::connect();

        $rtl = $db->table('temuans')
            ->select('temuans.*,
                audits.title as audit_title,
                audits.framework,
                audits.periode_id,
                periodes.nama_periode,
                auditee.fullname as auditee_name,
                auditor.fullname as auditor_name,
                audit_questions.clause_code,
                audit_questions.question_text')
            ->join('audits', 'audits.id = temuans.audit_id', 'left')
            ->join('periodes', 'periodes.id = audits.periode_id', 'left')
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->join('users as auditor', 'auditor.id = audits.created_by_auditor', 'left')
            ->join('audit_questions', 'audit_questions.id = temuans.question_id', 'left')
            ->where('temuans.id', $numericId)
            ->get()
            ->getRowArray();

        if (!$rtl) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // ==========================================================
        // PATH BUKTI PERBAIKAN (LANGSUNG dari public/uploads/bukti_perbaikan/)
        // ==========================================================
        // PERBAIKAN: jangan link langsung ke file statis, karena
        //  (1) nama file bisa mengandung karakter yang rusak di URL (mis. "+headphones.jpg"),
        //  (2) bukti dari alur revisi tidak punya file fisik (tersimpan sebagai BLOB).
        // Jadi semua akses bukti dilewatkan controller viewBukti() / downloadBukti().
        $buktiFilename    = $rtl['bukti_perbaikan'] ?? '';
        $buktiUrl         = !empty($buktiFilename) ? site_url('pimpinan/rtl/view-bukti/' . $rtl['id']) : null;
        $buktiDownloadUrl = !empty($buktiFilename) ? site_url('pimpinan/rtl/download-bukti/' . $rtl['id']) : null;

        // Hitung status deadline
        $deadlineStatus = '';
        $deadlineClass = '';
        $sisaHari = null;
        if (!empty($rtl['rtl_deadline'])) {
            $deadline = strtotime($rtl['rtl_deadline']);
            $today = strtotime(date('Y-m-d'));
            $diff = (int) (($deadline - $today) / (60 * 60 * 24));
            $sisaHari = $diff;
            if ($diff < 0 && $rtl['status'] !== 'Closed') {
                $deadlineStatus = 'Melebihi target (' . abs($diff) . ' hari)';
                $deadlineClass = 'text-danger';
            } elseif ($diff === 0 && $rtl['status'] !== 'Closed') {
                $deadlineStatus = 'Hari ini!';
                $deadlineClass = 'text-warning';
            } elseif ($diff < 30 && $rtl['status'] !== 'Closed') {
                $deadlineStatus = 'Segera (' . $diff . ' hari lagi)';
                $deadlineClass = 'text-warning';
            } else {
                $deadlineStatus = 'Masih dalam batas waktu';
                $deadlineClass = 'text-success';
            }
        }

        // Map status untuk tampilan
        $statusVerifMap = [
            'Open'        => 'Menunggu Tindak Lanjut Auditee',
            'In_Progress' => 'Sedang dalam Perbaikan oleh Auditee',
            'Closed'      => 'Terverifikasi & Ditutup oleh Auditor',
        ];

        // ==========================================================
        // BUILD ARRAY $rtlData
        // ==========================================================
        $rtlData = [
            'id'                    => 'RTL-' . str_pad($rtl['id'], 4, '0', STR_PAD_LEFT),
            'audit_id'              => (int) ($rtl['audit_id'] ?? 0),
            'risiko'                => strtolower($rtl['tingkat_risiko'] ?? 'sedang'),
            'risiko_label'          => ucfirst($rtl['tingkat_risiko'] ?? 'Sedang'),
            'status'                => strtolower(str_replace('_', '', $rtl['status'] ?? 'open')),
            'status_icon'           => $rtl['status'] === 'Closed' ? '✅' : ($rtl['status'] === 'In_Progress' ? '🔄' : ''),
            'status_label'          => str_replace('_', ' ', ucfirst($rtl['status'] ?? 'Open')),
            'progress'              => $rtl['status'] === 'Closed' ? 100 : (int) ($rtl['progress'] ?? 0),
            'judul'                 => (string) ($rtl['deskripsi_temuan'] ?? '-'),
            'lha_kode'              => 'LHA-' . date('Y', strtotime($rtl['created_at'] ?? 'now')) . '-' . str_pad($rtl['audit_id'] ?? 0, 3, '0', STR_PAD_LEFT),
            'lha_nama'              => (string) ($rtl['audit_title'] ?? '-'),
            'unit'                  => (string) ($rtl['auditee_name'] ?? '-'),
            'sistem'                => (string) ($rtl['framework'] ?? '-'),
            'deskripsi'             => (string) ($rtl['deskripsi_temuan'] ?? '-'),
            'rekomendasi'           => (string) ($rtl['rekomendasi'] ?? '-'),
            'rtl_description'       => (string) ($rtl['rtl_description'] ?? ''),
            'rtl_anggaran'          => (float) ($rtl['rtl_anggaran'] ?? 0),
            'rtl_deadline'          => $rtl['rtl_deadline'] ? date('d M Y', strtotime($rtl['rtl_deadline'])) : '-',
            'rtl_deadline_raw'      => $rtl['rtl_deadline'] ?? '',
            'rtl_deadline_status'   => $deadlineStatus,
            'rtl_deadline_class'    => $deadlineClass,
            'sisa_hari'             => $sisaHari,
            'bukti_perbaikan'       => $buktiFilename,            // <-- Sekarang terdefinisi
            'bukti_url'             => $buktiUrl,                 // <-- Sekarang terdefinisi
            'bukti_download_url'    => $buktiDownloadUrl,         // <-- Sekarang terdefinisi
            'catatan_auditor'       => (string) ($rtl['auditor_note'] ?? ''),
            'clause_code'           => (string) ($rtl['clause_code'] ?? '-'),
            'question_text'         => (string) ($rtl['question_text'] ?? '-'),
            'periode'               => (string) ($rtl['nama_periode'] ?? '-'),
            'auditor_name'          => (string) ($rtl['auditor_name'] ?? '-'),
            'created_at'            => $rtl['created_at'] ? date('d M Y H:i', strtotime($rtl['created_at'])) : '-',
            'closed_at'             => !empty($rtl['closed_at']) ? date('d M Y H:i', strtotime($rtl['closed_at'])) : null,
            'status_verifikasi'     => $statusVerifMap[$rtl['status']] ?? 'Belum diketahui',
            'status_verifikasi_note' => $this->getStatusNote($rtl['status']),
            'riwayat'               => $this->buildRiwayat($rtl),
            'berkas'                => [],
        ];

        return view('pimpinan/monitoring_rtl/rtl_detail', [
            'page_title'    => 'DETAIL RENCANA TINDAK LANJUT (RTL)',
            'page_subtitle' => $rtlData['id'] . ' — ' . $rtlData['judul'],
            'rtl'           => $rtlData,
        ]);
    }

    // ==========================================================
    // PREVIEW BUKTI PERBAIKAN (dibuka inline di tab baru)
    // ==========================================================
    public function viewBukti(int $temuanId)
    {
        return $this->serveBukti($temuanId, 'inline');
    }

    // ==========================================================
    // DOWNLOAD BUKTI PERBAIKAN
    // ==========================================================
    public function downloadBukti(int $temuanId)
    {
        return $this->serveBukti($temuanId, 'attachment');
    }

    /**
     * Mencari & mengirim file bukti perbaikan.
     * Urutan pencarian:
     *   1. public/uploads/bukti_perbaikan/   (hasil TindakLanjutController::saveBukti)
     *   2. writable/uploads/bukti_perbaikan/ (kalau file lama tersimpan di sini)
     *   3. BLOB audit_question_assignments.evidence_file (hasil alur revisi auditee)
     */
    private function serveBukti(int $temuanId, string $disposition)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/')->with('error', 'Silakan login terlebih dahulu.');
        }
        if (session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = Database::connect();

        $temuan = $db->table('temuans')
            ->select('id, audit_id, question_id, bukti_perbaikan')
            ->where('id', $temuanId)
            ->get()
            ->getRowArray();

        if (!$temuan || empty($temuan['bukti_perbaikan'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Bukti perbaikan untuk temuan ini tidak tersedia.'
            );
        }

        // basename() mencegah path traversal (mis. "../../.env")
        $filename = basename(trim($temuan['bukti_perbaikan']));

        // ---------- 1 & 2: cari file fisik ----------
        $candidates = [
            FCPATH . 'uploads/bukti_perbaikan/' . $filename,
            WRITEPATH . 'uploads/bukti_perbaikan/' . $filename,
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                if ($disposition === 'attachment') {
                    return $this->response->download($path, null)->setFileName($filename);
                }

                return $this->response
                    ->setHeader('Content-Type', $this->guessMime($filename, (string) file_get_contents($path)))
                    ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                    ->setBody((string) file_get_contents($path));
            }
        }

        // ---------- 3: fallback ke BLOB bukti dari jawaban auditee ----------
        $blobRow = $db->table('audit_question_assignments')
            ->select('evidence_file, evidence_filename')
            ->where('audit_id', $temuan['audit_id'])
            ->where('question_id', $temuan['question_id'])
            ->where('evidence_file IS NOT NULL')
            ->orderBy('evidence_uploaded_at', 'DESC')
            ->get()
            ->getRowArray();

        if ($blobRow && !empty($blobRow['evidence_file'])) {
            $content  = (string) $blobRow['evidence_file'];
            $realName = !empty($blobRow['evidence_filename']) ? basename($blobRow['evidence_filename']) : $filename;

            if ($disposition === 'attachment') {
                return $this->response->download($realName, $content, true);
            }

            return $this->response
                ->setHeader('Content-Type', $this->guessMime($realName, $content))
                ->setHeader('Content-Disposition', 'inline; filename="' . $realName . '"')
                ->setBody($content);
        }

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
            'File bukti "' . $filename . '" tercatat di database, tetapi berkasnya tidak ditemukan di server.'
        );
    }

    private function guessMime(string $filename, string $content = ''): string
    {
        if ($content !== '' && function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_buffer($finfo, $content);
                finfo_close($finfo);
                if (!empty($mime) && $mime !== 'application/x-empty') {
                    return $mime;
                }
            }
        }

        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'pdf'         => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png'         => 'image/png',
            'gif'         => 'image/gif',
            'doc'         => 'application/msword',
            'docx'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'         => 'application/vnd.ms-excel',
            'xlsx'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip'         => 'application/zip',
            default       => 'application/octet-stream',
        };
    }

    private function getStatusNote(string $status): string
    {
        return match ($status) {
            'Open' => 'Temuan ini <b>menunggu Auditee</b> untuk membuat Rencana Tindak Lanjut (RTL). Auditor akan me-review RTL yang diajukan.',
            'In_Progress' => 'RTL telah <b>disetujui Auditor</b>. Auditee sedang mengerjakan perbaikan sesuai rencana yang telah disepakati.',
            'Closed' => 'RTL telah dinyatakan <b>Closed (Selesai)</b> setelah bukti perbaikan diverifikasi oleh Auditor.',
            default => 'Status tidak diketahui.',
        };
    }

    private function buildRiwayat(array $rtl): array
    {
        $riwayat = [];

        // Tahap 1: Audit selesai
        $riwayat[] = [
            'tanggal'   => $rtl['created_at'] ? date('d M Y', strtotime($rtl['created_at'])) : '-',
            'oleh'      => (string) ($rtl['auditor_name'] ?? 'Auditor'),
            'judul'     => 'Temuan Dibuat',
            'isi'       => 'Temuan dicatat saat proses audit selesai.',
            'highlight' => false,
        ];

        // Tahap 2: RTL disetujui (jika In_Progress atau Closed)
        if (in_array($rtl['status'], ['In_Progress', 'Closed'])) {
            $riwayat[] = [
                'tanggal'   => $rtl['updated_at'] ? date('d M Y', strtotime($rtl['updated_at'])) : '-',
                'oleh'      => (string) ($rtl['auditor_name'] ?? 'Auditor'),
                'judul'     => 'RTL Disetujui',
                'isi'       => 'Auditor menyetujui RTL yang diajukan Auditee. Status berubah menjadi In_Progress.',
                'highlight' => true,
            ];
        }

        // Tahap 3: Closed
        if ($rtl['status'] === 'Closed') {
            $riwayat[] = [
                'tanggal'   => $rtl['updated_at'] ? date('d M Y', strtotime($rtl['updated_at'])) : '-',
                'oleh'      => (string) ($rtl['auditor_name'] ?? 'Auditor'),
                'judul'     => 'Bukti Perbaikan Diverifikasi',
                'isi'       => 'Bukti perbaikan telah diverifikasi dan temuan dinyatakan Closed.',
                'highlight' => true,
            ];
        }

        return $riwayat;
    }
}
