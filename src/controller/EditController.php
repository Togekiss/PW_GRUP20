<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;
use PWGram\controller\UserController as UserC;


class EditController {

    public function edit (Application $app, $user) {
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
}