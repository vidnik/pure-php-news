<?php

declare(strict_types = 1);

namespace App\Core;

use App\Core\Exceptions\Routing\RouteNotFoundException;
use App\Core\Utils\ErrorHandler;
use Dotenv\Dotenv;
use Symfony\Bridge\Twig\Extension\AssetExtension;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class App
{
    private static Database $db;

    public function __construct(
        protected Container $container,
        protected ?Router $router = null,
        protected array $request = [],
    ) {
    }

    public static function db(): Database
    {
        return static::$db;
    }

    public function boot(): static
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
        $config = new Config($_ENV);

        $loader = new FilesystemLoader(VIEW_PATH);
        $twig = new Environment($loader, [
            'cache' => STORAGE_PATH . '/cache',
            'auto_reload' => true
        ]);

        $versionStrategy = new EmptyVersionStrategy();
        $namedPackages = array(
            'assets' => new PathPackage('/assets', $versionStrategy),
            'upload' => new PathPackage('/upload', $versionStrategy),
            'images' => new PathPackage('/upload/images', $versionStrategy),
        );
        $defaultPackage = new Package($versionStrategy);
        $packages = new Packages($defaultPackage, $namedPackages);

        $twig->addExtension(new AssetExtension($packages));

        $this->container->set(Environment::class, fn()=>$twig);

        static::$db = new Database($config->db ?? []);

        session_start();

        return $this;
    }

    public function run(): void
    {
        try {
            echo $this->router->resolve($this->request['uri'], strtolower($this->request['method']));
        } catch (RouteNotFoundException) {
            echo $this->container->get(Environment::class)->render('error.twig', ErrorHandler::causeError(404));
        }
    }
}