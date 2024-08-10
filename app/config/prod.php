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
        'css' => array('base_path' => '/assets/css/'),
        'js' => array('base_path' => '/assets/js/'),
        'images' => array('base_urls' => array(
            // Use the HEROKU_APP_URL environment variable
            getenv('HEROKU_APP_URL') . '/assets/img/'
        )),
    ),
));

$app->register(new \Silex\Provider\SessionServiceProvider());

$app->register(new \Silex\Provider\FormServiceProvider());

$app->register(new \Silex\Provider\LocaleServiceProvider());

$app->register(new \Silex\Provider\ValidatorServiceProvider());

$app->register(new \Silex\Provider\TranslationServiceProvider(), array('translator.domains' => array(),));

$app->register(new \PWGram\providers\HelloServiceProvider(), array(
   'hello.default_name' => 'Marta',
));

$app->register(new Silex\Provider\SessionServiceProvider());

$url = parse_url(getenv('JAWSDB_URL'));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array (
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => ltrim($url['path'], '/'),
        'host'     => $url['host'],
        'user'     => $url['user'],
        'password' => $url['pass'],
        'port'     => isset($url['port']) ? $url['port'] : 3306,
    ),));
