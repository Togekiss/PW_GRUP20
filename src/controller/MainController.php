<?php

namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PWGram\controller\DatabaseController;
use Symfony\Component\HttpFoundation\Session;


class MainController {

    private $user = null;
    public $default = __DIR__ . '/../../res/default_portrait.png';

    public function renderMainPage (Application $app) {

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

        if (!$signUpController->signUp($app, $user)) {
            $userController = new DatabaseController();
            $user['password'] = md5($user['password']);
            $user['img'] = $request->get('img');
            if (!$user['img']) $user['img'] = $this->default;
            if ($userController->signUpAction($app, $user)) {
                $response->setStatusCode(Response::HTTP_OK);
                $content = $app['twig']->render('MainPage.twig', array(
                    'app' => ['name' => $app['app.name']],
                    'user' => 'Created!'));
            }
        }else if(isset($_POST['signup'])) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $app['twig']->render('error.twig', array(
                'app' => ['name' => $app['app.name']],
                'message' => 'nope'
            ));
            //$_POST['user'] = 'pepe';
        }

        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function login (Application $app, Request $request) {
        $user = $request->get('user');
        $pass = $request->get('pass');

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
            $response->setStatusCode(Response::HTTP_OK);
            $content = $app['twig']->render('hello.twig', array(
                'app' => [
                    'name' => $app['app.name']
                ],
                'user' => $user
            ));
            $app['session']->set('name', 'hola');
        }

        $response->headers->set('Content-Type', 'text/html');
        header("Location: grup20.com/login");
        $response->setContent($content);
        return $response;
    }

    public function edit (Application $app, Request $request) {
        $user = $request->get('user');
        $pass = $request->get('pass');
        $birthdate = $request->get('birthdate');
        $img = $request->get('img_path');

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
            $response->setStatusCode(Response::HTTP_OK);
            $content = $app['twig']->render('hello.twig', array(
                'app' => [
                    'name' => $app['app.name']
                ],
                'user' => $user
            ));
        }

        $response->headers->set('Content-Type', 'text/html');
        header("Location: grup20.com/login");
        $response->setContent($content);
        return $response;
    }
}