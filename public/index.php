<?php

declare(strict_types = 1);

use App\Controllers\AdminController;
use App\Core\App;
use App\Controllers\NewsController;
use App\Controllers\AuthController;
use App\Core\Container;
use App\Core\Config;
use App\Core\Router;

require_once __DIR__ . '/../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

const STORAGE_PATH = __DIR__ . '/../storage';
const VIEW_PATH = __DIR__ . '/../Views';

session_start();

$container = new Container();
$router    = new Router($container);

$router->registerControllerRoutes(
    [
        NewsController::class,
        AuthController::class,
        AdminController::class
    ]
);

(new App(
    $container,
    $router,
    ['uri' => $_SERVER['REQUEST_URI'], 'method' => $_SERVER['REQUEST_METHOD']],
    new Config($_ENV)
))->run();