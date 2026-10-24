<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarTabelasUnidade extends Migration
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
                'constraint' => 20,
            ],
            'razao_social' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'nome_fantasia' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'cnpj' => [
                'type'       => 'VARCHAR',
                'constraint' => 14,
            ],
            'telefone' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => true,
            ],
            'cep' => [
                'type'       => 'VARCHAR',
                'constraint' => 8,
            ],
            'logradouro' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'numero' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'complemento' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'bairro' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'cidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'uf' => [
                'type'       => 'CHAR',
                'constraint' => 2,
            ],
            'tipo_unidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'FILIAL',
            ],
            'ativa' => [
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
        $this->forge->addUniqueKey('cnpj');

        $this->forge->createTable('unidades');
    }

    public function down()
    {
        $this->forge->dropTable('unidades', true);
    }
}
