<?php
// Arquivo principal da aplicação PHP

session_start();

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Autoloader simples
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Carregar Router
require __DIR__ . '/src/Router.php';

// Importar controllers
use App\Controllers\AuthController;
use App\Controllers\HomeController;
use App\Controllers\CommunityController;
use App\Controllers\ChatController;

// Criar roteador
$router = new Router();

// ========== Rotas de Autenticação ==========
$router->get('/login', [AuthController::class, 'loginPage']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

// ========== Rotas Públicas ==========
$router->get('/', [HomeController::class, 'index']);
$router->get('/sobre-nos', [HomeController::class, 'about']);

// ========== Rotas de Comunidades ==========
$router->get('/minhas-comunidades', [CommunityController::class, 'index']);
$router->get('/criarcomunidade', [CommunityController::class, 'create']);
$router->post('/criarcomunidade', [CommunityController::class, 'create']);
$router->get('/editar-comunidade/{id}', [CommunityController::class, 'edit']);
$router->post('/editar-comunidade/{id}', [CommunityController::class, 'edit']);

// ========== Rotas de Chat ==========
$router->get('/chatcomunidade/{id}', [ChatController::class, 'show']);
$router->get('/chatcomunidade/{id}/mensagens', [ChatController::class, 'getMessages']);
$router->get('/chatcomunidade/{id}/novas', [ChatController::class, 'getNewMessages']);
$router->post('/chatcomunidade/{id}/enviar', [ChatController::class, 'sendMessage']);
$router->post('/chatcomunidade/{id}/visualizar', [ChatController::class, 'markAsRead']);
$router->get('/chatcomunidade/{id}/estatisticas', [ChatController::class, 'stats']);

// Obter path da URL
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Dispatch
$router->dispatch($method, $path);
