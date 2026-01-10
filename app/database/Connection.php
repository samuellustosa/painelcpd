<?php
namespace App\Database;

use PDO;
use PDOException;

class Connection {
    private static $instance;

    public static function getInstance() {
        if (!isset(self::$instance)) {
            try {
              
                self::$instance = new PDO("mysql:host=localhost;dbname=painelcpd", "root", "", [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]);
            } catch (PDOException $e) {
                die("Erro na conexão: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}