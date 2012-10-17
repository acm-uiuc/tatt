<?php
namespace tatt;
require_once "tatt/webcommon.php";

$page->assign('page_title','test page');

$page->display('/var/includes/php/tatt/smarty/templates/test.tpl');

?>
