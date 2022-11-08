<?php

namespace App\Controllers\Admin;

use App\Core\Attributes\Get;
use App\Core\Auth\Auth;
use App\Core\Utils\ErrorHandler;
use App\Models\User;
use Twig\Environment as Twig;

class AdminController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Get('/admin')]
    public function adminIndex(): string
    {
        if (! Auth::hasPermission('canManageUsers')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }
        header('Location: /admin/news');
        die();
    }
}
