<?php

namespace App\Models;

use App\Helpers\Database;

class Comunidade
{
    public static function create($data)
    {
        $sql = "INSERT INTO tb_comunidade 
                (nm_comunidade, criado_por, ds_comunidade, max_usuario, img_perfil, dt_criacao)
                VALUES (?, ?, ?, ?, ?, NOW())";

        Database::execute($sql, [
            $data['nm_comunidade'],
            $data['criado_por'],
            $data['ds_comunidade'],
            $data['max_usuario'],
            $data['img_perfil']
        ]);

        return Database::lastInsertId();
    }

    public static function findById($id)
    {
        $sql = "SELECT 
                    c.*,
                    u.nm_login as nome_criador,
                    (SELECT COUNT(*) FROM tb_usuario_comunidade WHERE id_comunidade = c.id_comunidade) as total_membros
                FROM tb_comunidade c
                INNER JOIN tb_usuario u ON c.criado_por = u.id_usuario
                WHERE c.id_comunidade = ?";

        $result = Database::query($sql, [$id]);
        return Database::fetchAssoc($result);
    }

    public static function getMembers($id)
    {
        $sql = "SELECT 
                    u.id_usuario,
                    u.nm_login,
                    u.img_perfil
                FROM tb_usuario_comunidade uc
                INNER JOIN tb_usuario u ON uc.id_usuario = u.id_usuario
                WHERE uc.id_comunidade = ?
                ORDER BY u.nm_login";

        $result = Database::query($sql, [$id]);
        return Database::fetchAll($result);
    }

    public static function getByUser($userId)
    {
        $sql = "SELECT c.* FROM tb_comunidade c
                INNER JOIN tb_usuario_comunidade uc ON c.id_comunidade = uc.id_comunidade
                WHERE uc.id_usuario = ?
                ORDER BY c.nm_comunidade";

        $result = Database::query($sql, [$userId]);
        return Database::fetchAll($result);
    }

    public static function addMember($userId, $communityId)
    {
        $sql = "INSERT INTO tb_usuario_comunidade (id_usuario, id_comunidade) VALUES (?, ?)";
        return Database::execute($sql, [$userId, $communityId]);
    }

    public static function isMember($userId, $communityId)
    {
        $sql = "SELECT 1 FROM tb_usuario_comunidade WHERE id_usuario = ? AND id_comunidade = ?";
        $result = Database::query($sql, [$userId, $communityId]);
        return Database::fetchAssoc($result) !== null;
    }

    public static function update($id, $data)
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE tb_comunidade SET " . implode(', ', $fields) . " WHERE id_comunidade = ?";
        return Database::execute($sql, $values);
    }
}
