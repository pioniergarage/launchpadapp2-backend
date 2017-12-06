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

// Add Acces-Control-Allow-Origin
$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

// START of queries:

// Check whether opened or closed.
// Return {"state":"0", "opensince":"{datetime}"} when closed and {"state":"1", "opensince":""} when opened.
$app->get('/openstate', function ($request, $response, $args) {
    $data = db()->query("SELECT * FROM opening_times ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($data["close_at"] != null) {
        $return = array("state" => "0", "opensince" => "");
        echo json_encode($return);
    } else if ($data["close_at"] == null) {
        $return = array("state" => "1", "opensince" => $data["open_at"]);
        echo json_encode($return);
    }
    
});

// Trigger change of the opened/closed state. Max. execution rate is 3sec.
// Return "successto0" when closed and "successto1" when opened.
$app->get('/openchange', function ($request, $response, $args) {
    // Get Last entry.
    $lastEntry = db()->query("SELECT * FROM opening_times ORDER BY id DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    // Getting the time in seconds since last change
    $timeSinceLastChange;
    if ($lastEntry["close_at"] == null) {
        $timeSinceLastChange = db()->query("SELECT TIMESTAMPDIFF(SECOND, open_at, NOW()) FROM `opening_times` WHERE id = :id", [":id" => $lastEntry["id"]])->fetch(PDO::FETCH_ASSOC);
        $timeSinceLastChange = $timeSinceLastChange["TIMESTAMPDIFF(SECOND, open_at, NOW())"];
    } else if ($lastEntry["close_at"] != null) {
        $timeSinceLastChange = db()->query("SELECT TIMESTAMPDIFF(SECOND, close_at, NOW()) FROM `opening_times` WHERE id = :id", [":id" => $lastEntry["id"]])->fetch(PDO::FETCH_ASSOC);
        $timeSinceLastChange = $timeSinceLastChange["TIMESTAMPDIFF(SECOND, close_at, NOW())"];
    }

    if (intval($timeSinceLastChange) >= 3) {
        // When the last chang had been more than 3 seconds ago
        if ($lastEntry["close_at"] == null) {
            // When last entry is open -> close it and return successto0
            db()->query("UPDATE opening_times SET close_at = CURRENT_TIMESTAMP WHERE id = :id", [":id" => $lastEntry["id"]]);
            $return = array("state" => "success", "changedTo" => "0");
            echo json_encode($return);
        } else if ($lastEntry["close_at"] != null) {
            // When last entry is closed -> open new entry and return successto1
            db()->query("INSERT INTO opening_times (open_at) VALUES (CURRENT_TIMESTAMP)");
            $return = array("state" => "success", "changedTo" => "1");
            echo json_encode($return);
        }

    } else {
        // Return Error
        $return = array("state" => "error", "changedTo" => "no change - to early");
        echo json_encode($return);
    }
    
    
});

// Get the time frames of the open state for today.
$app->get('/opentoday', function ($request, $response, $args) {
    // Get all entries that where opened or closed today.
    $todaysEntries = db()->query("SELECT * FROM opening_times WHERE open_at >= CURDATE() OR close_at >= CURDATE()")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($todaysEntries);
});

// Get the time frames of the open state for the last month.
$app->get('/openmonth', function ($request, $response, $args) {
    // Get all entries that where opened or closed in the last month.
    $monthsEntries = db()->query("SELECT * FROM opening_times WHERE open_at >= (CURRENT_TIMESTAMP - INTERVAL 1 MONTH) OR close_at >= (CURRENT_TIMESTAMP - INTERVAL 1 MONTH)")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($monthsEntries);
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