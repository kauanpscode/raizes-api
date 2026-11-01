<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // 1. Autenticação & Usuários
    $routes->post('auth/login', 'AuthController::login');
    $routes->get('auth/dados', 'AuthController::dados');

    // 2. Unidades
    $routes->get('unidades', 'UnidadesController::index');
    $routes->get('unidades/(:num)', 'UnidadesController::getUnidade/$1');
    $routes->post('unidades', 'UnidadesController::criaEmpresa');

    // 3. Cardápio de Produtos & Estoque
    $routes->get('produtos', 'ProdutosController::index');
    $routes->get('produtos/(:num)', 'ProdutosController::getProdutos/$1');

    // 4. Pedidos & Multicanalidade
    $routes->post('pedidos', 'PedidosController::create');
    $routes->get('pedidos', 'PedidosController::index');
    $routes->get('pedidos/(:num)', 'PedidosController::getPedidos/$1');
    $routes->patch('pedidos/(:num)/status', 'PedidosController::atualizarPedido/$1');

    // 5. Integração de Pagamento 
    $routes->post('pagamentos/mock', 'PagamentosController::processarMock');

    // 6. LGPD & Privacidade
    $routes->post('lgpd/consentimento', 'LgpdController::registrarConsentimento');

    // 7. Auditoria & Rastreabilidade
    $routes->get('auditoria/logs', 'AuditoriaController::index');
});
