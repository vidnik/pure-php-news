<?php

namespace App\Controllers;

use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\Auth\Authorization;
use App\Core\Utils\Validator;
use App\Core\View;
use App\Models\Auth\User;
use Twig\Environment as Twig;

class AdminController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Get('/admin')]
    public function adminIndex(): string
    {
        $userAuth = Authorization::authorize();
        if ($userAuth->canManageUsers()) {
            $allUsers = (new User()) -> getAllUsers();
            return $this->twig->render('admin/index.twig', ['users'=>$allUsers]);
        } else {
            return $this->twig->render('error/403.twig');
        }
    }

    #[Get('/admin/user')]
    public function adminUserIndex(): string
    {
        $userAuth = Authorization::authorize();
        if (! $userAuth->canManageUsers()) {
            return $this->twig->render('error/403.twig');
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->adminIndex();
        }

        $user = (new User())->getUserById($_GET["id"]);
        if (!$user) {
            return $this->adminIndex();
        }

        return $this->twig->render('admin/user/index.twig', ['user' => $user,
            'errors' => ['usernameError' => '', 'emailError' => '']]);
    }

    #[Post('/admin/user/update')]
    public function adminUserUpdate(): string
    {
        $userAuth = Authorization::authorize();
        if (! $userAuth->canManageUsers()) {
            return $this->twig->render('error/403.twig');
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->adminIndex();
        }

        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'id' => trim($_GET['id']),
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'is_superuser' => intval(array_key_exists('is_superuser', $_POST)),
            'is_staff' => intval(array_key_exists('is_staff', $_POST)),
            'is_active' => intval(array_key_exists('is_active', $_POST))
        ];

        $errors = Validator::validateUsername($data);
        $errors = Validator::validateEmail($data, $errors);

        if (empty($data['usernameError']) && empty($data['emailError'])) {
            if ((new User())->updateUser($data)) {
                return $this->adminIndex();
            } else {
                return $this->twig->render('error/500.twig');
            }
        } else {
            return $this->twig->render('admin/user/index.twig', ['user' => $data, 'errors' => $errors]);
        }
    }
}

