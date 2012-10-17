<?php

/*
 *  @author Vinay Hiremath
 *  PHP script that allows item lookup based on an item_id.
 *
 */

    namespace tatt;
    require_once("tatt/webcommon.php");

    if (isset($_GET['item_id'])){
        $item = new Item($_GET['item_id']);
        print(json_encode($item->to_array()));
    } else
        print ('INVALID QR SCAN');
