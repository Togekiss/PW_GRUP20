<?php
namespace PWGram\controller;

use PWGram\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class UserController {

    public function getAction(Application $app, $id) {
        $sql = "SELECT * FROM user WHERE id = ?";
        $user = $app['db']->fetchAssoc($sql, array((int)$id));
        $response = new Response();
        if (!$user) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $app['twig']->render('error.twig', ['message' => 'User not found']);
        }
        else {
            $response->setStatusCode(Response::HTTP_OK);
            $content = $app['twig']->render('user.twig', ['user' => $user]);
        }
        $response->setContent($content);
        return $response;
    }

    public function postAction(Application $app) {

    }

    public function deleteAction(Application $app) {

    }

    public function updateAction(Application $app) {

    }
}