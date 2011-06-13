<?php
require_once (dirname (__FILE__)."/lib/class.plancast.php");

$plancast = new Plancast ();

if ($plancast->error) {
    echo "Init Error: ".$plancast->error;
    die ();
}

if ($plancast->call ("account/verify_credentials") === FALSE) {
    echo "Call Error: ".$plancast->error;
    die ();
}

$user = $plancast->get_result ();

if ($plancast->call ("users/subscribers", array ('username'=>$user->username)) === FALSE) {
    echo "Call Error: ".$plancast->error;
    die ();
}

$subscribers = $plancast->get_result ();

foreach ($subscribers->users as $subscriber) {
    if ($plancast->call ("plans/user", array ('username'=>$subscriber->username)) === FALSE) {
        echo "Call Error: ".$plancast->error;
        die ();
    }

    $plans = $plancast->get_result ();

    foreach ($plans->plans as $plan) {
        if ($plancast->call ("plans/show", array ('plan_id'=>$plan->plan_id)) === FALSE) {
            echo "Call Error: ".$plancast->error;
            die ();
        }

        $full_plan = $plancast->get_result ();

        //print_r ($full_plan);
        echo $full_plan->what." :: ".$full_plan->where."<br />\n";
    }
}
if ($plancast->call ("users/subscriptions", array ('username'=>$user->username)) === FALSE) {
    echo "Call Error: ".$plancast->error;
    die ();
}

$subscribers = $plancast->get_result ();

foreach ($subscribers->users as $subscriber) {
    if ($plancast->call ("plans/user", array ('username'=>$subscriber->username)) === FALSE) {
        echo "Call Error: ".$plancast->error;
        die ();
    }

    $plans = $plancast->get_result ();

    if (isset ($plans->plans)) {
        foreach ($plans->plans as $plan) {
            if ($plancast->call ("plans/show", array ('plan_id'=>$plan->plan_id)) === FALSE) {
                echo "Call Error: ".$plancast->error;
                die ();
            }

            $full_plan = $plancast->get_result ();

            //print_r ($full_plan);
            echo $full_plan->what." :: ".$full_plan->where."<br />\n";
        }
    }
}
?>
