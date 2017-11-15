<?php
/*
* Author: Maximilian Wessendorf
* Version: 0.0.1
*/

require 'vendor/autoload.php';
include 'config.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

// Using Medoo namespace
use Medoo\Medoo;

// START of queries:


// END of queries.

$app->run();

function db() {
    return new Medoo([
        'database_type' => $database_type,
        'database_name' => $database_name,
        'server' => $server,
        'username' => $username,
        'password' => $password
    ]);
}

?>