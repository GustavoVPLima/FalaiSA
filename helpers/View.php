<?php

class View
{
    public static function render($template, $data = [])
    {
        extract($data);
        $templatePath = __DIR__ . '/../views/' . $template . '.php';

        if (!file_exists($templatePath)) {
            die("Template não encontrado: $template");
        }

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    public static function show($template, $data = [])
    {
        echo self::render($template, $data);
    }

    public static function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function redirect($path)
    {
        header("Location: $path");
        exit;
    }
}
