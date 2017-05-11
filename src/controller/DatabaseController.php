<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DatabaseController {

    public function getAction(Application $app, $id) {
        $sql = "SELECT * FROM user WHERE id = ?";
        $user = $app['db']->fetchAssoc($sql, array((int)$id));
        $response = new Response();
        if (!$user) {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $content = $app['twig']->render('error.twig', ['message' => 'User not found']);
        }
        else {
            $response->setStatusCode(Response::HTTP_OK);
            $content = $app['twig']->render('user.twig', ['user' => $user]);
        }
        $response->setContent($content);
        return $response;
    }

    public function postAction(Application $app, $name, $password) {
        $sql = "SELECT * FROM user WHERE username = ? AND password = ?";
        $user = $app['db']->fetchAssoc($sql, array($name, $password));
        return $user;
    }

    public function deleteAction(Application $app) {

    }

    public function signUpAction (Application $app, $user) {
       /* $sql = "INSERT INTO user (username, email, birthdate, password, img_path, active) VALUES (?, ?, ?, ?, ?, ?)";
        $ok = $app['db']->fetchAssoc($sql, array($user['name'], $user['email'], $user['birthdate'], $user['password'], $user['img'], 0));*/
        $stmt = $app['db']->prepare("INSERT INTO user (username, email, birthdate, password, img_path, active) VALUES (:username, :email, :birthdate, :password, :img_path, :active)");
        $stmt->execute(array(
            ':username' => $user['name'],
            ':email' => $user['email'],
            ':birthdate' => $user['birthdate'],
            ':password' => $user['password'],
            ':img_path' => $user['img'],
            ':active' => 0));
        echo "Database updated!";
        return $stmt->get_result();
    }

    public function updateAction(Application $app, $id, $name, $password, $birthdate, $img) {
        $first = false;

        $sql = "UPDATE user SET";
        if ($name) $sql = $sql . "username=? ,";
        if ($password) $sql = $sql . "password=? ,";
        if ($birthdate) $sql = $sql . "birthdate=? ,";
        if ($img) $sql = $sql . "img_path=? ,";
        $sql = $sql . "WHERE id=?";

        $user = $app['db']->fetchAssoc($sql, array($name, $password));
        return $user;
    }
}