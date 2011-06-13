<?php
require_once (dirname (__FILE__)."/lib/class.instagram.php");

session_start ();

if (isset ($_GET['clear'])) {
    foreach ($_SESSION as $key=>$val) {
        unset ($_SESSION[$key]);
    }
} else {
    if (isset ($_SESSION['access_token'])) {
        $instagram = new Instagram ($_SESSION['access_token']);
    } else {
        $instagram = new Instagram ();
    }

    if ($instagram->error) {
        echo "Init Error: ".$instagram->error;
        die ();
    }

    if (!isset ($_SESSION['access_token'])) {
        $_SESSION['access_token'] = $instagram->get_auth_token ();
    }

    $instagram->debug ("Instagram before", $instagram);

    if (!($instagram->call ("users/self/"))) {
        echo "Call Error: ".$instagram->error;
        die ();
    }


    print_r ($instagram->get_result ());
}
?>
