<?php

namespace tatt;
require_once 'tatt/webcommon.php';
$page->load_javascript_include("items/item.js");

//This file displays an individual item
//TODO Check item exists in database.
if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
	redirect_to_url('/'); //Send to homepage.
} else {
	$item_id = (int)$_GET['id'];
}

$item = new Item($item_id);
QR::item($item_id);

$valid_actions = array('delete', 'checkout', 'return');
if(isset($_GET['action']) && in_array($_GET['action'], $valid_actions)){
	$auth->require_login();
	$action = $_GET['action'];
	if( $action == 'delete' && $item->get_owner_id() == $user_id ){
		Item::delete($item_id);
		redirect_to_url('/items/view_items.php?u=' . $user_id);
	}
	//TODO: May want to add check if item is available to be checked out
	else if( $action == 'checkout' ){
		Item::checkout_by_ids($item_id, $user_id);
		redirect_to_url('/items/view_items.php?u=' . $user_id);
	}
	else if( $action == 'return' ){
		Item::return_by_id($item_id, $user_id);
		//redirect_to_url('/items/view_items.php?u=' . $user_id);
	}
}

	$page->assign('item', $item->to_array());
	$page->assign('page_title', 'Item Details');

	$page->display('items/item.tpl');

