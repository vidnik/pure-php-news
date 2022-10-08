<?php

declare(strict_types = 1);

namespace App\Core\Exceptions\View;

class ViewNotFoundException extends \Exception
{
    protected $message = '404 Not Found';
}