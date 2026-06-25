<?php
// exemplo-rotas.php - Exemplo de como adicionar mais rotas

// Exemplo 1: Rota simples (GET)
// $router->get('/sobre-nos', [HomeController::class, 'about']);

// Exemplo 2: Rota com parâmetro
// $router->get('/usuario/{id}', [UsuarioController::class, 'show']);

// Exemplo 3: Rota POST
// $router->post('/comunidade', [CommunityController::class, 'store']);

// Exemplo 4: API JSON
// $router->post('/api/mensagem/{id}', [ChatController::class, 'sendMessage']);

// Exemplo 5: Rota com múltiplos parâmetros
// $router->get('/comunidade/{id}/membro/{memberId}', [CommunityController::class, 'showMember']);

// ---

// Exemplo de Controller completo:
/*

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Request;
use App\Helpers\View;
use App\Models\Usuario;

class UsuarioController
{
    // GET /usuarios/1
    public function show($id)
    {
        Auth::check(); // Verifica autenticação
        
        $usuario = Usuario::findById($id);
        
        if (!$usuario) {
            http_response_code(404);
            exit('Usuário não encontrado');
        }
        
        View::show('usuario/show', [
            'usuario' => $usuario
        ]);
    }
    
    // POST /usuarios
    public function store()
    {
        Auth::check();
        
        if (!Request::isPost()) {
            View::redirect('/usuarios');
        }
        
        $data = [
            'nm_login' => Request::post('nm_login'),
            'ds_senha' => Request::post('ds_senha'),
            'nm_email' => Request::post('nm_email')
        ];
        
        try {
            $id = Usuario::create($data);
            $_SESSION['sucesso'] = 'Usuário criado com sucesso!';
            View::redirect('/usuarios/' . $id);
        } catch (\Exception $e) {
            $_SESSION['erro'] = $e->getMessage();
            View::redirect('/usuarios/novo');
        }
    }
    
    // GET /usuarios/1/editar
    public function edit($id)
    {
        Auth::check();
        
        $usuario = Usuario::findById($id);
        
        if (Request::isGet()) {
            View::show('usuario/edit', [
                'usuario' => $usuario
            ]);
            return;
        }
        
        // POST
        Usuario::update($id, [
            'nm_login' => Request::post('nm_login'),
            'nm_email' => Request::post('nm_email')
        ]);
        
        $_SESSION['sucesso'] = 'Usuário atualizado!';
        View::redirect('/usuarios/' . $id);
    }
}

*/
