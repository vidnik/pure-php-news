<?php

namespace App\Controllers;

use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\Auth\Auth;
use App\Core\Utils\ErrorHandler;
use App\Core\Utils\Validator;
use App\Models\Category;
use App\Models\Comment;
use App\Models\News;
use Twig\Environment as Twig;

class PageController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Get('/')]
    public function indexPage(): string
    {
        $articles = (new News())->getArticlesInDiapason(limit:20);

        foreach ($articles as $key => $article) {
            $categories = (new News())->getArticleCategories($article["id"]);
            $article["categories"] = $categories;
            $articles[$key] = $article;
        }

        $categories = (new Category())->getAllCategories();

        return $this->twig->render('index.twig', ["articles" => $articles,
            'permissions'=>Auth::getUserPermissions(), "categories"=>$categories, 'session' => $_SESSION]);
    }

    #[Get('/about')]
    public function aboutPage(): string
    {

        $categories = (new Category())->getAllCategories();

        return $this->twig->render('pages/about.twig', ['permissions'=>Auth::getUserPermissions(),
            "categories"=>$categories, 'session' => $_SESSION]);
    }

    #[Get('/category')]
    public function categoryPage(): string
    {
        $_GET = filter_input_array(INPUT_GET);
        if (! array_key_exists('name', $_GET)) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }
        if (! (new Category())->doesCategoryExist($_GET["name"])) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }
        $category = (new Category())->getCategoryBySlug($_GET["name"]);
        $articles = (new Category())->getAllArticlesInCategory($_GET["name"], limit: 12);

        foreach ($articles as $key => $article) {
            $categories = (new News())->getArticleCategories($article["id"]);
            $article["categories"] = $categories;
            $articles[$key] = $article;
        }

        $categories = (new Category())->getAllCategories();

        return $this->twig->render('pages/category.twig', ["category" => $category,
            'permissions'=>Auth::getUserPermissions(), "articles"=>$articles, "categories"=>$categories,
            'session' => $_SESSION]);
    }

    #[Get('/recent')]
    public function recentPage(): string
    {
        $articles = (new News())->getArticlesInDiapason(limit: 24);

        foreach ($articles as $key => $article) {
            $categories = (new News())->getArticleCategories($article["id"]);
            $article["categories"] = $categories;
            $articles[$key] = $article;
        }

        $categories = (new Category())->getAllCategories();

        return $this->twig->render('pages/recent.twig', [
            'permissions'=>Auth::getUserPermissions(), "articles"=>$articles, "categories"=>$categories,
            'session' => $_SESSION]);
    }

    #[Get('/article')]
    public function articlePage(): string
    {
        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }
        if (! (new News())->doesArticleExist($_GET["id"])) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $article = (new News())->getArticleById($_GET["id"]);
        $article["categories"] = (new News())->getArticleCategories($article["id"]);
        $categories = (new Category())->getAllCategories();

        $comments = (new Comment())->getAllComments($_GET["id"]);

        foreach ($comments as $key => $comment) {
            $comment["replies"] = (new Comment())->getCommentReplies($comment["id"]);
            $comments[$key] = $comment;
        }

        return $this->twig->render('pages/article.twig', ["article" => $article,
            'permissions'=>Auth::getUserPermissions(), "categories"=>$categories,
            "comments" => $comments, 'session' => $_SESSION]);
    }

    #[Post('/search')]
    public function searchPage(): string
    {
        $_POST = filter_input_array(INPUT_POST);
        $query = trim($_POST['query']);

        $articles = (new News())->search($query);

        foreach ($articles as $key => $article) {
            $categories = (new News())->getArticleCategories($article["id"]);
            $article["categories"] = $categories;
            $articles[$key] = $article;
        }

        $categories = (new Category())->getAllCategories();

        return $this->twig->render('pages/search.twig', [
            'permissions'=>Auth::getUserPermissions(), "articles"=>$articles, "categories"=>$categories,
            "query"=>$query, 'session' => $_SESSION]);
    }
}
