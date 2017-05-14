<?php

namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PWGram\controller\DatabaseController;
use Symfony\Component\HttpFoundation\Session;


class MainController {

    private $user;
    public $default = __DIR__ . '/../../res/default_portrait.png';
    public $upload = __DIR__ . '/../../web/upload/';

    public function renderMainPage (Application $app) {

        $response = new Response();
        $userController = new DatabaseController();
        $imgViewed = $userController->mostViewed($app);
        $userViewed = $userController->getActionId($app, $imgViewed[0]['user_id']);

        $imgRecent = $userController->mostRecent($app);
        $userRecent = $userController->getActionId($app, $imgRecent[0]['user_id']);

        $array = array(
            'app' => ['name' => $app['app.name']],
            'img' => $imgViewed[0],
            'user2'=> $userViewed);

        if ($app['session']->has('name')) {
            $userController = new DatabaseController();
            $this->user = $userController->getAction($app, $app['session']->get('name'));
            $array['user'] = $this->user;
        }

        $content = $app['twig']->render('MainPage.twig', $array);

        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function ShowsignUp (Application $app, Request $request) {
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

    public function signUp (Application $app, Request $request) {

        $user = array(
            'name' => $request->get('user'),
            'email' => $request->get('email'),
            'birthdate' => $request->get('birthdate'),
            'password' => $request->get('password'),
            'password2' => $request->get('password2'),
        );

        $signUpController = new SignupController();
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if (!$signUpController->signUp($app, $user)) {
            $userController = new DatabaseController();
            $user['password'] = md5($user['password']);

            if (!$request->files->get('img')->getError()) {
                $tmp_name = $request->files->get('img');
                $name = basename($request->files->get('img')->getClientOriginalName());
                $name = $this->upload . $name;
                move_uploaded_file($tmp_name, $name);
                $user['img'] = $name;
            }

            if (!$user['img']) $user['img'] = $this->default;

            if ($userController->signUpAction($app, $user)) {
                $app['session']->set('name', $user['name']);
                header('Location: ' . '/', true, 303);
                die();
            }
            $message = 'We had an issue signing you up. Please try again!';
        }

        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $content = $app['twig']->render('error.twig', array(
            'app' => ['name' => $app['app.name']],
            'message' => $message
        ));
        $response->setContent($content);
        return $response;
    }

    public function login (Application $app, Request $request) {
        $user = $request->get('user');
        $pass = $request->get('pass');
        $pass = md5($pass);

        $userController = new DatabaseController();
        $this->user = $userController->postAction($app, $user, $pass);
        $response = new Response();

        if (!$this->user) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $app['twig']->render('error.twig', array(
                'app' => ['name' => $app['app.name']],
                'message' => 'User not found'));
        }
        else {
            $app['session']->start();
            $app['session']->set('name', $this->user['username']);
            header('Location: ' . '/', true, 303);
            die();
        }

        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function edit (Application $app, Request $request) {

        $user = array(
            'name' => $request->get('user'),
            'birthdate' => $request->get('birthdate'),
            'password' => $request->get('password'),
        );

        $editController = new EditController();
        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if (!$editController->edit($app, $user)) {
            $userController = new DatabaseController();
            $this->user = $userController->getAction($app, $app['session']->get('name'));
            $user = array(
                'name' => $user['name']?$user['name']:null,
                'password' => $user['password']?md5($user['password']):null,
                'birthdate' => $user['birthdate']?$user['birthdate']:null,
                'img' => $request->get('img')?$request->get('img'):null,
                'id' => $this->user['id']
            );

            if ($userController->updateAction($app, $user) == count(array_filter($user))- 1) {
                if ($user['name']) $app['session']->set('name', $user['name']);
                $this->user = $userController->getAction($app, $app['session']->get('name'));
                header('Location: ' . '/', true, 303);
                die();
            }
            $message = 'We had an issue signing you up. Please try again!';
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $content = $app['twig']->render('error.twig', array(
            'app' => ['name' => $app['app.name']],
            'message' => $message
        ));
        $response->setContent($content);
        return $response;
    }

    public function upload (Application $app, Request $request) {
        $img = array('title' => $request->get('title'));

        $uploadController = new UploadController();
        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if (!$request->files->get('img')->getError()) {
            if (!$uploadController->upload($app, $img)) {
                $userController = new DatabaseController();
                $this->user = $userController->getAction($app, $app['session']->get('name'));

                $tmp_name = $request->files->get('img');
                $name = basename($request->files->get('img')->getClientOriginalName());
                $name = $this->upload . $name;
                move_uploaded_file($tmp_name, $name);

                $img = array(
                    'id' => $this->user['id'],
                    'title' => $request->get('title'),
                    'img' => $name,
                    'private' => $request->get('private') ? 1 : 0,
                );


                if ($userController->uploadAction($app, $img)) {
                    header('Location: ' . '/', true, 303);
                    die();
                }
                $message = 'We had an issue signing you up. Please try again!';

            }
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $content = $app['twig']->render('error.twig', array(
            'app' => ['name' => $app['app.name']],
            'message' => $message
        ));
        $response->setContent($content);
        return $response;
    }

    public function uploadComment(Application $app, Request $request, $idImg) {
        $comment = array('text' => $request->get('text'));

        $uploadController = new UploadController();
        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if (!$uploadController->uploadComment($app, $comment)) {
            $userController = new DatabaseController();
            $this->user = $userController->getAction($app, $app['session']->get('name'));

            if (!$userController->getComment($app, $idImg, $this->user['id'])) {
                $comment['image_id'] = $idImg;
                $comment['user_id'] = $this->user['id'];

                $notification = 0;

                if ($userController->uploadCommentAction($app, $comment) && $userController->uploadNotificationAction($app, $notification)) {
                    header('Location: ' . '/', true, 303);
                    die();
                }
            }
            $message = 'You can only comment once per photo!';
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $content = $app['twig']->render('error.twig', array(
            'app' => ['name' => $app['app.name']],
            'message' => $message
        ));
        $response->setContent($content);
        return $response;
    }

    public function uploadLike(Application $app, $idImg) {

        $userController = new DatabaseController();
        $this->user = $userController->getAction($app, $app['session']->get('name'));

        $like = array(
            'image_id' => $idImg,
            'user_id' => $this->user['id']
        );

        if (!$userController->getLike($app, $idImg, $this->user['id'])) $userController->uploadLikeAction($app, $like);
        else $userController->deleteLikeAction($app, $userController->getLike($app, $idImg, $this->user['id'])['id']);

        header('Location: ' . '/', true, 303);
        die();
    }

    public function logout (Application $app) {
        if ($app['session']->has('name')) {
            $this->user = null;
            $app['session']->clear();
        }
        header('Location: ' . '/', true, 303);
        die();
    }
}