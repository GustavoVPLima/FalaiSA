<?php

class File
{
    public static function isAllowed($filename, $type)
    {
        $config = require __DIR__ . '/../config/app.php';
        
        if (!isset($config['allowed_extensions'][$type])) {
            return false;
        }

        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return in_array(strtolower($ext), $config['allowed_extensions'][$type]);
    }

    public static function save($file, $folder)
    {
        $config = require __DIR__ . '/../config/app.php';

        if ($file['size'] > $config['max_file_size']) {
            throw new Exception('Arquivo muito grande (máx 10MB)');
        }

        $uploadPath = __DIR__ . '/../' . $config['upload_folders'][$folder];

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $filename = $file['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $newFilename = uniqid() . '_' . time() . '.' . $ext;
        $fullPath = $uploadPath . '/' . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            return $newFilename;
        }

        throw new Exception('Erro ao salvar arquivo');
    }

    public static function delete($filename, $folder)
    {
        $config = require __DIR__ . '/../config/app.php';
        $path = __DIR__ . '/../' . $config['upload_folders'][$folder] . '/' . $filename;

        if (file_exists($path)) {
            unlink($path);
            return true;
        }

        return false;
    }
}
