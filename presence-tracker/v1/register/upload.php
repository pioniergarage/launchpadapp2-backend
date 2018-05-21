<?php
    function generateRandomString($length = 15) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++)
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        return $randomString;
    }

    function uploadImage($type, $file) {
        $upload_folder = 'uploads/' . $type . '/';
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        //test if valid image extension
        $allowed_extensions = array('png', 'jpg', 'jpeg', 'ico');
        if (!in_array($extension, $allowed_extensions))
            return "Fehler, ungültige Dateiendung";
        //test file size
        $max_size = 5 * 1024 * 1024; //5 MB
        if ($file['size'] > $max_size)
            return "Fehler, keine Dateien größer als 5MB erlaubt";
        //test if file is correct image
        if (function_exists('exif_imagetype')) { //test if the exif-extension is installed on the server
            $allowed_types = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
            if (!in_array(exif_imagetype($file['tmp_name']), $allowed_types))
                return "Fehler, Datei ist kein Bild";
        }
        //find random unique file name
        do {
            $new_path = $upload_folder . generateRandomString() . '.' . $extension;
        } while (file_exists($new_path));
        //move image from temp to final directory
        if (!move_uploaded_file($file['tmp_name'], $new_path))
            return false;

        return $new_path;
    }
?>