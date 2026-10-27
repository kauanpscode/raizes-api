<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaConsentimentosLgpd extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type' => 'INTEGER',
            ],
            'termo_versao' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'v1.0',
            ],
            'aceitou' => [
                'type'    => 'BOOLEAN',
                'default' => true,
            ],
            'finalidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'ip_origem' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'canal' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('consentimentos_lgpd');
    }

    public function down()
    {
        $this->forge->dropTable('consentimentos_lgpd', true);
    }
}
