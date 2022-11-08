<?php

namespace App\Core\Utils;

use App\Models\Auth;
use App\Models\News;
use App\Models\User;

class Validator
{
    public static function validateUsername(array $data, array $errors = [], bool $usernameExistence = true): array
    {
        $usernameValidation = "/^[a-zA-Z0-9]*$/";
        $errors["usernameError"] = '';

        if (empty($data['username'])) {
            $errors['usernameError'] = 'Please enter username.';
        } elseif (!preg_match($usernameValidation, $data['username'])) {
            $errors['usernameError'] = 'Name can only contain letters and numbers.';
        } elseif (strlen($data['username']) > 150) {
            $errors['usernameError'] = 'Too many characters';
        } elseif ($usernameExistence) {
            if ((new User())->doesUsernameExist($data['username'])) {
                $errors['usernameError'] = 'Username is already taken.';
            }
        }
        return $errors;
    }

    public static function validateEmail(array $data, array $errors = [], bool $emailExistence = true): array
    {
        $errors['emailError'] = '';

        if (empty($data['email'])) {
            $errors['emailError'] = 'Please enter email address.';
        } elseif (strlen($data['email']) > 254) {
            $errors['emailError'] = 'Too many characters';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['emailError'] = 'Please enter the correct format.';
        } elseif ($emailExistence) {
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
        } elseif (strlen($data['password']) > 128) {
            $errors['passwordError'] = 'Too many characters';
        } elseif (strlen($data['password']) < 6) {
            $errors['passwordError'] = 'Password must be at least 8 characters';
        } elseif (preg_match($passwordValidation, $data['password'])) {
            $errors['passwordError'] = 'Password must be have at least one numeric value.';
        }

        if (empty($data['confirmPassword'])) {
            $errors['confirmPasswordError'] = 'Please enter password.';
        } elseif (strlen($data['confirmPassword']) > 128) {
            $errors['confirmPasswordError'] = 'Too many characters';
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
        } elseif (strlen($data['username']) > 150) {
            $errors['usernameError'] = 'Too many characters';
        }

        //Validate password
        if (empty($data['password'])) {
            $errors['passwordError'] = 'Please enter a password.';
        } elseif (strlen($data['password']) > 128) {
            $errors['passwordError'] = 'Too many characters';
        }
        return $errors;
    }

    public static function validateTitle(array $data, array $errors = []):array
    {
        $errors["titleError"] = '';

        if (empty($data['title'])) {
            $errors['titleError'] = 'Please enter a title.';
        } elseif (strlen($data['title']) > 255) {
            $errors['titleError'] = 'Too many characters';
        }
        return $errors;
    }

    public static function validateText(array $data, array $errors = []):array
    {
        $errors["textError"] = '';

        if (empty($data['text'])) {
            $errors['textError'] = 'Please enter some text.';
        }
        return $errors;
    }

    public static function validateDescription(array $data, array $errors = []):array
    {
        $errors["descriptionError"] = '';

        if (empty($data['description'])) {
            $errors['descriptionError'] = 'Please enter description.';
        }
        return $errors;
    }

    public static function validateDate(array $data, array $errors = []):array
    {
        $errors["dateError"] = '';

        if (empty($data['date'])) {
            $errors['dateError'] = 'Please enter a date.';
        }

        return $errors;
    }

    public static function validateImage(array $errors = []):array
    {
        $errors['imageError'] = '';

        if (!isset($_FILES["image"])) {
            $errors['imageError'] = "Please, select an image.";
            return $errors;
        }

        if (!file_exists($_FILES["image"]["tmp_name"])) {
            $errors['imageError'] = "I don't like this image, please, select another one.";
            return $errors;
        }

        $allowed_file_types = array(
            "image/png",
            "image/jpeg"
        );

        if (!in_array($_FILES["image"]["type"], $allowed_file_types)) {
            $errors['imageError'] = "Only PNG and JPEG image types are allowed.";
            return $errors;
        }

        $imageShape = getimagesize($_FILES["image"]["tmp_name"]);
        $width = $imageShape[0];
        $height = $imageShape[1];

        /*
        if ($width > "1280" || $height > "720") {
            $errors['imageError'] = "Select image with resolution less than 1280x720.";
            return $errors;
        } */

        if (($_FILES["image"]["size"] > 8000000)) {
            $errors['imageError'] = "Select image with size less than 8MB.";
            return $errors;
        }
        return $errors;
    }

    public static function validatesAsInt($number):bool
    {
        $number = filter_var($number, FILTER_VALIDATE_INT);
        return ($number !== false);
    }

    public static function validateGroupName(array $data, array $errors = [], bool $groupNameExistence = true): array
    {
        $groupNameValidation = "/^[a-zA-Z0-9 ]*$/";
        $errors["nameError"] = '';

        if (empty($data['name'])) {
            $errors['nameError'] = 'Please enter a group name.';
        } elseif (strlen($data['name']) > 150) {
            $errors['nameError'] = 'Too many characters';
        } elseif (!preg_match($groupNameValidation, $data['name'])) {
            $errors['nameError'] = 'Name can only contain letters and numbers.';
        } elseif ($groupNameExistence) {
            if ((new Auth())->doesGroupNameExist($data['name'])) {
                $errors['nameError'] = 'Group name is already taken.';
            }
        }
        return $errors;
    }

    public static function validateCategoryName(
        array $data,
        array $errors = [],
        bool $categoryNameExistence = true
    ): array {
        $categoryNameValidation = "/^[a-zA-Z0-9 ]*$/";
        $errors["nameError"] = '';

        if (empty($data['name'])) {
            $errors['nameError'] = 'Please enter a category name.';
        } elseif (!preg_match($categoryNameValidation, $data['name'])) {
            $errors['nameError'] = 'Name can only contain letters and numbers.';
        } elseif (strlen($data['name']) > 30) {
            $errors['nameError'] = 'Too many characters';
        } elseif ($categoryNameExistence) {
            if ((new News())->doesCategoryNameExist($data['name'])) {
                $errors['nameError'] = 'Category name is already taken.';
            }
        }
        return $errors;
    }
}
