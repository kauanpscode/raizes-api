<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaItensPedido extends Migration
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
            'produto_id' => [
                'type' => 'INTEGER',
            ],
            'quantidade' => [
                'type' => 'INTEGER',
            ],
            'preco_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'observacao' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pedido_id', 'pedidos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('produto_id', 'produtos', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('itens_pedido');
    }

    public function down()
    {
        $this->forge->dropTable('itens_pedido', true);
    }
}
