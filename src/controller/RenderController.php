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
    public $upload = __DIR__ . '/../../public/assets/img/';
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
        $dbController = new DatabaseController();
        $img = $dbController->getImageAction($app, $idImg);
        $comments = $dbController->getImageComments($app, $idImg, 3);
        $user = $dbController->getActionId($app, $img['user_id']);
        // session_start();
        $app['session']->remove('comments'); // This is equivalent to unset($_SESSION['comments']);



        $datetime1 = date_create($img['created_at']);
        $datetime2 = date_create('now');
        $interval = date_diff($datetime1, $datetime2);
        $img['days'] = $interval->format('%a');

        $array = array(
            'app' => ['name' => $app['app.name']],
            'img' => $img,
            'comments' => $comments,
            'user2'=> $user);

        if ($dbController->getAction($app, $app['session']->get('name'))) $array['user'] = $dbController->getAction($app, $app['session']->get('name'));

        $content = $app['twig']->render('Image.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function ShowComments (Application $app, Request $request, $idUser) {
        $dbController = new DatabaseController();
        $user = $dbController->getActionId($app, $idUser);
        $comments = $dbController->getAllComments($app, $idUser);

        for ($i = 0; $i < count($comments); $i++) {
            $comments[$i]['title'] = $dbController->getImageAction($app, $comments[$i]['image_id'])['title'];
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
        $dbController = new DatabaseController();
        $user = $dbController->getAction($app, $app['session']->get('name'));
        $notifications = $dbController->getAllNotifications($app, $user['id']);

        for ($i = 0; $i < count($notifications); $i++) {
            $notifications[$i]['title'] = $dbController->getImageAction($app, $notifications[$i]['image_id'])['title'];
            if ($notifications[$i]['is_like']) {
                $notifications[$i]['user_id'] = $dbController->getLike($app, $notifications[$i]['image_id'], $notifications[$i]['user_id'])['user_id'];
                $notifications[$i]['username'] = $dbController->getActionId($app, $notifications[$i]['user_id'])['username'];
            }else {
                $notifications[$i]['user_id'] = $dbController->getComment($app, $notifications[$i]['image_id'], $notifications[$i]['user_id'])['user_id'];
                $notifications[$i]['username'] = $dbController->getActionId($app, $notifications[$i]['user_id'])['username'];
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
        $dbController = new DatabaseController();
        $user = $dbController->getActionId($app, $idUser);

        if ($user) {
            $user['num_images'] = $dbController->getNumImages($app, $idUser);
            $user['comments'] = $dbController->getNumComment($app, $idUser);
            $img = $dbController->getPublicImages($app, $idUser, $selection);

            $array = array(
                'app' => ['name' => $app['app.name']],
                'user2' => $user,
                'images' => $img);

            if ($app['session']->has('name')) {
                $this->user = $dbController->getAction($app, $app['session']->get('name'));
                $array['user'] = $this->user;
                if ($user['id'] == $this->user['id']) {
                    $img = $dbController->getAllImages($app, $idUser, $selection);
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