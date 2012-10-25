<?php

namespace tatt;
require_once 'tatt/webcommon.php';
var_dump($_POST);
$auth->require_login();

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
    redirect_to_url('/items/view_items.php'); //Redirect to inventory page.
$item_id = (int)$_GET['id'];

$item = new Item($item_id);
if($item->get_owner_id() != $user_id)
    redirect_to_url('/items/view_items.php'); //Redirect to inventory page.

if(isset($_POST['name'])){
    //form was submitted
    $name = $db->escape_string($_POST['name']);
    $location = $db->escape_string($_POST['location']);
    $item->set_name($name);
    $item->set_location($location);

    if(isset($_POST['attributes'])){
        $attributes = $_POST['attributes'];
        foreach($attributes as $attribute){
            $attribute_id = (int)$attribute['id'];
            $value = $db->escape_string($attribute['value']);
//echo "ID: $attribute_id V: $value";

            $new_attribute = new Attribute($item_id, $attribute_id);
            $new_attribute->set_value($value);
        }
    }
    redirect_to_url("/items/item.php?id=$item_id");
}
//TODO Fix bug where all available attributes won't show if only a few were previously set.  (Do this by creating attribute objects in Item object when no value set. Just make the value NULL...)
$item = $item->to_array();

$page->assign('item', $item);
$page->assign('attributes', $item['attributes']);
$page->assign('type_id', $item['type_id']);
$page->assign('page_title', 'Edit Item');

$page->display('items/edit_item.tpl');

