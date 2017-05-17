<?php

namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use PWGram\controller\UserController as UserC;


class SignupController {

    private $user;
    public $default = '/assets/img/default_portrait.png';
    public $upload = __DIR__ . '/../../web/assets/img/';
    public $path = '/assets/img/';

    public function signUpValidation (Application $app, $user) {
        $date = date('Y-m-d');

        $constraint = new Assert\Collection(array(
            'name' => array(new Assert\NotBlank(), new Assert\Regex(array('pattern' => '/^[A-Za-z0-9]+$/')), new Assert\Length(array('max' => 20))),
            'email' => array(new Assert\NotBlank(), new Assert\Email(array('checkMX' => 'true', 'checkHost' => 'true'))),
            'birthdate' => array(new Assert\NotBlank(), new Assert\Date()),
            'password' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 6, 'max' => 12)), new Assert\Regex(array('pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'))),
            'password2' => array(new Assert\NotBlank(), new Assert\IdenticalTo(array('value' => $user['password']))),
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

    public function signUp (Application $app, Request $request) {

        $user = array(
            'name' => $request->get('user'),
            'email' => $request->get('email'),
            'birthdate' => $request->get('birthdate'),
            'password' => $request->get('password'),
            'password2' => $request->get('password2'),
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $message = 'Your introduced data is erroneous. Change the camps with errors!';

        if (!$this->signUpValidation($app, $user)) {
            $userController = new DatabaseController();
            $user['password'] = md5($user['password']);

            if ($request->files->get('img') && !$request->files->get('img')->getError()) {
                $tmp_name = $request->files->get('img');
                $nameBase = basename($request->files->get('img')->getClientOriginalName());
                $nameBase = uniqid() . "." . $nameBase;
                $name = $this->upload . $nameBase;
                move_uploaded_file($tmp_name, $name);
                $user['img'] = $this->path . $nameBase;
            }

            if (!$user['img']) $user['img'] = $this->default;
            $user['activate_string'] = md5(uniqid(rand()));
            if ($userController->signUpAction($app, $user)) {
                $this->sendMail($user['email'], $user['activate_string']);
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

    public function sendMail ($email, $id) {
        $mail = new \PHPMailer();
        $mail-> IsSMTP();
        $mail->SMTPAuth=true;
        $mail->SMTPSecure = "ssl";
        $mail->Host="smtp.gmail.com";
        $mail->Port=465;
        $mail->Username ="rogermarrugat96@gmail.com";
        $mail->Password="power123";
        $mail->SetFrom('rogermarrugat96@gmail.com','PWGRAM - GRUP 20');
        $mail->addReplyTo('rogermarrugat96@gmail.com','PWGRAM - GRUP 20');
        $mail->Subject="ACTIVATE USER PWGRAM";
        $mail->msgHTML("www.grup20.com/activateUser/". $id);
        $mail->addAddress($email," ");
        if(!$mail->send()) {
            echo "Error in mail: " . $mail->ErrorInfo;
        }else {
            echo "Mail sent!";
        }
    }

    public function activateUser (Application $app, Request $request, $idActivate) {
        if ($app['session']->has('name')) {
            $this->user = null;
            $app['session']->clear();
        }
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $userController = new DatabaseController();
        if ($userController->activateUser ($app, $idActivate)) {
            $user = $userController->getActionIdActive($app, $idActivate);
            $app['session']->start();
            $app['session']->set('name', $user['username']);
            header('Location: ' . '/', true, 303);
            die();
        }else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $app['twig']->render('error.twig', array(
                'app' => ['name' => $app['app.name']],
                'message' => "We could not activate your account. Please try again!"
            ));
            $response->setContent($content);
            return $response;
        }
    }

}