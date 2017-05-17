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

    public function getActionIdActive(Application $app, $id)
    {
        $sql = "SELECT * FROM user WHERE activate_string = ?";
        $user = $app['db']->fetchAssoc($sql, array((String)$id));
        return $user;
    }

    public function activateUser (Application $app, $id)
    {
        $stmt = $app['db']->prepare("UPDATE user SET active= 1 WHERE activate_string=:id");
        return $stmt->execute(array(':id' => $id));
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

    public function getNumComment(Application $app, $idUser) {
        $sql = "SELECT id FROM comments WHERE user_id = ?";
        $stmt  = $app['db']->prepare($sql);
        $stmt->bindValue(1, $idUser);
        $stmt->execute();
        $num = $stmt->fetchAll();
        if ($num) return count($num);
        return 0;
    }

    public function getNumImages(Application $app, $idUser) {
        $sql = "SELECT id FROM images WHERE user_id = ?";
        $stmt  = $app['db']->prepare($sql);
        $stmt->bindValue(1, $idUser);
        $stmt->execute();
        $num = $stmt->fetchAll();
        if ($num) return count($num);
        return 0;
    }

    public function getComment(Application $app, $idImg, $idUser)
    {
        $sql = "SELECT * FROM comments WHERE user_id = ? AND image_id = ?";
        $comment = $app['db']->fetchAssoc($sql, array((int)$idUser, (int)$idImg));
        return $comment;
    }

    public function getCommentId(Application $app, $idCom)
    {
        $sql = "SELECT * FROM comments WHERE id = ?";
        return $app['db']->fetchAssoc($sql, array((int)$idCom));
    }

    public function getAllComments(Application $app, $idUser)
    {
        $sql = "SELECT * FROM comments WHERE user_id = ?";
        $stmt = $app['db']->prepare($sql);
        $stmt->bindValue(1, $idUser);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllNotifications (Application $app, $idUser)
    {
        $sql = "SELECT * FROM notification WHERE user_id = ?";
        $stmt = $app['db']->prepare($sql);
        $stmt->bindValue(1, $idUser);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPublicImages(Application $app, $idUser, $selection) {
        if ($selection == 1) {
            $sql = "SELECT * FROM images WHERE private = 0 AND user_id = ? ORDER BY likes DESC";
        }else if ($selection == 2) {
            $sql = "SELECT images.*, count(comments.id) AS comment FROM images, comments WHERE private = 0 AND images.user_id = ? AND images.id = comments.image_id GROUP BY images.id ORDER BY comment DESC";
        }else if ($selection == 3) {
            $sql = "SELECT * FROM images WHERE private = 0 AND user_id = ? ORDER BY visits DESC";
        }else {
            $sql = "SELECT * FROM images WHERE private = 0 AND user_id = ? ORDER BY created_at DESC";
        }
        $stmt  = $app['db']->prepare($sql);
        $stmt->bindValue(1, $idUser);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllImages(Application $app, $idUser, $selection) {
        if ($selection == 1) {
            $sql = "SELECT * FROM images WHERE  user_id = ? ORDER BY likes DESC";
        }else if ($selection == 2) {
            $sql = "SELECT images.*, count(comments.id) AS comment FROM images, comments WHERE images.user_id = ? AND images.id = comments.image_id GROUP BY images.id ORDER BY comment DESC";
        }else if ($selection == 3) {
            $sql = "SELECT * FROM images WHERE user_id = ? ORDER BY visits DESC";
        }else {
            $sql = "SELECT * FROM images WHERE user_id = ? ORDER BY created_at DESC";
        }
        $stmt  = $app['db']->prepare($sql);
        $stmt->bindValue(1, $idUser);
        $stmt->execute();
        return $stmt->fetchAll();
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

    public function getNotificationId(Application $app, $idNot)
    {
        $sql = "SELECT * FROM notification WHERE id = ?";
        return $app['db']->fetchAssoc($sql, array((int)$idNot));
    }

    public function getNotificationNum(Application $app, $idImg) {
        $sql = "SELECT id FROM notification WHERE image_id = ?";
        $stmt  = $app['db']->prepare($sql);
        $stmt->bindValue(1, $idImg);
        $stmt->execute();
        return count($stmt->fetchAll());
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
        $stmt = $app['db']->prepare("INSERT INTO user (username, email, birthdate, password, img_path, active, activate_string) VALUES (:username, :email, :birthdate, :password, :img_path, :active, :activate_string)");
        return $stmt->execute(array(
            ':username' => $user['name'],
            ':email' => $user['email'],
            ':birthdate' => $user['birthdate'],
            ':password' => $user['password'],
            ':img_path' => $user['img'],
            ':active' => 0,
            ':activate_string' => $user['activate_string']));
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

    public function mostRecent(Application $app, $limit)
    {
        return $app['db']->fetchAll('SELECT * FROM images WHERE private = 0 ORDER BY created_at DESC LIMIT '. $limit);
    }

    public function mostRecentComment(Application $app, $idImg)
    {
        $sql = "SELECT * FROM comments WHERE image_id = ? ORDER BY id DESC LIMIT 1";
        return $app['db']->fetchAssoc($sql, array((int)$idImg));
    }

    public function getImageComments(Application $app, $idImg, $limit)
    {
        return $app['db']->fetchAll('SELECT comments.*, user.username FROM comments, user
                                      WHERE user.id = comments.user_id AND image_id = '. $idImg.' ORDER BY id DESC LIMIT '. $limit);

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

    public function updateNotificationUser(Application $app, $id, $number, $positive) {
        if ($positive) $stmt = $app['db']->prepare("UPDATE user SET notifications= notifications + :num WHERE id=:id");
        else $stmt = $app['db']->prepare("UPDATE user SET notifications= notifications - :num WHERE id=:id");
        return $stmt->execute(array(':id' => $id, ':num' => $number));
    }

    public function updateLikeImage (Application $app, $id, $positive) {
        if ($positive) $stmt = $app['db']->prepare("UPDATE images SET likes = likes + 1 WHERE id=:id");
        else $stmt = $app['db']->prepare("UPDATE images SET likes = likes - 1 WHERE id=:id");
        return $stmt->execute(array(':id' => $id));
    }

    public function updateVisits(Application $app, $id) {
        $stmt = $app['db']->prepare("UPDATE images SET visits= visits + 1 WHERE id=:id");
        return $stmt->execute(array(':id' => $id));
    }

    public function updateImage (Application $app, $img) {
        $errors = 0;

        if ($img['title']) {
            $stmt = $app['db']->prepare("UPDATE images SET title=:title WHERE id=:id");
            $errors += $stmt->execute(array(':title' => $img['title'], ':id' => $img['id']));
        }
        if ($img['img']) {
            $stmt = $app['db']->prepare("UPDATE images SET img_path=:img WHERE id=:id");
            $errors += $stmt->execute(array(':img' => $img['img'], ':id' => $img['id']));
        }
        if ($img['private']) {
            $stmt = $app['db']->prepare("UPDATE images SET private=:private WHERE id=:id");
            $errors += $stmt->execute(array(':private' => $img['private'], ':id' => $img['id']));
        }

        return $errors;
    }

    public function updateComment (Application $app, $comment) {

        if ($comment['text']) {
            $stmt = $app['db']->prepare("UPDATE comments SET text=:text WHERE id=:id");
            return $stmt->execute(array(':text' => $comment['text'], ':id' => $comment['id']));
        }

        return false;
    }

    public function deleteLikeAction(Application $app, $id) {
        $stmt = $app['db']->prepare("DELETE FROM likes WHERE id = :id");
        return $stmt->execute(array(':id' => $id));
    }

    public function deleteNotificationAction(Application $app, $id) {
        $stmt = $app['db']->prepare("DELETE FROM notification WHERE id = :id");
        return $stmt->execute(array(':id' => $id));
    }

    public function deleteCommentAction(Application $app, $id) {
        $stmt = $app['db']->prepare("DELETE FROM comments WHERE id = :id");
        return $stmt->execute(array(':id' => $id));
    }

    public function deleteImageAction (Application $app, $id) {
        $stmt = $app['db']->prepare("DELETE FROM images WHERE id = :id");
        return $stmt->execute(array(':id' => $id));
    }
}