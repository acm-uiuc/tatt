<?php

namespace tatt;
require_once 'tatt/webcommon.php';

//This file displays all items owned by a user
//User can be passed in by GET, or the currently logged in user.

//TODO: Display custom attributes in an elegant/convienient way.

//TODO: Check privacy settings and that user exists if requesting another user's inventory
if(isset($_GET['u']) && is_numeric($_GET['u'])){
	$requested_user_id = (int)$_GET['u'];
} else if($auth->is_logged_in() ) {
	$requested_user_id = $user_id;
} else {
	//TODO Redirect to login page
	$auth->require_login();
}

	$requested_user = new User($requested_user_id);

	//TODO get these dynamically via GET
	//Pagination variables
	$start_index = 1;
	$items_per_page = 50;

	$items = Item::get_items_by_user_id($requested_user_id, $start_index, $items_per_page);
	$items_array = array();

	foreach($items as $item){
		$items_array[] = $item->to_array();
	}

	$page->assign('items', $items_array);
	$page->assign('page_title', $requested_user->get_username() . '\'s Inventory');
	
	$page->display('items/view_items.tpl');

