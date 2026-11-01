<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class ProdutosController extends ResourceController
{
    protected $produtoModel;
    protected $helpers = ['script'];

    public function __construct()
    {
        $this->produtoModel = model('ProdutoModel');
    }

    #[OA\Get(
        path: "/produtos",
        summary: "Listar cardápio de produtos da franquia",
        description: "Retorna a lista de produtos regionais ativos, permitindo filtro opcional por categoria ou por unidade.",
        tags: ["Produtos"],
        parameters: [
            new OA\Parameter(
                name: "categoria",
                in: "query",
                description: "Filtrar por categoria (ex: Cuscuz, Tapiocas, Sobremesas, Bebidas, Juninos)",
                required: false,
                schema: new OA\Schema(type: "string", example: "Cuscuz")
            ),
            new OA\Parameter(
                name: "unidadeId",
                in: "query",
                description: "Filtrar produtos disponíveis no estoque de uma unidade específica",
                required: false,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Cardápio recuperado com sucesso"
            )
        ]
    )]
    public function index()
    {
        $categoria = $this->request->getGet('categoria');
        $unidadeId = $this->request->getGet('unidadeId');

        $produtos = $this->produtoModel->getProdutos($categoria, $unidadeId ? (int)$unidadeId : null);

        return $this->respond([
            'total'    => count($produtos),
            'produtos' => $produtos
        ], 200);
    }

    #[OA\Get(
        path: "/produtos/{id}",
        summary: "Detalhes de um produto específico",
        description: "Busca os dados de um item do cardápio pelo ID. Retorna 404 se não for encontrado.",
        tags: ["Produtos"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID numérico do produto",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Produto localizado com sucesso"),
            new OA\Response(response: 404, description: "Produto não encontrado")
        ]
    )]
    public function getProdutos($id = null)
    {
        $produto = $this->produtoModel->find($id);

        if (!$produto) {
            return erro_json(
                'PRODUTO_NAO_ENCONTRADO',
                "O produto com ID {$id} não foi encontrado no sistema.",
                [],
                404
            );
        }

        return $this->respond($produto, 200);
    }
}