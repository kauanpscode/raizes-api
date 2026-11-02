<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditoriaModel extends Model
{
    protected $table            = 'auditoria_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [];

    public function __construct()
    {
        parent::__construct();

        $colunasTabela = $this->db->getFieldNames($this->table);
        $this->allowedFields = $colunasTabela;
    }

    protected $useTimestamps = false;

    public function registrarLog(
        string $acao,
        string $entidade,
        ?string $entidadeId = null,
        ?int $usuarioId = null,
        ?string $canal = null,
        ?array $detalhes = []
    ): bool {
        $request = service('request');

        return (bool) $this->insert([
            'usuario_id'    => $usuarioId,
            'acao'          => strtoupper($acao),
            'entidade'      => strtolower($entidade),
            'entidade_id'   => $entidadeId,
            'canal'         => $canal ? strtoupper($canal) : null,
            'ip'            => $request->getIPAddress(),
            'detalhes_json' => !empty($detalhes) ? json_encode($detalhes, JSON_UNESCAPED_UNICODE) : null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Retorna os registros da trilha de auditoria com dados do usuário
     */
    public function getLogs(?string $entidade = null, ?string $acao = null, int $limit = 50)
    {
        $sql = "SELECT auditoria_logs.*, usuarios.nome as usuario_nome, usuarios.email as usuario_email 
                FROM auditoria_logs 
                LEFT JOIN usuarios ON usuarios.id = auditoria_logs.usuario_id 
                WHERE 1=1";

        $params = [];

        if (!empty($entidade)) {
            $sql .= " AND auditoria_logs.entidade = ?";
            $params[] = strtolower(trim($entidade));
        }

        if (!empty($acao)) {
            $sql .= " AND auditoria_logs.acao = ?";
            $params[] = strtoupper(trim($acao));
        }

        $sql .= " ORDER BY auditoria_logs.id DESC LIMIT ?";
        $params[] = $limit;

        $query = $this->db->query($sql, $params);
        return $query->getResultArray();
    }
}
