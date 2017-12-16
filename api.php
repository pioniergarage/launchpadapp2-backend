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

//AUTH-MIDDLEWARE
$app->add(function ($request, $response, $next) {
    $tokenAuth = $request->getHeaders();
    $receivedToken = $tokenAuth["HTTP_AUTHORIZATION"][0];

    $path = $request->getUri()->getPath();

    if (($path == "openchange") || ($path == "addTrackedMac")) {
        require 'config.php';
        if ($receivedToken == $apikey) {
            $newresponse = $next($request, $response);
        } else {
            $newresponse = $response->withStatus(401);
        }
    } else {
        $newresponse = $next($request, $response);
    }
	
	return $newresponse;
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
// Require api key to be postet {"key": "{a key}"}
$app->post('/openchange', function ($request, $response) {

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

$app->post('/addTrackedMac', function($request, $response) {
    $data = $request->getParsedBody();
    $macHash = $data['macHash'];

    $exec = db()->query("UPDATE presence_tracking SET updated_at = CURRENT_TIMESTAMP WHERE macHash = :macHash", [":macHash" => $macHash]);
    $isUpdated = $exec->rowCount();
    if ($isUpdated) {
        echo json_encode("success");
    } else if (!$isUpdated) {
        $exec = db()->query("INSERT INTO presence_tracking (macHash, created_at, updated_at) VALUES (:macHash, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)", [":macHash" => $macHash]);
        $isInserted = $exec->rowCount();
        if ($isInserted) {
            echo json_encode("success");
        } else {
            echo json_encode("fail");
        }
    }
});

$app->get('/getRecentMacs', function($request, $response, $args) {
    $data = db()->select("presence_tracking", [
        "macHash",
        "created_at",
        "updated_at"
    ]);
    echo json_encode($data);
});


// USER HANDLING

$app->post('/user/newuser', function($request, $response) {
    $data = $request->getParsedBody();
    $macHash = $data['macHash'];
    $name = $data['name'];
    $role = $data['role'];
    $imageRef = $data['imageRef'];

    if (($macHash != "") && ($name != "") && ($role != "") && ($imageRef != "")) {
        db()->insert('users', [
            'macHash' => $macHash,
            'name' => $name,
            'role' => $role,
            'imageRef' => $imageRef
        ]);
    }
});

$app->get('/user/activeUsers', function($request, $response) {
    $data = db()->query("SELECT users.name, users.role, users.imageRef FROM users INNER JOIN presence_tracking ON presence_tracking.macHash = users.macHash WHERE presence_tracking.updated_at >= (CURRENT_TIMESTAMP - INTERVAL 15 MINUTE)")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
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