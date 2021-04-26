<?php

/*
 * ==========================================================
 * UPLOAD.PHP
 * ==========================================================
 *
 * Manage all uploads of front-end and admin. © 2020 board.support. All rights reserved.
 *
 */

include_once('../config.php');

$allowed_extensions = array('psd','ai','jpg','jpeg','png','gif','pdf','doc','docx','key','ppt','odt','xls','xlsx','zip','rar','mp3','m4a','ogg','wav','mp4','mov','wmv','avi','mpg','ogv','3gp','3g2','mkv','txt','ico','csv','java','js','xml','unx','ttf','font','css');

if (isset($_FILES['file'])) {
    if (0 < $_FILES['file']['error']) {
        die(json_encode(array('error', 'Support Board: Error into upload.php file.')));
    } else {
        $file_name = $_FILES['file']['name'];
        $infos = pathinfo($file_name);
        $directory_date = date('d-m-y');
        $path = '../uploads/' . $directory_date;
        $url = SB_URL . '/uploads/' . $directory_date;
        if (in_array($infos['extension'], $allowed_extensions)) {
            if (defined('SB_UPLOAD_PATH') && SB_UPLOAD_PATH != '' && defined('SB_UPLOAD_URL') && SB_UPLOAD_URL != '') {
                $path = SB_UPLOAD_PATH . '/' . $directory_date;
                $url = SB_UPLOAD_URL . '/' . $directory_date;
            }
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            move_uploaded_file($_FILES['file']['tmp_name'], $path . '/' . $file_name);
            die(json_encode(array('success', $url . '/' . $file_name)));
        } else {
            die(json_encode(array('success', 'extension_error')));
        }
    }
} else {
    die(json_encode(array('error', 'Support Board Error: Key file in $_FILES not found.')));
}


?>