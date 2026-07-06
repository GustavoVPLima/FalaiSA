<?php

class AdminController
{
    public function dashboard()
    {
        Auth::check(true);

        View::show('admin/dashboard');
    }

    public function users()
    {
        Auth::check(true);

        $users = UsuarioDAO::getAllUsers();

        View::show('admin/usuarios', [
            'users' => $users
        ]);
    }

    public function communities()
    {
        Auth::check(true);

        $communities = ComunidadeDAO::getAllCommunities();

        View::show('admin/comunidades', [
            'communities' => $communities
        ]);
    }

    public function deleteUser($id)
    {
        Auth::check(true);

        if (Request::isPost()) {
            UsuarioDAO::deleteUser($id);
            $_SESSION['sucesso'] = 'Usuário deletado!';
        }

        View::redirect('/admin/usuarios');
    }

    public function deleteCommunity($id)
    {
        Auth::check(true);

        if (Request::isPost()) {
            ComunidadeDAO::deletarComunidade($id);
            $_SESSION['sucesso'] = 'Comunidade deletada!';
        }


        View::redirect('/admin/comunidades');
    }

    public function reports()
    {
        Auth::check(true);

        $totalUsers = Database::query("SELECT COUNT(*) as total FROM tb_usuario");
        $usersRow = Database::fetchAssoc($totalUsers);
        $totalUsers = $usersRow['total'];

        $totalCommunities = Database::query("SELECT COUNT(*) as total FROM tb_comunidade");
        $communitiesRow = Database::fetchAssoc($totalCommunities);
        $totalCommunities = $communitiesRow['total'];

        $totalMessages = Database::query("SELECT COUNT(*) as total FROM tb_chat");
        $messagesRow = Database::fetchAssoc($totalMessages);
        $totalMessages = $messagesRow['total'];

        View::show('admin/relatorios', [
            'total_usuarios' => $totalUsers,
            'total_comunidades' => $totalCommunities,
            'total_mensagens' => $totalMessages
        ]);
    }
}
