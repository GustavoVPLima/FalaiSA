<?php

namespace App\Models;

use App\Helpers\Database;

class Chat
{
    public static function create($data)
    {
        $sql = "INSERT INTO tb_chat 
                (id_chat_comunidade, id_chat_usuario, mensagem, tipo, arquivo_url, dt_envio)
                VALUES (?, ?, ?, ?, ?, NOW())";

        Database::execute($sql, [
            $data['id_chat_comunidade'],
            $data['id_chat_usuario'],
            $data['mensagem'],
            $data['tipo'],
            $data['arquivo_url'] ?? null
        ]);

        return Database::lastInsertId();
    }

    public static function getMessages($communityId, $limit = 50)
    {
        $sql = "SELECT 
                    c.*,
                    u.nm_login as usuario_nome,
                    u.img_perfil as usuario_avatar
                FROM tb_chat c
                INNER JOIN tb_usuario u ON c.id_chat_usuario = u.id_usuario
                WHERE c.id_chat_comunidade = ?
                ORDER BY c.dt_envio DESC
                LIMIT ?";

        $result = Database::query($sql, [$communityId, $limit]);
        $messages = Database::fetchAll($result);
        
        // Inverter para mostrar as mais antigas primeiro
        return array_reverse($messages);
    }

    public static function getNewMessages($communityId, $lastMessageId)
    {
        $sql = "SELECT 
                    c.*,
                    u.nm_login as usuario_nome,
                    u.img_perfil as usuario_avatar
                FROM tb_chat c
                INNER JOIN tb_usuario u ON c.id_chat_usuario = u.id_usuario
                WHERE c.id_chat_comunidade = ? AND c.id_chat > ?
                ORDER BY c.dt_envio ASC";

        $result = Database::query($sql, [$communityId, $lastMessageId]);
        return Database::fetchAll($result);
    }

    public static function markAsRead($communityId, $userId)
    {
        $sql = "UPDATE tb_chat 
                SET lida = TRUE 
                WHERE id_chat_comunidade = ? 
                AND id_chat_usuario != ? 
                AND lida = FALSE";

        return Database::execute($sql, [$communityId, $userId]);
    }

    public static function getLastView($userId, $communityId)
    {
        $sql = "SELECT ultima_visualizacao FROM tb_usuario_comunidade 
                WHERE id_usuario = ? AND id_comunidade = ?";

        $result = Database::query($sql, [$userId, $communityId]);
        return Database::fetchAssoc($result);
    }

    public static function updateLastView($userId, $communityId)
    {
        $sql = "UPDATE tb_usuario_comunidade 
                SET ultima_visualizacao = NOW()
                WHERE id_usuario = ? AND id_comunidade = ?";

        return Database::execute($sql, [$userId, $communityId]);
    }
}
