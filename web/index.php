<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 29/03/2017
 * Time: 19:13
 */

ini_set('display_errors', 0);
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../app/app.php';
require __DIR__.'/../app/config/prod.php';
require __DIR__.'/../app/config/routes.php';
$app['debug'] = true;
$app->run();