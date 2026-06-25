<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Request;
use App\Helpers\View;
use App\Models\Usuario;

class AuthController
{
    public function loginPage()
    {
        if (Auth::isLoggedIn()) {
            View::redirect(Auth::isAdmin() ? '/admin' : '/');
        }

        View::show('login');
    }

    public function login()
    {
        if (!Request::isPost()) {
            View::redirect('/login');
        }

        $username = Request::post('usuario');
        $password = Request::post('senha');

        if (empty($username) || empty($password)) {
            $_SESSION['erro'] = 'Preencha todos os campos!';
            View::redirect('/login');
        }

        // Tentar como usuário normal
        $user = Usuario::findByLoginAndPassword($username, $password);

        // Se não encontrou, tentar como admin
        if (!$user) {
            $user = Usuario::findAdminByLoginAndPassword($username, $password);
        }

        if ($user) {
            Auth::login($user);

            if ($user['tipo'] === 'admin') {
                View::redirect('/admin');
            } else {
                View::redirect('/');
            }
        } else {
            $_SESSION['erro'] = 'Usuário ou senha incorretos.';
            View::redirect('/login');
        }
    }

    public function logout()
    {
        Auth::logout();
    }
}
