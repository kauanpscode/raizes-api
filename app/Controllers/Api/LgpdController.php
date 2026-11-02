<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class LgpdController extends ResourceController
{
    protected $consentimentoModel;
    protected $usuarioModel;
    protected $helpers = ['script'];

    public function __construct()
    {
        $this->consentimentoModel = model('ConsentimentoLgpdModel');
        $this->usuarioModel       = model('UsuarioModel');
    }

    #[OA\Post(
        path: "/lgpd/consentimento",
        summary: "Registrar consentimento explícito do titular (Conformidade LGPD)",
        description: "Registra a manifestação livre, informada e inequívoca do titular para tratamento de dados cadastrais e programa de fidelização conforme a Lei Geral de Proteção de Dados (Lei nº 13.709/2018).",
        tags: ["LGPD & Privacidade"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["usuarioId", "aceitou"],
                properties: [
                    new OA\Property(property: "usuarioId", type: "integer", example: 5, description: "ID do usuário titular dos dados"),
                    new OA\Property(property: "aceitou", type: "boolean", example: true, description: "Aceite formal dos termos"),
                    new OA\Property(property: "termoVersao", type: "string", example: "v1.0", description: "Versão do termo de privacidade"),
                    new OA\Property(property: "canal", type: "string", example: "APP", description: "Canal de coleta (APP, TOTEM, WEB, BALCAO)")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Consentimento registrado com sucesso no banco de dados"
            ),
            new OA\Response(
                response: 404,
                description: "Usuário não encontrado"
            ),
            new OA\Response(
                response: 422,
                description: "Campos obrigatórios ausentes"
            )
        ]
    )]
    public function registrarConsentimento()
    {
        $dados = $this->request->getJSON(true);

        if (empty($dados['usuarioId'])) {
            return erro_json(
                'CAMPOS_OBRIGATORIOS_AUSENTES',
                'O campo "usuarioId" é obrigatório para registrar o consentimento.',
                [['field' => 'usuarioId', 'issue' => 'ID do usuário é obrigatório']],
                422
            );
        }

        if (!isset($dados['aceitou'])) {
            return erro_json(
                'CAMPOS_OBRIGATORIOS_AUSENTES',
                'O campo "aceitou" (booleano) deve ser informado.',
                [['field' => 'aceitou', 'issue' => 'Deve ser true ou false']],
                422
            );
        }

        $usuario = $this->usuarioModel->find((int)$dados['usuarioId']);
        if (!$usuario) {
            return erro_json(
                'USUARIO_NAO_ENCONTRADO',
                "O usuário com ID {$dados['usuarioId']} não foi encontrado.",
                [],
                404
            );
        }

        $registro = $this->consentimentoModel->registrarConsentimento([
            'usuario_id'   => (int)$dados['usuarioId'],
            'termo_versao' => $dados['termoVersao'] ?? 'v1.0',
            'aceitou'      => (bool)$dados['aceitou'],
            'finalidade'   => 'Tratamento de dados cadastrais para processamento de pedidos e concessão de descontos do programa de fidelidade conforme a LGPD (Art. 7º, I da Lei 13.709/2018).',
            'ip_origem'    => $this->request->getIPAddress(),
            'canal'        => strtoupper(trim($dados['canal'] ?? 'APP')),
        ]);

        return $this->respondCreated([
            'message'        => 'Consentimento LGPD registrado com sucesso.',
            'consentimento'  => [
                'id'          => (int)$registro['id'],
                'usuarioId'   => (int)$registro['usuario_id'],
                'termoVersao' => $registro['termo_versao'],
                'aceitou'     => (bool)$registro['aceitou'],
                'finalidade'  => $registro['finalidade'],
                'canal'       => $registro['canal'],
                'registradoEm'=> $registro['created_at'],
            ]
        ]);
    }
}
