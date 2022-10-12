<?php

namespace App\Core\Auth;

interface User
{
    public function canManageOwnComments():bool;
    public function canManageAllComments():bool;
    public function canManageOwnNews():bool;
    public function canManageAllNews():bool;
    public function canManageUsers():bool;
}
