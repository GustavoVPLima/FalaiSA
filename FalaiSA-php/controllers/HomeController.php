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
}
