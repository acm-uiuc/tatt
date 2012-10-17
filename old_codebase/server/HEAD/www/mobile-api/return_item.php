<?php

/*
 *  @author Vinay Hiremath
 *  PHP script that checks out an item for the Android application.
 *
 */

    namespace tatt;
    require_once("tatt/webcommon.php");

    if (isset($_GET['user_id']) && isset($_GET['item_id'])){
        Item::return_by_id($_GET['item_id'], $_GET['user_id']);
        print("SUCCESS");
    } else
        print("PARAMS NOT SET");
