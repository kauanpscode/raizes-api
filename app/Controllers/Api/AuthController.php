<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Raízes do Nordeste",
    version: "1.0.0",
    description: "Documentação da API para o Projeto Multidisciplinar Back-End"
)]
#[OA\Server(
    url: "http://localhost:8080/api",
    description: "Servidor Local"
)]

class AuthController extends ResourceController
{
    protected $usuarioModel;
    protected $helpers = ['script'];

    public function __construct()
    {
        $this->usuarioModel = model('UsuarioModel');
    }

    #[OA\Post(
        path: "/auth/login",
        summary: "Autenticar usuário",
        description: "Valida as credenciais e retorna o token de acesso com o perfil do usuário.",
        tags: ["Autenticação"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "senha"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "admin@raizesdonordeste.com.br"),
                    new OA\Property(property: "senha", type: "string", format: "password", example: "Admin@123")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login realizado com sucesso e token gerado",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "accessToken", type: "string", example: "eyJhbGciOi..."),
                        new OA\Property(property: "tokenType", type: "string", example: "Bearer"),
                        new OA\Property(property: "expiresIn", type: "integer", example: 3600),
                        new OA\Property(
                            property: "user",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "nome", type: "string", example: "Dona Francisca"),
                                new OA\Property(property: "email", type: "string", example: "admin@raizesdonordeste.com.br"),
                                new OA\Property(property: "id_perfil", type: "integer", example: 5),
                                new OA\Property(property: "perfil", type: "string", example: "ADMIN")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Credenciais inválidas"),
            new OA\Response(response: 422, description: "Campos obrigatórios ausentes")
        ]
    )]
    public function login()
    {
        $dados = $this->request->getJSON(true);

        if (empty($dados['email']) || empty($dados['senha'])) {
            return erro_json(
                'CAMPOS_OBRIGATORIOS_AUSENTES',
                'Os campos "email" e "senha" são obrigatórios.',
                [
                    ['field' => 'email', 'issue' => empty($dados['email']) ? 'Campo obrigatório' : 'OK'],
                    ['field' => 'senha', 'issue' => empty($dados['senha']) ? 'Campo obrigatório' : 'OK'],
                ],
                422
            );
        }

        $usuario = $this->usuarioModel->buscarPorEmail(trim($dados['email']));

        if (!$usuario || (int)$usuario['ativo'] !== 1 || !password_verify($dados['senha'], $usuario['senha_hash'])) {
            return erro_json(
                'CREDENCIAIS_INVALIDAS',
                'E-mail ou senha inválidos.',
                [],
                401
            );
        }

        $token = $this->gerarTokenJwt($usuario);

        return $this->respond([
            'accessToken' => $token,
            'tokenType'   => 'Bearer',
            'expiresIn'   => 3600,
            'user'        => [
                'id'        => (int)$usuario['id'],
                'nome'      => $usuario['nome'],
                'email'     => $usuario['email'],
                'id_perfil' => (int)$usuario['id_perfil'],
                'perfil'    => $usuario['perfil_nome'] ?? 'CLIENTE',
            ],
        ], 200);
    }

    #[OA\Get(
        path: "/auth/dados",
        summary: "Obter dados do usuário autenticado via Bearer Token",
        tags: ["Autenticação"],
        responses: [
            new OA\Response(response: 200, description: "Dados do usuário autenticado"),
            new OA\Response(response: 401, description: "Token não informado ou inválido")
        ]
    )]
    public function dados()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        if (empty($authHeader) || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return erro_json('TOKEN_NAO_INFORMADO', 'Token Bearer ausente ou inválido.', [], 401);
        }

        $tokenValido = $this->validarTokenJwt($matches[1]);
        if (!$tokenValido) {
            return erro_json('TOKEN_INVALIDO', 'Token expirado ou assinatura inválida.', [], 401);
        }

        return $this->respond([
            'user' => $tokenValido,
        ], 200);
    }

    private function gerarTokenJwt(array $usuario): string
    {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode([
            'sub'       => $usuario['id'],
            'nome'      => $usuario['nome'],
            'email'     => $usuario['email'],
            'id_perfil' => $usuario['id_perfil'],
            'perfil'    => $usuario['perfil_nome'] ?? 'CLIENTE',
            'iat'       => time(),
            'exp'       => time() + 3600,
        ]));

        $chave = getenv('encryption.key') ?: 'raizes-do-nordeste-secret-key-2026';
        $assinatura = base64_encode(hash_hmac('sha256', "$header.$payload", $chave, true));

        return "$header.$payload.$assinatura";
    }

    private function validarTokenJwt(string $token): ?array
    {
        $partes = explode('.', $token);
        if (count($partes) !== 3) {
            return null;
        }

        [$header, $payload, $assinatura] = $partes;
        $chave = getenv('encryption.key') ?: 'raizes-do-nordeste-secret-key-2026';
        $assinaturaValida = base64_encode(hash_hmac('sha256', "$header.$payload", $chave, true));

        if (!hash_equals($assinaturaValida, $assinatura)) {
            return null;
        }

        $dados = json_decode(base64_decode($payload), true);
        if (!$dados || empty($dados['exp']) || $dados['exp'] < time()) {
            return null;
        }

        return $dados;
    }
}
