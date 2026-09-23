<?php

namespace App\Controllers\Pimpinan;

use App\Controllers\BaseController;
use Config\Database;

class ArsipController extends BaseController
{
    public function index()
    {
        if (!session()->get('logged_in') || session()->get('role') !== 'pimpinan') {
            return redirect()->to('/')->with('error', 'Akses ditolak.');
        }

        $db = Database::connect();

        $filterPeriode = (string) ($this->request->getGet('periode') ?? '');
        $filterFramework = (string) ($this->request->getGet('framework') ?? '');
        $filterAuditee = (string) ($this->request->getGet('auditee') ?? '');

        // ==========================================================
        // PERIODE AKTIF
        // ==========================================================
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        $currentPeriodeLabel = ($currentMonth >= 8)
            ? 'Semester Ganjil ' . $currentYear . '/' . ($currentYear + 1)
            : 'Semester Genap ' . ($currentYear - 1) . '/' . $currentYear;

        $periodeAktifRow = $db->table('periodes')
            ->select('id, nama_periode')
            ->like('nama_periode', $currentPeriodeLabel)
            ->get()->getRow();

        $periodeAktifId = $periodeAktifRow ? (int) $periodeAktifRow->id : null;
        $periodeAktifNama = $periodeAktifRow ? (string) $periodeAktifRow->nama_periode : $currentPeriodeLabel;

        // ==========================================================
        // DROPDOWN FILTER
        // ==========================================================
        $periodes = $db->table('periodes')
            ->select('id, nama_periode')
            ->orderBy('nama_periode', 'DESC')
            ->get()->getResult();

        $frameworks = $db->table('audits')
            ->distinct()
            ->select('framework')
            ->where('status', 'selesai')
            ->where('framework IS NOT NULL')
            ->where('framework !=', '')
            ->orderBy('framework', 'ASC')
            ->get()->getResult();

        $auditees = $db->table('audits')
            ->distinct()
            ->select('auditee.id, auditee.fullname')
            ->join('users as auditee', 'auditee.id = audits.auditee_id', 'left')
            ->where('audits.status', 'selesai')
            ->orderBy('auditee.fullname', 'ASC')
            ->get()->getResult();

        // ==========================================================
        // RAW QUERY: Ambil audit yang memenuhi kriteria arsip
        // ==========================================================
        $sql = "
            SELECT a.*, 
                   p.nama_periode,
                   aud.fullname as auditee_name,
                   aud2.fullname as auditor_name,
                   COALESCE(ts.total_temuan, 0) as total_temuan,
                   COALESCE(ts.closed_temuan, 0) as closed_temuan
            FROM audits a
            LEFT JOIN periodes p ON p.id = a.periode_id
            LEFT JOIN users aud ON aud.id = a.auditee_id
            LEFT JOIN users aud2 ON aud2.id = a.created_by_auditor
            LEFT JOIN (
                SELECT audit_id,
                       COUNT(*) as total_temuan,
                       SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed_temuan
                FROM temuans
                GROUP BY audit_id
            ) ts ON ts.audit_id = a.id
            WHERE a.status = 'selesai'
              AND (ts.total_temuan IS NULL OR ts.total_temuan = ts.closed_temuan)
        ";

        $params = [];

        if (!empty($filterPeriode)) {
            $sql .= " AND a.periode_id = ?";
            $params[] = $filterPeriode;
        }
        if (!empty($filterFramework)) {
            $sql .= " AND a.framework = ?";
            $params[] = $filterFramework;
        }
        if (!empty($filterAuditee)) {
            $sql .= " AND a.auditee_id = ?";
            $params[] = $filterAuditee;
        }

        $sql .= " ORDER BY a.updated_at DESC";

        $arsipList = $db->query($sql, $params)->getResult();

        // ==========================================================
        // STATISTIK CARD
        // ==========================================================
        $sqlCount = "
            SELECT COUNT(*) as total
            FROM audits a
            LEFT JOIN (
                SELECT audit_id,
                       COUNT(*) as total_temuan,
                       SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) as closed_temuan
                FROM temuans
                GROUP BY audit_id
            ) ts ON ts.audit_id = a.id
            WHERE a.status = 'selesai'
              AND (ts.total_temuan IS NULL OR ts.total_temuan = ts.closed_temuan)
        ";

