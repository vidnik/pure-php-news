<?php

namespace App\Core\Auth;

class ActiveUser implements User
{

    public function canManageOwnComments(): bool
    {
        return true;
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