<?php
namespace tatt;

require_once "tatt/webcommon.php";
	$item_id = Item::create('book1', 1, 1);
	$item = new Item($item_id);
	$item->set_location("Bobs House");
	$item->set_last_accounted_for("2011-11-01");
	$item->set_photo("EMPTY");
	$item->update_last_accounted_for();
	$item->set_due_date("2011-11-01");
	$item->set_checked_out_by(4);
	$check_item = new Item($item_id);
	var_dump($check_item);
	$page->assign('page_title','test2');
	$page->display('test2.tpl');

?>
