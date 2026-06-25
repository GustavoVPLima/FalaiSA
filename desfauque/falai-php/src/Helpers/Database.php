<?php

namespace App\Helpers;

use mysqli;

class Database
{
    private static $connection = null;

    public static function connect()
    {
        if (self::$connection === null) {
            $config = require __DIR__ . '/../../config/database.php';

            self::$connection = new mysqli(
                $config['host'],
                $config['user'],
                $config['password'],
                $config['database']
            );

            if (self::$connection->connect_error) {
                die('Erro de conexão: ' . self::$connection->connect_error);
            }

            self::$connection->set_charset($config['charset']);
        }

        return self::$connection;
    }

    public static function close()
    {
        if (self::$connection !== null) {
            self::$connection->close();
            self::$connection = null;
        }
    }

    public static function query($sql, $params = [])
    {
        $conn = self::connect();

        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new \Exception('Erro ao preparar: ' . $conn->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            return $stmt->get_result();
        }

        return $conn->query($sql);
    }

    public static function execute($sql, $params = [])
    {
        $conn = self::connect();

        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new \Exception('Erro ao preparar: ' . $conn->error);
            }

            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            return $stmt->execute();
        }

        return $conn->query($sql);
    }

    public static function lastInsertId()
    {
        return self::connect()->insert_id;
    }

    public static function fetchAssoc($result)
    {
        return $result->fetch_assoc();
    }

    public static function fetchAll($result)
    {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
