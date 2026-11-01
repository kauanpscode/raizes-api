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
}
