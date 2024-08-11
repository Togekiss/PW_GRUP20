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
use PWGram\controller\DatabaseController;

$before = function (Request $request, Application $app) {
    if (!$app['session']->has('name')) {
        $response = new Response();
        $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You must be logged'));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        return $response;
    }
};

$imgCheck = function (Request $request, Application $app) {
    $idImg = $request->get('idImg');
    $dbController = new DatabaseController();
    $img = $dbController->getImageAction($app, $idImg);

    if (!$img || $img['private']) {
        $response = new Response();
        $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'The image does not exist or it is private.'));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        return $response;
    }
    $dbController->updateVisits($app, $idImg);
};

$removeCheck = function (Request $request, Application $app) {
    if (!$app['session']->has('name')) {
        $response = new Response();
        $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You must be logged'));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        return $response;
    }else {
        $idImg = $request->get('idImg');
        $dbController = new DatabaseController();
        $img = $dbController->getImageAction($app, $idImg);

        if (!$img || ($img['user_id'] != $dbController->getAction($app, $app['session']->get('name'))['id'])) {
            $response = new Response();
            $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'The image does not exist or you are not the owner.'));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }
    }
};

$userCheck = function (Request $request, Application $app) {
    if (!$app['session']->has('name')) {
        $response = new Response();
        $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You must be logged'));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        return $response;
    }else {
        $idUser = $request->get('idUser');
        $dbController = new DatabaseController();
        if($dbController->getAction($app, $app['session']->get('name'))['id'] != $idUser) {
            $response = new Response();
            $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You cannot access other users comment page'));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }
    }
};

$notificationCheck = function (Request $request, Application $app) {
    if (!$app['session']->has('name')) {
        $response = new Response();
        $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You must be logged'));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        return $response;
    } else {
        $idNot = $request->get('notificationId');
        $dbController = new DatabaseController();
        $notification = $dbController->getNotificationId($app, $idNot);

        if ($notification['user_id'] != $dbController->getAction($app, $app['session']->get('name'))['id']) {
            $response = new Response();
            $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You cannot access other account notifications!'));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }
    }
};

$commentCheck = function (Request $request, Application $app) {
    if (!$app['session']->has('name')) {
        $response = new Response();
        $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You must be logged'));
        $response->setContent($content);
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        return $response;
    }else {
        $idCom = $request->get('idComment');
        $dbController = new DatabaseController();
        $comment = $dbController->getCommentId($app, $idCom);

        if ($comment['user_id'] != $dbController->getAction($app, $app['session']->get('name'))['id']) {
            $response = new Response();
            $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'You cannot access other account comments!'));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }
    }
};

$app->get('/', 'PWGram\\controller\\MainController::renderMainPage');
$app->get('/edit-profile', 'PWGram\\controller\\EditController::editUser')->before($before);
$app->get('/login', 'PWGram\\controller\\MainController::renderMainPage');
$app->get('/logout', 'PWGram\\controller\\MainController::logout');
$app->get('/upload-image', 'PWGram\\controller\\UploadController::uploadImage')->before($before);
$app->get('/like/{idImg}', 'PWGram\\controller\\UploadController::uploadLike')->before($before);
$app->get('/image/{idImg}', 'PWGram\\controller\\RenderController::ShowImage')->before($imgCheck);
$app->get('/user/{idUser}/{selection}', 'PWGram\\controller\\RenderController::ShowUser');
$app->get('/comment-list/{idUser}', 'PWGram\\controller\\RenderController::ShowComments')->before($userCheck);
$app->get('/comment/{idImg}', 'PWGram\\controller\\UploadController::uploadComment')->before($before);
$app->get('/remove/{idImg}', 'PWGram\\controller\\RemoveController::removeImage')->before($removeCheck);
$app->get('/ajax/images', 'PWGram\\controller\\MainController::loadMoreImages');
$app->get('/notifications', 'PWGram\\controller\\RenderController::ShowNotifications')->before($before);
$app->get('/remove-comment/{idComment}', 'PWGram\\controller\\RemoveController::removeComment')->before($commentCheck);
$app->get('/modify-comment/{idComment}', 'PWGram\\controller\\EditController::modifyComment')->before($commentCheck);
$app->get('/remove-notification/{notificationId}', 'PWGram\\controller\\RemoveController::removeNotification')->before($notificationCheck);
$app->get('/ajax/comments/{idImg}', 'PWGram\\controller\\MainController::loadMoreComments');
$app->get('/edit-image/{idImg}', 'PWGram\\controller\\EditController::editImage')->before($removeCheck);
$app->get('/activateUser/{idActivate}', 'PWGram\\controller\\SignupController::activateUser');
$app->get('/register', 'PWGram\\controller\\RenderController::ShowsignUp');

$app->post('/', 'PWGram\\controller\\MainController::login');
$app->post('/login', 'PWGram\\controller\\MainController::login');
$app->post('/edit-profile', 'PWGram\\controller\\EditController::editUser')->before($before);
$app->post('/register', 'PWGram\\controller\\SignupController::signUp');
$app->post('/logout', 'PWGram\\controller\\MainController::logout');
$app->post('/upload-image', 'PWGram\\controller\\UploadController::uploadImage');
$app->post('/comment/{idImg}', 'PWGram\\controller\\UploadController::uploadComment')->before($before);
$app->post('/edit-image/{idImg}', 'PWGram\\controller\\EditController::editImage')->before($removeCheck);
$app->post('/modify-comment/{idComment}', 'PWGram\\controller\\EditController::modifyComment')->before($commentCheck);
$app->post('/comment-list/{idUser}', 'PWGram\\controller\\RenderController::ShowComments')->before($userCheck);



