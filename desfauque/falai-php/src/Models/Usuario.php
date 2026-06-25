<?php

namespace App\Models;

use App\Helpers\Database;

class Usuario
{
    public static function findByLoginAndPassword($login, $password)
    {
        $result = Database::query(
            "SELECT *, 'usuario' as tipo FROM tb_usuario WHERE nm_login = ? AND ds_senha = ?",
            [$login, $password]
        );

        return Database::fetchAssoc($result);
    }

    public static function findAdminByLoginAndPassword($login, $password)
    {
        $result = Database::query(
            "SELECT *, 'admin' as tipo FROM tb_admin WHERE nm_login = ? AND ds_senha = ?",
            [$login, $password]
        );

        return Database::fetchAssoc($result);
    }

    public static function findById($id)
    {
        $result = Database::query(
            "SELECT * FROM tb_usuario WHERE id_usuario = ?",
            [$id]
        );

        return Database::fetchAssoc($result);
    }

    public static function create($data)
    {
        $sql = "INSERT INTO tb_usuario (nm_login, ds_senha, nm_email, img_perfil, dt_criacao)
                VALUES (?, ?, ?, ?, NOW())";

        Database::execute($sql, [
            $data['nm_login'],
            $data['ds_senha'],
            $data['nm_email'],
            $data['img_perfil'] ?? 'perfilplaceholder.png'
        ]);

        return Database::lastInsertId();
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

        $sql = "UPDATE tb_usuario SET " . implode(', ', $fields) . " WHERE id_usuario = ?";
        return Database::execute($sql, $values);
    }
}
