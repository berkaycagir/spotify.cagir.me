<?php
include "header.php";

if(empty($_POST['mail'])) {
    echo "One or more required parameters have not been sent.";
    include "footer.php";
    exit();
}

$mail = $_POST['mail'];
$id = $_POST['id'];

if(!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email format.";
    include "footer.php";
    exit();
} else if(empty($id)) {
    exec("python /var/www/spotify.cagir.me/delete.py ".escapeshellarg($mail), $output, $return_val);
} else {
    exec("python /var/www/spotify.cagir.me/delete.py ".escapeshellarg($mail)." ".escapeshellarg($id), $output, $return_val);
}

if ($return_val == 0) {
    echo "You will get a confirmation mail.";
} else {
    if(empty($id)) {
        echo "You don't have any checkers registered.";
    } else {
        echo "We can't find a checker with the ID and mail address provided.";
    }
}
include "footer.php";
?>
