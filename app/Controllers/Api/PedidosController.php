<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class PedidosController extends ResourceController
{
    protected $pedidoModel;
    protected $produtoModel;
    protected $estoqueModel;
    protected $unidadeModel;
    protected $helpers = ['script'];

    private const CANAIS_VALIDOS = ['APP', 'TOTEM', 'BALCAO', 'PICKUP', 'WEB'];
    private const STATUS_VALIDOS = ['AGUARDANDO_PAGAMENTO', 'PAGO', 'EM_PREPARO', 'PRONTO', 'ENTREGUE', 'CANCELADO'];

    public function __construct()
    {
        $this->pedidoModel  = model('PedidoModel');
        $this->produtoModel = model('ProdutoModel');
        $this->estoqueModel = model('EstoqueUnidadeModel');
        $this->unidadeModel = model('UnidadeModel');
    }

    #[OA\Get(
        path: "/pedidos",
        summary: "Listar pedidos",
        description: "Lista pedidos com opção de filtro por canal de atendimento e status.",
        tags: ["Pedidos"],
        parameters: [
            new OA\Parameter(
                name: "canalPedido",
                in: "query",
                description: "Filtrar por canal de origem (APP, TOTEM, BALCAO, PICKUP, WEB)",
                required: false,
                schema: new OA\Schema(type: "string", example: "TOTEM")
            ),
            new OA\Parameter(
                name: "status",
                in: "query",
                description: "Filtrar por status do pedido (AGUARDANDO_PAGAMENTO, PAGO, EM_PREPARO, PRONTO, ENTREGUE, CANCELADO)",
                required: false,
                schema: new OA\Schema(type: "string", example: "AGUARDANDO_PAGAMENTO")
            ),
            new OA\Parameter(
                name: "unidadeId",
                in: "query",
                description: "Filtrar por unidade física",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Lista de pedidos recuperada com sucesso")
        ]
    )]
    public function index()
    {
        $canalPedido = $this->request->getGet('canalPedido');
        $status      = $this->request->getGet('status');
        $unidadeId   = $this->request->getGet('unidadeId');

        if (!empty($canalPedido) && !in_array(strtoupper(trim($canalPedido)), self::CANAIS_VALIDOS, true)) {
            return erro_json(
                'CANAL_INVALIDO',
                'O valor fornecido para canalPedido é inválido.',
                [['field' => 'canalPedido', 'issue' => 'Valores aceitos: ' . implode(', ', self::CANAIS_VALIDOS)]],
                422
            );
        }

        $pedidos = $this->pedidoModel->getPedidos(
            $canalPedido,
            $status,
            $unidadeId ? (int)$unidadeId : null
        );

        return $this->respond([
            'total'   => count($pedidos),
            'pedidos' => $pedidos
        ], 200);
    }

    #[OA\Get(
        path: "/pedidos/{id}",
        summary: "Detalhes de um pedido específico e seus itens",
        tags: ["Pedidos"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID do pedido",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Pedido e itens localizados"),
            new OA\Response(response: 404, description: "Pedido não encontrado")
        ]
    )]
    public function show($id = null)
    {
        $pedido = $this->pedidoModel->getPedidoComItens((int)$id);

        if (!$pedido) {
            return erro_json(
                'PEDIDO_NAO_ENCONTRADO',
                "O pedido com ID {$id} não foi encontrado.",
                [],
                404
            );
        }

        return $this->respond($pedido, 200);
    }

    public function getPedidos($id = null)
    {
        return $this->show($id);
    }

    #[OA\Post(
        path: "/pedidos",
        summary: "Criar novo pedido",
        description: "Valida regras de negócio, verifica o estoque por unidade, valida o campo obrigatório canalPedido e cria o pedido com status AGUARDANDO_PAGAMENTO.",
        tags: ["Pedidos"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["canalPedido", "unidadeId", "itens"],
                properties: [
                    new OA\Property(property: "canalPedido", type: "string", example: "TOTEM", description: "Valores: APP, TOTEM, BALCAO, PICKUP, WEB"),
                    new OA\Property(property: "unidadeId", type: "integer", example: 1),
                    new OA\Property(property: "usuarioId", type: "integer", example: 5, nullable: true),
                    new OA\Property(property: "formaPagamento", type: "string", example: "PIX"),
                    new OA\Property(property: "observacoes", type: "string", example: "Sem cebola"),
                    new OA\Property(
                        property: "itens",
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "produtoId", type: "integer", example: 1),
                                new OA\Property(property: "quantidade", type: "integer", example: 2)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Pedido criado com sucesso"),
            new OA\Response(response: 404, description: "Unidade ou Produto não encontrado"),
            new OA\Response(response: 409, description: "Estoque insuficiente para um ou mais itens"),
            new OA\Response(response: 422, description: "Dados obrigatórios ausentes ou inválidos")
        ]
    )]
    public function create()
    {
        $dados = $this->request->getJSON(true);

        // 1. Validação de presença do canalPedido
        if (empty($dados['canalPedido'])) {
            return erro_json(
                'VALOR_INVALIDO',
                'O campo canalPedido é obrigatório.',
                [['field' => 'canalPedido', 'issue' => 'Valores permitidos: ' . implode(', ', self::CANAIS_VALIDOS)]],
                422
            );
        }

        $canalPedido = strtoupper(trim($dados['canalPedido']));
        if (!in_array($canalPedido, self::CANAIS_VALIDOS, true)) {
            return erro_json(
                'CANAL_INVALIDO',
                "O canal '{$canalPedido}' não é reconhecido pela franqueadora.",
                [['field' => 'canalPedido', 'issue' => 'Valores permitidos: ' . implode(', ', self::CANAIS_VALIDOS)]],
                422
            );
        }

        // 2. Validação da unidade física
        if (empty($dados['unidadeId'])) {
            return erro_json(
                'CAMPOS_OBRIGATORIOS_AUSENTES',
                'O campo "unidadeId" é obrigatório para direcionamento do pedido.',
                [['field' => 'unidadeId', 'issue' => 'ID da unidade é obrigatório']],
                422
            );
        }

        $unidade = $this->unidadeModel->find($dados['unidadeId']);
        if (!$unidade || (int)$unidade['ativa'] !== 1) {
            return erro_json(
                'UNIDADE_NAO_ENCONTRADA',
                "A unidade ID {$dados['unidadeId']} não existe ou está inativa.",
                [],
                404
            );
        }

        // 3. Validação da lista de itens
        if (empty($dados['itens']) || !is_array($dados['itens'])) {
            return erro_json(
                'ITENS_INVALIDOS',
                'O pedido deve conter ao menos um item no array "itens".',
                [],
                422
            );
        }

        // 4. Validação de existência de produto e estoque
        $itensProcessados = [];
        $valorTotal = 0.0;

        foreach ($dados['itens'] as $indice => $item) {
            $produtoId  = $item['produtoId'] ?? null;
            $quantidade = (int)($item['quantidade'] ?? 0);

            if (!$produtoId || $quantidade <= 0) {
                return erro_json(
                    'ITEM_INVALIDO',
                    "O item no índice {$indice} possui produtoId ou quantidade inválida.",
                    [['field' => "itens[{$indice}]", 'issue' => 'produtoId e quantidade > 0 obrigatórios']],
                    422
                );
            }

            $produto = $this->produtoModel->find($produtoId);
            if (!$produto || (int)$produto['ativo'] !== 1) {
                return erro_json(
                    'PRODUTO_NAO_ENCONTRADO',
                    "O produto com ID {$produtoId} não foi encontrado no cardápio.",
                    [],
                    404
                );
            }

            // Checagem de saldo em estoque na unidade
            $estoque = $this->estoqueModel->getEstoque((int)$dados['unidadeId'], (int)$produtoId);
            $saldoDisponivel = $estoque ? (int)$estoque['quantidade'] : 0;

            if ($saldoDisponivel < $quantidade) {
                return erro_json(
                    'ESTOQUE_INSUFICIENTE',
                    'Não há quantidade suficiente para um ou mais itens.',
                    [[
                        'field' => "itens[{$indice}].quantidade",
                        'issue' => "Disponível: {$saldoDisponivel}"
                    ]],
                    409
                );
            }

            $precoUnitario = (float)$produto['preco'];
            $subtotal      = $precoUnitario * $quantidade;
            $valorTotal   += $subtotal;

            $itensProcessados[] = [
                'produto_id'     => (int)$produtoId,
                'nome'           => $produto['nome'],
                'quantidade'     => $quantidade,
                'preco_unitario' => $precoUnitario,
                'subtotal'       => $subtotal,
                'observacao'     => $item['observacao'] ?? null,
            ];
        }

        // 5. Delegação completa da transação para a Model (Separação de Responsabilidades)
        $dadosPedido = [
            'unidade_id'     => (int)$dados['unidadeId'],
            'usuario_id'     => !empty($dados['usuarioId']) ? (int)$dados['usuarioId'] : null,
            'canal_pedido'   => $canalPedido,
            'status'         => 'AGUARDANDO_PAGAMENTO',
            'valor_subtotal' => $valorTotal,
            'valor_desconto' => 0.00,
            'valor_total'    => $valorTotal,
            'observacoes'    => $dados['observacoes'] ?? null,
        ];

        $resultado = $this->pedidoModel->criarPedidoCompleto($dadosPedido, $itensProcessados);

        if (!$resultado) {
            return erro_json(
                'ERRO_TRANSACAO',
                'Não foi possível registrar o pedido no banco de dados.',
                [],
                500
            );
        }

        return $this->respondCreated($resultado);
    }

    #[OA\Patch(
        path: "/pedidos/{id}/status",
        summary: "Atualizar ciclo de status do pedido",
        description: "Atualiza o status do pedido (PAGO, EM_PREPARO, PRONTO, ENTREGUE, CANCELADO) e registra log de auditoria.",
        tags: ["Pedidos"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID do pedido",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["status"],
                properties: [
                    new OA\Property(property: "status", type: "string", example: "EM_PREPARO")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Status atualizado com sucesso"),
            new OA\Response(response: 404, description: "Pedido não encontrado"),
            new OA\Response(response: 422, description: "Status inválido ou não permitido")
        ]
    )]
    public function atualizarPedido($id = null)
    {
        if (empty($id)) {
            return erro_json('PARAMETRO_OBRIGATORIO', 'O ID do pedido é obrigatório.', [], 422);
        }

        $pedido = $this->pedidoModel->find($id);
        if (!$pedido) {
            return erro_json(
                'PEDIDO_NAO_ENCONTRADO',
                "O pedido com ID {$id} não foi encontrado.",
                [],
                404
            );
        }

        $dados = $this->request->getJSON(true);
        if (empty($dados['status'])) {
            return erro_json(
                'STATUS_OBRIGATORIO',
                'O campo "status" deve ser informado no corpo da requisição.',
                [['field' => 'status', 'issue' => 'Valores aceitos: ' . implode(', ', self::STATUS_VALIDOS)]],
                422
            );
        }

        $novoStatus = strtoupper(trim($dados['status']));
        if (!in_array($novoStatus, self::STATUS_VALIDOS, true)) {
            return erro_json(
                'STATUS_INVALIDO',
                "O status '{$novoStatus}' não é válido para o ciclo de vida do pedido.",
                [['field' => 'status', 'issue' => 'Valores aceitos: ' . implode(', ', self::STATUS_VALIDOS)]],
                422
            );
        }

        // Delegação da atualização e auditoria para a Model
        $resultado = $this->pedidoModel->atualizarStatus((int)$id, $novoStatus);

        if (!$resultado) {
            return erro_json(
                'ERRO_ATUALIZACAO',
                'Falha ao atualizar status do pedido no banco de dados.',
                [],
                500
            );
        }

        return $this->respond([
            'message'        => 'Status do pedido atualizado com sucesso.',
            'pedidoId'       => $resultado['pedidoId'],
            'numeroPedido'   => $resultado['numeroPedido'],
            'statusAnterior' => $resultado['statusAnterior'],
            'statusAtual'    => $resultado['statusAtual'],
            'updatedAt'      => $resultado['updatedAt']
        ], 200);
    }
}
