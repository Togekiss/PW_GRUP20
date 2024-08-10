<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;


class UploadController {

    private $user;
    public $default = '/assets/img/default_portrait.png';
    public $upload = __DIR__ . '/../../public/assets/img/';
    public $path = '/assets/img/';

    public function uploadValidator (Application $app, $img) {

        $constraint = new Assert\Collection(array(
            'title' => array(new Assert\NotBlank(), new Assert\Regex(array('pattern' => '/^[A-Za-z0-9 ]+$/')), new Assert\Length(array('max' => 255))),
        ));

        $errors = $app['validator']->validate($img, $constraint);

        if (count($errors)) {
            foreach ($errors as $error) {
                echo $error->getPropertyPath().' '.$error->getMessage()."\n";
            }
        }
        return count($errors);
    }

    public function updateValidator (Application $app, $img) {

        $constraint = new Assert\Collection(array(
            'title' => array(new Assert\Regex(array('pattern' => '/^[A-Za-z0-9]+$/')), new Assert\Length(array('max' => 255))),
        ));

        $errors = $app['validator']->validate($img, $constraint);

        if (count($errors)) {
            foreach ($errors as $error) {
                echo $error->getPropertyPath().' '.$error->getMessage()."\n";
            }
        }
        return count($errors);
    }

    public function uploadCommentValidator (Application $app, $comment) {
        $constraint = new Assert\Collection(array(
            'text' => array(new Assert\NotBlank(), new Assert\Length(array('max' => 255))),
        ));

        $errors = $app['validator']->validate($comment, $constraint);

        if (count($errors)) {
            foreach ($errors as $error) {
                echo $error->getPropertyPath().' '.$error->getMessage()."\n";
            }
        }
        return count($errors);
    }

    public function uploadComment(Application $app, Request $request, $idImg) {
        $comment = array('text' => $request->get('text'));

        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if (!$this->uploadCommentValidator($app, $comment)) {
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

    public function uploadImage (Application $app, Request $request) {
        $img = array('title' => $request->get('title'));

        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if ($request->files->get('img') && !$request->files->get('img')->getError()) {
            if (!$this->uploadValidator($app, $img)) {
                $userController = new DatabaseController();
                $this->user = $userController->getAction($app, $app['session']->get('name'));

                $tmp_name = $request->files->get('img');
                $nameBase = basename($request->files->get('img')->getClientOriginalName());
                $nameBase = uniqid() . "." . $nameBase;
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

                $thumb = imagecreatetruecolor(100, 100);
                $info = getimagesize($name);
                list($width, $height) = getimagesize($name);

                if ($info['mime'] == 'image/jpeg')
                    $image = imagecreatefromjpeg($name);

                else if ($info['mime'] == 'image/gif')
                    $image = imagecreatefromgif($name);

                else if ($info['mime'] == 'image/png')
                    $image = imagecreatefrompng($name);

                imagecopyresized($thumb, $image, 0, 0, 0, 0, 100, 100, $width, $height);

                imagejpeg($thumb, $this->upload . substr($nameBase, 0, strlen($nameBase) - 4) . "100x100.jpg", 75);

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

}