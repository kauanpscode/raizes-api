<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Grupo de rotas da API
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // 1. Autenticação
    $routes->post('auth/login', 'AuthController::login');

    $routes->get('unidades', 'UnidadesController::index');          // Listar todas as unidades
    $routes->get('unidades/(:num)', 'UnidadesController::show/$1'); // Detalhar uma unidade específica
    $routes->post('unidades', 'UnidadesController::create');      // Cadastrar nova unidade (Admin)

    // (Futuro) Estrutura para os próximos módulos do projeto:
    /*
    $routes->get('produtos', 'ProdutosController::index');
    $routes->get('produtos/(:num)', 'ProdutosController::show/$1');
    
    $routes->post('pedidos', 'PedidosController::create');          // Requer canalPedido
    $routes->get('pedidos', 'PedidosController::index');            // Filtro por ?canalPedido=...
    $routes->patch('pedidos/(:num)/status', 'PedidosController::updateStatus');
    
    $routes->post('pagamentos', 'PagamentosController::mock');
    */
});
