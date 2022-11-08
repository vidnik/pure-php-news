<?php

namespace App\Controllers\Admin;

use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\Auth\Auth;
use App\Core\Utils\ErrorHandler;
use App\Core\Utils\StringUtils;
use App\Core\Utils\Validator;
use App\Models\Category;
use Twig\Environment as Twig;

class AdminCategoryController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Get('/admin/category')]
    public function adminCategoryIndex(): string
    {
        if (! Auth::hasPermission("canManageCategories")) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }
        $allCategories = (new Category())->getAllCategories();

        return $this->twig->render(
            'admin/category/index.twig',
            ['categories'=>$allCategories, 'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]
        );
    }

    #[Get('/admin/category/add')]
    public function adminCategoryAddIndex(): string
    {
        if (! Auth::hasPermission('canManageCategories')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $category = [
            'name' => '',
            'description' =>  '',
        ];

        $errors = [
            'nameError' => '',
            'textError' => ''
        ];

        return $this->twig->render('admin/category/add.twig', ["category" => $category, "errors" => $errors,
            'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
    }

    #[Post('/admin/category/add')]
    public function adminCategoryAdd(): string
    {
        if (! Auth::hasPermission('canManageCategories')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_POST = filter_input_array(INPUT_POST);
        $category = [
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
        ];

        $errors = Validator::validateCategoryName($category, categoryNameExistence: false);
        $errors = Validator::validateDescription($category, $errors);

        $category["slug"] = StringUtils::slugify($category["name"]);

        if (empty($errors['nameError']) && empty($errors["descriptionError"])) {
            $categoryId = (new Category())->addCategory($category);
            if ($_POST['action'] == "save") {
                header('Location: /admin/category');
                die();
            } elseif ($_POST['action'] == "save_and_add") {
                header('Location: /admin/category/add');
                die();
            } else {
                return $this->twig->render('error.twig', ErrorHandler::causeError(500));
            }
        } else {
            return $this->twig->render('admin/category/add.twig', ['category' => $category, 'errors' => $errors,
                'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
        }
    }

    #[Get('/admin/category/update')]
    public function adminCategoryUpdateIndex(): string
    {
        if (! Auth::hasPermission('canManageCategories')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $category = (new Category())->getCategoryById($_GET["id"]);
        if (!$category) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $data = ['category' => $category, 'errors' => ['nameError' => '', 'textError' => ''],
            'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION];

        return $this->twig->render('admin/category/update.twig', $data);
    }

    #[Post('/admin/category/update')]
    public function adminCategoryUpdate(): string
    {
        if (! Auth::hasPermission('canManageCategories')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $_POST = filter_input_array(INPUT_POST);
        $category = [
            'id' => trim($_GET['id']),
            'name' => trim($_POST['name']),
            'description' => trim($_POST['description']),
        ];

        $errors = Validator::validateCategoryName($category, categoryNameExistence: false);
        $errors = Validator::validateDescription($category, $errors);

        $category["slug"] = StringUtils::slugify($category["name"]);

        if (empty($errors['nameError']) && empty($errors['descriptionError'])) {
            if ((new Category())->updateCategory($category)) {
                header('Location: /admin/category');
                die();
            } else {
                return $this->twig->render('error.twig', ErrorHandler::causeError(500));
            }
        } else {
            return $this->twig->render('admin/category/update.twig', ['category' => $category, 'errors' => $errors,
                'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
        }
    }

    #[Get('/admin/category/delete')]
    public function adminCategoryDelete(): string
    {
        if (! Auth::hasPermission('canManageCategories')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            header('Location: /admin/category');
            die();
        }

        $category_id = trim($_GET['id']);

        if ((new Category())->deleteCategory($category_id)) {
            header('Location: /admin/category');
            die();
        } else {
            return $this->twig->render('error.twig', ErrorHandler::causeError(500));
        }
    }
}
