<?php

namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use PWGram\controller\DatabaseController;
use Symfony\Component\HttpFoundation\Session;


class MainController {

    private $user;
    public $default = '/assets/img/default_portrait.png';
    public $upload = __DIR__ . '/../../public/assets/img/';
    public $path = '/assets/img/';

    public function renderMainPage (Application $app, Request $request) {
        $response = new Response();
        $dbController = new DatabaseController();
        $imgViewed = $dbController->mostViewed($app);
        $imgRecent = $dbController->mostRecent($app, 5);
        // session_start();
        $app['session']->remove('images'); // This is equivalent to unset($_SESSION['images']);


        for ($i = 0; $i < count($imgViewed); $i++) {
            $imgViewed[$i]['username'] = $dbController->getActionId($app, $imgViewed[$i]['user_id'])['username'];
        }

        for ($i = 0; $i < count($imgRecent); $i++) {
            $imgRecent[$i]['username'] = $dbController->getActionId($app, $imgRecent[$i]['user_id'])['username'];
            $comment = $dbController->mostRecentComment($app, $imgRecent[$i]['id']);
            $imgRecent[$i]['userc_id'] = $comment['user_id'];
            $imgRecent[$i]['textc'] = htmlentities($comment['text']);
            $imgRecent[$i]['usernamec'] = $dbController->getActionId($app, $comment['user_id'])['username'];
        }

        $array = array(
            'app' => ['name' => $app['app.name']],
            'most_viewed_images' => $imgViewed,
            'most_recent_images' => $imgRecent);

        if ($app['session']->has('name')) {
            $dbController = new DatabaseController();
            $this->user = $dbController->getAction($app, $app['session']->get('name'));
            $array['user'] = $this->user;
        }

        $content = $app['twig']->render('MainPage.twig', $array);

        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function login (Application $app, Request $request) {
        $user = $request->get('user');
        $pass = $request->get('pass');
        $pass = md5($pass);

        $dbController = new DatabaseController();
        $this->user = $dbController->postAction($app, $user, $pass);
        $response = new Response();

        if (!$this->user) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $app['twig']->render('error.twig', array(
                'app' => ['name' => $app['app.name']],
                'message' => 'User not found'));
        }
        if ($this->user['active']) {
            //$app['session']->start();
            $app['session']->set('name', $this->user['username']);
            header('Location: ' . '/', true, 303);
            die();
        } else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $app['twig']->render('error.twig', array(
                'app' => ['name' => $app['app.name']],
                'message' => 'You must activate your account! You will find a link in your email!'));
        }

        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function logout (Application $app) {
        if ($app['session']->has('name')) {
            $this->user = null;
            $app['session']->clear();
        }
        header('Location: ' . '/', true, 303);
        die();
    }

    public function loadMoreImages(Application $app) {
        $response = new Response();
        $dbController = new DatabaseController();
        // session_start();
        if(!isset($_SESSION['images'])) {
            $_SESSION['images'] = 10;
        } else {
            $_SESSION['images'] = $_SESSION['images'] + 5;
        }
        $imgRecent = $dbController->mostRecent($app, $_SESSION['images']);


        for ($i = 0; $i < count($imgRecent); $i++) {
            $imgRecent[$i]['username'] = $dbController->getActionId($app, $imgRecent[$i]['user_id'])['username'];
            $comment = $dbController->mostRecentComment($app, $imgRecent[$i]['id']);
            $imgRecent[$i]['userc_id'] = $comment['user_id'];
            $imgRecent[$i]['textc'] = htmlentities($comment['text']);
            $imgRecent[$i]['usernamec'] = $dbController->getActionId($app, $comment['user_id'])['username'];
        }

        $array = array(
            'app' => ['name' => $app['app.name']],
            'most_recent_images' => $imgRecent);

        if ($app['session']->has('name')) {
            $dbController = new DatabaseController();
            $this->user = $dbController->getAction($app, $app['session']->get('name'));
            $array['user'] = $this->user;
        }

        $content = $app['twig']->render('ImageList.twig', $array);

        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function loadMoreComments (Application $app, $idImg) {
        $dbController = new DatabaseController();
        $img = $dbController->getImageAction($app, $idImg);
        // session_start();
        if(!isset($_SESSION['comments'])) {
            $_SESSION['comments'] = 6;
        } else {
            $_SESSION['comments'] = $_SESSION['comments'] + 3;
        }
        $comments = $dbController->getImageComments($app, $idImg, $_SESSION['comments']);
        $user = $dbController->getActionId($app, $img['user_id']);

        $datetime1 = date_create($img['created_at']);
        $datetime2 = date_create('now');
        $interval = date_diff($datetime1, $datetime2);
        $img['days'] = $interval->format('%a');

        $array = array(
            'app' => ['name' => $app['app.name']],
            'img' => $img,
            'comments' => $comments,
            'user2'=> $user,
            'user' =>  $dbController->getAction($app, $app['session']->get('name')));

        $content = $app['twig']->render('CommentList.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }


}