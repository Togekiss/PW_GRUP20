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
$app->get('/edit', 'PWGram\\controller\\MainController::edit')->before($before);
$app->get('/login', 'PWGram\\controller\\MainController::renderMainPage');
$app->get('/logout', 'PWGram\\controller\\MainController::logout');
$app->get('/upload-image', 'PWGram\\controller\\MainController::upload')->before($before);
$app->get('/like/{idImg}', 'PWGram\\controller\\MainController::uploadLike')->before($before);

$app->post('/', 'PWGram\\controller\\MainController::login');
$app->post('/login', 'PWGram\\controller\\MainController::login');
$app->post('/edit', 'PWGram\\controller\\MainController::edit')->before($before);
$app->post('/register', 'PWGram\\controller\\MainController::signUp');
$app->post('/logout', 'PWGram\\controller\\MainController::logout');
$app->post('/upload-image', 'PWGram\\controller\\MainController::upload');
$app->post('/comment/{idImg}', 'PWGram\\controller\\MainController::uploadComment')->before($before);
//$app->post('/login', 'PWGram\\controller\\MainController::login');

$app->get('/register', 'PWGram\\controller\\MainController::ShowsignUp');

