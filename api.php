<?php
/*
* Author: Maximilian Wessendorf
* Version: 0.0.1
*/

require 'vendor/autoload.php';

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

// Check whether opened or closed.
// Return "0" when closed and "1" when opened.
$app->get('/ampelstate', function ($request, $response, $args) {
    $data = db()->query("SELECT * FROM opening_times ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($data["close_at"] != null) {
        echo json_encode("0");
    } else if ($data["close_at"] == null) {
        echo json_encode("1");
    }
    
});

// END of queries.

$app->run();

function db() {
    require 'config.php';
    return new Medoo([
        'database_type' => $database_type,
        'database_name' => $database_name,
        'server' => $server,
        'username' => $username,
        'password' => $password
    ]);
}

?>