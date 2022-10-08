<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    public function all(): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, username
             FROM user'
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }
}
