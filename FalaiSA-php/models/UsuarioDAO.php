<?php

class UsuarioDAO
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

    public static function findByLogin($login)
    {
        $result = Database::query(
            "SELECT * FROM tb_usuario WHERE nm_login = ?",
            [$login]
        );

        return Database::fetchAssoc($result);
    }

    public static function create($data)
    {
        $sql = "INSERT INTO tb_usuario (nm_login, ds_senha, nm_email, nr_numero, img_perfil, dt_cadastro)
                VALUES (?, ?, ?, ?, ?, NOW())";

        Database::execute($sql, [
            $data['nm_login'],
            $data['ds_senha'],
            $data['nm_email'],
            $data['nr_numero'],
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

    public static function getAllUsers()
    {
        $result = Database::query("SELECT * FROM tb_usuario ORDER BY nm_login");
        return Database::fetchAll($result);
    }

    public static function deleteUser($id)
    {
        return Database::execute("DELETE FROM tb_usuario WHERE id_usuario = ?", [$id]);
    }
}
