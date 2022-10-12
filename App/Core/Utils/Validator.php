<?php

namespace App\Core\Utils;

use App\Models\Auth\User;

class Validator
{
    public static function validateUsername(array $data, array $errors = []): array
    {
        $usernameValidation = "/^[a-zA-Z0-9]*$/";
        $errors["usernameError"] = '';

        if (empty($data['username'])) {
            $errors['usernameError'] = 'Please enter username.';
        } elseif (!preg_match($usernameValidation, $data['username'])) {
            $errors['usernameError'] = 'Name can only contain letters and numbers.';
        } elseif ((new User())->doesUsernameExist($data['username'])) {
            $errors['usernameError'] = 'Username is already taken.';
        }
        return $errors;
    }

    public static function validateEmail(array $data, array $errors = []): array
    {
        $errors['emailError'] = '';

        if (empty($data['email'])) {
            $errors['emailError'] = 'Please enter email address.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['emailError'] = 'Please enter the correct format.';
        } else {
            //Check if email exists.
            if ((new User())->doesEmailExist($data['email'])) {
                $errors['emailError'] = 'Email is already taken.';
            }
        }
        return $errors;
    }

    public static function validatePassword(array $data, array $errors = []): array
    {
        $passwordValidation = "/^(.{0,7}|[^a-z]*|\D*)$/i";
        $errors['passwordError'] = '';

        if (empty($data['password'])) {
            $errors['passwordError'] = 'Please enter password.';
        } elseif (strlen($data['password']) < 6) {
            $errors['passwordError'] = 'Password must be at least 8 characters';
        } elseif (preg_match($passwordValidation, $data['password'])) {
            $errors['passwordError'] = 'Password must be have at least one numeric value.';
        }

        if (empty($data['confirmPassword'])) {
            $errors['confirmPasswordError'] = 'Please enter password.';
        } else {
            if ($data['password'] != $data['confirmPassword']) {
                $errors['confirmPasswordError'] = 'Passwords do not match, please try again.';
            }
        }
        return $errors;
    }

    public static function validateLogInData(array $data, array $errors = []):array
    {
        $errors['usernameError'] = '';
        $errors['passwordError'] = '';

        if (empty($data['username'])) {
            $errors['usernameError'] = 'Please enter a username.';
        }

        //Validate password
        if (empty($data['password'])) {
            $errors['passwordError'] = 'Please enter a password.';
        }
        return $errors;
    }
    public static function validatesAsInt($number):bool
    {
        $number = filter_var($number, FILTER_VALIDATE_INT);
        return ($number !== false);
    }

}