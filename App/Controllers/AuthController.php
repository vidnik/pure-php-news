<?php

namespace App\Controllers;

use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\Auth\Auth;
use App\Core\Utils\Validator;
use App\Core\View;
use App\Models\Category;
use App\Models\User;
use Twig\Environment as Twig;

class AuthController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Get('/sign-up')]
    public function signUpIndex(): string
    {
        $data = [
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
        $categories = (new Category())->getAllCategories();
        return $this->twig->render('auth/sign-up.twig', ["data" => $data, "errors"=>$errors,
            'permissions'=>Auth::getUserPermissions(), "categories"=>$categories, 'session' => $_SESSION]);
    }

    #[Post('/sign-up')]
    public function signUp(): string
    {
        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'confirmPassword' => trim($_POST['confirmPassword']),
        ];
        $errors = Validator::validateUsername($data);
        $errors = Validator::validateEmail($data, $errors);
        $errors = Validator::validatePassword($data, $errors);

        $categories = (new Category())->getAllCategories();
        if (empty($errors['usernameError']) && empty($errors['emailError']) &&
            empty($errors['passwordError']) && empty($errors['confirmPasswordError'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            $userId =(new User())->signUp(['username'=>$data['username'], 'email'=>$data['email'],
                'password'=>$data['password']]) ;
            $defaultGroups = (new \App\Models\Auth())->getDefaultGroups();
            (new \App\Models\Auth())->updateUserGroups($userId, $defaultGroups);
            header('Location: /log-in');
            die();
        } else {
            return $this->twig->render('auth/sign-up.twig', ["data" => $data, "errors" => $errors,
                'permissions'=>Auth::getUserPermissions(), "categories"=>$categories, 'session' => $_SESSION]);
        }
    }

    #[Get('/log-in')]
    public function logInIndex(): string
    {
        $data = [
            'username' => '',
            'password' => ''
        ];
        $errors = [
            'usernameError' => '',
            'passwordError' => ''
        ];
        $categories = (new Category())->getAllCategories();
        return $this->twig->render('auth/log-in.twig', ["data" => $data, "errors" => $errors,
            'permissions'=>Auth::getUserPermissions(), "categories"=>$categories, 'session' => $_SESSION]);
    }

    #[Post('/log-in')]
    public function logIn(): string
    {
        $_POST = filter_input_array(INPUT_POST);

        $data = [
            'username' => trim($_POST['username']),
            'password' => trim($_POST['password']),
        ];

        $errors = Validator::validateLogInData($data);

        $categories = (new Category())->getAllCategories();

        if (empty($errors['usernameError']) && empty($errors['passwordError'])) {
            $loggedInUser = (new User())->logIn(username:$data['username'], password:$data['password']);

            if ($loggedInUser) {
                $this->createUserSession($loggedInUser);
                header('Location: /');
                die();
            } else {
                $errors['passwordError'] = 'Password or username is incorrect. Please try again.';

                return $this->twig->render('auth/log-in.twig', ["data" => $data, "errors"=>$errors,
                    'permissions'=>Auth::getUserPermissions(), "categories"=>$categories, 'session' => $_SESSION]);
            }
        } else {
            return $this->twig->render('auth/log-in.twig', ["data" => $data, "errors"=>$errors,
                'permissions'=>Auth::getUserPermissions(), "categories"=>$categories, 'session' => $_SESSION]);
        }
    }

    #[get('/log-out')]
    public function logOut(): string
    {
        if (!empty($_SESSION)) {
            if (session_destroy()) {
                header('Location: /');
                die();
            }
        }
        header('Location: /');
        die();
    }

    private function createUserSession($user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
    }

}