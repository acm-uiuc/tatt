<?php

/*
 *  Yes, I realize that this is hacky...
 */

namespace tatt;

function format_graph_date($timestamp) {
  return date('Y-m-d', $timestamp);
}

require_once 'tatt/webcommon.php';

$page->load_external_javascript_include("https://www.google.com/jsapi");

// This file displays the checkin/checkout activity over the past 30 days or so

$checkoutcounts = array();
$checkincounts = array();

for ($i = 0; $i < 30; $i++) {
  $checkoutcounts[$i] = Item::checkout_count_by_day($i);
  $checkincounts[$i] = Item::return_count_by_day($i);
}

// Build up an array of the top days for use with Google Charts
$itemcountsjs = '';

for($i = 29; $i >= 0; $i--) {
      $itemcountsjs = "$itemcountsjs\n data.setValue(" . (29 - $i) . ",0,'" . format_graph_date(time() - ($i * 86400)) . "');\n data.setValue(" . (29 - $i) . ",1," . $checkoutcounts[$i] . ");\n data.setValue(" . (29 - $i) . ",2," . $checkincounts[$i] . ");\n";
}

// Attempt to set up some Google Charts JavaScript
$page->load_javascript_text( <<<EOD
google.load('visualization', '1.0', {'packages':['corechart']});
google.setOnLoadCallback(drawChart);

function drawChart() {
  var data = new google.visualization.DataTable();
  data.addColumn('string', 'Item');
  data.addColumn('number', 'Checkouts');
  data.addColumn('number', 'Checkins');
  data.addRows(30);
  $itemcountsjs

  var options = {'title':'Recent Activity','width':600,'height':800};

  var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
  chart.draw(data, options);
}

EOD
);

$page->assign('page_title', 'Recent Activity');

$page->display('generic_graph.tpl');
