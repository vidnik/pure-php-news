<?php

declare(strict_types = 1);

namespace App\Core\Enums;

enum Method: string
{
    case Get = 'get';
    case Post = 'post';
    case Put = 'put';
    case Head = 'head';
}