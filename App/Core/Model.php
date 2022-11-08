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


    /**
     * Generates string with sql Values() for multiple records and execute full query
     * @param array $list
     * @param int $id
     * @param string $query
     * @return void
     */
    public function multipleInsert(array $list, int $id, string $query): void
    {
        $insertData = array();
        $insertQuery = array();
        foreach ($list as $element) {
            $insertQuery[] = '(?, ?)';
            $insertData[] = $id;
            $insertData[] = intval($element);
        }
        $query .= implode(', ', $insertQuery) . ';';
        $query = $this->db->prepare($query);
        $query->execute($insertData);
    }
}
