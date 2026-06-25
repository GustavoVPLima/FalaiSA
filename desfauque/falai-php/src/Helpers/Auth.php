<?php

namespace App\Helpers;

class Auth
{
    public static function login($usuario)
    {
        $_SESSION['logado'] = true;
        $_SESSION['tipo_usuario'] = $usuario['tipo'];
        $_SESSION['usuario'] = $usuario['nm_login'];
        $_SESSION['id'] = $usuario['id'] ?? $usuario['id_usuario'];

        if ($usuario['tipo'] === 'admin') {
            $_SESSION['idadm'] = $usuario['id_admin'];
            $_SESSION['isadmin'] = $usuario['isadmin'];
        } else {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['foto_perfil'] = $usuario['img_perfil'] ?? 'perfilplaceholder.png';
        }
    }

    public static function logout()
    {
        session_destroy();
        header('Location: /login');
        exit;
    }

    public static function isLoggedIn()
    {
        return isset($_SESSION['logado']) && $_SESSION['logado'] === true;
    }

    public static function isAdmin()
    {
        return self::isLoggedIn() && $_SESSION['tipo_usuario'] === 'admin';
    }

    public static function userId()
    {
        return $_SESSION['id'] ?? null;
    }

    public static function username()
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function check($requiredAdmin = false)
    {
        if (!self::isLoggedIn()) {
            header('Location: /login');
            exit;
        }

        if ($requiredAdmin && !self::isAdmin()) {
            http_response_code(403);
            exit('Acesso negado');
        }
    }
}
