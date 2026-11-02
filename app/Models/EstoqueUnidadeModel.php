<?php

namespace App\Models;

use CodeIgniter\Model;

class EstoqueUnidadeModel extends Model
{
    protected $table            = 'estoque_unidade';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';

    public function __construct()
    {
        parent::__construct();

        $colunasTabela = $this->db->getFieldNames($this->table);
        $this->allowedFields = $colunasTabela;
    }

    public function getEstoque(int $unidadeId, int $produtoId)
    {
        $sql = "SELECT * FROM estoque_unidade 
                WHERE unidade_id = ? AND produto_id = ? 
                LIMIT 1";

        $query = $this->db->query($sql, [$unidadeId, $produtoId]);
        return $query->getRowArray();
    }

    public function darBaixa(int $unidadeId, int $produtoId, int $quantidade): bool
    {
        $estoque = $this->getEstoque($unidadeId, $produtoId);
        
        if (!$estoque || $estoque['quantidade'] < $quantidade) {
            return false;
        }

        $novaQuantidade = $estoque['quantidade'] - $quantidade;

        return $this->update($estoque['id'], [
            'quantidade' => $novaQuantidade,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}