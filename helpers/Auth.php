<?php

class Auth
{
    public static function login($usuario)
    {
        // Salva o array completo do usuário
        $_SESSION['user'] = $usuario;
        
        // Dados principais
        $_SESSION['logado'] = true;
        $_SESSION['tipo_usuario'] = $usuario['tipo'] ?? 'usuario';
        $_SESSION['usuario'] = $usuario['nm_login'];
        $_SESSION['id'] = $usuario['id'] ?? $usuario['id_usuario'];
        $_SESSION['usuario_nome'] = $usuario['nm_login'];
        $_SESSION['usuario_email'] = $usuario['nm_email'];
        $_SESSION['foto_perfil'] = $usuario['img_perfil'] ?? 'perfilplaceholder.png';

        // Dados específicos do admin
        if (($usuario['tipo'] ?? '') === 'admin') {
            $_SESSION['idadm'] = $usuario['id_admin'] ?? null;
            $_SESSION['isadmin'] = $usuario['isadmin'] ?? false;
            $_SESSION['id_usuario'] = $usuario['id_usuario'] ?? null;
        } else {
            // Dados específicos do usuário comum
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
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
        return self::isLoggedIn() && ($_SESSION['tipo_usuario'] ?? '') === 'admin';
    }

    public static function userId()
    {
        return $_SESSION['id'] ?? null;
    }

    public static function username()
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function getUser()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function getFotoPerfil()
    {
        return $_SESSION['foto_perfil'] ?? 'perfilplaceholder.png';
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