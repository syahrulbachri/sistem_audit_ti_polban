<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRevisionLogicToAudits extends Migration
{
    public function up()
    {
        // 1. Update ENUM status di tabel audits
        $this->db->query("ALTER TABLE `audits` MODIFY COLUMN `status` ENUM('aktif', 'menunggu_jawaban', 'menunggu_penilaian', 'menunggu_revisi', 'menunggu_finalisasi', 'selesai') NOT NULL DEFAULT 'aktif'");

        // 2. Tambahkan kolom needs_revision dan is_improve_request di tabel audit_question_assignments
        $this->forge->addColumn('audit_question_assignments', [
            'needs_revision' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Auditor menandai wajib revisi, 0 = Tidak perlu revisi',
                'after'      => 'score'
            ],
            'is_improve_request' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = Auditee mengajukan request improve nilai, 0 = Normal',
                'after'      => 'needs_revision'
            ]
        ]);
    }

    public function down()
    {
        // ROLLBACK: Hapus kolom yang ditambahkan jika terjadi error
        $this->forge->dropColumn('audit_question_assignments', ['needs_revision', 'is_improve_request']);

        // ROLLBACK: Kembalikan ENUM status ke kondisi semula
        $this->db->query("ALTER TABLE `audits` MODIFY COLUMN `status` ENUM('aktif', 'menunggu_jawaban', 'menunggu_penilaian', 'revisi', 'selesai') NOT NULL DEFAULT 'aktif'");
    }
}