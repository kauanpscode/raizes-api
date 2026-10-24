<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: "API Rede Raízes do Nordeste",
    version: "1.0.0",
    description: "Documentação da API para o Projeto Multidisciplinar Back-End"
)]
#[OA\Server(
    url: "http://localhost:8080/api",
    description: "Servidor Local"
)]
class UnidadesController extends ResourceController
{
    #[OA\Get(
        path: "/unidades",
        summary: "Listar todas as unidades",
        tags: ["Unidades"]
    )]
    #[OA\Response(
        response: 200,
        description: "Lista de unidades retornada com sucesso"
    )]
    public function index()
    {
        return $this->respond([
            ['id' => 1, 'nome' => 'Matriz Recife', 'cidade' => 'Recife/PE']
        ]);
    }
}