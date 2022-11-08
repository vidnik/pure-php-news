<?php

namespace App\Controllers\Admin;

use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\Auth\Auth;
use App\Core\Utils\ErrorHandler;
use App\Core\Utils\Validator;
use App\Models\User;
use Twig\Environment as Twig;

class AdminUserController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Get('/admin/user')]
    public function adminUserIndex(): string
    {
        if (! Auth::hasPermission('canManageUsers')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }
        $allUsers = (new User())->getAllUsers();

        foreach ($allUsers as $key => $user) {
            $groups =  (new \App\Models\Auth())->getUserGroups($user["id"]);
            $user["groups"] = $groups;
            $allUsers[$key] = $user;
        }

        return $this->twig->render(
            'admin/user/index.twig',
            ['users'=>$allUsers, 'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]
        );
    }

    #[Get('/admin/user/update')]
    public function adminUserUpdateIndex(): string
    {
        if (! Auth::hasPermission('canManageUsers')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $user = (new User())->getUserById($_GET["id"]);
        if (!$user) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $serializedGroups = (new \App\Models\Auth())->serializeUserGroups($_GET["id"]);

        return $this->twig->render('admin/user/update.twig', ['user' => $user, 'groups' => $serializedGroups,
            'errors' => ['usernameError' => '', 'emailError' => ''],
            'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
    }

    #[Post('/admin/user/update')]
    public function adminUserUpdate(): string
    {
        if (! Auth::hasPermission('canManageUsers')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'id' => trim($_GET['id']),
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
        ];
        $groups = $_POST['groups'] ?? [];

        $errors = Validator::validateUsername($data, usernameExistence: false);
        $errors = Validator::validateEmail($data, $errors, emailExistence: false);

        $serializedGroups = (new \App\Models\Auth())->serializeUserGroups($_GET["id"]);

        if (empty($errors['usernameError']) && empty($errors['emailError'])) {
            if ((new User())->updateUser($data)) {
                (new \App\Models\Auth())->updateUserGroups($data["id"], $groups);
                header('Location: /admin/user');
                die();
            } else {
                return $this->twig->render('error.twig', ErrorHandler::causeError(500));
            }
        } else {
            return $this->twig->render('admin/user/update.twig', ['user' => $data, 'groups' => $serializedGroups,
                'errors' => $errors,
                'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
        }
    }

    #[Get('/admin/user/add')]
    public function adminUserAddIndex(): string
    {
        if (! Auth::hasPermission('canManageUsers')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $user = [
            'username' => '',
            'email' =>  '',
            'password' => '',
            'confirmPassword' => '',
        ];

        $errors = [
            'usernameError' => '',
            'emailError' => '',
            'passwordError' => '',
            'confirmPasswordError' => ''
        ];

        $groups = (new \App\Models\Auth())->getAllGroups();

        return $this->twig->render('admin/user/add.twig', ["user" => $user, "errors" => $errors,
            'groups' => $groups, 'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
    }

    #[Post('/admin/user/add')]
    public function adminUserAdd(): string
    {
        if (! Auth::hasPermission('canManageUsers')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'confirmPassword' => trim($_POST['confirmPassword'])
        ];

        $groups = $_POST['groups'] ?? [];

        $errors = Validator::validateUsername($data);
        $errors = Validator::validateEmail($data, $errors);
        $errors = Validator::validatePassword($data, $errors);

        if (empty($errors['usernameError']) && empty($errors['emailError']) &&
            empty($errors['passwordError']) && empty($errors['confirmPasswordError'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $userId = (new User())->addUser(['username'=>$data['username'], 'email'=>$data['email'],
                'password'=>$data['password']]);
            (new \App\Models\Auth())->updateUserGroups($userId, $groups);
            if ($_POST['action'] == "save") {
                header('Location: /admin/user');
                die();
            } elseif ($_POST['action'] == "save_and_add") {
                header('Location: /admin/user/add');
                die();
            } else {
                return $this->twig->render('error.twig', ErrorHandler::causeError(500));
            }
        } else {
            $serializedGroups = (new \App\Models\Auth())->serializeUserGroupsByData($groups);
            return $this->twig->render('/admin/user/add.twig', ["user" => $data, "errors" => $errors,
                'groups'=>$serializedGroups, 'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
        }
    }

    #[Get('/admin/user/delete')]
    public function adminUserDelete(): string
    {
        if (! Auth::hasPermission('canManageUsers')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            header('Location: /admin/user');
            die();
        }

        $user_id = trim($_GET['id']);

        if ((new User())->deleteUser($user_id)) {
            header('Location: /admin/user');
            die();
        } else {
            return $this->twig->render('error.twig', ErrorHandler::causeError(500));
        }
    }
}
