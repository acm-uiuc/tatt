<?php

/*
 *  Yes, I realize that this is hacky...
 */

namespace tatt;

require_once 'tatt/webcommon.php';

$page->load_external_javascript_include("https://www.google.com/jsapi");

// This file displays the 25 most popular books (by checkout count)

$items = Item::get_all_items();

$item_counts = array();
$item_array = array();

foreach($items as $item) {
	$item_array[$item->get_id()] = $item;
        $item_counts[$item->get_id()] = Item::checkout_count_by_id($item->get_id());
}

// Sort the items so that the items with the most checkouts are on top
// We use ASORT() to maintain the association to the indices
arsort($item_counts, SORT_NUMERIC);

// Build up an array of the top 25 items for use with Google Charts
$itemcountjs = '';
$i = 0;
foreach($item_counts as $item_id => $checkoutcount) {
      $itemcountjs = "$itemcountjs\n data.setValue($i,0,'" . addslashes($item_array[$item_id]->get_name()) . "');\n data.setValue($i,1," . $checkoutcount  . ");";
      $i++;
      if ($i >= 25) {
            break;
      }
}

// Attempt to set up some Google Charts JavaScript
$page->load_javascript_text( <<<EOD
google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(drawChart);

function drawChart() {
  var data = new google.visualization.DataTable();
  data.addColumn('string', 'Item');
  data.addColumn('number', 'Checkouts');
  data.addRows(25);
  $itemcountjs

  var options = {'title':'Popular Items','width':725,'height':700,hAxis:{title: 'Checkouts', titleTextStyle: {color: 'red'}}};

  var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
  chart.draw(data, options);
}

EOD
);

$page->assign('page_title', 'Popular Items');

$page->display('generic_graph.tpl');
