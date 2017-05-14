<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolation;


class UploadController {

    public function upload (Application $app, $img) {

        $constraint = new Assert\Collection(array(
            'title' => array(new Assert\NotBlank(), new Assert\Regex(array('pattern' => '/^[A-Za-z0-9]+$/')), new Assert\Length(array('max' => 255))),
        ));

        $errors = $app['validator']->validate($img, $constraint);

        if (count($errors)) {
            foreach ($errors as $error) {
                echo $error->getPropertyPath().' '.$error->getMessage()."\n";
            }
        }
        return count($errors);
    }

    public function uploadComment (Application $app, $comment) {
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
}