<?php

namespace App\Core\Auth;

class StaffUser implements User
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
        return false;
    }

    public function canManageUsers():bool
    {
        return false;
    }
}