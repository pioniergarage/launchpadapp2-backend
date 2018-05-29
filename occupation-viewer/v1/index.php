<?php
/**
 * Author: Joscha Eckert
 * Organisation: PionierGarage e.V.
 * Version: 0.1
 * Date: 18.05.2018
 * Time: 22:26
 */

    require 'config.php';

    //edit set content to json
    header('Content-Type: application/json');

    //create database connection
    $conn = new mysqli($db_ip, $db_user, $db_pwd, $db_name);
    if ($conn->connect_error) die(json_encode("no database connection"));

    function set_status_code($code) {
        header('X-PHP-Response-Code: ' . $code, true, $code);
        die("$code");
    }

    function cleanup_database($conn) {
        //delete row from database if launchpad was under 10min opened
        $conn->query("
DELETE FROM `occupation_viewer` 
WHERE 
  `opened_at` + INTERVAL 10 MINUTE > `closed_at` 
OR 
  `closed_at` IS NULL");
    }

    # ------- API FUNCTIONS ----------

    // SETS THE STATUS TO A SUBMITTED STATE
    if (isset($_GET['changeStatus'])) {
        //test api key
        if (!isset($_POST['token']) or $_POST['token'] != $token)
            set_status_code(401);
        if ($_GET['changeStatus'] == "open") {
            //test if status is currently on 'closed' or on 'opened'
            $result = $conn->query("SELECT `closed_at` FROM `occupation_viewer` WHERE `id`=(SELECT max(`id`)) LIMIT 1");
            $success = false;
            if ($result->num_rows == 0 or $result->fetch_assoc()) {
                //open door
                if ($conn->query("INSERT INTO `occupation_viewer`(`id`) VALUES (0);"))
                    $success = true;
            }
            echo json_encode(array("success" => $success));

        } elseif ($_GET['changeStatus'] == "close") {
            $sql = "
SET @id := -1;
UPDATE `occupation_viewer` 
    SET 
        `closed_at`=CURRENT_TIMESTAMP, 
        `id`=(SELECT @id := id) 
    WHERE 
        `closed_at` IS NULL; 
SELECT @id AS id;";
            $id = -1;
            if (mysqli_multi_query($conn,$sql))
                do {
                    if ($result=mysqli_store_result($conn)) {
                        while ($row=mysqli_fetch_row($result))
                            $id = $row[0];
                        mysqli_free_result($result);
                    }
                } while (mysqli_next_result($conn));
            //test if door was already closed
            if ($id == -1) echo json_encode(array("success" => false,));
            else {
                echo json_encode(array("success" => true));
                cleanup_database($conn);
            }

        } else set_status_code(400);
    }

    // TOGGLE THE STATUS (OPENED/CLOSED)
    if (isset($_GET['toggleStatus'])) {
        if (isset($_POST['token']) && $_POST['token'] == $token) {
            //create new entry or update the entry in case the hashed mac already exists
            $sql = "
SET @id := -1;
UPDATE `occupation_viewer` 
    SET 
        `closed_at`=CURRENT_TIMESTAMP, 
        `id`=(SELECT @id := id) 
    WHERE 
        `closed_at` IS NULL; 
SELECT @id AS id;";
            $id = -1;
            if (mysqli_multi_query($conn,$sql))
                do {
                    if ($result=mysqli_store_result($conn)) {
                        while ($row=mysqli_fetch_row($result))
                            $id = $row[0];
                        mysqli_free_result($result);
                    }
                } while (mysqli_next_result($conn));
            if ($id == -1) {
                //launchpad is closed -> open launchpad
                if ($conn->query("INSERT INTO `occupation_viewer`(`id`) VALUES (0);"))
                    echo json_encode(array('status' => 'opened'));
            } else {
                //launchpad was opened and is now closed
                echo json_encode(array('status' => 'closed'));

                cleanup_database($conn);
            }
        } else {
            set_status_code(401);
        }
    }

    // OUTPUT THE CURRENT STATUS OF THE TRAFFIC LIGHT
    if (isset($_GET['currentStatus'])) {
        $row = $conn->query("SELECT * FROM `occupation_viewer` WHERE (SELECT MAX(`id`) FROM `occupation_viewer`)")->fetch_assoc();
        if ($row['closed_at'] == null) {
            echo json_encode(array(
                "status" => "opened",
                "time" => $row['opened_at']
            ));
        } else {
            echo json_encode(array(
                "status" => "closed",
                "time" => $row['closed_at']
            ));
        }
    }
    // OUTPUTS THE OPEN HISTORY OF THE LAST GIVEN TIME; OUTPUT 7 DAYS IF THE DAYS ARE NOT SPECIFIED
    if (isset($_GET['history'])) {
        if (empty($_GET['history'])) $days = 7;
        else $days = $_GET['history'];

        $data = $conn->query("
SELECT 
  `opened_at`,`closed_at` 
FROM 
  `occupation_viewer` 
WHERE 
  `opened_at` > CURRENT_TIMESTAMP - INTERVAL " . mysqli_escape_string($conn, $days) . " DAY");
        $output = array();
        while ($row = $data->fetch_assoc())
            array_push($output, $row);
        echo json_encode($output);
    }

    // OUTPUT THE TABLE ENTIRES FOR DEBUGGING
    if (isset($_GET['listTable']) && $debug) {
        $data_times = $conn->query("SELECT * FROM `occupation_viewer`;");
        $output = array();
        while ($row = $data_times->fetch_assoc()) {
            array_push($output, $row);
        }
        echo json_encode($output);
    }


    $conn->close();
?>