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
            $dbController = new DatabaseController();
            $img = $dbController->getImageAction($app, $idImg);
            $user = $dbController->getAction($app, $app['session']->get('name'));

            if (!$dbController->getComment($app, $idImg, $user['id'])) {
                $comment['image_id'] = $idImg;
                $comment['user_id'] = $user['id'];
                $ok = $dbController->uploadCommentAction($app, $comment);

                $comment = $dbController->getComment($app, $idImg, $user['id']);
                $notification = array (
                    'user_id' => $img['user_id'],
                    'image_id' => $idImg,
                    'like_id' => $comment['id'],
                    'is_like' => 0
                );

                if ($ok && $dbController->uploadNotificationAction($app, $notification) &&
                    $dbController->updateNotificationUser($app, $img['user_id'], 1, 1)) {
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

        $dbController = new DatabaseController();
        $img = $dbController->getImageAction($app, $idImg);
        $user = $dbController->getAction($app, $app['session']->get('name'));

        $like = array(
            'image_id' => $idImg,
            'user_id' => $user['id']
        );

        if (!$dbController->getLike($app, $idImg, $user['id'])) {

            $dbController->uploadLikeAction($app, $like);
            $like = $dbController->getLike($app, $idImg, $user['id']);

            $notification = array (
                'user_id' => $img['user_id'],
                'image_id' => $idImg,
                'like_id' => $like['id'],
                'is_like' => 1
            );

            $dbController->uploadNotificationAction($app, $notification);
            $dbController->updateNotificationUser($app, $img['user_id'], 1,  1);
            $dbController->updateLikeImage ($app, $img['id'], 1);
        }
        else {
            $dbController->deleteLikeAction($app, $dbController->getLike($app, $idImg, $user['id'])['id']);
            $dbController->deleteNotificationAction($app, $dbController->getNotification($app, $idImg, $img['user_id'])['id']);
            $dbController->updateNotificationUser($app, $img['user_id'], 1, 0);
            $dbController->updateLikeImage ($app, $img['id'], 0);
        }

        //header('Location: ' . '/', true, 303);
        //header('Location: ' . $_SERVER['HTTP_REFERER'], true, 303);
        //die();
        $img = $dbController->getImageAction($app, $idImg);
        // Check if the image path is valid
        if (!file_exists($img['img_path']) || !is_file($img['img_path'])) {
            // Generate a random number between 1 and 5
            $randomNumber = rand(1, 5);
            $img['img_path'] = '/assets/img/' . $randomNumber . '.png'; // Set to a random image
        }
        $array = array('img' => $img);

        $content = $app['twig']->render('ReloadLike.twig', $array);
        $response = new Response();
        $response->setStatusCode($response::HTTP_OK);
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent($content);
        return $response;
    }

    public function uploadImage(Application $app, Request $request) {
        $img = array('title' => $request->get('title'));
    
        $message = 'Your introduced data is erroneous. Change the camps with errors!';
        $imgFileArray = $request->files->get('img');
        var_dump($imgFileArray);
    
        // Check if imgFileArray is an array and contains the necessary file data
        if (is_array($imgFileArray) && isset($imgFileArray['tmp_name']) && $imgFileArray['error'] == 0) {
            if (!$this->uploadValidator($app, $img)) {
                $dbController = new DatabaseController();
                $this->user = $dbController->getAction($app, $app['session']->get('name'));
    
                // Assigning the tmp_name for further processing
                $tmp_name = $imgFileArray['tmp_name'];
                $nameBase = basename($imgFileArray['name']);
                $nameBase = uniqid() . "." . $nameBase;
                $name = $this->upload . $nameBase;
    
                move_uploaded_file($tmp_name, $name);
    
                header('Content-Type: image/jpeg');
    
                $thumb = imagecreatetruecolor(400, 300);
                $info = getimagesize($name);
                list($width, $height) = getimagesize($name);
    
                // Create the image resource based on the file type
                if ($info['mime'] == 'image/jpeg') {
                    $image = imagecreatefromjpeg($name);
                } elseif ($info['mime'] == 'image/gif') {
                    $image = imagecreatefromgif($name);
                } elseif ($info['mime'] == 'image/png') {
                    $image = imagecreatefrompng($name);
                }
    
                imagecopyresized($thumb, $image, 0, 0, 0, 0, 400, 300, $width, $height);
                imagejpeg($thumb, $this->upload . substr($nameBase, 0, strlen($nameBase) - 4) . "400x300.jpg", 75);
    
                $thumb = imagecreatetruecolor(100, 100);
                imagecopyresized($thumb, $image, 0, 0, 0, 0, 100, 100, $width, $height);
                imagejpeg($thumb, $this->upload . substr($nameBase, 0, strlen($nameBase) - 4) . "100x100.jpg", 75);
    
                $img = array(
                    'id' => $this->user['id'],
                    'title' => $request->get('title'),
                    'img' => $this->path . $nameBase,
                    'private' => $request->get('private') ? 1 : 0,
                );
    
                if ($dbController->uploadAction($app, $img)) {
                    header('Location: ' . '/', true, 303);
                    die();
                }
    
                $message = 'We had an issue signing you up. Please try again!';
            }
        } else {
            // Handle the case where the file is not uploaded correctly
            $message = 'Image upload failed. Please try again!';
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