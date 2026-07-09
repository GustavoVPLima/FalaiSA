<?php
// ===============================
// FALAÍ - PLATAFORMA DE COMUNIDADES
// Arquivo Principal - Router
// ===============================

// Iniciar sessão
session_start();

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// Carregar configurações
$config = require __DIR__ . '/config/app.php';

// Autoloader simples para helpers
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/helpers/' . $class . '.php';
    if (file_exists($file)) {
        require $file; 
    }
});

// Carregar todos os helpers
require __DIR__ . '/helpers/Database.php';
require __DIR__ . '/helpers/Auth.php';
require __DIR__ . '/helpers/Request.php';
require __DIR__ . '/helpers/View.php';
require __DIR__ . '/helpers/File.php';

// Autoloader para Models
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/models/' . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Carregar todos os DAOs
require __DIR__ . '/models/UsuarioDAO.php';
require __DIR__ . '/models/ComunidadeDAO.php';
require __DIR__ . '/models/MensagemDAO.php';

// Autoloader para Controllers
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/controllers/' . $class . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Carregar Router
require __DIR__ . '/src/Router.php';

// Importar controllers
require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/HomeController.php';
require __DIR__ . '/controllers/CommunityController.php';
require __DIR__ . '/controllers/ChatController.php';
require __DIR__ . '/controllers/AdminController.php';

// Criar roteador
$router = new Router();

// ========== Rotas de Autenticação ==========
$router->get('/login', ['AuthController', 'loginPage']);
$router->post('/login', ['AuthController', 'login']);
$router->get('/logout', ['AuthController', 'logout']);
$router->get('/cadastro', ['AuthController', 'register']);
$router->post('/cadastro', ['AuthController', 'registerPost']);

// ========== Rotas Públicas ==========
$router->get('/', ['HomeController', 'index']);
$router->get('/sobre', ['HomeController', 'about']);

// ========== Rotas de Usuários ==========
$router->get('/perfil', ['HomeController', 'userProfile']);
$router->get('/perfil/editar', ['HomeController', 'editProfile']);
$router->post('/perfil/atualizar', ['HomeController', 'updateProfile']);
$router->get('/perfil/alterar-senha', ['HomeController', 'changePassword']);
$router->post('/perfil/alterar-senha', ['HomeController', 'updatePassword']);


// ========== Rotas de Comunidades ==========
$router->get('/comunidades', ['CommunityController', 'index']);
$router->get('/criarcomunidade', ['CommunityController', 'create']);
$router->post('/criarcomunidade', ['CommunityController', 'create']);
$router->get('/comunidade/{id}', ['CommunityController', 'show']);
$router->get('/comunidade/{id}/editar', ['CommunityController', 'edit']);
$router->post('/comunidade/{id}/editar', ['CommunityController', 'edit']);
$router->post('/comunidade/{id}/deletar', ['CommunityController', 'delete']);
$router->get('/comunidade/{id}/entrar', ['CommunityController', 'joinCommunity']);
$router->get('/comunidade/{id}/sair', ['CommunityController', 'leaveCommunity']);

// ========== Rotas de Chat ==========
$router->get('/chat/{id}', ['ChatController', 'show']);
$router->get('/chat/{id}/mensagens', ['ChatController', 'getMessages']);
$router->get('/chat/{id}/novas', ['ChatController', 'getNewMessages']);
$router->post('/chat/{id}/enviar', ['ChatController', 'sendMessage']);
$router->post('/chat/{id}/visualizar', ['ChatController', 'markAsRead']);

// ========== Rotas de Admin ==========
$router->get('/admin', ['AdminController', 'dashboard']);
$router->get('/admin/usuarios', ['AdminController', 'users']);
$router->get('/admin/comunidades', ['AdminController', 'communities']);
$router->get('/admin/relatorios', ['AdminController', 'reports']);
$router->post('/admin/usuario/{id}/deletar', ['AdminController', 'deleteUser']);
$router->post('/admin/comunidade/{id}/deletar', ['AdminController', 'deleteCommunity']);

// ========== Dispatcher ==========
// Obter método HTTP e caminho
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remover barra final
if ($path !== '/' && substr($path, -1) === '/') {
    $path = substr($path, 0, -1);
}

// Dispatchar a rota
$router->dispatch($method, $path);
?>
