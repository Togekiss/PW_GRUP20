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
    public $upload = __DIR__ . '/../../public/assets/img/';
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

    public function signUp(Application $app, Request $request) {
        // Collect user data from the request
        $user = array(
            'name' => $request->get('user'),
            'email' => $request->get('email'),
            'birthdate' => $request->get('birthdate'),
            'password' => $request->get('password'),
            'password2' => $request->get('password2'),
        );
    
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $message = 'Your introduced data is erroneous. Change the fields with errors!';
    
        // Validate the user data
        if (!$this->signUpValidation($app, $user)) {
            $userController = new DatabaseController();
            $user['password'] = md5($user['password']);
    
            // Check if an image file was uploaded and if there are no errors
            $imgFile = $request->files->get('img');
            if ($imgFile && !$imgFile->getError()) {
                // Process the uploaded image
                $tmp_name = $imgFile->getPathname();
                $nameBase = uniqid() . "." . basename($imgFile->getClientOriginalName());
                $name = $this->upload . $nameBase;
                move_uploaded_file($tmp_name, $name);
                $user['img'] = $this->path . $nameBase;
            } else {
                // Use the default image if no file was uploaded
                $user['img'] = $this->default;
            }
    
            // Generate an activation string
            $user['activate_string'] = md5(uniqid(rand()));
    
            // Check if the username or email is already in use
            if (!$userController->getAction($app, $user['name']) && !$userController->getActionEmail($app, $user['email'])) {
                // Attempt to sign up the user
                if ($userController->signUpAction($app, $user)) {
                    // Use a mock email sending function to simulate activation email
                    $mockEmailResponse = $this->sendMail($user['email'], $user['activate_string']);
                    $message = $mockEmailResponse; // Display mock message instead of actual email
                    $response->setStatusCode(Response::HTTP_OK);
                } else {
                    $message = 'An error occurred while creating your account. Please try again!';
                }
            } else {
                $message = 'Username or email already in use. Please try a different one!';
            }
        }
    
        // Render the error or success message
        $content = $app['twig']->render('error.twig', array(
            'app' => ['name' => $app['app.name']],
            'message' => $message
        ));
        $response->setContent($content);
        $response->setStatusCode($response->getStatusCode() === Response::HTTP_OK ? Response::HTTP_OK : Response::HTTP_NOT_FOUND);
    
        return $response;
    }
    
    

    public function sendMail($email, $id) {
        // Instead of sending an email, return a mock response for testing
        return "Mock email sent to {$email} with activation link: togekiss-pw-20.com/activateUser/{$id}";
    }
    
    /*
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
    }
    */

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