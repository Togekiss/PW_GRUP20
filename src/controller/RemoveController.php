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
    public $upload = __DIR__ . '/../../public/assets/img/';
    public $path = '/assets/img/';

    public function removeImage (Application $app, $idImg) {
        $dbController = new DatabaseController();
        $img = $dbController->getImageAction($app, $idImg);

        if ($dbController->getNotificationNum($app, $idImg)) {
            $dbController->updateNotificationUser($app, $img['user_id'], $dbController->getNotificationNum($app, $idImg), 0);
        }

        $dbController->deleteImageAction($app, $idImg);
        header('Location: ' . '/user/' . $img['user_id'] . "/1", true, 303);
        die();
    }

    public function removeComment (Application $app, $idComment) {
        $dbController = new DatabaseController();
        $com = $dbController->getCommentId($app, $idComment);

        if ($dbController->getNumComment($app, $idComment)) {
            $dbController->updateNotificationUser($app, $com['user_id'], $dbController->getNumComment($app, $idComment), 0);
        }

        $dbController->deleteCommentAction($app, $idComment);
        header('Location: ' . '/comment-list/' . $com['user_id'], true, 303);
        die();
    }

    public function removeNotification (Application $app, $notificationId) {
        $dbController = new DatabaseController();

        $dbController->updateNotificationUser($app, $dbController->getAction($app, $app['session']->get('name'))['id'], 1, 0);
        $dbController->deleteNotificationAction($app, $notificationId);
        header('Location: ' . '/notifications', true, 303);
        die();
    }

}