<?php

namespace App\Controllers;


use App\Core\Attributes\Get;
use App\Core\View;
use App\Models\User;

class UserController
{
    #[Get('/users')]
    public function index(): View
    {
        $users = (new User)->all();

        return View::make('users/index', ['users' => $users]);
    }
}