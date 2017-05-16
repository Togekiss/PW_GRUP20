<?php

namespace PWGram\controller;

//TODO Comments i likes per Javascript

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DateTime;
use PWGram\controller\DatabaseController;
use Symfony\Component\HttpFoundation\Session;

class MainController {

    private $user;
    public $default = '/assets/img/default_portrait.png';
    public $upload = __DIR__ . '/../../web/assets/img/';
    public $path = '/assets/img/';

    public function renderMainPage (Application $app, Request $request) {
        $response = new Response();
        $userController = new DatabaseController();
        $imgViewed = $userController->mostViewed($app);
        $imgRecent = $userController->mostRecent($app, 5);

        for ($i = 0; $i < count($imgViewed); $i++) {
            $imgViewed[$i]['username'] = $userController->getActionId($app, $imgViewed[$i]['user_id'])['username'];
        }

        for ($i = 0; $i < count($imgRecent); $i++) {
            $imgRecent[$i]['username'] = $userController->getActionId($app, $imgRecent[$i]['user_id'])['username'];
            $comment = $userController->mostRecentComment($app, $imgRecent[$i]['id']);
            $imgRecent[$i]['userc_id'] = $comment['user_id'];
            $imgRecent[$i]['textc'] = htmlentities($comment['text']);
            $imgRecent[$i]['usernamec'] = $userController->getActionId($app, $comment['user_id'])['username'];
        }

        $array = array(
            'app' => ['name' => $app['app.name']],
            'most_viewed_images' => $imgViewed,
            'most_recent_images' => $imgRecent);

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

    public function ShowsignUp (Application $app) {
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

    public function ShowImage (Application $app, $idImg) {
        $userController = new DatabaseController();
        $img = $userController->getImageAction($app, $idImg);
        $comments = $userController->getImageComments($app, $idImg, 3);
        $user = $userController->getActionId($app, $img['user_id']);

        $datetime1 = date_create($img['created_at']);
        $datetime2 = date_create('now');
        $interval = date_diff($datetime1, $datetime2);
        $img['days'] = $interval->format('%a');

        $array = array(
            'app' => ['name' => $app['app.name']],
            'img' => $img,
            'comments' => $comments,
            'user2'=> $user,
            'user' =>  $userController->getAction($app, $app['session']->get('name')));

        $content = $app['twig']->render('Image.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function ShowUser (Application $app, $idUser) {
        $userController = new DatabaseController();
        $user = $userController->getActionId($app, $idUser);
        $user['num_images'] = $userController->getNumImages($app, $idUser);
        $user['comments'] = $userController->getNumComment($app, $idUser);
        $img = $userController->getPublicImages($app, $idUser);

        $array = array(
            'app' => ['name' => $app['app.name']],
            'user2'=> $user,
            'images' => $img);

        if ($app['session']->has('name')) {
            $this->user = $userController->getAction($app, $app['session']->get('name'));
            $array['user'] = $this->user;
            if ($user['id'] == $this->user['id']) {
                $img = $userController->getAllImages($app, $idUser);
                $array['images'] = $img;
            }
        }

        $content = $app['twig']->render('Profile.twig', $array);
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

            if ($request->files->get('img') && !$request->files->get('img')->getError()) {
                $tmp_name = $request->files->get('img');
                $nameBase = basename($request->files->get('img')->getClientOriginalName());
                $name = $this->upload . $nameBase;
                move_uploaded_file($tmp_name, $name);
                $user['img'] = $this->path . $nameBase;
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

        if ($request->files->get('img') && !$request->files->get('img')->getError()) {
            if (!$uploadController->upload($app, $img)) {
                $userController = new DatabaseController();
                $this->user = $userController->getAction($app, $app['session']->get('name'));

                $tmp_name = $request->files->get('img');
                $nameBase = basename($request->files->get('img')->getClientOriginalName());
                $name = $this->upload . $nameBase;
                move_uploaded_file($tmp_name, $name);

                $img = array(
                    'id' => $this->user['id'],
                    'title' => $request->get('title'),
                    'img' => $this->path . $nameBase,
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
            $img = $userController->getImageAction($app, $idImg);
            $user = $userController->getAction($app, $app['session']->get('name'));

            if (!$userController->getComment($app, $idImg, $user['id'])) {
                $comment['image_id'] = $idImg;
                $comment['user_id'] = $user['id'];
                $ok = $userController->uploadCommentAction($app, $comment);

                $comment = $userController->getComment($app, $idImg, $user['id']);
                $notification = array (
                    'user_id' => $img['user_id'],
                    'image_id' => $idImg,
                    'like_id' => $comment['id'],
                    'is_like' => 0
                );

                if ($ok && $userController->uploadNotificationAction($app, $notification) &&
                    $userController->updateNotificationUser($app, $img['user_id'], 1, 1)) {
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

    public function uploadLike(Application $app, Request $request, $idImg) {

        $userController = new DatabaseController();
        $img = $userController->getImageAction($app, $idImg);
        $user = $userController->getAction($app, $app['session']->get('name'));

        $like = array(
            'image_id' => $idImg,
            'user_id' => $user['id']
        );

        if (!$userController->getLike($app, $idImg, $user['id'])) {

            $userController->uploadLikeAction($app, $like);
            $like = $userController->getLike($app, $idImg, $user['id']);

            $notification = array (
                'user_id' => $img['user_id'],
                'image_id' => $idImg,
                'like_id' => $like['id'],
                'is_like' => 1
            );

            $userController->uploadNotificationAction($app, $notification);
            $userController->updateNotificationUser($app, $img['user_id'], 1,  1);
            $userController->updateLikeImage ($app, $img['id'], 1);
        }
        else {
            $userController->deleteLikeAction($app, $userController->getLike($app, $idImg, $user['id'])['id']);
            $userController->deleteNotificationAction($app, $userController->getNotification($app, $idImg, $img['user_id'])['id']);
            $userController->updateNotificationUser($app, $img['user_id'], 1, 0);
            $userController->updateLikeImage ($app, $img['id'], 0);
        }

        header('Location: ' . '/', true, 303);
        die();
    }

    public function removeImage (Application $app, $idImg) {
        $userController = new DatabaseController();

        $userController->updateNotificationUser(
            $app,
            $this->user = $userController->getAction($app,
            $app['session']->get('name')), $userController->getNotificationNum($app, $idImg),
            0);

        $userController->deleteImageAction($app, $idImg);
        header('Location: ' . '/user/' . $this->user['id'], true, 303);
        die();
    }

    public function editImage (Application $app, Request $request, $idImg) {
        $userController = new DatabaseController();

        $imgCheck = array('title' => $request->get('title'));

        $uploadController = new UploadController();
        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if (!$uploadController->update($app, $imgCheck)) {

            if ($request->files->get('img') && !$request->files->get('img')->getError()) {
                $this->user = $userController->getAction($app, $app['session']->get('name'));

                $tmp_name = $request->files->get('img');
                $name = basename($request->files->get('img')->getClientOriginalName());
                $name = $this->upload . $name;
                move_uploaded_file($tmp_name, $name);
            }

            $img = array(
                'id' => $idImg,
                'title' => $request->get('title')?$request->get('title'):null,
                'img' => $name?$name:null,
                'private' => $request->get('private') ? 1 : 0,
            );

            if ($userController->updateImage($app, $img) == count(array_filter($img))- 1) {
                header('Location: ' . '/user/' . $userController->getAction($app, $app['session']->get('name'))['id'], true, 303);
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

    public function ShowComments (Application $app, Request $request, $idUser) {
        $userController = new DatabaseController();
        $user = $userController->getActionId($app, $idUser);
        $comments = $userController->getAllComments($app, $idUser);

        for ($i = 0; $i < count($comments); $i++) {
            $comments[$i]['title'] = $userController->getImageAction($app, $comments[$i]['image_id'])['title'];
        }

        $array = array(
            'app' => ['name' => $app['app.name']],
            'user'=> $user,
            'comments' => $comments);

        $content = $app['twig']->render('Comments.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function logout (Application $app) {
        if ($app['session']->has('name')) {
            $this->user = null;
            $app['session']->clear();
        }
        header('Location: ' . '/', true, 303);
        die();
    }


    public function loadMoreImages()
    {
        $entity = $em->getRepository('PublishDemandsBundle:Demands')->findAll();

        return $this->render('PublishDemandsBundle:Demands:liste.html.twig', array(
            'entity' => $entity
        ));
    }

}