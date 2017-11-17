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

// Trigger change of the opened/closed state. Max. execution rate is 5sec.
// Return "successto0" when closed and "successto1" when opened.
$app->get('/ampelchange', function ($request, $response, $args) {
    // Get Last entry.
    $lastEntry = db()->query("SELECT * FROM opening_times ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($lastEntry["close_at"] == null) {
        // When last entry is open -> close it and return successto0
        db()->query("UPDATE opening_times SET close_at = CURRENT_TIMESTAMP WHERE id = :id", [":id" => $lastEntry["id"]]);
        echo json_encode("successto0");
    } else if ($lastEntry["close_at"] != null) {
        // When last entry is closed -> open new entry and return successto1
        db()->query("INSERT INTO opening_times (open_at) VALUES (CURRENT_TIMESTAMP)");
        echo json_encode("successto1");
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