<?php

namespace tatt;
if (!defined('IN_TATT')){
    exit;
}

class Items_On_Loan{

    // function that returns the user's items on loan
    static public function get_items_on_loan($user_id){
        global $db;
        $query = 'SELECT item_id ' .
                 'FROM ' . TATT_PREFIX . 'items ' .
                 'WHERE checked_out_by = ' . $user_id;
        $result = $db->query($query);
        $items_array = array();
        while($item_id = $result->fetch_object()){
            $item = new Item($item_id->item_id);
            $items_array[] = $item;
        }
        return $items_array;
    }
}
