<?php

declare(strict_types = 1);

namespace App\Core;

use PDOStatement;

abstract class Model
{
    protected Database $db;

    public function __construct()
    {
        $this->db = App::db();
    }

    public function fetchLazy(PDOStatement $stmt): \Generator
    {
        foreach($stmt as $record) {
            yield $record;
        }
    }
}