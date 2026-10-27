<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaPagamentos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'pedido_id' => [
                'type' => 'INTEGER',
            ],
            'metodo' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'PIX',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'PENDENTE',
            ],
            'valor' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'transacao_mock_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'mensagem_retorno' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'payload_retorno' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pagamentos');
    }

    public function down()
    {
        $this->forge->dropTable('pagamentos', true);
    }
}
