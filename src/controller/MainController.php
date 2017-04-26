<?php

namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class MainController {

    public function renderMainPage (Application $app) {

        //$name = $request->query->get('name');
        /*$content = $app['twig']->render('hello.twig', array(
            'user' => $name,
            'app' => [
                'name' => $app['app.name']
            ]
        ));*/


        $content = $app['twig']->render('MainPage.twig', array(
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
}