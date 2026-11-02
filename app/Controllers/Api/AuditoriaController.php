<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use OpenApi\Attributes as OA;

class AuditoriaController extends ResourceController
{
    protected $auditoriaModel;
    protected $helpers = ['script'];

    public function __construct()
    {
        $this->auditoriaModel = model('AuditoriaModel');
    }

    #[OA\Get(
        path: "/auditoria/logs",
        summary: "Consultar trilha de auditoria e rastreabilidade da franquia",
        description: "Lista o histórico de operações executadas no sistema para fins de governança e conformidade.",
        tags: ["Auditoria & Logs"],
        parameters: [
            new OA\Parameter(
                name: "entidade",
                in: "query",
                description: "Filtrar por entidade",
                required: false,
                schema: new OA\Schema(type: "string", example: "pedidos")
            ),
            new OA\Parameter(
                name: "acao",
                in: "query",
                description: "Filtrar por ação sensível",
                required: false,
                schema: new OA\Schema(type: "string", example: "PAGAMENTO_APROVADO")
            ),
            new OA\Parameter(
                name: "limit",
                in: "query",
                description: "Quantidade máxima de registros retornados (padrão: 50)",
                required: false,
                schema: new OA\Schema(type: "integer", example: 20)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Trilha de auditoria recuperada com sucesso"
            )
        ]
    )]
    public function index()
    {
        $entidade = $this->request->getGet('entidade');
        $acao     = $this->request->getGet('acao');
        $limit    = (int)($this->request->getGet('limit') ?: 50);

        if ($limit <= 0 || $limit > 100) {
            $limit = 50;
        }

        $logs = $this->auditoriaModel->getLogs($entidade, $acao, $limit);

        $logsFormatados = array_map(static function ($log) {
            $log['detalhes'] = !empty($log['detalhes_json']) ? json_decode($log['detalhes_json'], true) : null;
            unset($log['detalhes_json']);
            return $log;
        }, $logs);

        return $this->respond([
            'total' => count($logsFormatados),
            'limit' => $limit,
            'logs'  => $logsFormatados,
        ], 200);
    }
}
