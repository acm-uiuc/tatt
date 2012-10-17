<?php

namespace tatt;
require_once 'tatt/webcommon.php';

//This file allows for searching, and dynamically populates a table based on a search query.

//XXX: Do we want users to be logged in for searching?
//Eric: Eventually, yes
$auth->require_login();

$valid_search_term = false;
if(!(isset($_GET['search']) && $_GET['search'] != "" ) ) {
	//TODO only display search box
}
else {
	$valid_search_term = true;
	$items = Item::search($_GET['search']);
		$items_array = array();
	foreach($items as $item) {
		$items_array[] = $item->to_array();
	}

	$page->assign('items', $items_array);
}
	$page->assign('valid_search_term', $valid_search_term);
	$page->assign('page_title', 'Search Result');
	$page->display('search.tpl');
