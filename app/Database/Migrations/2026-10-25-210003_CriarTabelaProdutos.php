<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelaProdutos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'auto_increment' => true,
            ],
            'codigo' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
            ],
            'nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'descricao' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'categoria' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'preco' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
            ],
            'sazonal' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'ativo' => [
                'type'    => 'BOOLEAN',
                'default' => true,
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
        $this->forge->addUniqueKey('codigo');
        $this->forge->createTable('produtos');
    }

    public function down()
    {
        $this->forge->dropTable('produtos', true);
    }
}
