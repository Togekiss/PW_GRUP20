<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 05/04/2017
 * Time: 19:15
 */

namespace Marta\Silex\src\providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;


class HelloServiceProvider implements ServiceProviderInterface {

    public function register(Container $app) {
        // TODO: Implement register() method.

        $app['hello'] = $app->protect(function ($name) use ($app) {
           $default = $app['hello.default_name'] ? $app['hello.default_name']: '';
           $name = $name ?: $default;

           return $app['twig']->render('hello.twig', array(
               'user' => $name,
                'app' => [
                    'name' => $app['app.name'
                ]]
            ));
        });

    }

}