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

        $colunasTabela = $this->getFieldNames($this->table);
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

        $query = $this->query($sql, $params);
        return $query->getResultArray();
    }

    public function getPedidoComItens(int $id): ?array
    {
        $pedido = $this->find($id);
        if (!$pedido) {
            return null;
        }

        $itemPedidoModel = model('ItemPedidoModel');
        $pedido['itens'] = $itemPedidoModel->getItensPorPedido($id);

        return $pedido;
    }

    public function gerarNumeroPedido(): string
    {
        $ano = date('Y');

        $sql = "SELECT id FROM pedidos ORDER BY id DESC LIMIT 1";
        $query = $this->query($sql);
        $ultimo = $query->getRowArray();

        $proximo = $ultimo ? ((int)$ultimo['id'] + 1) : 1;

        return sprintf("PED-%s-%04d", $ano, $proximo);
    }
    public function criarPedidoCompleto(array $dadosPedido, array $itensProcessados)
    {
        $this->transStart();

        $numeroPedido = $this->gerarNumeroPedido();
        $dadosPedido['numero_pedido'] = $numeroPedido;

        $pedidoId = $this->insert($dadosPedido);

        $itemPedidoModel = model('ItemPedidoModel');
        $estoqueModel    = model('EstoqueUnidadeModel');
        $auditoriaModel  = model('AuditoriaModel');

        foreach ($itensProcessados as $item) {
            $itemPedidoModel->insert([
                'pedido_id'      => $pedidoId,
                'produto_id'     => $item['produto_id'],
                'quantidade'     => $item['quantidade'],
                'preco_unitario' => $item['preco_unitario'],
                'subtotal'       => $item['subtotal'],
                'observacao'     => $item['observacao'] ?? null,
            ]);

            $estoqueModel->darBaixa(
                (int)$dadosPedido['unidade_id'],
                (int)$item['produto_id'],
                (int)$item['quantidade']
            );
        }

        $auditoriaModel->registrarLog(
            'CRIAR_PEDIDO',
            'pedidos',
            (string)$pedidoId,
            $dadosPedido['usuario_id'] ?? null,
            $dadosPedido['canal_pedido'],
            [
                'numero_pedido' => $numeroPedido,
                'total'         => $dadosPedido['valor_total'],
                'itens_qtd'     => count($itensProcessados)
            ]
        );

        $this->transComplete();

        if ($this->transStatus() === false) {
            return false;
        }

        return [
            'pedidoId'     => (int)$pedidoId,
            'numeroPedido' => $numeroPedido,
            'status'       => $dadosPedido['status'],
            'canalPedido'  => $dadosPedido['canal_pedido'],
            'total'        => round((float)$dadosPedido['valor_total'], 2),
            'itens'        => array_map(static function ($i) {
                return [
                    'produtoId'     => (int)$i['produto_id'],
                    'nome'          => $i['nome'],
                    'quantidade'    => (int)$i['quantidade'],
                    'precoUnitario' => round((float)$i['preco_unitario'], 2),
                    'subtotal'      => round((float)$i['subtotal'], 2),
                ];
            }, $itensProcessados),
            'createdAt'    => gmdate('Y-m-d\TH:i:s\Z')
        ];
    }

    public function atualizarStatus(int $id, string $novoStatus)
    {
        $pedido = $this->find($id);
        if (!$pedido) {
            return false;
        }

        $statusAnterior = $pedido['status'];

        $this->transStart();

        $this->update($id, ['status' => $novoStatus]);

        $auditoriaModel = model('AuditoriaModel');
        $auditoriaModel->registrarLog(
            'MUDAR_STATUS_PEDIDO',
            'pedidos',
            (string)$id,
            null,
            $pedido['canal_pedido'],
            ['status_anterior' => $statusAnterior, 'novo_status' => $novoStatus]
        );

        $this->transComplete();

        if ($this->transStatus() === false) {
            return false;
        }

        return [
            'pedidoId'       => (int)$id,
            'numeroPedido'   => $pedido['numero_pedido'],
            'statusAnterior' => $statusAnterior,
            'statusAtual'    => $novoStatus,
            'updatedAt'      => gmdate('Y-m-d\TH:i:s\Z')
        ];
    }
}
