<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 29/03/2017
 * Time: 19:07
 */

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../../src/view/template',
));

$app->register(new Silex\Provider\AssetServiceProvider(), array(
    'assets.version' => 'v1',
    'assets.version_format' => '%s?version=%s',
    'assets.named_packages' => array(
        'css' => array('base_path' => 'assets/css'),
        'js' => array('base_path' => 'assets/js'),
        'images' => array('base_urls' => array('http://silexapp.dev/assets/img')),
    ),
));

$app->register(new \PWGram\providers\HelloServiceProvider(), array(
   'hello.default_name' => 'Marta',
));

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\FormServiceProvider());

