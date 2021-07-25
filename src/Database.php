<?php

namespace App;

use InvalidArgumentException;
use PDO;
use PDOException;
class Database
{
    private PDO $connection;
    public function __construct(string $dsn, string $username = '', string $password = '') {
        try {
            $this->connection = new PDO($dsn, $username, $password);
        } catch (PDOException $exception) {
            throw new InvalidArgumentException('Database error: ' . $exception->getMessage());
        }
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}