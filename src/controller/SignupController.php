<?php

namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use PWGram\controller\UserController as UserC;


class SignupController {

    public function signUp (Application $app, Request $request) {

        $user = array(
            'name' => $request->get('user'),
            'email' => $request->get('email'),
            'birthdate' => $request->get('birthdate'),
            'password' => $request->get('password'),
            'password2' => $request->get('password2'),
            'img' => $request->get('img')
        );

        $constraint = new Assert\Collection(array(
            'name' => new Assert\Length(array('min' => 10)),
            'email' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 10))),
            'birthdate' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 10))),
            'password' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 10))),
            'password2' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 10))),
            'img' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 10))),
        ));

        $errors = $app['validator']->validate($user, $constraint);

        /*$user = $request->get('user');
        $pass = $request->get('pass');

        $userController = new UserController();
        $userFound = $userController->postAction($app, $user, $pass);
        $response = new Response();

        if (!$userFound) {
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
        $response->setContent($content);
        return $response;*/
    }
}