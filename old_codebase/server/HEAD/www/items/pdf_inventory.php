<?php
namespace tatt;
ini_set('max_execution_time', 6000);
require_once('third_party/tcpdf/config/lang/eng.php');
require_once('third_party/tcpdf/tcpdf.php');
require_once('tatt/webcommon.php');

if(isset($_GET['u']) && is_numeric($_GET['u'])){
    $requested_user_id = (int)$_GET['u'];
} else if($auth->is_logged_in() ) {
    $requested_user_id = $user_id;
} else {
    //TODO Redirect to login page
    $auth->require_login();
}

// create new PDF document
$pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


$requested_user = new User($requested_user_id);

//TODO get these dynamically via GET
//Pagination variables
$start_index = 1;
$items_per_page = 50;

$items = Item::get_items_by_user_id($requested_user_id, $start_index, $items_per_page);
$items_array = array();

foreach($items as $item){
    $items_array[] = $item->to_array();
}


// Set some content to print

$html = <<<EOD
    <table class="table_items">
    <thead>
    <tr>
    <th>Name</th>
    <th>Type</th>
    <th>Location</th>
    <th>Last Accounted For</th>
    <th>On Loan To</th>
    </tr>
    </thead>
    <tfoot>
    </tfoot>
    <tbody>
EOD;


foreach($items_array as $item) {
    if($item['location'] != NULL) {
        $location = "$item[location]";
        }
        else {
            $location = '--';
        }


    if($item['checked_out_by'] != NULL){
        $array = $item['checked_out_by'];
        $checked_out_by = "$array[username]";
    }
    else {
        $checked_out_by = '--';
    }

    $html .= <<<EOD
        <tr>
        <td><a href="tatt.com/items/item.php?id={$item['id']}">{$item['name']}</a></td>
        <td>{$item['type_name']}</td>
        <td>{$location}</td>
        <td>{$item['last_accounted_for']}</td>
        <td>{$checked_out_by}</td>
        </tr>
EOD;
}

$html .= <<<EOD
    </tbody>
    </table>
EOD;
// Print text using writeHTMLCell()
$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
