<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use PWGram\controller\UserController as UserC;


class RenderController {

    private $user;
    public $default = '/assets/img/default_portrait.png';
    public $upload = __DIR__ . '/../../web/assets/img/';
    public $path = '/assets/img/';

    public function ShowsignUp (Application $app) {
        $content = $app['twig']->render('Register.twig', array(
            'app' => [
                'name' => $app['app.name']
            ]
        ));
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function ShowImage (Application $app, $idImg) {
        $userController = new DatabaseController();
        $img = $userController->getImageAction($app, $idImg);
        $comments = $userController->getImageComments($app, $idImg, 3);
        $user = $userController->getActionId($app, $img['user_id']);
        session_start();
        unset($_SESSION['comments']);


        $datetime1 = date_create($img['created_at']);
        $datetime2 = date_create('now');
        $interval = date_diff($datetime1, $datetime2);
        $img['days'] = $interval->format('%a');

        $array = array(
            'app' => ['name' => $app['app.name']],
            'img' => $img,
            'comments' => $comments,
            'user2'=> $user);

        if ($userController->getAction($app, $app['session']->get('name'))) $array['user'] = $userController->getAction($app, $app['session']->get('name'));

        $content = $app['twig']->render('Image.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function ShowComments (Application $app, Request $request, $idUser) {
        $userController = new DatabaseController();
        $user = $userController->getActionId($app, $idUser);
        $comments = $userController->getAllComments($app, $idUser);

        for ($i = 0; $i < count($comments); $i++) {
            $comments[$i]['title'] = $userController->getImageAction($app, $comments[$i]['image_id'])['title'];
        }

        $array = array(
            'app' => ['name' => $app['app.name']],
            'user'=> $user,
            'comments' => $comments);

        $content = $app['twig']->render('Comments.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function ShowNotifications (Application $app, Request $request) {
        $userController = new DatabaseController();
        $user = $userController->getAction($app, $app['session']->get('name'));
        $notifications = $userController->getAllNotifications($app, $user['id']);

        for ($i = 0; $i < count($notifications); $i++) {
            $notifications[$i]['title'] = $userController->getImageAction($app, $notifications[$i]['image_id'])['title'];
            if ($notifications[$i]['is_like']) {
                $notifications[$i]['user_id'] = $userController->getLike($app, $notifications[$i]['image_id'], $notifications[$i]['user_id'])['user_id'];
                $notifications[$i]['username'] = $userController->getActionId($app, $notifications[$i]['user_id'])['username'];
            }else {
                $notifications[$i]['user_id'] = $userController->getComment($app, $notifications[$i]['image_id'], $notifications[$i]['user_id'])['user_id'];
                $notifications[$i]['username'] = $userController->getActionId($app, $notifications[$i]['user_id'])['username'];
            }
        }

        $array = array(
            'app' => ['name' => $app['app.name']],
            'user'=> $user,
            'notifications' => $notifications);

        $content = $app['twig']->render('Notifications.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function ShowUser (Application $app, $idUser, $selection) {
        $userController = new DatabaseController();
        $user = $userController->getActionId($app, $idUser);

        if ($user) {
            $user['num_images'] = $userController->getNumImages($app, $idUser);
            $user['comments'] = $userController->getNumComment($app, $idUser);
            $img = $userController->getPublicImages($app, $idUser, $selection);

            $array = array(
                'app' => ['name' => $app['app.name']],
                'user2' => $user,
                'images' => $img);

            if ($app['session']->has('name')) {
                $this->user = $userController->getAction($app, $app['session']->get('name'));
                $array['user'] = $this->user;
                if ($user['id'] == $this->user['id']) {
                    $img = $userController->getAllImages($app, $idUser, $selection);
                    $array['images'] = $img;
                }
            }

            $content = $app['twig']->render('Profile.twig', $array);
            $response = new Response();
            $response->setStatusCode($response::HTTP_OK);
            $response->headers->set('Content-Type', 'text/html');
            $response->setContent($content);
            return $response;
        }else {
            $response = new Response();
            $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'Requested user does not exist!'));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }
    }

}