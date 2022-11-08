<?php

namespace App\Controllers;

use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\Auth\Auth;
use App\Core\Utils\ErrorHandler;
use App\Core\Utils\Validator;
use App\Models\Category;
use App\Models\Comment;
use Twig\Environment as Twig;

class CommentController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Post('/comment/add')]
    public function addComment(): string
    {
        if ((! Auth::hasPermission('canManageOwnComments')) &&
            (! Auth::hasPermission('canManageAllComments') )) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            header('Location: /');
            die();
        }

        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'id' => trim($_GET['id']),
            'title' => trim($_POST['title']),
            'text' => strip_tags(trim($_POST['text']), "<p><a><b><i><u><h2><img>"),
        ];

        $errors = Validator::validateTitle($data);
        $errors = Validator::validateText($data, $errors);

        #todo HANDLE THIS ERRORS!!!!!!
        if (empty($errors['titleError']) && empty($errors['textError'])) {
            $data["user"] = $_SESSION["user_id"];
            $commentId = ((new Comment())->addComment($data));
        }

        header('Location: /article?id='.$data["id"]);
        die();
    }

    #[Get('/comment/reply')]
    public function replyToCommentPage(): string
    {
        if ((! Auth::hasPermission('canManageOwnComments')) &&
            (! Auth::hasPermission('canManageAllComments') )) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }
        if (! (new Comment())->doesCommentExist($_GET["id"])) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $comment = (new Comment())->getCommentById($_GET["id"]);

        $categories = (new Category())->getAllCategories();

        return $this->twig->render('pages/reply.twig', ["comment" => $comment,
            'permissions'=>Auth::getUserPermissions(), "categories"=>$categories, 'session' => $_SESSION]);
    }

    #[Post('/comment/reply')]
    public function replyToComment(): string
    {
        if ((! Auth::hasPermission('canManageOwnComments')) &&
            (! Auth::hasPermission('canManageAllComments') )) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            header('Location: /');
            die();
        }

        $comment = (new Comment())->getCommentById($_GET["id"]);

        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'article_id' => $comment["article_id"],
            'title' => trim($_POST['title']),
            'text' => strip_tags(trim($_POST['text']), "<p><a><b><i><u><h2><img>"),
            "parent_id" => $comment["id"]
        ];

        $errors = Validator::validateTitle($data);
        $errors = Validator::validateText($data, $errors);

        # todo PLEASE, DON'T FORGET TO HANDLE IT!
        if (empty($errors['titleError']) && empty($errors['textError'])) {
            $data["user"] = $_SESSION["user_id"];
            $commentId = ((new Comment())->addReply($data));
            header('Location: /article?id='.$comment["article_id"]);
            die();
        } else {
            header('Location: /comment/reply?id='.$_GET["id"]);
            die();
        }
    }

    #[Get('/comment/edit')]
    public function editCommentPage(): string
    {
        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }
        if (! (new Comment())->doesCommentExist($_GET["id"])) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $comment = (new Comment())->getCommentById($_GET["id"]);

        if ((!Auth::hasPermission('canManageOwnComments') || $comment["user"] != $_SESSION["user_id"]) &&
            !Auth::hasPermission('canManageAllComments')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }
        $categories = (new Category())->getAllCategories();
        return $this->twig->render('pages/edit-comment.twig', ["comment" => $comment,
                'permissions' => Auth::getUserPermissions(), "categories" => $categories, 'session' => $_SESSION]);
    }

    #[Post('/comment/edit')]
    public function editComment(): string
    {
        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        if (! (new Comment())->doesCommentExist($_GET["id"])) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $comment = (new Comment())->getCommentById($_GET["id"]);

        if ((!Auth::hasPermission('canManageOwnComments') || $comment["user"] != $_SESSION["user_id"]) &&
            !Auth::hasPermission('canManageAllComments')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }


        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'id' => trim($_GET['id']),
            'title' => trim($_POST['title']),
            'text' => strip_tags(trim($_POST['text']), "<p><a><b><i><u><h2><img>"),
        ];

        $errors = Validator::validateTitle($data);
        $errors = Validator::validateText($data, $errors);

        if (empty($errors['titleError']) && empty($errors['textError'])) {
            $data["user"] = $_SESSION["user_id"];
            (new Comment())->editComment($data);
            header('Location: /article?id='.$comment["article_id"]);
        } else {
            header('Location: /comment/edit?id='.$_GET["id"]);
        }
        die();
    }

    #[Get('/comment/delete')]
    public function deleteComment(): string
    {
        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        if (! (new Comment())->doesCommentExist($_GET["id"])) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $comment = (new Comment())->getCommentById($_GET["id"]);

        if ((!Auth::hasPermission('canManageOwnComments') || $comment["user"] != $_SESSION["user_id"]) &&
            !Auth::hasPermission('canManageAllComments')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        (new Comment())->deleteComment($comment);
        header('Location: /article?id='.$comment["article_id"]);
        die();
    }
}
