<?php

namespace App\Core\Utils;

class ErrorHandler
{
    public static function causeError(int $errorCode): array
    {
        $errorMessage = match ($errorCode) {
            400 => "Bad request",
            403 => "Forbidden",
            404 => "Page not found",
            500 => "Internal server error",
        };
        http_response_code($errorCode);
        return ["errorCode" => $errorCode, "errorMessage" => $errorMessage];
    }
}