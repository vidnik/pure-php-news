<?php

namespace App\Core\Auth;

use App\Core\View;
use App\Models\Auth\User;

class Authorization
{
    public static function authorize(): SuperuserUser|StaffUser|ActiveUser|GuestUser|RestrictedUser
    {
        if (! empty($_SESSION)) {
            $user = (new User())->getUserById($_SESSION['user_id']);
            if ($user["is_superuser"]) {
                return new SuperuserUser();
            } elseif ($user["is_staff"]) {
                return new StaffUser();
            } elseif ($user["is_active"]) {
                return new ActiveUser();
            } else {
                return new RestrictedUser();
            }
        } else {
            return new GuestUser();
        }
    }
}