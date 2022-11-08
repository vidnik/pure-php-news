<?php

namespace App\Models;

use App\Core\Model;

class Auth extends Model
{
    public function getUserPermissions(int $userId): array
    {
        $query = $this->db->prepare('SELECT ap.codename, ap.name FROM auth_permission ap
                  LEFT JOIN auth_group_permissions agp on ap.id = agp.permission_id 
                  LEFT JOIN auth_group ag on ag.id = agp.group_id
                  LEFT JOIN auth_user_group aug on ag.id = aug.group_id where aug.user_id = :userId;');
        $query->execute(['userId' => $userId]);
        return $query->fetchAll();
    }

    public function addGroup(array $data): int
    {
        $query = $this->db->prepare('INSERT INTO auth_group (name, `default`) 
                                      VALUES(:name, :default)');
        $query->execute([$data["name"], $data["default"]]);

        return $this->db->lastInsertId();
    }

    public function updateGroupPermissions(int $groupId, array $permissions): void
    {
        $query = 'DELETE FROM auth_group_permissions WHERE group_id = ?' ;
        $query = $this->db->prepare($query);
        $query->execute([$groupId]);
        if (!empty($permissions)) {
            $query = 'INSERT INTO auth_group_permissions (group_id, permission_id) VALUES ';
            $this->multipleInsert($permissions, $groupId, $query);
        }
    }

    public function updateUserGroups(int $userId, array $groups): void
    {
        $query = 'DELETE FROM auth_user_group WHERE user_id = ?' ;
        $query = $this->db->prepare($query);
        $query->execute([$userId]);
        if (!empty($groups)) {
            $query = 'INSERT INTO auth_user_group (user_id, group_id) VALUES ';
            $this->multipleInsert($groups, $userId, $query);
        }
    }

    public function getAllPermissions(): array
    {
        $query = $this->db->prepare('SELECT id, codename, name FROM auth_permission');
        $query->execute();
        return $query->fetchAll();
    }

    public function getUserGroups(int $userId): array
    {
        $query = $this->db->prepare('SELECT auth_group.id, auth_group.name, auth_group.`default` FROM auth_group 
                                    left join auth_user_group aug on auth_group.id = aug.group_id
                                    left join auth_user au on au.id = aug.user_id where aug.user_id = ?;');
        $query->execute([$userId]);
        return $query->fetchAll();
    }

    public function serializeUserGroups(int $id): array
    {
        $userGroups = $this->getUserGroups($id);
        $allGroups = $this->getAllGroups();
        $serializedGroups = [];
        foreach ($allGroups as $group) {
            if (in_array($group, $userGroups)) {
                $group["active"] = true;
            } else {
                $group["active"] = false;
            }
            $serializedGroups[] = $group;
        }
        return $serializedGroups;
    }

    public function serializeUserGroupsByData(array $data): array
    {
        $allGroups = $this->getAllGroups();
        $serializedGroups = [];

        foreach ($allGroups as $element) {
            if (in_array($element["id"], $data)) {
                $group = [];
                $group["id"] = $element["id"];
                $group["name"] = $element["name"];
                $group["active"] = true;
            } else {
                $group["id"] = $element["id"];
                $group["name"] = $element["name"];
                $group["active"] = false;
            }
            $serializedGroups[] = $group;
        }
        return $serializedGroups;
    }

    public function getAllGroups(): array
    {
        $query = $this->db->prepare('SELECT id, name, `default` FROM auth_group');
        $query->execute();
        return $query->fetchAll();
    }

    public function getDefaultGroups(): array
    {
        $query = $this->db->prepare('SELECT id, name, `default` FROM auth_group where `default`=true');
        $query->execute();
        $groups = $query->fetchAll();
        $groups_list = [];
        foreach ($groups as $group) {
            $groups_list[] = $group["id"];
        }
        return $groups_list;
    }

    public function getGroupPermissions(int $id): array
    {
        $query = $this->db->prepare('SELECT ap.id, ap.codename, ap.name FROM auth_permission ap
                  LEFT JOIN auth_group_permissions agp on ap.id = agp.permission_id 
                  LEFT JOIN auth_group ag on ag.id = agp.group_id where ag.id = :id;');
        $query->execute(['id' => $id]);
        return $query->fetchAll();
    }

    public function getGroupById(int $id)
    {
        $query =  $this->db->prepare('SELECT * FROM auth_group WHERE id = :id');

        $query->execute(["id" => $id]);
        return $query->fetch();
    }

    public function serializeGroupPermissions(int $id): array
    {
        $groupPermissions = $this->getGroupPermissions($id);
        $allPermissions = $this->getAllPermissions();
        $serializedPermissions = [];
        foreach ($allPermissions as $permission) {
            if (in_array($permission, $groupPermissions)) {
                $permission["active"] = true;
            } else {
                $permission["active"] = false;
            }
            $serializedPermissions[] = $permission;
        }
        return $serializedPermissions;
    }

    public function serializeGroupPermissionsByData(array $data): array
    {
        $allPermissions = $this->getAllPermissions();
        $serializedPermissions = [];

        foreach ($allPermissions as $element) {
            if (in_array($element["id"], $data)) {
                $permission = [];
                $permission["id"] = $element["id"];
                $permission["codename"] = $element["codename"];
                $permission["name"] = $element["name"];
                $permission["active"] = true;
            } else {
                $permission["id"] = $element["id"];
                $permission["codename"] = $element["codename"];
                $permission["name"] = $element["name"];
                $permission["active"] = false;
            }
            $serializedPermissions[] = $permission;
        }
        return $serializedPermissions;
    }

    public function doesGroupNameExist(string $name): bool
    {
        $query =  $this->db->prepare('SELECT * FROM auth_group WHERE name = :name');
        $query->execute(["name" => $name]);

        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updateGroup(array $data):bool
    {
        $query = $this->db->prepare('UPDATE auth_group SET name = :name, `default` = :default
                     WHERE id = :id');
        return $query->execute([$data["name"], $data["default"], $data["id"]]);
    }

    public function deleteGroup(int $id): bool
    {
        $query =  $this->db->prepare('DELETE FROM auth_group WHERE id=:id');
        if ($query->execute([$id])) {
            return true;
        } else {
            return false;
        }
    }
}
