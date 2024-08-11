<?php
/**
 * Created by PhpStorm.
 * User: Marta
 * Date: 29/03/2017
 * Time: 19:13
 */

ob_start();

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../app/app.php';
require __DIR__.'/../app/config/prod.php';
require __DIR__.'/../app/config/routes.php';

// Call session_start() after the configuration settings
session_start();

$app['debug'] = true;

// Load and execute the SQL schema file
$schemaFile = __DIR__ . '/../app/config/schema.sql';
if (file_exists($schemaFile)) {
    $schemaSQL = file_get_contents($schemaFile);
    $app['db']->executeQuery($schemaSQL);
} else {
    throw new Exception("Schema file not found: $schemaFile");
}

$app->run();

ob_end_flush(); // Send the output buffer content
