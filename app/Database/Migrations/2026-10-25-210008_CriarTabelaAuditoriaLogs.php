<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaAuditoriaLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type'       => 'INTEGER',
                'null'       => true,
            ],
            'acao' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
            ],
            'entidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'entidade_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'canal' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'detalhes_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('auditoria_logs');
    }

    public function down()
    {
        $this->forge->dropTable('auditoria_logs', true);
    }
}
