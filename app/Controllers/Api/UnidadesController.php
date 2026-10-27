<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class UnidadesController extends ResourceController
{
    protected $unidadeModel;
    protected $helpers = ['script'];

    public function __construct()
    {
        $this->unidadeModel = model('UnidadeModel');
    }

    #[OA\Get(
        path: "/unidades",
        summary: "Listar todas as unidades da franquia",
        description: "Retorna a lista de matriz e filiais ativas.",
        tags: ["Unidades"],
        parameters: [
            new OA\Parameter(
                name: "uf",
                in: "query",
                description: "Filtrar unidades por UF",
                required: false,
                schema: new OA\Schema(type: "string", example: "PE")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de unidades retornada com sucesso"
            )
        ]
    )]
    public function index()
    {
        $uf = $this->request->getGet('uf');

        $unidades = $this->unidadeModel->getUnidades($uf);

        return $this->respond([
            'total'    => count($unidades),
            'unidades' => $unidades
        ], 200);
    }

    #[OA\Get(
        path: "/unidades/{id}",
        summary: "Detalhes de uma unidade específica",
        description: "Busca os dados de uma unidade pelo ID. Retorna 404 caso não seja encontrada.",
        tags: ["Unidades"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                description: "ID numérico da unidade",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: "Unidade localizada com sucesso"),
            new OA\Response(response: 404, description: "Unidade não encontrada")
        ]
    )]
    public function show($id = null)
    {
        $unidade = $this->unidadeModel->find($id);

        if (!$unidade) {
            // Usa o script_helper para retornar o JSON padronizado com HTTP 404
            return erro_json(
                'UNIDADE_NAO_ENCONTRADA',
                "A unidade com ID {$id} não foi encontrada no sistema.",
                [],
                404
            );
        }

        return $this->respond($unidade, 200);
    }

    #[OA\Post(
        path: "/unidades",
        summary: "Cadastrar nova unidade (Expansão da rede)",
        description: "Cadastra uma nova filial no sistema. Exige código, nome fantasia, CNPJ, cidade e UF.",
        tags: ["Unidades"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["codigo", "nome_fantasia", "cnpj", "cidade", "uf"],
                properties: [
                    new OA\Property(property: "codigo", type: "string", example: "NAT-01"),
                    new OA\Property(property: "nome_fantasia", type: "string", example: "Raízes do Nordeste - Filial Natal"),
                    new OA\Property(property: "razao_social", type: "string", example: "Raizes Natal Lanchonete Ltda"),
                    new OA\Property(property: "cnpj", type: "string", example: "12345678000433"),
                    new OA\Property(property: "cidade", type: "string", example: "Natal"),
                    new OA\Property(property: "uf", type: "string", example: "RN"),
                    new OA\Property(property: "tipo_unidade", type: "string", example: "FILIAL")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Unidade cadastrada com sucesso"),
            new OA\Response(response: 409, description: "Código ou CNPJ já cadastrado"),
            new OA\Response(response: 422, description: "Campos obrigatórios ausentes")
        ]
    )]
    public function create()
    {
        $dados = $this->request->getJSON(true);

        // 1. Validação de campos obrigatórios
        $obrigatorios = ['codigo', 'nome_fantasia', 'cnpj', 'cidade', 'uf'];
        $erros = [];

        foreach ($obrigatorios as $campo) {
            if (empty($dados[$campo])) {
                $erros[] = ['field' => $campo, 'issue' => 'Campo obrigatório'];
            }
        }

        if (!empty($erros)) {
            return erro_json(
                'CAMPOS_OBRIGATORIOS_AUSENTES',
                'Preencha todos os campos obrigatórios da unidade.',
                $erros,
                422
            );
        }

        // 2. Verifica se o código ou CNPJ já estão em uso (Regra de Negócio: Unicidade)
        $existe = $this->unidadeModel->where('codigo', $dados['codigo'])
                                     ->orWhere('cnpj', $dados['cnpj'])
                                     ->first();

        if ($existe) {
            return erro_json(
                'UNIDADE_JA_EXISTE',
                'Já existe uma unidade cadastrada com este código ou CNPJ.',
                [],
                409
            );
        }

        // 3. Insere a nova unidade no banco
        $novoId = $this->unidadeModel->insert([
            'codigo'        => strtoupper(trim($dados['codigo'])),
            'razao_social'  => $dados['razao_social'] ?? $dados['nome_fantasia'],
            'nome_fantasia' => trim($dados['nome_fantasia']),
            'cnpj'          => preg_replace('/\D/', '', $dados['cnpj']),
            'telefone'      => $dados['telefone'] ?? null,
            'email'         => $dados['email'] ?? null,
            'cep'           => $dados['cep'] ?? null,
            'logradouro'    => $dados['logradouro'] ?? null,
            'numero'        => $dados['numero'] ?? null,
            'complemento'   => $dados['complemento'] ?? null,
            'bairro'        => $dados['bairro'] ?? null,
            'cidade'        => trim($dados['cidade']),
            'uf'            => strtoupper(trim($dados['uf'])),
            'tipo_unidade'  => $dados['tipo_unidade'] ?? 'FILIAL',
            'ativa'         => 1,
        ]);

        $unidadeCriada = $this->unidadeModel->find($novoId);

        return $this->respondCreated([
            'message' => 'Unidade cadastrada com sucesso!',
            'unidade' => $unidadeCriada
        ]);
    }
}