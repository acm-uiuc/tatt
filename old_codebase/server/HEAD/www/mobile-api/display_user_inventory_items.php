<?php

    /*
     *  @author Vinay Hiremath
     *  PHP file that displays the database inventory in JSON format for the
     *  Android application
     */

    namespace tatt;
    require_once("tatt/webcommon.php");

    // check if the user is logged in before getting the items
    if ($auth->is_logged_in()){
        // we will grab the inventory items from the database and display them
        // in JSON format

        // grab the first 50 items
        $items_array = Item::get_items_by_user_id($auth->get_user_id(), 1, 50);

        $array_to_encode = array();

        // iterate through the items_array and store each item as a JSON-encoded string
        // into a new array
        foreach ($items_array as &$item){
            array_push($array_to_encode, json_encode($item->to_array()));
        }

        // print the new array
        print(json_encode($array_to_encode));

    } else{
        print("NOT LOGGED IN");
    }
