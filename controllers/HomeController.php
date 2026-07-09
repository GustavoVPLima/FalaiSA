<?php

class HomeController
{
    public function index()
    {
        if (!Auth::isLoggedIn()) {
            View::redirect('/login');
        }

        $userId = Auth::userId();
        $communities = ComunidadeDAO::getByUser($userId);

        View::show('index', [
            'communities' => $communities
        ]);
    }

    public function about()
    {
        View::show('sobre');
    }

    public function admin()
    {
        Auth::check(true);

        View::show('admin/dashboard');
    }

    public function userProfile()
    {
        Auth::check();

        $userId = Auth::userId();
        $user = UsuarioDAO::findById($userId);

        View::show('usuarios/perfil', [
            'user' => $user
        ]);
    }

    public function editProfile()
    {
        Auth::check();

        $userId = Auth::userId();
        $user = UsuarioDAO::findById($userId);

        View::show('usuarios/editar', [
            'user' => $user
        ]);
    }

    public function updateProfile()
    {
        if (!Request::isPost()) {
            View::redirect('/perfil');
        }

        Auth::check();

        $userId = Auth::userId();
        $email = Request::post('email');

        $data = [
            'nm_email' => $email
        ];

        if (Request::hasFile('foto_perfil')) {
            $file = Request::file('foto_perfil');
            if (File::isAllowed($file['name'], 'imagem')) {
                $filename = File::save($file, 'usuarios');
                $data['img_perfil'] = $filename;
            }
        }

        UsuarioDAO::update($userId, $data);

        $_SESSION['sucesso'] = 'Perfil atualizado com sucesso!';
        View::redirect('/perfil');
    }

    public function changePassword()
    {
        Auth::check();

        $userId = Auth::userId();
        $user = UsuarioDAO::findById($userId);

        View::show('usuarios/alterar-senha', [
            'user' => $user
        ]);
    }

    public function updatePassword()
    {
        if (!Request::isPost()) {
            View::redirect('/perfil');
        }

        Auth::check();

        $userId = Auth::userId();

        $senhaAtual = Request::post('senha_atual');
        $novaSenha = Request::post('nova_senha');
        $confirmarSenha = Request::post('confirmar_senha');

        if (empty($senhaAtual) || empty($novaSenha) || empty($confirmarSenha)) {
            $_SESSION['erro'] = 'Preencha todos os campos!';
            View::redirect('/perfil/alterar-senha');
        }

        if ($novaSenha !== $confirmarSenha) {
            $_SESSION['erro'] = 'Senhas não conferem!';
            View::redirect('/perfil/alterar-senha');
        }

        $user = UsuarioDAO::findById($userId);
        if (!$user) {
            $_SESSION['erro'] = 'Usuário não encontrado!';
            View::redirect('/perfil');
        }

        // Auth usa comparação exata na tabela (ds_senha), então validamos também assim
        if ($user['ds_senha'] !== $senhaAtual) {
            $_SESSION['erro'] = 'Senha atual incorreta!';
            View::redirect('/perfil/alterar-senha');
        }

        UsuarioDAO::update($userId, [
            'ds_senha' => $novaSenha
        ]);

        $_SESSION['sucesso'] = 'Senha alterada com sucesso!';
        View::redirect('/perfil');
    }
}

