<?php

namespace App\Core\Auth;

class Auth
{

    public static function hasPermission(string $permission): bool
    {
        if (! empty($_SESSION)) {
            $userPermissions = (new \App\Models\Auth())->getUserPermissions($_SESSION['user_id']);
            $filteredPermissions = [];
            foreach ($userPermissions as $userPermission) {
                $filteredPermissions[] = $userPermission["codename"];
            }
            return in_array($permission, $filteredPermissions);
        } else {
            return false;
        }
    }

    public static function getUserPermissions(): array
    {
        if (! empty($_SESSION)) {
            $userPermissions = (new \App\Models\Auth())->getUserPermissions($_SESSION['user_id']);
            $filteredPermissions = [];
            foreach ($userPermissions as $userPermission) {
                $filteredPermissions[] = $userPermission["codename"];
            }
            return $filteredPermissions;
        } else {
            return [];
        }
    }
}
