<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaPedidos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'numero_pedido' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'unidade_id' => [
                'type' => 'INTEGER',
            ],
            'usuario_id' => [
                'type' => 'INTEGER',
                'null' => true,
            ],
            'canal_pedido' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'AGUARDANDO_PAGAMENTO',
            ],
            'valor_subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'valor_desconto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
            ],
            'valor_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'observacoes' => [
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
        $this->forge->addUniqueKey('numero_pedido');
        $this->forge->addForeignKey('unidade_id', 'unidades', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'SET NULL');
        $this->forge->createTable('pedidos');
    }

    public function down()
    {
        $this->forge->dropTable('pedidos', true);
    }
}
