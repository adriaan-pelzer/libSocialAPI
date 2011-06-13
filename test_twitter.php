<?php
require (dirname (__FILE__).'/lib/class.twitter.php');

$twitter = new Twitter ();

if ($twitter->error) {
    echo "Init Error: ".$twitter->error;
    die ();
}

if ( isset($_REQUEST['wipe'])) {
    $twitter->destroy();
} elseif ( isset($_SESSION['access_token']) ) {
    if (!($twitter->set_up_stored ($_SESSION['access_token']))) {
        echo "Set up from stored Error: ".$twitter->error;
        die ();
    }
} elseif (isset($_REQUEST['oauth_verifier'])) {
    if (!($twitter->request_access_token ($_REQUEST['oauth_verifier']))) {
        echo "Request Access Token Error: ".$twitter->error;
        die ();
    }
} elseif ( isset($_REQUEST['signin']) || isset($_REQUEST['allow']) ) {
    if (!($twitter->kickstart ($_REQUEST))) {
        echo "Kickstart Error: ".$twitter->error;
        die ();
    }
}

if ($twitter->get_state() == TW_STATES::TOKEN) {
    $last_id_str = "";
    $tweet_nr = 0;

    for ($i = 0; $i < 5; $i++) {
        if ($last_id_str == "") {
            $parameters = array ('count'=>200);
        } else {
            $parameters = array ('count'=>200, 'max_id'=>$last_id_str);
        }

        if (!($twitter->call ('statuses/home_timeline', $parameters))) {
            echo "Call Error: ".$twitter->error;
            die ();
        }
        foreach ($twitter->get_result () as $tweet) {
            if ($tweet->id_str != $last_id_str) {
                echo "<pre>\n";
                print_r ($tweet);
                echo "</pre>\n";
                echo "<div>\n";
                echo "<div>\n";
                echo "<p>\n";
                echo "#: ".$tweet_nr++."\n";
                echo "</p>\n";
                echo "<p>\n";
                echo "ID: ".$tweet->id_str."\n";
                echo "</p>\n";
                echo "<p>\n";
                echo "Created At: ".$tweet->created_at."\n";
                echo "</p>\n";
                echo "</div>\n";
                echo "<div>\n";
                echo "<a href=\"http://twitter.com/".$tweet->user->screen_name."\"><img width=\"48\" height=\"48\" src=\"".$tweet->user->profile_image_url."\" /></a>\n";
                echo "</div>\n";
                echo "<p>\n";
                echo $tweet->text."\n";
                echo "</p>\n";
                echo "</div>\n";
                $last_id_str = $tweet->id_str;
            }
        }
    }
}
?>
<ul>
  <li><a href="?signin=1">Sign in with Twitter</a></li>
  <li><a href="?signin=1&amp;force=1">Sign in with Twitter (force)</a></li>
  <li><a href="?allow=1">Allow Application (callback)</a></li>
  <li><a href="?allow=1&amp;oob=1">Allow Application (oob)</a></li>
  <li><a href="?allow=1&amp;force_read=1">Allow Application (callback) (read)</a></li>
  <li><a href="?allow=1&amp;force_write=1">Allow Application (callback) (write)</a></li>
  <li><a href="?wipe=1">Start Over</a></li>
</ul>

