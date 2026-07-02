<?php

class CommunityController
{
    public function index()
    {
        Auth::check();

        $userId = Auth::userId();
       $communities = ComunidadeDAO::getAllCommunities();

        View::show('comunidades/lista', [
            'communities' => $communities
        ]);
    }

    public function create()
    {
        Auth::check();

        if (Request::isPost()) {
            return $this->store();
        }

        View::show('comunidades/form');
    }

    public function store()
    {
        Auth::check();

        if (!Request::isPost()) {
            View::redirect('/criarcomunidade');
        }

        $name = Request::post('nome');
        $description = Request::post('descricao');
        $maxUsers = Request::post('max_usuarios') ?? 50;
        $userId = Auth::userId();

        if (empty($name) || empty($description)) {
            $_SESSION['erro'] = 'Preencha todos os campos!';
            View::redirect('/criarcomunidade');
        }

        $image = 'comunidade_placeholder.png';
        if (Request::hasFile('imagem')) {
            $file = Request::file('imagem');
            if (File::isAllowed($file['name'], 'imagem')) {
                $image = File::save($file, 'comunidades');
            }
        }

        $data = [
            'nm_comunidade' => $name,
            'ds_comunidade' => $description,
            'criado_por' => $userId,
            'max_usuario' => $maxUsers,
            'img_perfil' => $image
        ];

        $communityId = ComunidadeDAO::create($data);

        if ($communityId) {
            // Adicionar criador como membro
            ComunidadeDAO::addMember($userId, $communityId);
            
            $_SESSION['sucesso'] = 'Comunidade criada com sucesso!';
            View::redirect("/comunidade/$communityId");
        } else {
            $_SESSION['erro'] = 'Erro ao criar comunidade!';
            View::redirect('/criarcomunidade');
        }
    }

    public function show($id)
    {
        Auth::check();

        $userId = Auth::userId();
        $community = ComunidadeDAO::findById($id);

        if (!$community) {
            http_response_code(404);
            echo 'Comunidade não encontrada';
            exit;
        }

        if (!ComunidadeDAO::isMember($userId, $id)) {
            http_response_code(403);
            echo 'Você não é membro desta comunidade';
            exit;
        }

        $members = ComunidadeDAO::getMembers($id);

        View::show('comunidades/detalhes', [
            'community' => $community,
            'members' => $members
        ]);
    }

    public function edit($id)
    {
        Auth::check();

        $userId = Auth::userId();
        $community = ComunidadeDAO::findById($id);

        if (!$community) {
            http_response_code(404);
            exit('Comunidade não encontrada');
        }

        if ($community['criado_por'] != $userId) {
            http_response_code(403);
            exit('Você não tem permissão para editar esta comunidade');
        }

        if (Request::isPost()) {
            return $this->update($id);
        }

        View::show('comunidades/editar', [
            'community' => $community
        ]);
    }

    public function update($id)
    {
        Auth::check();

        $userId = Auth::userId();
        $community = ComunidadeDAO::findById($id);

        if ($community['criado_por'] != $userId) {
            http_response_code(403);
            exit('Sem permissão');
        }

        $name = Request::post('nome');
        $description = Request::post('descricao');
        $maxUsers = Request::post('max_usuarios') ?? 50;

        if (empty($name) || empty($description)) {
            $_SESSION['erro'] = 'Preencha todos os campos!';
            View::redirect("/comunidade/$id/editar");
        }

        $data = [
            'nm_comunidade' => $name,
            'ds_comunidade' => $description,
            'max_usuario' => $maxUsers
        ];

        if (Request::hasFile('imagem')) {
            $file = Request::file('imagem');
            if (File::isAllowed($file['name'], 'imagem')) {
                $image = File::save($file, 'comunidades');
                $data['img_perfil'] = $image;
            }
        }

        ComunidadeDAO::update($id, $data);

        $_SESSION['sucesso'] = 'Comunidade atualizada!';
        View::redirect("/comunidade/$id");
    }

    public function delete($id)
    {
        Auth::check();

        $userId = Auth::userId();
        $community = ComunidadeDAO::findById($id);

        if ($community['criado_por'] != $userId) {
            http_response_code(403);
            exit('Sem permissão');
        }

        ComunidadeDAO::delete($id);

        $_SESSION['sucesso'] = 'Comunidade deletada!';
        View::redirect('/comunidades');
    }

    public function joinCommunity($id)
    {
        Auth::check();

        $userId = Auth::userId();
        $community = ComunidadeDAO::findById($id);

        if (!$community) {
            http_response_code(404);
            exit('Comunidade não encontrada');
        }

        if (ComunidadeDAO::isMember($userId, $id)) {
            $_SESSION['erro'] = 'Você já é membro!';
            View::redirect("/comunidade/$id");
        }

        ComunidadeDAO::addMember($userId, $id);

        $_SESSION['sucesso'] = 'Você entrou na comunidade!';
        View::redirect("/comunidade/$id");
    }

    public function leaveCommunity($id)
    {
        Auth::check();

        $userId = Auth::userId();
        $community = ComunidadeDAO::findById($id);

        if ($community['criado_por'] == $userId) {
            $_SESSION['erro'] = 'O criador não pode sair da comunidade!';
            View::redirect("/comunidade/$id");
        }

        ComunidadeDAO::removeMember($userId, $id);

        $_SESSION['sucesso'] = 'Você saiu da comunidade!';
        View::redirect('/comunidades');
    }
}
