<?php

namespace tatt;

require_once 'tatt/webcommon.php';
$auth->require_login();

$items = Items_On_Loan::get_items_on_loan($user_id);

$items_array = array();

foreach($items as $item) {
        $items_array[] = $item->to_array();
}

$page->assign('page_title', 'Recent User Activity');
$page->assign('checkedoutitems', $items_array);
$page->assign('activity', $activity_array);

$page->display('user/user_history.tpl');
