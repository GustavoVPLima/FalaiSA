<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Request;
use App\Helpers\View;
use App\Helpers\File;
use App\Models\Chat;
use App\Models\Comunidade;

class ChatController
{
    public function show($id)
    {
        Auth::check();

        if (Auth::isAdmin()) {
            View::redirect('/admin');
        }

        $comunidade = Comunidade::findById($id);

        if (!$comunidade) {
            http_response_code(404);
            exit('Comunidade não encontrada');
        }

        if (!Comunidade::isMember(Auth::userId(), $id)) {
            http_response_code(403);
            exit('Você não é membro desta comunidade');
        }

        $membros = Comunidade::getMembers($id);

        View::show('chat_comunidade', [
            'usuario' => Auth::username(),
            'comunidade' => $comunidade,
            'membros' => $membros,
            'total_membros' => count($membros)
        ]);
    }

    public function getMessages($id)
    {
        Auth::check();

        if (!Comunidade::isMember(Auth::userId(), $id)) {
            View::json(['success' => false, 'error' => 'Acesso negado']);
        }

        $mensagens = Chat::getMessages($id);

        View::json([
            'success' => true,
            'mensagens' => $mensagens
        ]);
    }

    public function getNewMessages($id)
    {
        Auth::check();

        if (!Comunidade::isMember(Auth::userId(), $id)) {
            View::json(['success' => false, 'error' => 'Acesso negado']);
        }

        $ultimaId = Request::get('ultima', 0);
        $mensagens = Chat::getNewMessages($id, $ultimaId);

        View::json([
            'success' => true,
            'mensagens' => $mensagens
        ]);
    }

    public function sendMessage($id)
    {
        Auth::check();

        if (!Request::isPost()) {
            View::json(['success' => false, 'error' => 'Método não permitido']);
        }

        if (!Comunidade::isMember(Auth::userId(), $id)) {
            View::json(['success' => false, 'error' => 'Acesso negado']);
        }

        $mensagem = trim(Request::post('mensagem', ''));
        $arquivo = Request::file('arquivo');

        if (empty($mensagem) && !Request::hasFile('arquivo')) {
            View::json(['success' => false, 'error' => 'Mensagem ou arquivo necessário']);
        }

        try {
            $tipo = 'texto';
            $arquivoUrl = null;

            // Processar arquivo
            if (Request::hasFile('arquivo')) {
                $filename = $arquivo['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

                if (File::isAllowed($filename, 'imagem')) {
                    $tipo = 'imagem';
                } elseif (File::isAllowed($filename, 'audio')) {
                    $tipo = 'audio';
                } else {
                    $tipo = 'arquivo';
                }

                $arquivoUrl = File::save($arquivo, 'chat');
            }

            // Criar mensagem
            $msgId = Chat::create([
                'id_chat_comunidade' => $id,
                'id_chat_usuario' => Auth::userId(),
                'mensagem' => $mensagem,
                'tipo' => $tipo,
                'arquivo_url' => $arquivoUrl
            ]);

            // Buscar mensagem criada
            $novasMensagens = Chat::getNewMessages($id, $msgId - 1);
            $mensagemEnviada = end($novasMensagens);

            View::json([
                'success' => true,
                'mensagem' => $mensagemEnviada
            ]);
        } catch (\Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function markAsRead($id)
    {
        Auth::check();

        if (!Request::isPost()) {
            View::json(['success' => false, 'error' => 'Método não permitido']);
        }

        if (!Comunidade::isMember(Auth::userId(), $id)) {
            View::json(['success' => false, 'error' => 'Acesso negado']);
        }

        try {
            Chat::markAsRead($id, Auth::userId());
            Chat::updateLastView(Auth::userId(), $id);

            View::json(['success' => true]);
        } catch (\Exception $e) {
            View::json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function stats($id)
    {
        Auth::check();

        if (!Comunidade::isMember(Auth::userId(), $id)) {
            View::json(['success' => false, 'error' => 'Acesso negado']);
        }

        $lastView = Chat::getLastView(Auth::userId(), $id);

        View::json([
            'success' => true,
            'ultima_visualizacao' => $lastView['ultima_visualizacao'] ?? null
        ]);
    }
}
