<?php

namespace tatt;
require_once 'tatt/webcommon.php';

$recommendations = Recommendation::recommend_items(4);

foreach($recommendations as $recommendation) {
    $item = new Item($recommendation);
    $items[] = $item->to_array();
}

$page->assign('items', $items);
$page->assign('page_title', 'Recommendations');
$page->display('items/item_recommendations.tpl');
