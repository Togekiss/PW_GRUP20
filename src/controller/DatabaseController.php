<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DatabaseController {

    public function getAction (Application $app, $username) {
        $sql = "SELECT * FROM user WHERE username = ?";
        $user = $app['db']->fetchAssoc($sql, array((String)$username));
        return $user;
    }

    public function getActionId (Application $app, $id) {
        $sql = "SELECT * FROM user WHERE id = ?";
        $user = $app['db']->fetchAssoc($sql, array((int)$id));
        return $user;
    }

    public function getComment (Application $app, $idImg, $idUser) {
        $sql = "SELECT * FROM comments WHERE user_id = ? AND image_id = ?";
        $comment = $app['db']->fetchAssoc($sql, array((int)$idUser, (int)$idImg));
        return $comment;
    }

    public function postAction (Application $app, $name, $password) {
        $sql = "SELECT * FROM user WHERE username = ? AND password = ?";
        $user = $app['db']->fetchAssoc($sql, array($name, $password));
        if (!$user) {
            $sql = "SELECT * FROM user WHERE email = ? AND password = ?";
            $user = $app['db']->fetchAssoc($sql, array($name, $password));
        }
        return $user;
    }

    public function uploadAction (Application $app, $img) {
        $stmt = $app['db']->prepare("INSERT INTO images (user_id, title, img_path, visits, private, created_at) 
        VALUES (:user_id, :title, :img_path, :visits, :private, :created_at)");
        return $stmt->execute(array(
            ':user_id' => $img['id'],
            ':title' => $img['title'],
            ':img_path' => $img['img'],
            ':visits' => 0,
            ':private' => $img['private'],
            ':created_at' => $date = date('c')));
    }

    public function deleteAction (Application $app) {

    }

    public function signUpAction (Application $app, $user) {
        $stmt = $app['db']->prepare("INSERT INTO user (username, email, birthdate, password, img_path, active) VALUES (:username, :email, :birthdate, :password, :img_path, :active)");
        return $stmt->execute(array(
            ':username' => $user['name'],
            ':email' => $user['email'],
            ':birthdate' => $user['birthdate'],
            ':password' => $user['password'],
            ':img_path' => $user['img'],
            ':active' => 0));
    }

    public function updateAction (Application $app, $user) {
        $errors = 0;

        if ($user['name']) {
            $stmt = $app['db']->prepare("UPDATE user SET username=:name WHERE id=:id");
            $errors += $stmt->execute(array(':name' => $user['name'], ':id' => $user['id']));
        }
        if ($user['password']) {
            $stmt = $app['db']->prepare("UPDATE user SET password=:password WHERE id=:id");
            $errors += $stmt->execute(array(':password' => $user['password'], ':id' => $user['id']));
        }
        if ($user['birthdate']) {
            $stmt = $app['db']->prepare("UPDATE user SET birthdate=:birthdate WHERE id=:id");
            $errors += $stmt->execute(array(':birthdate' => $user['birthdate'], ':id' => $user['id']));
        }
        if ($user['img']) {
            $stmt = $app['db']->prepare("UPDATE user SET img_path=:img WHERE id=:id");
            $errors += $stmt->execute(array(':img' => $user['img'], ':id' => $user['id']));
        }

        return $errors;
    }

    public function mostViewed (Application $app) {
        return $app['db']->fetchAll('SELECT * FROM images ORDER BY visits DESC LIMIT 5');
    }

    public function mostRecent (Application $app) {
        return $app['db']->fetchAll('SELECT * FROM images ORDER BY created_at DESC LIMIT 5');
    }
}