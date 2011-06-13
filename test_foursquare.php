<?php
require_once (dirname (__FILE__)."/lib/class.foursquare.php");

session_start ();

if (isset ($_SESSION['auth_token'])) {
    $foursquare = new Foursquare ($_SESSION['auth_token']);
} else {
    $foursquare = new Foursquare ();
}

if ($foursquare->error) {
    echo "Init Error: ".$foursquare->error;
    die ();
}

if (!($foursquare->call ("users/self"))) {
    echo "Call Error: ".$foursquare->error;
    die ();
}

if (!isset ($_SESSION['auth_token'])) {
    $_SESSION['auth_token'] = $foursquare->get_auth_token ();
}

print_r ($foursquare->get_result ());
//print_r ($foursquare);
?>
