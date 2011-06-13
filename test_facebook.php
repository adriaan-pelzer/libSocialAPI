<?php
require_once (dirname (__FILE__)."/lib/class.facebook.php");

session_start ();

if (isset ($_SESSION['access_token']) && isset ($_SESSION['expires_at'])) {
    $facebook = new Facebook (NULL, $_SESSION['access_token'], $_SESSION['expires_at']);
} else if (isset ($_SESSION['access_token'])) {
    $facebook = new Facebook (NULL, $_SESSION['access_token']);
} else {
    $facebook = new Facebook ();
}

if ($facebook->error) {
    echo "Init Error: ".$facebook->error;
    die ();
}

if (!($facebook->call ("me"))) {
    echo "Call Error: ".$facebook->error;
    die ();
}

if (!isset ($_SESSION['access_token'])) {
    $auth_token = $facebook->get_auth_token ();
    $_SESSION['access_token'] = $auth_token['access_token'];
    $_SESSION['expires_at'] = $auth_token['expires_at'];
}

//echo "Expires: ".strftime ("%Y-%m-%d %H:%M:%S", $_SESSION['expires_at'])."<br />\n";

print_r ($facebook->get_result ());
//print_r ($facebook);
?>
