<?php

namespace App\Models;

use CodeIgniter\Model;

class PagamentoModel extends Model
{
    protected $table            = 'pagamentos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'pedido_id',
        'metodo',
        'status',
        'valor',
        'transacao_mock_id',
        'mensagem_retorno',
        'payload_retorno',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Retorna os pagamentos associados a um pedido
     */
    public function getPagamentosPorPedido(int $pedidoId)
    {
        return $this->where('pedido_id', $pedidoId)->findAll();
    }

    /**
     * Persiste o pagamento aprovado, atualiza o pedido para PAGO e grava log
     * de auditoria de forma atômica e transacional na camada de persistência.
     *
     * @param array $pedido Dados do pedido a ser liquidado
     * @param string $metodo Método de pagamento (ex: PIX, CARTAO_CREDITO)
     * @param string $transacaoMockId ID gerado pelo gateway
     * @param string $authCode Código de autorização bancária
     * @param array $payloadGateway Payload detalhado do provedor
     * @return array|false Dados do pagamento registrado ou false
     */
    public function registrarPagamentoAprovado(
        array $pedido,
        string $metodo,
        string $transacaoMockId,
        string $authCode,
        array $payloadGateway
    ) {
        $pedidoId = (int)$pedido['id'];
        $valor    = (float)$pedido['valor_total'];

        $this->db->transStart();

        // 1. Grava registro na tabela pagamentos
        $pagamentoId = $this->insert([
            'pedido_id'         => $pedidoId,
            'metodo'            => $metodo,
            'status'            => 'APROVADO',
            'valor'             => $valor,
            'transacao_mock_id' => $transacaoMockId,
            'mensagem_retorno'  => 'Pagamento aprovado com sucesso via mock gateway.',
            'payload_retorno'   => json_encode($payloadGateway, JSON_UNESCAPED_UNICODE),
        ]);

        // 2. Atualiza status do pedido para PAGO
        $pedidoModel = model('PedidoModel');
        $pedidoModel->update($pedidoId, [
            'status' => 'PAGO'
        ]);

        // 3. Registra na trilha de auditoria
        $auditoriaModel = model('AuditoriaModel');
        $auditoriaModel->registrarLog(
            'PAGAMENTO_APROVADO',
            'pagamentos',
            (string)$pagamentoId,
            $pedido['usuario_id'] ?? null,
            $pedido['canal_pedido'],
            [
                'pedido_id'    => $pedidoId,
                'transacao_id' => $transacaoMockId,
                'auth_code'    => $authCode,
                'valor'        => $valor,
                'novo_status'  => 'PAGO'
            ]
        );

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            return false;
        }

        return [
            'pagamentoId'       => (int)$pagamentoId,
            'transacaoMockId'   => $transacaoMockId,
            'authorizationCode' => $authCode,
            'metodo'            => $metodo,
            'valor'             => round($valor, 2),
            'status'            => 'APROVADO',
            'pagoEm'            => gmdate('Y-m-d\TH:i:s\Z'),
        ];
    }

    /**
     * Persiste a recusa de pagamento e registra log de auditoria da falha.
     *
     * @param array $pedido Dados do pedido
     * @param string $metodo Método de pagamento
     * @param string $transacaoMockId ID gerado pelo gateway
     * @param string $mensagemErro Motivo da recusa
     * @param array $payloadGateway Payload detalhado do provedor
     * @return int ID do registro de pagamento recusado
     */
    public function registrarPagamentoRecusado(
        array $pedido,
        string $metodo,
        string $transacaoMockId,
        string $mensagemErro,
        array $payloadGateway
    ): int {
        $pedidoId = (int)$pedido['id'];
        $valor    = (float)$pedido['valor_total'];

        $pagamentoId = $this->insert([
            'pedido_id'         => $pedidoId,
            'metodo'            => $metodo,
            'status'            => 'RECUSADO',
            'valor'             => $valor,
            'transacao_mock_id' => $transacaoMockId,
            'mensagem_retorno'  => $mensagemErro,
            'payload_retorno'   => json_encode($payloadGateway, JSON_UNESCAPED_UNICODE),
        ]);

        $auditoriaModel = model('AuditoriaModel');
        $auditoriaModel->registrarLog(
            'PAGAMENTO_RECUSADO',
            'pagamentos',
            (string)$pedidoId,
            $pedido['usuario_id'] ?? null,
            $pedido['canal_pedido'],
            [
                'transacao' => $transacaoMockId,
                'motivo'    => $mensagemErro,
                'valor'     => $valor
            ]
        );

        return (int)$pagamentoId;
    }
}
