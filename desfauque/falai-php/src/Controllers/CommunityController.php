<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Request;
use App\Helpers\View;
use App\Helpers\File;
use App\Models\Comunidade;
use App\Models\Usuario;

class CommunityController
{
    public function index()
    {
        Auth::check();

        $usuario = Usuario::findById(Auth::userId());
        $comunidades = Comunidade::getByUser(Auth::userId());

        View::show('minhas_comunidades', [
            'usuario' => $usuario,
            'comunidades' => $comunidades
        ]);
    }

    public function create()
    {
        Auth::check();

        if (Request::isGet()) {
            View::show('criar_comunidade');
            return;
        }

        // POST
        $nome = Request::post('nome_comunidade');
        $descricao = Request::post('descricao') ?? 'Comunidade sem descrição';
        $maxUsuarios = Request::post('max_usuario');
        $semLimite = Request::post('sem_limite');

        if (empty($nome) || (empty($maxUsuarios) && empty($semLimite))) {
            $_SESSION['erro'] = 'Preencha todos os campos!';
            View::redirect('/criarcomunidade');
        }

        $maxUsuarios = $semLimite ? 0 : intval($maxUsuarios);

        if ($maxUsuarios > 0 && $maxUsuarios < 2) {
            $_SESSION['erro'] = 'Mínimo de usuários é 2';
            View::redirect('/criarcomunidade');
        }

        // Processar imagem
        $imagemPerfil = 'perfilplaceholder.png';

        if (Request::hasFile('imagem_comunidade')) {
            $file = Request::file('imagem_comunidade');

            if (File::isAllowed($file['name'], 'imagem')) {
                try {
                    $imagemPerfil = File::save($file, 'comunidades');
                } catch (\Exception $e) {
                    $_SESSION['erro'] = $e->getMessage();
                    View::redirect('/criarcomunidade');
                }
            } else {
                $_SESSION['erro'] = 'Tipo de arquivo não permitido!';
                View::redirect('/criarcomunidade');
            }
        }

        try {
            $comunidadeId = Comunidade::create([
                'nm_comunidade' => $nome,
                'criado_por' => Auth::userId(),
                'ds_comunidade' => $descricao,
                'max_usuario' => $maxUsuarios,
                'img_perfil' => $imagemPerfil
            ]);

            // Adicionar criador como membro
            Comunidade::addMember(Auth::userId(), $comunidadeId);

            $_SESSION['sucesso'] = 'Comunidade criada com sucesso!';
            View::redirect('/minhas-comunidades');
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao criar comunidade: ' . $e->getMessage();
            View::redirect('/criarcomunidade');
        }
    }

    public function edit($id)
    {
        Auth::check();

        $comunidade = Comunidade::findById($id);

        if (!$comunidade || $comunidade['criado_por'] != Auth::userId()) {
            http_response_code(403);
            exit('Acesso negado');
        }

        if (Request::isGet()) {
            View::show('editar_comunidade', [
                'comunidade' => $comunidade
            ]);
            return;
        }

        // POST
        $nome = Request::post('nome_comunidade');
        $descricao = Request::post('descricao');

        $updates = [
            'nm_comunidade' => $nome,
            'ds_comunidade' => $descricao
        ];

        // Processar imagem se enviada
        if (Request::hasFile('imagem_comunidade')) {
            $file = Request::file('imagem_comunidade');

            if (File::isAllowed($file['name'], 'imagem')) {
                try {
                    // Deletar imagem antiga se não for placeholder
                    if ($comunidade['img_perfil'] !== 'perfilplaceholder.png') {
                        File::delete($comunidade['img_perfil'], 'comunidades');
                    }

                    $updates['img_perfil'] = File::save($file, 'comunidades');
                } catch (\Exception $e) {
                    $_SESSION['erro'] = $e->getMessage();
                    View::redirect("/editar-comunidade/$id");
                }
            }
        }

        try {
            Comunidade::update($id, $updates);
            $_SESSION['sucesso'] = 'Comunidade atualizada com sucesso!';
            View::redirect('/minhas-comunidades');
        } catch (\Exception $e) {
            $_SESSION['erro'] = 'Erro ao atualizar comunidade';
            View::redirect("/editar-comunidade/$id");
        }
    }
}
