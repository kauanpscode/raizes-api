<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function __construct()
    {
        parent::__construct();

        $colunasTabela = $this->db->getFieldNames($this->table);

        $this->allowedFields = $colunasTabela;
    }

    public function buscarPorEmail(string $email)
    {
        $sql = "SELECT {$this->table}.*, perfis.nome as perfil_nome, perfis.descricao as perfil_descricao
                FROM {$this->table}
                LEFT JOIN perfis ON perfis.id = {$this->table}.id_perfil
                WHERE {$this->table}.email = ?";

        return $this->query($sql, [$email])->getRowArray();
    }
}
