<?php

namespace App\Core;

use PDO;

class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $defaultOptions = [
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new PDO(
                $config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['database'],
                $config['user'],
                $config['pass'],
                $config['options'] ?? $defaultOptions
            );
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int) $e->getCode());
        }
        $this->initDatabase();
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->pdo, $name], $arguments);
    }

    private function initDatabase()
    {
        $query = $this->prepare('show tables;');
        $query->execute();
        if (count($query->fetchall()) == 0) {
            $sql = file_get_contents(STORAGE_PATH.'/schema.sql');
            $this->exec($sql);
        }
    }
}