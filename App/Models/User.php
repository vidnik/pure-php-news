<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function signUp(array $data):int
    {
        $query =  $this->db->prepare('INSERT INTO auth_user (password, username, email) 
             VALUES(:password, :username, :email)');

        $query->execute($data);

        return $this->db->lastInsertId();
    }

    /* Separate method because of defaults values of permissions fields */
    public function addUser(array $data):int
    {
        $query = $this->db->prepare('INSERT INTO auth_user (password, username, email) 
                                      VALUES(:password, :username, :email)');
        $query->execute($data);

        return $this->db->lastInsertId();
    }

    public function logIn(string $username, string $password): array|bool
    {
        $query = $this->db->prepare('SELECT * FROM auth_user WHERE username = :username');
        $query->execute(["username" => $username]);

        $row = $query->fetch();

        if (! $row) {
            return false;
        }

        $hashedPassword = $row["password"];

        if (password_verify($password, $hashedPassword)) {
            return $row;
        } else {
            return false;
        }
    }

    public function updateUser(array $data):bool
    {
        $query = $this->db->prepare('UPDATE auth_user SET username = :username, email = :email
                     WHERE id = :id');
        return $query->execute($data);
    }

    public function deleteUser(int $id): bool
    {
        $query =  $this->db->prepare('DELETE FROM auth_user WHERE id=:id');
        return $query->execute(["id"=>$id]);
    }

    public function doesUsernameExist(string $username): bool
    {
        $query =  $this->db->prepare('SELECT * FROM auth_user WHERE username = :username');
        $query->execute(["username" => $username]);

        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function doesEmailExist(string $email): bool
    {
        $query =  $this->db->prepare('SELECT * FROM auth_user WHERE email = :email');
        $query->execute(["email" => $email]);

        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getUserById(int $id): array|bool
    {
        $query =  $this->db->prepare('SELECT * FROM auth_user WHERE id = :id');

        $query->execute(["id" => $id]);
        return $query->fetch();
    }

    public function getAllUsers():array
    {
        $query =  $this->db->prepare(
            'SELECT id, password, username, email FROM auth_user'
        );
        $query->execute();
        return $query->fetchAll();
    }
}
