<?php

declare(strict_types = 1);

namespace App\Core\Exceptions\Routing;

class RouteNotFoundException extends \Exception
{
    protected $message = '404 Not Found';
}