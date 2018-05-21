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

    # ------- API FUNCTIONS ----------

    // ADD TRACKED MACS, CALLED BY THE SNIFFER
    if (isset($_GET['pushMacs']) && $debug) {
        if (isset($_POST['macHash']) && !empty($_POST['macHash'])
            && isset($_POST['token']) && $_POST['token'] == $token) {
            $macs = explode("', '", substr($_POST['macHash'], 2, -2));
            foreach ($macs as $mac) {
                //create new entry or update the entry in case the hashed mac already exists
                $result = $conn->query("
INSERT INTO `presence_tracker_macs`
    (`mac_hash`) VALUES ('" . mysqli_escape_string($conn, $mac) . "') 
ON DUPLICATE KEY UPDATE 
    `here_since`=IF(`last_seen` + INTERVAL 10 MINUTE < CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, `here_since`), 
    `last_seen`=CURRENT_TIMESTAMP");
                if ($result == false) set_status_code(400);
            }
            $result = $conn->query("
UPDATE `presence_tracker_macs` SET `blacklisted`=1 
WHERE `here_since` < CURRENT_TIMESTAMP - INTERVAL 1 DAY 
  AND `last_seen` > CURRENT_TIMESTAMP - INTERVAL 10 MINUTE 
  AND `blacklisted`=0");
            if ($result == false) set_status_code(400);
            echo json_encode("success");
        } else {
            set_status_code(400);
            die("400");
        }
    }

    //GET USERS THAT HAVE BEEN AT THE LAUNCHPAD IN THE LAST 10 MINUTES
    if (isset($_GET['activeUsers'])) {
        $output = array();
        //get the amount of recently tracked macs that are not blacklisted and not associated with a registrated user
        $ouput['others'] = intval($conn->query("
SELECT 
  COUNT(*) AS amount
FROM `presence_tracker_macs` 
WHERE `blacklisted`=0 
  AND `user_id` IS NULL 
  AND `last_seen` > CURRENT_TIMESTAMP - INTERVAL 10 MINUTE")->fetch_assoc()["amount"]);

        //get all users whose mac address has been tracked in the last 10 minutes
        $data_users = $conn->query("
SELECT 
	first_name, 
    last_name, 
    presence_tracker_users.profile_img AS img_profile, 
    presence_tracker_orga.img AS img_orga, 
    here_since 
FROM presence_tracker_users 
LEFT JOIN presence_tracker_orga ON presence_tracker_orga.id=presence_tracker_users.orga_id 
LEFT JOIN presence_tracker_macs ON presence_tracker_macs.user_id = presence_tracker_users.id
WHERE 
	here_since =  ( SELECT MIN(here_since) FROM presence_tracker_macs 
                   WHERE presence_tracker_macs.user_id=presence_tracker_users.id )
AND
    last_seen > CURRENT_TIMESTAMP - INTERVAL 10 MINUTE");
        $ouput['users'] = array();
        while ($row = $data_users->fetch_assoc()) {
            $user = array();
            $user['name'] = $row['first_name'] . ' ' . $row['last_name'];
            $user['time'] = substr($row['here_since'],11,5);
            $pic = array();
            $pic['orga'] = $row['img_orga'];
            $pic['profile'] = $row['img_profile'];
            $user['pic'] = $pic;
            array_push($ouput['users'], $user);
        }
        echo json_encode($ouput);
    }

    //ADD NEW USER TO THE DATABASE
    if (isset($_GET['newUser'])) {
        //test if the needed variables exists
        if (!isset($_POST['first_name']) || empty($_POST['first_name'])
            || !isset($_POST['last_name']) || empty($_POST['last_name'])
            || !isset($_POST['orga_name']) || empty($_POST['orga_name'])
            || !isset($_POST['profile_url']) || empty($_POST['profile_url'])
            || !isset($_POST['mac1']) || empty($_POST['mac1'])) {
            set_status_code(400);
            die("400");
        }
        $user_id = -1;
        $data = $conn->query("SELECT `id` FROM `presence_tracker_users` WHERE `first_name`='" . mysqli_escape_string($conn, $_POST['first_name']) . "' AND `last_name`='" . mysqli_escape_string($conn, $_POST['last_name']) . "'");
        if ($data->num_rows > 0) {
            //user exists, update data
            $user_id = $data->fetch_assoc()['id'];
            //TODO update data
        } else {
            //add user to database
            $sql = "
INSERT INTO `presence_tracker_users`(`first_name`, `last_name`, `orga_id`, `profile_img`) VALUES ('" . mysqli_escape_string($conn, $_POST['first_name']) . "','" . mysqli_escape_string($conn, $_POST['last_name']) . "', (SELECT id FROM presence_tracker_orga WHERE presence_tracker_orga.name='" . mysqli_escape_string($conn, $_POST['orga_name']) . "'), '" . mysqli_escape_string($conn, $_POST['profile_url']) . "'); 
SELECT LAST_INSERT_ID() AS id;";
            if (mysqli_multi_query($conn,$sql))
                do {
                    if ($result=mysqli_store_result($conn)) {
                        while ($row=mysqli_fetch_row($result))
                            $user_id = $row[0];
                        mysqli_free_result($result);
                    }
                } while (mysqli_next_result($conn));
        }
        if ($user_id == -1)
            set_status_code(400);
        $sql = '';
        $i=1;
        while (isset($_POST['mac' . $i])) {
            $sql .= "
INSERT INTO `presence_tracker_macs`
  (`mac_hash`, `user_id`) VALUES
  ('" . mysqli_escape_string($conn, $_POST['mac' . $i]) . "'," . mysqli_escape_string($conn, $user_id) . ") 
ON DUPLICATE KEY UPDATE `user_id`=" . mysqli_escape_string($conn, $user_id) . "; ";
            $i++;
        }
        if (!$conn->query($sql))
            set_status_code(400);
        echo json_encode("success");
    }

    //ADD NEW ORGANIZATION TO THE DATABASE
    if (isset($_GET['newOrganization'])) {
        if (isset($_POST['logo_url']) && !empty($_POST['logo_url'])
            && isset($_POST['name']) && !empty($_POST['name'])) {
            //add organization to database
            $conn->query("
INSERT INTO `presence_tracker_orga`
  (`name`, `img`) 
VALUES 
  ('" . mysqli_escape_string($conn, $_POST['name']) . "','" . mysqli_escape_string($conn, $_POST['logo_url']) . "') 
ON DUPLICATE KEY 
UPDATE 
  `img`='" . mysqli_escape_string($conn, $_POST['logo_url']) . "'");
            echo json_encode("success");
        } else set_status_code(400);
    }

    if (isset($_GET['listOrganizations'])) {
        $data_orga = $conn->query("SELECT `name` FROM `presence_tracker_orga`");
        $orga = array();
        while ($row = $data_orga->fetch_assoc())
            array_push($orga, $row['name']);
        echo json_encode($orga);
    }

    if (isset($_GET['listTableMacs']) && $debug) {
        $data_macs = $conn->query("SELECT * FROM `presence_tracker_macs`");
        $macs = array();
        while ($row = $data_macs->fetch_assoc()) {
            array_push($macs, $row);
        }
        echo json_encode($macs);
    }

    if (isset($_GET['listTableUsers']) && $debug) {
        $data_macs = $conn->query("SELECT * FROM `presence_tracker_users`");
        $macs = array();
        while ($row = $data_macs->fetch_assoc()) {
            array_push($macs, $row);
        }
        echo json_encode($macs);
    }

    if (isset($_GET['listTableOrga']) && $debug) {
        $data_macs = $conn->query("SELECT * FROM `presence_tracker_orga`");
        $macs = array();
        while ($row = $data_macs->fetch_assoc()) {
            array_push($macs, $row);
        }
        echo json_encode($macs);
    }

    if (isset($_GET['activeMacs']) && $debug) {
        $data_macs = $conn->query("SELECT * FROM `presence_tracker_macs` WHERE `last_seen` > CURRENT_TIMESTAMP - INTERVAL 10 MINUTE ORDER BY `here_since`");
        $macs = array();
        while ($row = $data_macs->fetch_assoc()) {
            array_push($macs, $row);
        }
        echo json_encode($macs);
    }


    $conn->close();
?>