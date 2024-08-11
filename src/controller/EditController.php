<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use PWGram\controller\UserController as UserC;


class EditController {

    private $user;
    public $default = '/assets/img/default_portrait.png';
    public $upload = __DIR__ . '/../../public/assets/img/';
    public $path = '/assets/img/';

    public function editValidation (Application $app, $user) {
        $date = date('Y-m-d');

        $constraint = new Assert\Collection(array(
            'name' => array(new Assert\Regex(array('pattern' => '/^[A-Za-z0-9]+$/')), new Assert\Length(array('max' => 20))),
            'birthdate' => array(new Assert\Date()),
            'password' => array(new Assert\Length(array('min' => 6, 'max' => 12)), new Assert\Regex(array('pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'))),
        ));

        $errors = $app['validator']->validate($user, $constraint);

        if ($user['birthdate'] > $date) {
            $error1 = new ConstraintViolation('This value should not be in the future.', '', [], $user['birthdate'], '[birthdate]', 'birthdate');
            $errors->add($error1);
        }
        if (count($errors)) {
            foreach ($errors as $error) {
                echo $error->getPropertyPath().' '.$error->getMessage()."\n";
            }
        }
        return count($errors);
    }

    public function editUser (Application $app, Request $request) {

        $user = array(
            'name' => $request->get('user'),
            'birthdate' => $request->get('birthdate'),
            'password' => $request->get('password'),
        );

        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if (!$this->editValidation($app, $user)) {
            $dbController = new DatabaseController();
            $this->user = $dbController->getAction($app, $app['session']->get('name'));

            $imgFileArray = $request->files->get('img');

            if (is_array($imgFileArray) && isset($imgFileArray['tmp_name']) && $imgFileArray['error'] == 0) {
                
                $this->user = $dbController->getAction($app, $app['session']->get('name'));
            
                // Using the tmp_name for further processing
                $tmp_name = $imgFileArray['tmp_name'];
                $nameBase = basename($imgFileArray['name']);
                $nameBase = uniqid() . "." . $nameBase;
                $name = $this->upload . $nameBase;
            
                move_uploaded_file($tmp_name, $name);
            }
            

            $user = array(
                'name' => $user['name']?$user['name']:null,
                'password' => $user['password']?md5($user['password']):null,
                'birthdate' => $user['birthdate']?$user['birthdate']:null,
                'img' => $nameBase?$this->path . $nameBase:null,
                'id' => $this->user['id']
            );

            if (!$dbController->getAction($app, $user['name'])) {
                if ($dbController->updateAction($app, $user) == count(array_filter($user)) - 1) {
                    if ($user['name']) $app['session']->set('name', $user['name']);
                    $this->user = $dbController->getAction($app, $app['session']->get('name'));
                    header('Location: ' . $_SERVER['HTTP_REFERER'], true, 303);
                    die();
                }
            }
            $message = 'Repeated username. Please try a diferent one!';
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

    public function editImage(Application $app, Request $request, $idImg) {
        $dbController = new DatabaseController();
    
        $imgCheck = array('title' => $request->get('title'));
    
        $uploadController = new UploadController();
        $message = 'Your introduced data is erroneous. Change the camps with errors!';
    
        if (!$uploadController->updateValidator($app, $imgCheck)) {
            $imgFileArray = $request->files->get('img');
    
            // Check if imgFileArray is an array and contains the necessary file data
            if (is_array($imgFileArray) && isset($imgFileArray['tmp_name']) && $imgFileArray['error'] == 0) {
                $this->user = $dbController->getAction($app, $app['session']->get('name'));
    
                // Assigning the tmp_name for further processing
                $tmp_name = $imgFileArray['tmp_name'];
                $nameBase = basename($imgFileArray['name']);
                $nameBase = uniqid() . "." . $nameBase;
                $name = $this->upload . $nameBase;
    
                move_uploaded_file($tmp_name, $name);
            }
    
            $img = array(
                'id' => $idImg,
                'title' => $request->get('title') ? $request->get('title') : null,
                'img' => isset($nameBase) ? $this->path . $nameBase : null,
                'private' => $request->get('private') ? 1 : 0,
            );
    
            if ($dbController->updateImage($app, $img) == count(array_filter($img)) - 1) {
                header('Location: ' . '/user/' . $dbController->getAction($app, $app['session']->get('name'))['id'] . "/1", true, 303);
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
        $dbController = new DatabaseController();

        $comment = array('id' => $idComment, 'text' => $request->get('text'));

        if ($dbController->updateComment($app, $comment)) {
            header('Location: ' . '/comment-list/' . $dbController->getAction($app, $app['session']->get('name'))['id'], true, 303);
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

}