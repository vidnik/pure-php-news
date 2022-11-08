<?php

declare(strict_types = 1);

use App\Controllers\Admin\AdminCategoryController;
use App\Controllers\Admin\AdminController;
use App\Controllers\Admin\AdminGroupController;
use App\Controllers\Admin\AdminNewsController;
use App\Controllers\Admin\AdminUserController;
use App\Controllers\AuthController;
use App\Controllers\CommentController;
use App\Controllers\PageController;
use App\Core\App;
use App\Core\Container;
use App\Core\Router;

require_once __DIR__ . '/../vendor/autoload.php';

const STORAGE_PATH = __DIR__ . '/../storage';
const UPLOAD_PATH = __DIR__ . '/../web/upload';
const VIEW_PATH = __DIR__ . '/../Views';

$container = new Container();
$router    = new Router($container);

try {
    $router->registerControllerRoutes(
        [
            PageController::class,
            CommentController::class,
            AuthController::class,
            AdminController::class,
            AdminGroupController::class,
            AdminUserController::class,
            AdminNewsController::class,
            AdminCategoryController::class,
        ]
    );
} catch (ReflectionException $e) {
    echo 'It seems like there no one or few of this controllers';
}

(new App(
    $container,
    $router,
    ['uri' => $_SERVER['REQUEST_URI'], 'method' => $_SERVER['REQUEST_METHOD']],
))->boot()->run();
