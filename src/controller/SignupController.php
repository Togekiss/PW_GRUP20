<?php

namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use PWGram\controller\UserController as UserC;


class SignupController {

    public function signUp (Application $app, $user) {

        $constraint = new Assert\Collection(array(
            'name' => array(new Assert\NotBlank(), new Assert\Regex(array('pattern' => '/^[A-Za-z0-9]+$/')), new Assert\Length(array('max' => 20))),
            'email' => array(new Assert\NotBlank(), new Assert\Email(array('checkMX' => 'true', 'checkHost' => 'true'))),
            'birthdate' => array(new Assert\NotBlank(), new Assert\Date()),
            'password' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 6, 'max' => 12)), new Assert\Regex(array('pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'))),
            'password2' => array(new Assert\NotBlank(), new Assert\IdenticalTo(array('value' => $user['password']))),
        ));

        $errors = $app['validator']->validate($user, $constraint);
        if (count($errors)) {
            foreach ($errors as $error) {
                echo $error->getPropertyPath().' '.$error->getMessage()."\n";
            }
        }
        return count($errors);
    }
}