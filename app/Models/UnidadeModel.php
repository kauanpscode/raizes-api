<?php

namespace App\Models;

use CodeIgniter\Model;

class UnidadeModel extends Model
{
    protected $table            = 'unidades';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
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

    public function getUnidades(?string $uf = '') 
    {
        $sql = "SELECT * FROM {$this->table} WHERE ativa = 1";
        $params = [];

        if (!empty($uf)) {
            $sql .= " AND uf = ?";
            $params[] = $uf;
        }

        return $this->query($sql, $params)->getResult();
    }
}
