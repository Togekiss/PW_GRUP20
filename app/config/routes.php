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
use Symfony\Component\HttpFoundation;

$before = function (Request $request, Application $app) {
    if (!$app['session']->has('name')) {
        $response = new Response();
        $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You must be logged'));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        return $response;
    }
};

$app->get('/', 'PWGram\\controller\\MainController::renderMainPage');
$app->post('/edit', 'PWGram\\controller\\MainController::edit')->before($before);
$app->get('/edit', 'PWGram\\controller\\MainController::edit')->before($before);
$app->post('/', 'PWGram\\controller\\MainController::login');
//$app->post('/login', 'PWGram\\controller\\MainController::login');

$app->get('/hello/{name}', 'PWGram\\controller\\HelloController::indexAction');
$app->get('/add/{num1}/{num2}', 'PWGram\\controller\\HelloController::addAction');

//SESSION
$app->get('/', 'PWGram\\controller\\BaseController::indexAction');
$app->get('/admin', 'PWGram\\controller\\BaseController::adminAction')->before($before);