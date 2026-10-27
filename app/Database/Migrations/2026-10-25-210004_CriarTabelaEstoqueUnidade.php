<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaEstoqueUnidade extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'unidade_id' => [
                'type' => 'INTEGER',
            ],
            'produto_id' => [
                'type' => 'INTEGER',
            ],
            'quantidade' => [
                'type'    => 'INTEGER',
                'default' => 0,
            ],
            'quantidade_minima' => [
                'type'    => 'INTEGER',
                'default' => 5,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['unidade_id', 'produto_id']);
        $this->forge->addForeignKey('unidade_id', 'unidades', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('produto_id', 'produtos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('estoque_unidade');
    }

    public function down()
    {
        $this->forge->dropTable('estoque_unidade', true);
    }
}
