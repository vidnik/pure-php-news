<?php

namespace App\Core\Utils;

class ImageHandler
{
    public static function uploadImage(): string
    {
        $ext = pathinfo($_FILES['image']['name'])['extension'];
        $unique_name = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 25) . '.' . $ext;
        $upload_path = UPLOAD_PATH . '/images/' . $unique_name;
        move_uploaded_file($_FILES["image"]["tmp_name"], $upload_path);
        return $unique_name;
    }

    public static function deleteImage(string $imageName): bool
    {
        return unlink(UPLOAD_PATH . '/' . $imageName);
    }
}
