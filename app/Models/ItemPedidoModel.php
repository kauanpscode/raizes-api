<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemPedidoModel extends Model
{
    protected $table            = 'itens_pedido';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [];

    protected $useTimestamps = false;

    public function __construct()
    {
        parent::__construct();

        $colunasTabela = $this->db->getFieldNames($this->table);
        $this->allowedFields = $colunasTabela;
    }

    public function getItensPorPedido(int $pedidoId)
    {
        $sql = "SELECT itens_pedido.*, produtos.nome as produto_nome, produtos.codigo as produto_codigo 
                FROM itens_pedido 
                JOIN produtos ON produtos.id = itens_pedido.produto_id 
                WHERE itens_pedido.pedido_id = ?";

        $query = $this->db->query($sql, [$pedidoId]);
        
        return $query->getResultArray();
    }
}