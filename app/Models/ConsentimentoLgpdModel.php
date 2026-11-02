<?php

namespace App\Models;

use CodeIgniter\Model;

class ConsentimentoLgpdModel extends Model
{
    protected $table            = 'consentimentos_lgpd';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    protected $allowedFields    = [
        'usuario_id',
        'termo_versao',
        'aceitou',
        'finalidade',
        'ip_origem',
        'canal',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Registra o consentimento de um usuário e gera log de auditoria
     */
    public function registrarConsentimento(array $dados): array
    {
        $registroId = $this->insert([
            'usuario_id'   => $dados['usuario_id'],
            'termo_versao' => $dados['termo_versao'] ?? 'v1.0',
            'aceitou'      => $dados['aceitou'] ? 1 : 0,
            'finalidade'   => $dados['finalidade'] ?? 'Tratamento de dados pessoais para pedidos e programa de fidelização conforme LGPD (Art. 7º, I da Lei 13.709/2018)',
            'ip_origem'    => $dados['ip_origem'] ?? null,
            'canal'        => $dados['canal'] ?? 'APP',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $auditoriaModel = model('AuditoriaModel');
        $auditoriaModel->registrarLog(
            'REGISTRAR_CONSENTIMENTO_LGPD',
            'consentimentos_lgpd',
            (string)$registroId,
            $dados['usuario_id'],
            $dados['canal'] ?? 'APP',
            [
                'termo_versao' => $dados['termo_versao'] ?? 'v1.0',
                'aceitou'      => (bool)$dados['aceitou'],
                'ip'           => $dados['ip_origem'] ?? null
            ]
        );

        return $this->find($registroId);
    }

    /**
     * Retorna o histórico de consentimentos de um usuário
     */
    public function getConsentimentosPorUsuario(int $usuarioId)
    {
        return $this->where('usuario_id', $usuarioId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }
}
