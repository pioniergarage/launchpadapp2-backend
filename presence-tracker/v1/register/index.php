<?php
    require 'config.php';
    require 'upload.php';

    function newOrganization($api_url) {
        if (isset($_POST['org_name']) && !empty($_POST['org_name']) && isset($_FILES['org_logo'])) {
            $logo_url = uploadImage('logo', $_FILES['org_logo']);
            if (substr($logo_url, 0, 6) === "Fehler") {
                return false;
            }

            $opts = array('http' =>
                array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => http_build_query(
                        array(
                            'name' => $_POST['org_name'],
                            'logo_url' => $logo_url
                        )
                    )
                )
            );
            $result = file_get_contents($api_url . '?newOrganization', false, stream_context_create($opts));
            if ($result)
                return true;
        }
        return false;
    }

    function newUser($api_url) {
        $profile_url = uploadImage('profile', $_FILES['img_profile']);
        if (substr($profile_url, 0, 6) === "Fehler")
            return false;
        if ($_POST['orga_select'] == '1' && isset($_POST['org_name']) && !empty($_POST['org_name']))
            $orga_name = $_POST['org_name'];
        else $orga_name = $_POST['orga_select'];

        $variables = array(
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'mac1' => md5(strtolower($_POST['mac1'])),
            'profile_url' => $profile_url,
            'orga_name' => $orga_name
        );
        $i = 1;
        while (isset($_POST["mac" . $i])) {
            $variables["mac" . $i] = md5(strtolower($_POST["mac" . $i]));
            $i++;
        }

        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query($variables)
            )
        );
        $result = file_get_contents($api_url . '?newUser', false, stream_context_create($opts));
        if ($result)
            return true;
        return false;
    }


    $sent = false;
    $error = false;
    if (isset($_POST['first_name']) && !empty($_POST['first_name'])
        && isset($_POST['last_name']) && !empty($_POST['last_name'])
        && isset($_POST['mac1']) && !empty($_POST['mac1'])
        && isset($_POST['orga_select']) && !empty($_POST['orga_select'])) {

        $sent = true;

        if ($_POST['orga_select'] == '1' && !$error)
            $error = !newOrganization($api_url);

        if (!$error)
            $error = !newUser($api_url);


    }
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PresenceTracker Registrierung - PionierGarage</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/font-awesome.min.css">
    <link rel="stylesheet" href="assets/fonts/material-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
</head>

<body style="margin:40px; vertical-align: center">
        <form style="vertical-align: center" class="align-items-center align-content-center" action="index.php"
              id="register" enctype="multipart/form-data" method="post">
            <h2 class="text-center">Registrieren</h2>
            <?php if ($error) echo "<div role='alert' class='alert alert-danger'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button><span>Es ist ein Fehler aufgetreten. Bitte versuche es nochmal.</span></div>"; ?>
            <?php if (!$error && $sent) echo "<div role='alert' class='alert alert-success'><button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>×</span></button><span>Du hast dich erfolgreich registriert. Ab sofort solltest du auch angezeigt werden.</span></div>"; ?>
            <div class="form-row">
                <div class="col" style="margin-bottom:20px;">
                    <input class="form-control"
                           value="<?php if (($sent || !$error) && isset($_POST['first_name'])) echo $_POST['first_name']; ?>"
                           type="text" name="first_name" required="" placeholder="Vorname">
                </div>
                <div class="col" style="margin-bottom:20px;">
                    <input class="form-control"
                           value="<?php if (($sent || !$error) && isset($_POST['last_name'])) echo $_POST['last_name']; ?>"
                           type="text" name="last_name" required="" placeholder="Nachname">
                </div>
            </div>
            <div id="macs">
                <div class="form-row" style="margin-bottom:20px;">
                    <div class="col">
                        <input class="form-control"
                               value="<?php if (($sent || !$error) && isset($_POST['mac1'])) echo $_POST['mac1']; ?>"
                               id="mac1" type="text" name="mac1" required placeholder="Mac Adresse">
                    </div>
                    <div onclick="add_mac_input()" id="icAddMac" class="col-auto align-self-center">
                        <i class="fa fa-plus float-right" style="color:rgb(0,128,0);"></i>
                    </div>
                </div>
