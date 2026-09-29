<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDraftStatusToPeriodes extends Migration
{
    public function up()
    {
         $this->db->query("
            ALTER TABLE periodes
            MODIFY COLUMN status
            ENUM('draft', 'open', 'closed')
            NOT NULL
            DEFAULT 'draft'
        ");
    }

    public function down()
    {
         $this->db->query("
            ALTER TABLE periodes
            MODIFY COLUMN status
            ENUM('open', 'closed')
            NOT NULL
        ");
    }
}
