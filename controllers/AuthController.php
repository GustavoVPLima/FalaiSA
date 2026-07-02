<?php

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

        // O login usa comparação exata no banco; normalizamos espaços
        // para evitar falhas por entradas com espaços no início/fim.
        $username = $username !== null ? trim((string) $username) : '';
        $password = $password !== null ? trim((string) $password) : '';

        if (empty($username) || empty($password)) {
            $_SESSION['erro'] = 'Preencha todos os campos!';
            View::redirect('/login');
        }

        // Tentar como usuário normal
        $user = UsuarioDAO::findByLoginAndPassword($username, $password);

        // Se não encontrou, tentar como admin
        if (!$user) {
            $user = UsuarioDAO::findAdminByLoginAndPassword($username, $password);
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

    public function register()
    {
        if (Auth::isLoggedIn()) {
            View::redirect('/');
        }

        View::show('usuarios/cadastro');
    }

    public function registerPost()
    {
        if (!Request::isPost()) {
            View::redirect('/cadastro');
        }

        $username = Request::post('usuario');
        $email = Request::post('email');
        $password = Request::post('senha');
        $passwordConfirm = Request::post('senha_confirma');

        if (empty($username) || empty($email) || empty($password)) {
            $_SESSION['erro'] = 'Preencha todos os campos!';
            View::redirect('/cadastro');
        }

        if ($password !== $passwordConfirm) {
            $_SESSION['erro'] = 'Senhas não conferem!';
            View::redirect('/cadastro');
        }

        if (UsuarioDAO::findByLogin($username)) {
            $_SESSION['erro'] = 'Usuário já existe!';
            View::redirect('/cadastro');
        }

        $data = [
            'nm_login' => $username,
            'nm_email' => $email,
            'ds_senha' => $password,
        ];

        $userId = UsuarioDAO::create($data);

        if ($userId) {
            $_SESSION['sucesso'] = 'Conta criada com sucesso! Faça login.';
            View::redirect('/login');
        } else {
            $_SESSION['erro'] = 'Erro ao criar conta!';
            View::redirect('/cadastro');
        }
    }
}
