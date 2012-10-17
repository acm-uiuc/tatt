<?php

/*
 *  @author Vinay Hiremath
 *  PHP script that gets items on loan for a user for the Android application.
 *
 */

    namespace tatt;
    require_once("tatt/webcommon.php");

    if (isset($_GET['user_id'])){

        $items = Items_On_Loan::get_items_on_loan($_GET['user_id']);

        $array_to_encode = array();

        foreach($items as &$item){
            $array_to_encode[] = json_encode($item->to_array());
        }

        print(json_encode($array_to_encode));
    } else
        print("PARAMS NOT SET");
