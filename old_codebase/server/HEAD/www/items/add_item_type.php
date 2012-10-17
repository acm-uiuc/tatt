<?php
namespace tatt;

require_once "tatt/webcommon.php";

$page->assign('page_title','Add New Item Type');

$auth->require_login();

$page->display('/var/includes/php/tatt/smarty/templates/items/add_item_type.tpl');

if(!isset($_POST["type_name"]) || $_POST["type_name"] == NULL){
    // Do nothing, not set or is null
}
else{
    $typename = $_POST["type_name"];
    if(Item_Type::exhists($typename, $user_id)){
    }
    else
    {
        Item_Type::create($typename, $user_id);
    }
}
?>
