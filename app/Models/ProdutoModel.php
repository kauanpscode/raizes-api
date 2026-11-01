<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoModel extends Model
{
    protected $table            = 'produtos';
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

    public function getProdutos(?string $categoria = null, ?int $unidadeId = null) 
    {
        if ($unidadeId) {
            $sql = "SELECT p.*, COALESCE(e.quantidade, 0) as quantidade_estoque
                    FROM produtos p
                    LEFT JOIN estoque_unidade e ON e.produto_id = p.id AND e.unidade_id = ?
                    WHERE p.ativo = 1";
            
            $params = [$unidadeId];

            if (!empty($categoria)) {
                $sql .= " AND p.categoria = ?";
                $params[] = $categoria;
            }

            return $this->query($sql, $params)->getResultArray();
        }

        $sql = "SELECT p.* FROM produtos
                where ativo = 1";

        if (!empty($categoria)) {
            $sql .= " AND categoria = $categoria";
        }

        return $this->query($sql)->findAll();
    }
}
