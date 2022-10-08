<?php

declare(strict_types = 1);

namespace App\Core;

use App\Core\Config;
use App\Core\Container;
use App\Core\Database;
use App\Core\Exceptions\Routing\RouteNotFoundException;
use App\Core\Router;
use App\Core\View;


class App
{
    private static Database $db;

    public function __construct(
        protected Container $container,
        protected Router $router,
        protected array $request,
        protected Config $config
    ) {
        static::$db = new Database($config->db ?? []);
    }

    public static function db(): Database
    {
        return static::$db;
    }

    public function run()
    {
        try {
            echo $this->router->resolve($this->request['uri'], strtolower($this->request['method']));
        } catch (RouteNotFoundException) {
            http_response_code(404);

            echo View::make('error/404');
        }
    }
}