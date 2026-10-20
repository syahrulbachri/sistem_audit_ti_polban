<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminViewedToAudits extends Migration
{
    public function up()
    {
        $this->forge->addColumn('audits', [
            'admin_viewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at' // Menambahkan kolom setelah updated_at
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('audits', 'admin_viewed_at');
    }
}