<?php
    $i = 2;
    while (isset($_POST["mac$i"])) {
        $mac_value = $_POST["mac$i"];
        echo "<div class='form-row' style='margin-bottom:20px;'><div class='col'><input class='form-control' value='$mac_value' type='text' name='mac$i' placeholder='Mac Adresse'></div></div>";
        $i++;
    }
?>
            </div>
            <script>
                amountMacs = 1;

                function add_mac_input() {
                    if (amountMacs < 5) {
                        mac1 = document.getElementById("mac1").value;
                        amountMacs++;
                        document.getElementById("macs").innerHTML += "<div class='form-row' style='margin-bottom:20px;'><div class='col'><input class='form-control' type='text' id='mac" + amountMacs + "' name='mac" + amountMacs + "' placeholder='Mac Adresse " + amountMacs + "'></div></div>";
                        document.getElementById("mac1").value = mac1;
                    }
                    if (amountMacs >= 5) {
                        document.getElementById('icAddMac').style.display = 'none';
                    }
                }

                //try to get mac address automaticly
                xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function () {
                    if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                        document.getElementById("mac1").value = xmlhttp.responseText;
                    }
                };
                xmlhttp.open("GET", "http://100.84.47.90", false);
                xmlhttp.send();
            </script>
            <div class="form-row">
                <div class="col">
                    <div class="form-row" style="margin-bottom:20px;">
                        <div class="col-lg-3 justify-content-center align-items-center align-content-center align-self-center">
                            <h5 style="margin-bottom:0px;max-width:200px;">Profilbild hochladen</h5>
                        </div>
                        <div class="col justify-content-center align-items-center align-content-center">
                            <input type="file" accept=".jpg,.png,.jpeg,.ico" name="img_profile" required="">
                        </div>
                    </div>
                </div>
            </div>
            <script>
                function toggle_orga() {
                    select = document.getElementById("organisation");
                    orgName = document.getElementById("org_name");
                    orgLogo = document.getElementById("org_name");
                    if (parseInt(select.options[select.selectedIndex].value) === 1) {
                        orgName.required = true;
                        orgLogo.required = true;
                        document.getElementById("div_org_name").style.display = 'inherit';
                        document.getElementById("div_org_logo").style.display = 'inherit';
                    } else {
                        orgName.required = false;
                        orgLogo.required = false;
                        orgLogo.value = "";
                        orgName.value = "";
                        document.getElementById("div_org_name").style.display = 'none';
                        document.getElementById("div_org_logo").style.display = 'none';
                    }
                }
            </script>
            <div class="form-row">
                <div class="col" style="margin-bottom:20px;">
                    <select id="organisation" onchange="toggle_orga()" class="form-control" name="orga_select"
                            title="Organisation auswählen" required="">
                        <option value="0" selected="">keine Organisation</option>
                        <option value="1">nicht aufgelistet</option>
                        <optgroup label="">
                            <?php
                                $orga_list = json_decode(file_get_contents($api_url . "?listOrganizations"));
                                foreach ($orga_list as $orga) {
                                    echo "<option value=\"$orga\">$orga</option>";
                                }
                            ?>
                        </optgroup>
                    </select>
                </div>
                <div class="col-lg-8" id="div_org_name" style="margin-bottom:20px; display: none">
                    <input class="form-control" type="text" name="org_name" id="org_name"
                           placeholder="Name der Organisation">
                </div>
            </div>
            <div class="form-row" id="div_org_logo" style="margin-bottom:20px; display: none">
                <div class="col-lg-3 justify-content-center align-items-center align-content-center align-self-center">
                    <h5 style="margin-bottom:0px;max-width:200px;">Logo hochladen</h5>
                </div>
                <div class="col justify-content-center align-items-center align-content-center">
                    <input type="file" accept="image/*" name="org_logo"
                           value="<?php if (($sent || !$error) && isset($_FILES['org_logo'])) echo $_FILES['org_logo'] ?>">
                </div>
            </div>
            <script> toggle_orga(); </script>
            <div class="form-group">
                <button class="btn btn-primary btn-block btn-lg" form="register" name="submit" type="submit">
                    Bestätigen
                </button>
            </div>
        </form>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/script.min.js"></script>
</body>

</html>