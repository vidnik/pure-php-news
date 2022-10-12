<?php

namespace App\Core\Auth;

class RestrictedUser implements User
{

    public function canManageOwnComments(): bool
    {
        return false;
    }

    public function canManageAllComments(): bool
    {
        return false;
    }

    public function canManageOwnNews(): bool
    {
        return false;
    }

    public function canManageAllNews(): bool
    {
        return false;
    }

    public function canManageUsers():bool
    {
        return false;
    }
}