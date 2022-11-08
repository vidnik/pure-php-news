<?php

namespace App\Controllers\Admin;

use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\Auth\Auth;
use App\Core\Utils\ErrorHandler;
use App\Core\Utils\ImageHandler;
use App\Core\Utils\Validator;
use App\Models\Category;
use App\Models\News;
use Twig\Environment as Twig;

class AdminNewsController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Get('/admin/news')]
    public function adminNewsIndex(): string
    {
        if (! Auth::hasPermission('canManageAllNews')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }
        $allArticles = (new News()) -> getAllArticles();

        foreach ($allArticles as $key => $article) {
            $categories = (new News())->getArticleCategories($article["id"]);
            $article["categories"] = $categories;
            $allArticles[$key] = $article;
        }

        return $this->twig->render('admin/news/index.twig', ["articles" => $allArticles,
            'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
    }

    #[Get('/admin/news/add')]
    public function adminNewsAddIndex(): string
    {
        if (! Auth::hasPermission('canManageAllNews')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $article = [
            'title' => '',
            'text' =>  '',
            'date' => '',
            'image_path' => '',
        ];

        $errors = [
            'usernameError' => '',
            'emailError' => '',
            'passwordError' => '',
            'confirmPasswordError' => ''
        ];

        $article["categories"] = (new Category())->getAllCategories();

        return $this->twig->render('admin/news/add.twig', ["article" => $article, "errors" => $errors,
            'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
    }

    #[Post('/admin/news/add')]
    public function adminNewsAdd(): string
    {
        if (! Auth::hasPermission('canManageAllNews')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'title' => trim($_POST['title']),
            'text' => strip_tags(trim($_POST['inputText']), "<p><a><b><i><u><h2><img>"),
            'date' => $_POST['date']
        ];

        $categories = $_POST['categories'] ?? [];

        $errors = Validator::validateTitle($data);
        $errors = Validator::validateText($data, $errors);
        $errors = Validator::validateImage($errors);
        $errors = Validator::validateDate($data, $errors);

        if (empty($categories)) {
            $errors["categoriesError"] = "Select at least one category";
        }

        $serializedCategories = (new Category())->serializeArticleCategoriesByData($categories);
        $data['categories'] = $serializedCategories;

        if (empty($errors['titleError']) && empty($errors['imageError']) &&
            empty($errors['dateError']) && empty($errors["categoriesError"])) {
            $unique_name = ImageHandler::uploadImage();
            $data["image_path"] = $unique_name;
            $data["user"] = $_SESSION["user_id"];
            $articleId = ((new News())->addArticle($data));
            (new News())->updateArticleCategories($articleId, $categories);
            if ($_POST['action'] == "save") {
                header('Location: /admin/news');
                die();
            } elseif ($_POST['action'] == "save_and_add") {
                header('Location: /admin/news/add');
                die();
            } else {
                return $this->twig->render('error.twig', ErrorHandler::causeError(500));
            }
        } else {
            return $this->twig->render('/admin/news/add.twig', ["article" => $data, "errors" => $errors,
                'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
        }
    }

    #[Get('/admin/news/update')]
    public function adminNewsUpdateIndex(): string
    {
        if (! Auth::hasPermission('canManageAllNews')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            header('Location: /admin/news');
            die();
        }

        $article = (new News())->getArticleById($_GET["id"]);
        if (!$article) {
            header('Location: /admin/news');
            die();
        }

        $serializedCategories = (new News())->serializeArticleCategories($_GET["id"]);
        $article["categories"] = $serializedCategories;

        return $this->twig->render('admin/news/update.twig', ['article' => $article,
            'errors' => ['titleError' => '', 'imageError' => '', 'dateError' => ''],
            'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
    }

    #[Post('/admin/news/update')]
    public function adminNewsUpdate(): string
    {
        if (! Auth::hasPermission('canManageAllNews')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            header('Location: /admin/news');
            die();
        }

        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'id' => trim($_GET['id']),
            'title' => trim($_POST['title']),
            'text' => strip_tags(trim($_POST['inputText']), "<p><a><b><i><u><h2><img>"),
            'date' => $_POST['date']
        ];

        $categories = $_POST['categories'] ?? [];

        $errors = Validator::validateTitle($data);
        $errors = Validator::validateText($data, $errors);
        $errors = Validator::validateDate($data, $errors);
        $errors['imageError'] = '';

        if (empty($categories)) {
            $errors["categoriesError"] = "Select at least one category";
        }

        $serializedCategories = (new Category())->serializeArticleCategoriesByData($categories);
        $data['categories'] = $serializedCategories;

        $data["image_path"] = (new News())->getArticleById($data["id"])["image"];
        if (($_FILES["image"]["error"] !== 4)) {
            $errors = Validator::validateImage($errors);
            $unique_name = ImageHandler::uploadImage();
            ImageHandler::deleteImage($data["image_path"]);
            $data["image_path"] = $unique_name;
        }

        if (empty($errors['titleError']) && empty($errors['imageError']) &&
            empty($errors['dateError'])) {
            if ((new News())->updateArticle($data)) {
                (new News())->updateArticleCategories($data["id"], $categories);
                if ($_POST['action'] == "save") {
                    header('Location: /admin/news');
                    die();
                } elseif ($_POST['action'] == "save_and_continue") {
                    header('Location: /admin/news/update?id='.$data["id"]);
                    die();
                } else {
                    return $this->twig->render('error.twig', ErrorHandler::causeError(500));
                }
            } else {
                return $this->twig->render('error.twig', ErrorHandler::causeError(500));
            }
        } else {
            return $this->twig->render('admin/news/update.twig', ['article' => $data, 'errors' => $errors,
                'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
        }
    }

    #[Get('/admin/news/delete')]
    public function adminNewsDelete(): string
    {
        if (! Auth::hasPermission('canManageAllNews')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            header('Location: /admin/news');
            die();
        }

        $article_id = trim($_GET['id']);
        $article = (new News())->getArticleById($article_id);


        if ((new News())->deleteArticle($article)) {
            header('Location: /admin/news');
            die();
        } else {
            return $this->twig->render('error.twig', ErrorHandler::causeError(500));
        }
    }
}
