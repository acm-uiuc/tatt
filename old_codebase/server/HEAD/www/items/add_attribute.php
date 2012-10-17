<?php

namespace tatt;
require_once 'tatt/webcommon.php';
$auth->require_login();

$item_types = Item_Type::get_item_types_by_owner_id($user_id);

$page->load_javascript_include("items/add_attribute.js");
$page->assign('item_types', $item_types);
$page->assign('page_title', 'Add Attributes');

$page->display('items/add_attribute.tpl');


$type_id = null;
if(isset($_POST['type_id']) && is_numeric($_POST['type_id']) && isset($_POST['attributes']) && isset($_POST['new_attributes']) ){
    $type_id = $_POST['type_id'];
    foreach($_POST['attributes'] as $attribute){
        if($attribute['name'] != ""){
            Attribute::set_name( (int)$attribute['id'], $db->escape_string($attribute['name']));
        }
    }
    foreach($_POST['new_attributes'] as $new_attribute){
        if($new_attribute != ""){
            Attribute::create($db->escape_string($new_attribute), $type_id);
        }
    }
}
