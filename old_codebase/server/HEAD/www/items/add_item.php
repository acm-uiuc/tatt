<?php

namespace tatt;
require_once 'tatt/webcommon.php';

$auth->require_login();

$item_types = Item_Type::get_item_types_by_owner_id($user_id);

$page->load_javascript_include("items/add_item.js");
$page->assign('item_types', $item_types);
$page->assign('page_title', 'Add Item');

$page->display('items/add_item.tpl');


$type_id = $name = $location = null;
if(isset($_POST['type_id'])){
    $type_id = $_POST['type_id'];
}
if(isset($_POST['name'])){
    $name = $_POST['name'];
}
if(isset($_POST['location'])){
    $location = $_POST['location'];
}
if($type_id != null && $name != null && $location != null){
    $new_item_id = Item::create($db->escape_string($name), $type_id, $user_id);
    Item::set_location_by_id($new_item_id, $db->escape_string($location));

    if(isset($_POST['attributes'])){
        $attributes = $_POST['attributes'];
        foreach($attributes as $attribute){
            $attribute_id = (int)$attribute['id'];
            $value = $db->escape_string($attribute['value']);
//echo "ID: $attribute_id V: $value";

            $new_attribute = new Attribute($new_item_id, $attribute_id);
            $new_attribute->set_value($value);
        }
    }
    redirect_to_url("/items/item.php?id=$new_item_id");

}
