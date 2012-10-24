<?php

namespace tatt;
require_once 'tatt/webcommon.php';

$auth->require_login();

if(!isset($_GET['item_type']) || !is_numeric($_GET['item_type']))
    exit();

$item_type = (int)$_GET['item_type'];

$attributes = Attribute::get_attribute_info_by_item_type($item_type);

$page->assign('type_id', $item_type);
$page->assign('attributes', $attributes);
$page->display('items/add_attribute_form.tpl');

