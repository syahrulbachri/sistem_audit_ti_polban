<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterTemuansBuktiPerbaikanLongtext extends Migration
{
    public function up()
    {
        $fields = [
            'bukti_perbaikan' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
        ];

        $this->forge->modifyColumn('temuans', $fields);
    }

    public function down()
    {
        $fields = [
            'bukti_perbaikan' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ];

        $this->forge->modifyColumn('temuans', $fields);
    }
}