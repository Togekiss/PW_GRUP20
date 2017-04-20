<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 29/03/2017
 * Time: 19:08
 */

use Silex\Application;

$app = new Application();
$app['app.name'] = 'SilexApp';
$app['calc'] = function(){
    return new \PWGram\model\services\Calculator();
};
return $app;