<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 03/04/2017
 * Time: 15:45
 */

$app->get('/hello/{name}', 'PWGram\\controller\\HelloController::indexAction');
$app->get('/add/{num1}/{num2}', 'PWGram\\controller\\HelloController::addAction');