<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 03/04/2017
 * Time: 15:52
 */

namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class HelloController {

    public function indexAction(Application $app, Request $request, $name) {

        //$name = $request->query->get('name');
        $content = $app['twig']->render('hello.twig', array(
           'user' => $name,
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

    public function addAction(Application $app, $num1, $num2) {
        return "The result is: " . $app['calc']->add($num1, $num2);
    }

}