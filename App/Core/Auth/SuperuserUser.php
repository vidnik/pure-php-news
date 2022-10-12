<?php

namespace App\Core\Auth;

class SuperuserUser implements User
{

    public function canManageOwnComments(): bool
    {
        return true;
    }

    public function canManageAllComments(): bool
    {
        return true;
    }

    public function canManageOwnNews(): bool
    {
        return true;
    }

    public function canManageAllNews(): bool
    {
        return true;
    }

    public function canManageUsers():bool
    {
        return true;
    }
}