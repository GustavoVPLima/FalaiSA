<?php

class Request
{
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public static function isPost()
    {
        return self::method() === 'POST';
    }

    public static function isGet()
    {
        return self::method() === 'GET';
    }

    public static function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    public static function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    public static function input($key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public static function file($key)
    {
        return $_FILES[$key] ?? null;
    }

    public static function hasFile($key)
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    public static function all()
    {
        return array_merge($_GET, $_POST);
    }
}
