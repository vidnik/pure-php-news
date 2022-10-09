<?php

namespace App\Controllers;

use App\Core\Attributes\Get;
use App\Core\View;
use App\Models\UserAuth;

class AdminController
{
    #[Get('/admin')]
    public function AdminIndex(): View
    {
        if (! empty($_SESSION)) {
            $user = (new UserAuth())->getUserById($_SESSION['user_id']);
            if (! $user["is_superuser"]){
                return View::make('error/403');
            } else {
                return View::make('admin/index');
            }
        } else {
            return (new AuthController)->LogInIndex();
        }
    }
}