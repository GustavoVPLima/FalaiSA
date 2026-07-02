<?php

class ChatController
{
    public function show($id)
    {
        Auth::check();

        $userId = Auth::userId();
        $community = ComunidadeDAO::findById($id);

        if (!$community) {
            http_response_code(404);
            exit('Comunidade não encontrada');
        }

        if (!ComunidadeDAO::isMember($userId, $id)) {
            http_response_code(403);
            exit('Você não é membro');
        }

        $messages = MensagemDAO::getMessages($id);
        $members = ComunidadeDAO::getMembers($id);

        MensagemDAO::markAsRead($id, $userId);
        MensagemDAO::updateLastView($userId, $id);

        View::show('comunidades/chat', [
            'community' => $community,
            'messages' => $messages,
            'members' => $members
        ]);
    }

    public function getMessages($id)
    {
        Auth::check();

        $userId = Auth::userId();

        if (!ComunidadeDAO::isMember($userId, $id)) {
            http_response_code(403);
            exit;
        }

        $messages = MensagemDAO::getMessages($id);

        View::json(['messages' => $messages]);
    }

    public function getNewMessages($id)
    {
        Auth::check();

        $userId = Auth::userId();
        $lastId = Request::get('last_id', 0);

        if (!ComunidadeDAO::isMember($userId, $id)) {
            http_response_code(403);
            exit;
        }

        $messages = MensagemDAO::getNewMessages($id, $lastId);

        View::json(['messages' => $messages]);
    }

    public function sendMessage($id)
    {
        if (!Request::isPost()) {
            http_response_code(400);
            exit;
        }

        Auth::check();

        $userId = Auth::userId();

        if (!ComunidadeDAO::isMember($userId, $id)) {
            http_response_code(403);
            exit;
        }

        $message = Request::post('mensagem');
        $type = Request::post('tipo', 'texto');

        if (empty($message)) {
            View::json(['erro' => 'Mensagem vazia']);
            exit;
        }

        $data = [
            'id_chat_comunidade' => $id,
            'id_chat_usuario' => $userId,
            'mensagem' => $message,
            'tipo' => $type,
            'arquivo_url' => null
        ];

        if (Request::hasFile('arquivo')) {
            $file = Request::file('arquivo');
            if (File::isAllowed($file['name'], $type)) {
                $filename = File::save($file, 'chat');
                $data['arquivo_url'] = $filename;
            }
        }

        $messageId = MensagemDAO::create($data);

        View::json(['id' => $messageId, 'sucesso' => true]);
    }

    public function markAsRead($id)
    {
        if (!Request::isPost()) {
            http_response_code(400);
            exit;
        }

        Auth::check();

        $userId = Auth::userId();

        if (!ComunidadeDAO::isMember($userId, $id)) {
            http_response_code(403);
            exit;
        }

        MensagemDAO::markAsRead($id, $userId);
        MensagemDAO::updateLastView($userId, $id);

        View::json(['sucesso' => true]);
    }
}
