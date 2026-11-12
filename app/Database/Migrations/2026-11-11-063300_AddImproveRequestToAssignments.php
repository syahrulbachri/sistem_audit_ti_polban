<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImproveRequestToAssignments extends Migration
{
    public function up()
    {
        // Tambahkan kolom is_improve_request
        $this->forge->addColumn('audit_question_assignments', [
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
        // Hapus kolom jika rollback
        $this->forge->dropColumn('audit_question_assignments', 'is_improve_request');
    }
}