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
        session_start();
        unset($_SESSION['images']);

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
        session_start();
        unset($_SESSION['comments']);


        $datetime1 = date_create($img['created_at']);
        $datetime2 = date_create('now');
        $interval = date_diff($datetime1, $datetime2);
        $img['days'] = $interval->format('%a');

        $array = array(
            'app' => ['name' => $app['app.name']],
            'img' => $img,
            'comments' => $comments,
            'user2'=> $user);

        if ($userController->getAction($app, $app['session']->get('name'))) $array['user'] = $userController->getAction($app, $app['session']->get('name'));

        $content = $app['twig']->render('Image.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function ShowUser (Application $app, $idUser, $selection) {
        $userController = new DatabaseController();
        $user = $userController->getActionId($app, $idUser);

        if ($user) {
            $user['num_images'] = $userController->getNumImages($app, $idUser);
            $user['comments'] = $userController->getNumComment($app, $idUser);
            $img = $userController->getPublicImages($app, $idUser, $selection);

            $array = array(
                'app' => ['name' => $app['app.name']],
                'user2' => $user,
                'images' => $img);

            if ($app['session']->has('name')) {
                $this->user = $userController->getAction($app, $app['session']->get('name'));
                $array['user'] = $this->user;
                if ($user['id'] == $this->user['id']) {
                    $img = $userController->getAllImages($app, $idUser, $selection);
                    var_dump($img);
                    $array['images'] = $img;
                }
            }

            $content = $app['twig']->render('Profile.twig', $array);
            $response = new Response();
            $response->setStatusCode($response::HTTP_OK);
            $response->headers->set('Content-Type', 'text/html');
            $response->setContent($content);
            return $response;
        }else {
            $response = new Response();
            $content = $app['twig']->render('error.twig', array('app' => ['name' => $app['app.name']], 'message' => 'Requested user does not exist!'));
            $response->setContent($content);
            $response->setStatusCode(Response::HTTP_FORBIDDEN);
            return $response;
        }
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

                header('Content-Type: image/jpeg');

                $thumb = imagecreatetruecolor(400, 300);
                $info = getimagesize($name);
                list($width, $height) = getimagesize($name);

                if ($info['mime'] == 'image/jpeg')
                    $image = imagecreatefromjpeg($name);

                else if ($info['mime'] == 'image/gif')
                    $image = imagecreatefromgif($name);

                else if ($info['mime'] == 'image/png')
                    $image = imagecreatefrompng($name);

                imagecopyresized($thumb, $image, 0, 0, 0, 0, 400, 300, $width, $height);

                imagejpeg($thumb, $this->upload . substr($nameBase, 0, strlen($nameBase) - 4)  . "400x300.jpg", 75);

                $thumb = imagecreatetruecolor(16, 16);
                $info = getimagesize($name);
                list($width, $height) = getimagesize($name);

                if ($info['mime'] == 'image/jpeg')
                    $image = imagecreatefromjpeg($name);

                else if ($info['mime'] == 'image/gif')
                    $image = imagecreatefromgif($name);

                else if ($info['mime'] == 'image/png')
                    $image = imagecreatefrompng($name);

                imagecopyresized($thumb, $image, 0, 0, 0, 0, 16, 16, $width, $height);

                imagejpeg($thumb, $this->upload . substr($nameBase, 0, strlen($nameBase) - 4) . "16x16.jpg", 75);

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
                    header('Location: ' . $_SERVER['HTTP_REFERER'], true, 303);
                    die();
                    /*$img = $userController->getImageAction($app, $idImg);
                    $array = array('img' => $img);

                    $content = $app['twig']->render('ReloadLike.twig', $array);
                    $response = new Response();
                    $response->setStatusCode($response::HTTP_OK);
                    $response->headers->set('Content-Type', 'text/html');
                    $response->setContent($content);
                    return $response;*/
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

        //header('Location: ' . '/', true, 303);
        //header('Location: ' . $_SERVER['HTTP_REFERER'], true, 303);
        //die();
        $img = $userController->getImageAction($app, $idImg);
        $array = array('img' => $img);

        $content = $app['twig']->render('ReloadLike.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function removeImage (Application $app, $idImg) {
        $userController = new DatabaseController();
        $img = $userController->getImageAction($app, $idImg);

        if ($userController->getNotificationNum($app, $idImg)) {
            $userController->updateNotificationUser($app, $img['user_id'], $userController->getNotificationNum($app, $idImg), 0);
        }

        $userController->deleteImageAction($app, $idImg);
        header('Location: ' . '/user/' . $img['user_id'], true, 303);
        die();
    }

    public function removeComment (Application $app, $idComment) {
        $userController = new DatabaseController();
        $com = $userController->getCommentId($app, $idComment);

        if ($userController->getNumComment($app, $idComment)) {
            $userController->updateNotificationUser($app, $com['user_id'], $userController->getNumComment($app, $idComment), 0);
        }

        $userController->deleteCommentAction($app, $idComment);
        header('Location: ' . '/comment-list/' . $com['user_id'], true, 303);
        die();
    }

    public function removeNotification (Application $app, $notificationId) {
        $userController = new DatabaseController();

        $userController->updateNotificationUser($app, $userController->getAction($app, $app['session']->get('name'))['id'], 1, 0);
        $userController->deleteNotificationAction($app, $notificationId);
        header('Location: ' . '/notifications', true, 303);
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
                $nameBase = basename($request->files->get('img')->getClientOriginalName());
                $name = $this->upload . $nameBase;
                move_uploaded_file($tmp_name, $name);
            }

            $img = array(
                'id' => $idImg,
                'title' => $request->get('title')?$request->get('title'):null,
                'img' => $nameBase?$this->path . $nameBase:null,
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

    public function modifyComment (Application $app, Request $request, $idComment) {
        $userController = new DatabaseController();

        $comment = array('id' => $idComment, 'text' => $request->get('text'));

        if ($userController->updateComment($app, $comment)) {
            header('Location: ' . '/comment-list/' . $userController->getAction($app, $app['session']->get('name'))['id'], true, 303);
            die();
        }


        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setStatusCode(Response::HTTP_NOT_FOUND);
        $content = $app['twig']->render('error.twig', array(
            'app' => ['name' => $app['app.name']],
            'message' => 'The update of the comment failed. Please, try again!'
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

    public function ShowNotifications (Application $app, Request $request) {
        $userController = new DatabaseController();
        $user = $userController->getAction($app, $app['session']->get('name'));
        $notifications = $userController->getAllNotifications($app, $user['id']);

        for ($i = 0; $i < count($notifications); $i++) {
            $notifications[$i]['title'] = $userController->getImageAction($app, $notifications[$i]['image_id'])['title'];
            if ($notifications[$i]['is_like']) {
                $notifications[$i]['user_id'] = $userController->getLike($app, $notifications[$i]['image_id'], $notifications[$i]['user_id'])['user_id'];
                $notifications[$i]['username'] = $userController->getActionId($app, $notifications[$i]['user_id'])['username'];
            }else {
                $notifications[$i]['user_id'] = $userController->getComment($app, $notifications[$i]['image_id'], $notifications[$i]['user_id'])['user_id'];
                $notifications[$i]['username'] = $userController->getActionId($app, $notifications[$i]['user_id'])['username'];
            }
        }

        $array = array(
            'app' => ['name' => $app['app.name']],
            'user'=> $user,
            'notifications' => $notifications);

        $content = $app['twig']->render('Notifications.twig', $array);
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


    public function loadMoreImages(Application $app) {
        $response = new Response();
        $userController = new DatabaseController();
        session_start();
        if(!isset($_SESSION['images'])) {
            $_SESSION['images'] = 10;
        } else {
            $_SESSION['images'] = $_SESSION['images'] + 5;
        }
        $imgRecent = $userController->mostRecent($app, $_SESSION['images']);


        for ($i = 0; $i < count($imgRecent); $i++) {
            $imgRecent[$i]['username'] = $userController->getActionId($app, $imgRecent[$i]['user_id'])['username'];
            $comment = $userController->mostRecentComment($app, $imgRecent[$i]['id']);
            $imgRecent[$i]['userc_id'] = $comment['user_id'];
            $imgRecent[$i]['textc'] = htmlentities($comment['text']);
            $imgRecent[$i]['usernamec'] = $userController->getActionId($app, $comment['user_id'])['username'];
        }

        $array = array(
            'app' => ['name' => $app['app.name']],
            'most_recent_images' => $imgRecent);

        if ($app['session']->has('name')) {
            $userController = new DatabaseController();
            $this->user = $userController->getAction($app, $app['session']->get('name'));
            $array['user'] = $this->user;
        }

        $content = $app['twig']->render('ImageList.twig', $array);

        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }


    public function loadMoreComments (Application $app, $idImg) {
        $userController = new DatabaseController();
        $img = $userController->getImageAction($app, $idImg);
        session_start();
        if(!isset($_SESSION['comments'])) {
            $_SESSION['comments'] = 6;
        } else {
            $_SESSION['comments'] = $_SESSION['comments'] + 3;
        }
        $comments = $userController->getImageComments($app, $idImg, $_SESSION['comments']);
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

        $content = $app['twig']->render('CommentList.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }


}