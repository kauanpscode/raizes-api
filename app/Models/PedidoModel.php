<?php

namespace App\Models;

use CodeIgniter\Model;

class PedidoModel extends Model
{
    protected $table            = 'pedidos';
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

    public function getPedidos(?string $canalPedido = null, ?string $status = null, ?int $unidadeId = null)
    {
        $sql = "SELECT pedidos.*, unidades.nome_fantasia as unidade_nome, usuarios.nome as cliente_nome 
                FROM pedidos 
                LEFT JOIN unidades ON unidades.id = pedidos.unidade_id 
                LEFT JOIN usuarios ON usuarios.id = pedidos.usuario_id 
                WHERE 1=1";

        $params = [];

        if (!empty($canalPedido)) {
            $sql .= " AND pedidos.canal_pedido = ?";
            $params[] = strtoupper(trim($canalPedido));
        }

        if (!empty($status)) {
            $sql .= " AND pedidos.status = ?";
            $params[] = strtoupper(trim($status));
        }

        if (!empty($unidadeId)) {
            $sql .= " AND pedidos.unidade_id = ?";
            $params[] = $unidadeId;
        }

        $sql .= " ORDER BY pedidos.id DESC";

        $query = $this->db->query($sql, $params);
        return $query->getResultArray();
    }

    public function gerarNumeroPedido(): string
    {
        $ano = date('Y');

        $sql = "SELECT id FROM pedidos ORDER BY id DESC LIMIT 1";
        $query = $this->db->query($sql);
        $ultimo = $query->getRowArray();

        $proximo = $ultimo ? ((int)$ultimo['id'] + 1) : 1;

        return sprintf("PED-%s-%04d", $ano, $proximo);
    }
}
