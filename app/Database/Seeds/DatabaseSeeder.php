<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $this->db->disableForeignKeyChecks();

        $this->db->table('auditoria_logs')->emptyTable();
        $this->db->table('pagamentos')->emptyTable();
        $this->db->table('itens_pedido')->emptyTable();
        $this->db->table('pedidos')->emptyTable();
        $this->db->table('estoque_unidade')->emptyTable();
        $this->db->table('produtos')->emptyTable();
        $this->db->table('consentimentos_lgpd')->emptyTable();
        $this->db->table('usuarios')->emptyTable();
        $this->db->table('perfis')->emptyTable();
        $this->db->table('unidades')->emptyTable();

        $this->db->enableForeignKeyChecks();

        $perfis = [
            [
                'id'         => 1,
                'nome'       => 'CLIENTE',
                'descricao'  => 'Cliente consumidor final (App, Totem, Balcão e Web)',
                'ativo'      => 1,
                'created_at' => $now,
            ],
            [
                'id'         => 2,
                'nome'       => 'ATENDENTE',
                'descricao'  => 'Atendente de balcão e operador de caixa da unidade',
                'ativo'      => 1,
                'created_at' => $now,
            ],
            [
                'id'         => 3,
                'nome'       => 'COZINHA',
                'descricao'  => 'Equipe de preparo e finalização de pedidos na cozinha',
                'ativo'      => 1,
                'created_at' => $now,
            ],
            [
                'id'         => 4,
                'nome'       => 'GERENTE',
                'descricao'  => 'Gerente operacional da filial/unidade',
                'ativo'      => 1,
                'created_at' => $now,
            ],
            [
                'id'         => 5,
                'nome'       => 'ADMIN',
                'descricao'  => 'Administrador geral da franqueadora / matriz',
                'ativo'      => 1,
                'created_at' => $now,
            ],
        ];
        $this->db->table('perfis')->insertBatch($perfis);

        $unidades = [
            [
                'id'            => 1,
                'codigo'        => 'REC-01',
                'razao_social'  => 'Raizes do Nordeste Alimentos Ltda',
                'nome_fantasia' => 'Raízes do Nordeste - Matriz Recife',
                'cnpj'          => '12345678000190',
                'telefone'      => '8133334444',
                'email'         => 'recife@raizesdonordeste.com.br',
                'cep'           => '50030230',
                'logradouro'    => 'Rua do Bom Jesus',
                'numero'        => '120',
                'complemento'   => 'Térreo',
                'bairro'        => 'Bairro do Recife',
                'cidade'        => 'Recife',
                'uf'            => 'PE',
                'tipo_unidade'  => 'MATRIZ',
                'ativa'         => 1,
                'created_at'    => $now,
            ],
            [
                'id'            => 2,
                'codigo'        => 'SSA-01',
                'razao_social'  => 'Raizes SSA Lanchonete Ltda',
                'nome_fantasia' => 'Raízes do Nordeste - Filial Salvador',
                'cnpj'          => '12345678000271',
                'telefone'      => '7132225555',
                'email'         => 'salvador@raizesdonordeste.com.br',
                'cep'           => '40020010',
                'logradouro'    => 'Avenida Sete de Setembro',
                'numero'        => '500',
                'complemento'   => 'Loja 2',
                'bairro'        => 'Vitória',
                'cidade'        => 'Salvador',
                'uf'            => 'BA',
                'tipo_unidade'  => 'FILIAL',
                'ativa'         => 1,
                'created_at'    => $now,
            ],
            [
                'id'            => 3,
                'codigo'        => 'FOR-01',
                'razao_social'  => 'Raizes Fortaleza Franquia Ltda',
                'nome_fantasia' => 'Raízes do Nordeste - Filial Fortaleza',
                'cnpj'          => '12345678000352',
                'telefone'      => '8531116666',
                'email'         => 'fortaleza@raizesdonordeste.com.br',
                'cep'           => '60165121',
                'logradouro'    => 'Avenida Beira Mar',
                'numero'        => '2200',
                'complemento'   => 'Quiosque 4',
                'bairro'        => 'Meireles',
                'cidade'        => 'Fortaleza',
                'uf'            => 'CE',
                'tipo_unidade'  => 'FILIAL',
                'ativa'         => 1,
                'created_at'    => $now,
            ],
        ];
        $this->db->table('unidades')->insertBatch($unidades);

        $usuarios = [
            [
                'id'         => 1,
                'id_perfil'  => 5,
                'nome'       => 'Dona Francisca (Admin Matriz)',
                'email'      => 'admin@raizesdonordeste.com.br',
                'senha_hash' => password_hash('Admin@123', PASSWORD_BCRYPT),
                'telefone'   => '81999990001',
                'unidade_id' => 1,
                'ativo'      => 1,
                'created_at' => $now,
            ],
            [
                'id'         => 2,
                'id_perfil'  => 4,
                'nome'       => 'Marcos Oliveira (Gerente Recife)',
                'email'      => 'gerente.recife@raizesdonordeste.com.br',
                'senha_hash' => password_hash('Gerente@123', PASSWORD_BCRYPT),
                'telefone'   => '81999990002',
                'unidade_id' => 1,
                'ativo'      => 1,
                'created_at' => $now,
            ],
            [
                'id'         => 3,
                'id_perfil'  => 2,
                'nome'       => 'Juliana Costa (Atendente Balcão)',
                'email'      => 'atendente.recife@raizesdonordeste.com.br',
                'senha_hash' => password_hash('Atendente@123', PASSWORD_BCRYPT),
                'telefone'   => '81999990003',
                'unidade_id' => 1,
                'ativo'      => 1,
                'created_at' => $now,
            ],
            [
                'id'         => 4,
                'id_perfil'  => 3,
                'nome'       => 'Mestre Tião (Chefe Cozinha)',
                'email'      => 'cozinha.recife@raizesdonordeste.com.br',
                'senha_hash' => password_hash('Cozinha@123', PASSWORD_BCRYPT),
                'telefone'   => '81999990004',
                'unidade_id' => 1,
                'ativo'      => 1,
                'created_at' => $now,
            ],
            [
                'id'         => 5,
                'id_perfil'  => 1,
                'nome'       => 'Maria Silva (Cliente App)',
                'email'      => 'cliente@exemplo.com',
                'senha_hash' => password_hash('Senha@123', PASSWORD_BCRYPT),
                'telefone'   => '81988887777',
                'unidade_id' => null,
                'ativo'      => 1,
                'created_at' => $now,
            ],
        ];
        $this->db->table('usuarios')->insertBatch($usuarios);

        $consentimento = [
            'id'           => 1,
            'usuario_id'   => 5,
            'termo_versao' => 'v1.0',
            'aceitou'      => 1,
            'finalidade'   => 'Consentimento explícito para dados de cadastro, fidelização e pedidos conforme LGPD',
            'ip_origem'    => '127.0.0.1',
            'canal'        => 'APP',
            'created_at'   => $now,
        ];
        $this->db->table('consentimentos_lgpd')->insert($consentimento);

        $produtos = [
            [
                'id'          => 1,
                'codigo'      => 'CUSC-01',
                'nome'        => 'Cuscuz Tradicional com Manteiga de Garrafa',
                'descricao'   => 'Cuscuz de milho fofo servido quentinho com generosa manteiga de garrafa sertaneja.',
                'categoria'   => 'Cuscuz',
                'preco'       => 12.00,
                'sazonal'     => 0,
                'ativo'       => 1,
                'created_at'  => $now,
            ],
            [
                'id'          => 2,
                'codigo'      => 'CUSC-02',
                'nome'        => 'Cuscuz Sertanejo Especial Recheado',
                'descricao'   => 'Cuscuz recheado com carne de sol desfiada na nata e queijo coalho tostado.',
                'categoria'   => 'Cuscuz',
                'preco'       => 22.90,
                'sazonal'     => 0,
                'ativo'       => 1,
                'created_at'  => $now,
            ],
            [
                'id'          => 3,
                'codigo'      => 'TAPI-01',
                'nome'        => 'Tapioca Rendada de Queijo Coalho e Carne Seca',
                'descricao'   => 'Massa artesanal de tapioca crocante com crosta de queijo coalho e carne seca acebolada.',
                'categoria'   => 'Tapiocas',
                'preco'       => 24.50,
                'sazonal'     => 0,
                'ativo'       => 1,
                'created_at'  => $now,
            ],
            [
                'id'          => 4,
                'codigo'      => 'TAPI-02',
                'nome'        => 'Tapioca Doce de Coco Fresco com Leite Condensado',
                'descricao'   => 'Tapioca quentinha recheada com coco ralado na hora e leite condensado.',
                'categoria'   => 'Tapiocas',
                'preco'       => 16.00,
                'sazonal'     => 0,
                'ativo'       => 1,
                'created_at'  => $now,
            ],
            [
                'id'          => 5,
                'codigo'      => 'SOBR-01',
                'nome'        => 'Bolo de Macaxeira Cremoso',
                'descricao'   => 'Fatia generosa do tradicional bolo de macaxeira artesanal com calda suave de caramelo.',
                'categoria'   => 'Sobremesas',
                'preco'       => 14.00,
                'sazonal'     => 0,
                'ativo'       => 1,
                'created_at'  => $now,
            ],
            [
                'id'          => 6,
                'codigo'      => 'BEBI-01',
                'nome'        => 'Suco Regional de Graviola 400ml',
                'descricao'   => 'Polpa natural pura de graviola batida geladinha.',
                'categoria'   => 'Bebidas',
                'preco'       => 9.50,
                'sazonal'     => 0,
                'ativo'       => 1,
                'created_at'  => $now,
            ],
            [
                'id'          => 7,
                'codigo'      => 'BEBI-02',
                'nome'        => 'Café Coado na Hora (Peneira de Pano)',
                'descricao'   => 'Café passado na hora em coador de pano tradicional, aroma marcante.',
                'categoria'   => 'Bebidas',
                'preco'       => 6.00,
                'sazonal'     => 0,
                'ativo'       => 1,
                'created_at'  => $now,
            ],
            [
                'id'          => 8,
                'codigo'      => 'JUNI-01',
                'nome'        => 'Canjica Junina Nordestina com Canela',
                'descricao'   => 'Canjica de milho verde com leite condensado e canela em pó (Edição Festas Juninas).',
                'categoria'   => 'Juninos',
                'preco'       => 11.50,
                'sazonal'     => 1,
                'ativo'       => 1,
                'created_at'  => $now,
            ],
        ];
        $this->db->table('produtos')->insertBatch($produtos);

        $estoque = [
            ['unidade_id' => 1, 'produto_id' => 1, 'quantidade' => 100, 'quantidade_minima' => 10, 'updated_at' => $now],
            ['unidade_id' => 1, 'produto_id' => 2, 'quantidade' => 50,  'quantidade_minima' => 10, 'updated_at' => $now],
            ['unidade_id' => 1, 'produto_id' => 3, 'quantidade' => 40,  'quantidade_minima' => 10, 'updated_at' => $now],
            ['unidade_id' => 1, 'produto_id' => 4, 'quantidade' => 35,  'quantidade_minima' => 10, 'updated_at' => $now],
            ['unidade_id' => 1, 'produto_id' => 5, 'quantidade' => 30,  'quantidade_minima' => 5,  'updated_at' => $now],
            ['unidade_id' => 1, 'produto_id' => 6, 'quantidade' => 60,  'quantidade_minima' => 15, 'updated_at' => $now],
            ['unidade_id' => 1, 'produto_id' => 7, 'quantidade' => 120, 'quantidade_minima' => 20, 'updated_at' => $now],
            ['unidade_id' => 1, 'produto_id' => 8, 'quantidade' => 1,   'quantidade_minima' => 5,  'updated_at' => $now],

            ['unidade_id' => 2, 'produto_id' => 1, 'quantidade' => 80,  'quantidade_minima' => 10, 'updated_at' => $now],
            ['unidade_id' => 2, 'produto_id' => 2, 'quantidade' => 45,  'quantidade_minima' => 10, 'updated_at' => $now],
            ['unidade_id' => 2, 'produto_id' => 3, 'quantidade' => 50,  'quantidade_minima' => 10, 'updated_at' => $now],
        ];
        $this->db->table('estoque_unidade')->insertBatch($estoque);

        $pedidos = [
            [
                'id'             => 1,
                'numero_pedido'  => 'PED-2026-0001',
                'unidade_id'     => 1,
                'usuario_id'     => 5,
                'canal_pedido'   => 'TOTEM',
                'status'         => 'PAGO',
                'valor_subtotal' => 34.90,
                'valor_desconto' => 0.00,
                'valor_total'    => 34.90,
                'observacoes'    => 'Pedido realizado no totem da matriz',
                'created_at'     => $now,
            ],
            [
                'id'             => 2,
                'numero_pedido'  => 'PED-2026-0002',
                'unidade_id'     => 1,
                'usuario_id'     => 5,
                'canal_pedido'   => 'APP',
                'status'         => 'AGUARDANDO_PAGAMENTO',
                'valor_subtotal' => 22.90,
                'valor_desconto' => 0.00,
                'valor_total'    => 22.90,
                'observacoes'    => 'Pedido antecipado para pick-up via App',
                'created_at'     => $now,
            ],
            [
                'id'             => 3,
                'numero_pedido'  => 'PED-2026-0003',
                'unidade_id'     => 1,
                'usuario_id'     => null,
                'canal_pedido'   => 'BALCAO',
                'status'         => 'ENTREGUE',
                'valor_subtotal' => 18.00,
                'valor_desconto' => 0.00,
                'valor_total'    => 18.00,
                'observacoes'    => 'Atendimento rápido no balcão',
                'created_at'     => $now,
            ],
        ];
        $this->db->table('pedidos')->insertBatch($pedidos);

        $itens = [
            [
                'pedido_id'      => 1,
                'produto_id'     => 1,
                'quantidade'     => 1,
                'preco_unitario' => 12.00,
                'subtotal'       => 12.00,
                'observacao'     => 'Caprichar na manteiga',
            ],
            [
                'pedido_id'      => 1,
                'produto_id'     => 2,
                'quantidade'     => 1,
                'preco_unitario' => 22.90,
                'subtotal'       => 22.90,
                'observacao'     => null,
            ],
            [
                'pedido_id'      => 2,
                'produto_id'     => 2,
                'quantidade'     => 1,
                'preco_unitario' => 22.90,
                'subtotal'       => 22.90,
                'observacao'     => null,
            ],
            [
                'pedido_id'      => 3,
                'produto_id'     => 5,
                'quantidade'     => 1,
                'preco_unitario' => 14.00,
                'subtotal'       => 14.00,
                'observacao'     => null,
            ],
        ];
        $this->db->table('itens_pedido')->insertBatch($itens);

        $pagamento = [
            'id'                => 1,
            'pedido_id'         => 1,
            'metodo'            => 'PIX',
            'status'            => 'APROVADO',
            'valor'             => 34.90,
            'transacao_mock_id' => 'MOCK-GW-REC-2026-9901',
            'mensagem_retorno'  => 'Pagamento processado com sucesso pelo gateway externo.',
            'payload_retorno'   => json_encode([
                'gateway'      => 'MockPay-Nordeste',
                'status'       => 'PAID',
                'authorizationCode' => 'AUTH-882190',
                'timestamp'    => $now,
            ]),
            'created_at'        => $now,
        ];
        $this->db->table('pagamentos')->insert($pagamento);

        $logs = [
            [
                'usuario_id'    => 5,
                'acao'          => 'CRIAR_PEDIDO',
                'entidade'      => 'pedidos',
                'entidade_id'   => '1',
                'canal'         => 'TOTEM',
                'ip'            => '127.0.0.1',
                'detalhes_json' => json_encode(['numero_pedido' => 'PED-2026-0001', 'total' => 34.90]),
                'created_at'    => $now,
            ],
            [
                'usuario_id'    => 5,
                'acao'          => 'PAGAMENTO_APROVADO',
                'entidade'      => 'pagamentos',
                'entidade_id'   => '1',
                'canal'         => 'TOTEM',
                'ip'            => '127.0.0.1',
                'detalhes_json' => json_encode(['transacao' => 'MOCK-GW-REC-2026-9901', 'status' => 'APROVADO']),
                'created_at'    => $now,
            ],
            [
                'usuario_id'    => 2,
                'acao'          => 'MUDAR_STATUS_PEDIDO',
                'entidade'      => 'pedidos',
                'entidade_id'   => '1',
                'canal'         => 'BALCAO',
                'ip'            => '127.0.0.1',
                'detalhes_json' => json_encode(['status_anterior' => 'AGUARDANDO_PAGAMENTO', 'novo_status' => 'PAGO']),
                'created_at'    => $now,
            ],
        ];
        $this->db->table('auditoria_logs')->insertBatch($logs);
    }
}
