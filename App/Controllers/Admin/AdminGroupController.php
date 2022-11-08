<?php

namespace App\Controllers\Admin;

use App\Core\Attributes\Get;
use App\Core\Attributes\Post;
use App\Core\Auth\Auth;
use App\Core\Utils\ErrorHandler;
use App\Core\Utils\Validator;
use Twig\Environment as Twig;

class AdminGroupController
{
    public function __construct(private Twig $twig)
    {
    }

    #[Get('/admin/group')]
    public function adminGroupIndex(): string
    {
        if (! Auth::hasPermission("canManageGroups")) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }
        $allGroups = (new \App\Models\Auth())->getAllGroups();

        foreach ($allGroups as $key => $group) {
            $permissions =  (new \App\Models\Auth())->getGroupPermissions($group["id"]);
            $group["permissions"] = $permissions;
            $allGroups[$key] = $group;
        }

        return $this->twig->render(
            'admin/group/index.twig',
            ['groups'=>$allGroups, 'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]
        );
    }

    #[Get('/admin/group/update')]
    public function adminGroupUpdateIndex(): string
    {
        if (! Auth::hasPermission('canManageGroups')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $group = (new \App\Models\Auth())->getGroupById($_GET["id"]);
        if (!$group) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $serializedPermissions = (new \App\Models\Auth())->serializeGroupPermissions($_GET["id"]);
        $group['permissions'] = $serializedPermissions;
        $data = ['group' => $group, 'errors' => ['nameError' => ''],
            'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION];

        return $this->twig->render('admin/group/update.twig', $data);
    }

    #[Post('/admin/group/update')]
    public function adminGroupUpdate(): string
    {
        if (! Auth::hasPermission('canManageGroups')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(404));
        }

        $_POST = filter_input_array(INPUT_POST);
        $data = [
            'id' => trim($_GET['id']),
            'name' => trim($_POST['name']),
            'default' => intval(key_exists('default', $_POST))
        ];
        $permissions = $_POST['permissions'] ?? [];

        $errors = Validator::validateGroupName($data, groupNameExistence: false);

        $serializedPermissions = (new \App\Models\Auth())->serializeGroupPermissionsByData($permissions);
        $data['permissions'] = $serializedPermissions;

        if (empty($errors['nameError'])) {
            if ((new \App\Models\Auth())->updateGroup($data)) {
                (new \App\Models\Auth())->updateGroupPermissions($data["id"], $permissions);
                header('Location: /admin/group');
                die();
            } else {
                return $this->twig->render('error.twig', ErrorHandler::causeError(500));
            }
        } else {
            return $this->twig->render('admin/group/update.twig', ['group' => $data, 'errors' => $errors,
                'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
        }
    }

    #[Get('/admin/group/add')]
    public function adminGroupAddIndex(): string
    {
        if (! Auth::hasPermission('canManageGroups')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $group = [
            'name' => '',
            'default' =>  false,
        ];

        $errors = [
            'nameError' => ''
        ];

        $group['permissions'] = (new \App\Models\Auth())->getAllPermissions();

        return $this->twig->render('admin/group/add.twig', ["group" => $group, "errors" => $errors,
            'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
    }

    #[Post('/admin/group/add')]
    public function adminGroupAdd(): string
    {
        if (! Auth::hasPermission('canManageGroups')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_POST = filter_input_array(INPUT_POST);
        $group = [
            'name' => trim($_POST['name']),
            'default' => intval(key_exists('default', $_POST))
        ];
        $permissions = $_POST['permissions'] ?? [];

        $errors = Validator::validateGroupName($group, groupNameExistence: false);

        $serializedPermissions = (new \App\Models\Auth())->serializeGroupPermissionsByData($permissions);
        $group['permissions'] = $serializedPermissions;

        if (empty($errors['nameError'])) {
            $groupId = (new \App\Models\Auth())->addGroup($group);
            (new \App\Models\Auth())->updateGroupPermissions($groupId, $permissions);
            if ($_POST['action'] == "save") {
                header('Location: /admin/group');
                die();
            } elseif ($_POST['action'] == "save_and_add") {
                header('Location: /admin/group/add');
                die();
            } else {
                return $this->twig->render('error.twig', ErrorHandler::causeError(500));
            }
        } else {
            return $this->twig->render('admin/group/add.twig', ['group' => $group, 'errors' => $errors,
                'permissions'=>Auth::getUserPermissions(), 'session' => $_SESSION]);
        }
    }

    #[Get('/admin/group/delete')]
    public function adminGroupDelete(): string
    {
        if (! Auth::hasPermission('canManageGroups')) {
            return $this->twig->render('error.twig', ErrorHandler::causeError(403));
        }

        $_GET = filter_input_array(INPUT_GET);
        if (! (array_key_exists('id', $_GET) && Validator::validatesAsInt($_GET["id"]))) {
            header('Location: /admin/group');
            die();
        }

        $group_id = trim($_GET['id']);

        if ((new \App\Models\Auth())->deleteGroup($group_id)) {
            header('Location: /admin/group');
            die();
        } else {
            return $this->twig->render('error.twig', ErrorHandler::causeError(500));
        }
    }
}
