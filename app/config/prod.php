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
        'css' => array('base_path' => '/assets/css/'), // Relative to your application's root URL
        'js' => array('base_path' => '/assets/js/'), // Relative to your application's root URL
        'images' => array('base_urls' => array(
            getenv('HEROKU_APP_URL') . '/assets/img/' // Full URL based on environment variable
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

$dburl = parse_url(getenv('JAWSDB_URL'));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array (
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => ltrim($dburl['path'], '/'),
        'host'     => $dburl['host'],
        'user'     => $dburl['user'],
        'password' => $dburl['pass'],
        'port'     => isset($dburl['port']) ? $dburl['port'] : 3306,
    ),));
