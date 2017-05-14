<?php
namespace PWGram\controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class DatabaseController
{

    public function getAction(Application $app, $username)
    {
        $sql = "SELECT * FROM user WHERE username = ?";
        $user = $app['db']->fetchAssoc($sql, array((String)$username));
        return $user;
    }

    public function getImageAction(Application $app, $id)
    {
        $sql = "SELECT * FROM images WHERE id = ?";
        return $app['db']->fetchAssoc($sql, array((int)$id));
    }

    public function getActionId(Application $app, $id)
    {
        $sql = "SELECT * FROM user WHERE id = ?";
        $user = $app['db']->fetchAssoc($sql, array((int)$id));
        return $user;
    }

    public function getComment(Application $app, $idImg, $idUser)
    {
        $sql = "SELECT * FROM comments WHERE user_id = ? AND image_id = ?";
        $comment = $app['db']->fetchAssoc($sql, array((int)$idUser, (int)$idImg));
        return $comment;
    }

    public function getLike(Application $app, $idImg, $idUser)
    {
        $sql = "SELECT * FROM likes WHERE user_id = ? AND image_id = ?";
        $comment = $app['db']->fetchAssoc($sql, array((int)$idUser, (int)$idImg));
        return $comment;
    }

    public function getNotification(Application $app, $idImg, $idUser)
    {
        $sql = "SELECT * FROM notification WHERE user_id = ? AND image_id = ? AND is_like = 1";
        return $notifications = $app['db']->fetchAssoc($sql, array((int)$idUser, (int)$idImg));
    }

    public function postAction(Application $app, $name, $password)
    {
        $sql = "SELECT * FROM user WHERE username = ? AND password = ?";
        $user = $app['db']->fetchAssoc($sql, array($name, $password));
        if (!$user) {
            $sql = "SELECT * FROM user WHERE email = ? AND password = ?";
            $user = $app['db']->fetchAssoc($sql, array($name, $password));
        }
        return $user;
    }

    public function uploadAction(Application $app, $img)
    {
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

    public function deleteAction(Application $app)
    {

    }

    public function signUpAction(Application $app, $user)
    {
        $stmt = $app['db']->prepare("INSERT INTO user (username, email, birthdate, password, img_path, active) VALUES (:username, :email, :birthdate, :password, :img_path, :active)");
        return $stmt->execute(array(
            ':username' => $user['name'],
            ':email' => $user['email'],
            ':birthdate' => $user['birthdate'],
            ':password' => $user['password'],
            ':img_path' => $user['img'],
            ':active' => 0));
    }

    public function updateAction(Application $app, $user)
    {
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

    public function mostViewed(Application $app)
    {
        return $app['db']->fetchAll('SELECT * FROM images WHERE private = 0 ORDER BY visits DESC LIMIT 5');
    }

    public function mostRecent(Application $app)
    {
        return $app['db']->fetchAll('SELECT * FROM images WHERE private = 0 ORDER BY created_at DESC LIMIT 5');
    }

    public function uploadCommentAction(Application $app, $comment) {
        $stmt = $app['db']->prepare("INSERT INTO comments (user_id, image_id, text) VALUES (:user_id, :image_id, :text)");
        return $stmt->execute(array(
            ':user_id' => $comment['user_id'],
            ':image_id' => $comment['image_id'],
            ':text' => $comment['text']));
    }

    public function uploadLikeAction(Application $app, $like) {
    $stmt = $app['db']->prepare("INSERT INTO likes (user_id, image_id) VALUES (:user_id, :image_id)");
    return $stmt->execute(array(
        ':user_id' => $like['user_id'],
        ':image_id' => $like['image_id']));
    }

    public function uploadNotificationAction(Application $app, $notification) {
        $stmt = $app['db']->prepare("INSERT INTO notification (user_id, image_id, like_id, is_like) VALUES (:user_id, :image_id, :like_id, :is_like)");
        return $stmt->execute(array(
            ':user_id' => $notification['user_id'],
            ':image_id' => $notification['image_id'],
            ':like_id' => $notification['like_id'],
            ':is_like' => $notification['is_like']
        ));
    }

    public function updateNotificationUser(Application $app, $id, $positive) {
        if ($positive) $stmt = $app['db']->prepare("UPDATE user SET notifications= notifications + 1 WHERE id=:id");
        else $stmt = $app['db']->prepare("UPDATE user SET notifications= notifications - 1 WHERE id=:id");
        return $stmt->execute(array(':id' => $id));
    }

    public function deleteLikeAction(Application $app, $id) {
        $stmt = $app['db']->prepare("DELETE FROM likes WHERE id = :id");
        return $stmt->execute(array(':id' => $id));
    }

    public function deleteNotificationAction(Application $app, $id) {
        $stmt = $app['db']->prepare("DELETE FROM notification WHERE id = :id");
        return $stmt->execute(array(':id' => $id));
    }
}