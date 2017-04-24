<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 03/04/2017
 * Time: 15:45
 */

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/hello/{name}', 'PWGram\\controller\\HelloController::indexAction');
$app->get('/add/{num1}/{num2}', 'PWGram\\controller\\HelloController::addAction');



$before = function(Request $request, Application $app) {
    if (!$app['session']->has('name')) {
        $response = new Response();
        $content = $app['twig']->render('hello.twig', array(
            'user' => 'You have to be logged',
            'app' => [
                'name' => $app['app.name']
            ]
        ));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        return $response;
    }
};

//SESSION
$app->get('/', 'PWGram\\controller\\BaseController::indexAction');
$app->get('/admin', 'PWGram\\controller\\BaseController::adminAction')->before($before);