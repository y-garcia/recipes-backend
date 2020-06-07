<?php
/**
 * Author: Yeray García Quintana
 * Date: 25.11.2018
 */

namespace Recipes\db;


use PDO;
use PDOException;
use Recipes\config\Config;

class Database
{
    private $pdo;

    /**
     * Call this method to get singleton
     *
     * @return PDO
     */
    public static function getInstance()
    {
        static $inst = null;
        if ($inst === null) {
            $inst = new Database();
        }
        return $inst->getPDO();
    }

    /**
     * Private constructor so nobody else can instantiate it
     */
    private function __construct()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . "; charset=UTF8",
                Config::DB_USER,
                Config::DB_PASS
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->pdo = null;
        }
    }

    /**
     * @return PDO
     */
    private function getPDO()
    {
        return $this->pdo;
    }
}
