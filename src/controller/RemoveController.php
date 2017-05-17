<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use PWGram\controller\UserController as UserC;


class RemoveController {

    private $user;
    public $default = '/assets/img/default_portrait.png';
    public $upload = __DIR__ . '/../../web/assets/img/';
    public $path = '/assets/img/';

    public function removeImage (Application $app, $idImg) {
        $userController = new DatabaseController();
        $img = $userController->getImageAction($app, $idImg);

        if ($userController->getNotificationNum($app, $idImg)) {
            $userController->updateNotificationUser($app, $img['user_id'], $userController->getNotificationNum($app, $idImg), 0);
        }

        $userController->deleteImageAction($app, $idImg);
        header('Location: ' . '/user/' . $img['user_id'] . "/1", true, 303);
        die();
    }

    public function removeComment (Application $app, $idComment) {
        $userController = new DatabaseController();
        $com = $userController->getCommentId($app, $idComment);

        if ($userController->getNumComment($app, $idComment)) {
            $userController->updateNotificationUser($app, $com['user_id'], $userController->getNumComment($app, $idComment), 0);
        }

        $userController->deleteCommentAction($app, $idComment);
        header('Location: ' . '/comment-list/' . $com['user_id'], true, 303);
        die();
    }

    public function removeNotification (Application $app, $notificationId) {
        $userController = new DatabaseController();

        $userController->updateNotificationUser($app, $userController->getAction($app, $app['session']->get('name'))['id'], 1, 0);
        $userController->deleteNotificationAction($app, $notificationId);
        header('Location: ' . '/notifications', true, 303);
        die();
    }

}