<?php

namespace App\Controllers;


use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\View;
use App\Models\UserAuth;

class AuthController
{
    #[Get('/sign-up')]
    public function SignUpIndex(): View
    {
        $data = [
            'username' => '',
            'email' =>  '',
            'password' => '',
            'confirmPassword' => '',
            'usernameError' => '',
            'emailError' => '',
            'passwordError' => '',
            'confirmPasswordError' => ''
        ];
        return View::make('users/sign-up', ["data" => $data]);
    }

    #[Post('/sign-up')]
    public function signUp(): View
    {
        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password' => trim($_POST['password']),
            'confirmPassword' => trim($_POST['confirmPassword']),
            'usernameError' => '',
            'emailError' => '',
            'passwordError' => '',
            'confirmPasswordError' => ''
        ];
        $data = $this->validateSignUpData($data);

        if (empty($data['usernameError']) && empty($data['emailError']) && empty($data['passwordError']) && empty($data['confirmPasswordError'])) {

            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            if ((new UserAuth())->signUp($data)) {
                return View::make('users/log-in');
            } else {
                return View::make('error/500');
            }
        } else {
            return View::make('users/sign-up', ["data" => $data]);
        }
    }

    #[Get('/log-in')]
    public function LogInIndex(): View
    {
        $data = [
            'username' => '',
            'password' => '',
            'usernameError' => '',
            'passwordError' => ''
        ];
        return View::make('users/log-in', ["data" => $data]);
    }

    #[Post('/log-in')]
    public function LogIn(): View
    {
        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'username' => trim($_POST['username']),
            'password' => trim($_POST['password']),
            'usernameError' => '',
            'passwordError' => '',
        ];
        $data = $this->validateLogInData($data);

        if (empty($data['usernameError']) && empty($data['passwordError'])) {
            #$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            $loggedInUser = (new UserAuth())->logIn(username:$data['username'], password:$data['password']);

            if ($loggedInUser) {
                $this->createUserSession($loggedInUser);
                return View::make('index');

            } else {
                $data['passwordError'] = 'Password or username is incorrect. Please try again.';

                return View::make('users/log-in', ["data" => $data]);
            }
        } else {
            return View::make('users/log-in', ["data" => $data]);
        }
    }

    #[get('/log-out')]
    public function logOut(): View {
        if (!empty($_SESSION)) {
            if (session_destroy()) {
                return View::make('index');
            }
        }
        return View::make('index');
    }

    private function validateSignUpData(array $data):array
    {
        $nameValidation = "/^[a-zA-Z0-9]*$/";
        $passwordValidation = "/^(.{0,7}|[^a-z]*|[^\d]*)$/i";

        //Validate username on letters/numbers
        if (empty($data['username'])) {
            $data['usernameError'] = 'Please enter username.';
        } elseif (!preg_match($nameValidation, $data['username'])) {
            $data['usernameError'] = 'Name can only contain letters and numbers.';
        } else
            //Check if email exists.
            if ((new UserAuth())->doesUsernameExist($data['username'])) {
                $data['usernameError'] = 'Username is already taken.';
            }

        //Validate email
        if (empty($data['email'])) {
            $data['emailError'] = 'Please enter email address.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $data['emailError'] = 'Please enter the correct format.';
        } else {
            //Check if email exists.
            if ((new UserAuth())->doesEmailExist($data['email'])) {
                $data['emailError'] = 'Email is already taken.';
            }
        }

        // Validate password on length, numeric values,
        if(empty($data['password'])){
            $data['passwordError'] = 'Please enter password.';
        } elseif(strlen($data['password']) < 6){
            $data['passwordError'] = 'Password must be at least 8 characters';
        } elseif (preg_match($passwordValidation, $data['password'])) {
            $data['passwordError'] = 'Password must be have at least one numeric value.';
        }

        //Validate confirm password
        if (empty($data['confirmPassword'])) {
            $data['confirmPasswordError'] = 'Please enter password.';
        } else {
            if ($data['password'] != $data['confirmPassword']) {
                $data['confirmPasswordError'] = 'Passwords do not match, please try again.';
            }
        }
        return $data;
    }

    private function validateLogInData(array $data):array
    {
        if (empty($data['username'])) {
            $data['usernameError'] = 'Please enter a username.';
        }

        //Validate password
        if (empty($data['password'])) {
            $data['passwordError'] = 'Please enter a password.';
        }
        return $data;
    }

    private function createUserSession($user): void
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
    }

}