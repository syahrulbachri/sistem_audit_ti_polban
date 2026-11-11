<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRevisionLogicToAudits extends Migration
{
    public function up()
    {
        // 1. Update ENUM status di tabel audits
        // Kita gunakan raw query untuk modifikasi ENUM agar lebih aman dan tidak merusak data existing
        $this->db->query("ALTER TABLE `audits` MODIFY COLUMN `status` ENUM('aktif', 'menunggu_jawaban', 'menunggu_penilaian', 'menunggu_revisi', 'menunggu_finalisasi', 'selesai') NOT NULL DEFAULT 'aktif'");

        // 2. Tambahkan kolom needs_revision di tabel audit_question_assignments
        $this->forge->addColumn('audit_question_assignments', [
            'needs_revision' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Auditor menandai perlu revisi, 0 = Tidak perlu revisi (bisa request improve)',
                'after'      => 'score' // Kolom ini akan ditaruh setelah kolom 'score'
            ]
        ]);
    }

    public function down()
    {
        // ROLLBACK: Hapus kolom needs_revision jika terjadi error
        $this->forge->dropColumn('audit_question_assignments', 'needs_revision');

        // ROLLBACK: Kembalikan ENUM status ke kondisi semula
        $this->db->query("ALTER TABLE `audits` MODIFY COLUMN `status` ENUM('aktif', 'menunggu_jawaban', 'menunggu_penilaian', 'revisi', 'selesai') NOT NULL DEFAULT 'aktif'");
    }
}