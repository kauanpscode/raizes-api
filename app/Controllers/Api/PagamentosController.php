<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class PagamentosController extends ResourceController
{
    protected $pagamentoModel;
    protected $pedidoModel;
    protected $helpers = ['script'];

    private const METODOS_VALIDOS = ['PIX', 'CARTAO_CREDITO', 'CARTAO_DEBITO', 'DINHEIRO'];

    public function __construct()
    {
        $this->pagamentoModel = model('PagamentoModel');
        $this->pedidoModel    = model('PedidoModel');
    }

    #[OA\Post(
        path: "/pagamentos/mock",
        summary: "Processar pagamento simulado",
        description: "Simula o envio de cobrança a um provedor externo de pagamentos.",
        tags: ["Pagamentos"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["pedidoId"],
                properties: [
                    new OA\Property(property: "pedidoId", type: "integer", example: 4, description: "ID do pedido a ser pago"),
                    new OA\Property(property: "metodo", type: "string", example: "PIX", description: "PIX, CARTAO_CREDITO, CARTAO_DEBITO, DINHEIRO"),
                    new OA\Property(property: "simulacao", type: "string", example: "APROVADO", description: "APROVADO ou RECUSADO")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Pagamento aprovado e status do pedido atualizado para PAGO"
            ),
            new OA\Response(
                response: 404,
                description: "Pedido não encontrado"
            ),
            new OA\Response(
                response: 409,
                description: "Pagamento recusado pela operadora ou pedido já pago/cancelado"
            ),
            new OA\Response(
                response: 422,
                description: "Campos obrigatórios ausentes ou método inválido"
            )
        ]
    )]
    public function processarMock()
    {
        $dados = $this->request->getJSON(true);

        if (empty($dados['pedidoId'])) {
            return erro_json(
                'CAMPOS_OBRIGATORIOS_AUSENTES',
                'O campo "pedidoId" é obrigatório para processar o pagamento.',
                [['field' => 'pedidoId', 'issue' => 'ID do pedido obrigatório']],
                422
            );
        }

        $pedidoId = (int)$dados['pedidoId'];
        $pedido   = $this->pedidoModel->find($pedidoId);

        if (!$pedido) {
            return erro_json(
                'PEDIDO_NAO_ENCONTRADO',
                "O pedido com ID {$pedidoId} não foi localizado.",
                [],
                404
            );
        }

        if ($pedido['status'] === 'PAGO') {
            return erro_json(
                'PAGAMENTO_JA_CONFIRMADO',
                'Este pedido já foi pago.',
                [['field' => 'status', 'issue' => 'Pedido já está PAGO']],
                409
            );
        }

        if ($pedido['status'] === 'CANCELADO') {
            return erro_json(
                'PEDIDO_CANCELADO',
                'Não é possível efetuar pagamento para um pedido que foi cancelado.',
                [['field' => 'status', 'issue' => 'Pedido encontra-se CANCELADO']],
                409
            );
        }

        $metodo = strtoupper(trim($dados['metodo'] ?? 'PIX'));
        if (!in_array($metodo, self::METODOS_VALIDOS, true)) {
            return erro_json(
                'METODO_INVALIDO',
                "O método '{$metodo}' não é suportado.",
                [['field' => 'metodo', 'issue' => 'Valores aceitos: ' . implode(', ', self::METODOS_VALIDOS)]],
                422
            );
        }

        $simulacao = strtoupper(trim($dados['simulacao'] ?? 'APROVADO'));
        $valor     = (float)$pedido['valor_total'];

        if ($simulacao === 'RECUSADO') {
            $transacaoMockId = 'MOCK-REC-' . uniqid() . '-' . date('Ymd');
            $mensagemErro    = 'Transação recusada pela instituição bancária / emissor do cartão.';

            $payloadGateway = [
                'gateway'       => 'MockPay-Nordeste-v1',
                'status'        => 'DENIED',
                'transacaoId'   => $transacaoMockId,
                'codigoRetorno' => '51_INSUFFICIENT_FUNDS',
                'mensagem'      => $mensagemErro,
                'metodo'        => $metodo,
                'valor'         => $valor,
                'timestamp'     => gmdate('Y-m-d\TH:i:s\Z'),
            ];

            $this->pagamentoModel->registrarPagamentoRecusado(
                $pedido,
                $metodo,
                $transacaoMockId,
                $mensagemErro,
                $payloadGateway
            );

            return erro_json(
                'PAGAMENTO_RECUSADO',
                'O pagamento foi recusado pelo serviço bancário simulado.',
                [
                    ['field' => 'pagamento', 'issue' => $mensagemErro],
                    ['field' => 'transacaoId', 'issue' => $transacaoMockId]
                ],
                409
            );
        }

        $transacaoMockId = 'MOCK-APP-' . strtoupper(substr(md5(uniqid()), 0, 8)) . '-' . date('Ymd');
        $authCode        = 'AUTH-' . mt_rand(100000, 999999);

        $payloadGateway = [
            'gateway'           => 'MockPay-Nordeste-v1',
            'status'            => 'APPROVED',
            'transacaoId'       => $transacaoMockId,
            'authorizationCode' => $authCode,
            'metodo'            => $metodo,
            'valor'             => $valor,
            'timestamp'         => gmdate('Y-m-d\TH:i:s\Z'),
        ];

        $resultado = $this->pagamentoModel->registrarPagamentoAprovado(
            $pedido,
            $metodo,
            $transacaoMockId,
            $authCode,
            $payloadGateway
        );

        if (!$resultado) {
            return erro_json(
                'ERRO_TRANSACAO',
                'Falha ao persistir confirmação de pagamento no banco de dados.',
                [],
                500
            );
        }

        return $this->respond([
            'message'      => 'Pagamento aprovado e pedido atualizado para PAGO!',
            'pedidoId'     => $pedidoId,
            'numeroPedido' => $pedido['numero_pedido'],
            'statusPedido' => 'PAGO',
            'pagamento'    => [
                'id'                => $resultado['pagamentoId'],
                'transacaoMockId'   => $resultado['transacaoMockId'],
                'authorizationCode' => $resultado['authorizationCode'],
                'metodo'            => $resultado['metodo'],
                'valor'             => $resultado['valor'],
                'status'            => $resultado['status'],
                'pagoEm'            => $resultado['pagoEm'],
            ]
        ], 200);
    }
}