        $card3Total = (int) $db->query($sqlCount)->getRow()->total;

        $sqlCountPeriode = $sqlCount . " AND a.periode_id = ?";
        $card1Total = $periodeAktifId
            ? (int) $db->query($sqlCountPeriode, [$periodeAktifId])->getRow()->total
            : 0;

        $card4Total = (int) $db->table('periodes')->countAllResults();

        // ==========================================================
        // FORMAT DATA - LOGIKA TANGGAL MASUK ARSIP
        // ==========================================================
        $formattedArsip = [];

        foreach ($arsipList as $a) {
            $rawScore = (float) ($a->final_score ?? 0);
            $normalizedScore = $rawScore > 5 ? ($rawScore / 100) * 5 : $rawScore;

            $auditSelesaiDate = $a->updated_at ? strtotime($a->updated_at) : strtotime($a->created_at);
            $totalTemuan = (int) ($a->total_temuan ?? 0);

            // DEFAULT: tanggal audit selesai
            $tanggalMasukArsip = $auditSelesaiDate;

            // Jika ADA temuan: cari tanggal temuan TERAKHIR yang di-closed
            if ($totalTemuan > 0) {
                // RAW QUERY untuk memastikan data benar
                $sqlLastClosed = "
                    SELECT MAX(closed_at) as last_closed
                    FROM temuans
                    WHERE audit_id = ?
                      AND status = 'Closed'
                      AND closed_at IS NOT NULL
                ";

                $rowLastClosed = $db->query($sqlLastClosed, [$a->id])->getRow();

                if ($rowLastClosed && !empty($rowLastClosed->last_closed)) {
                    $tanggalMasukArsip = strtotime($rowLastClosed->last_closed);
                }
            }

            $formattedArsip[] = [
                'id' => (int) $a->id,
                'judul' => (string) ($a->title ?? '-'),
                'periode' => (string) ($a->nama_periode ?? '-'),
                'auditee' => (string) ($a->auditee_name ?? '-'),
                'framework' => (string) ($a->framework ?? '-'),
                'skor' => $normalizedScore,
                'skor_label' => $this->getMaturityLabel($normalizedScore),
                'tanggal' => date('d M Y', $tanggalMasukArsip),
                'timestamp_arsip' => $tanggalMasukArsip,
            ];
        }

        // Urutkan berdasarkan tanggal masuk arsip (terbaru di atas)
        usort($formattedArsip, function ($a, $b) {
            return $b['timestamp_arsip'] <=> $a['timestamp_arsip'];
        });

        return view('pimpinan/arsip', [
            'page_title' => 'ARSIP LAPORAN HASIL AUDIT (LHA) RESMI',
            'page_subtitle' => 'Dokumen Audit Selesai (Semua Temuan Closed atau Tanpa Temuan)',
            'arsipList' => $formattedArsip,
            'card1Total' => $card1Total,
            'periodeAktifNama' => $periodeAktifNama,
            'card3Total' => $card3Total,
            'card4Total' => $card4Total,
            'totalArsip' => count($formattedArsip),
            'periodes' => $periodes,
            'frameworks' => $frameworks,
            'auditees' => $auditees,
            'filterPeriode' => $filterPeriode,
            'filterFramework' => $filterFramework,
            'filterAuditee' => $filterAuditee,
        ]);
    }

    private function getMaturityLabel(float $score): string
    {
        if ($score >= 4.5) return 'Optimal';
        if ($score >= 3.5) return 'Predictable';
        if ($score >= 2.5) return 'Established';
        if ($score >= 1.5) return 'Managed';
        if ($score >= 0.5) return 'Performed';
        return 'Incomplete';
    }
}
