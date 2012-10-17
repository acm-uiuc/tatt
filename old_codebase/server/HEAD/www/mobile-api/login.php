<?php

/*
 *  @author Vinay Hiremath
 *  PHP file that serves as the login protocol for the Android application.
 */

    require_once("tatt/webcommon.php");

    $login_status = false;

    // get the username and password if they don't already exist
    if (isset($_GET['userName']) && isset($_GET['passWrd'])){
        $userName = $db->escape_string($_GET["userName"]);
        $passWrd = $db->escape_string($_GET["passWrd"]);
        $login_status = $auth->login($userName, $passWrd, TRUE);
    }

    if($login_status){
        print("SUCCESSFUL LOGIN" . "\n" . $auth->get_user_id());
    }
    else
        print("FAILURE LOGIN");
