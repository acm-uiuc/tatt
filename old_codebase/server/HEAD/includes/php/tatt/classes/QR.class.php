<?php
/*
The MIT License

Copyright (c) 2011 Eric Parsons

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
namespace tatt;
if (!defined('IN_TATT')) {
    exit;
}
require_once TATT_LIBRARIES . 'phpqrcode/qrlib.php';

//TODO: Write Documentation for this class
class QR extends \QRcode{
    
    static public function item( $item_id ) {
        if(!is_numeric($item_id)) {
            trigger_error('Invalid parameter for QR::item()');
            return;
        }
        $domain = $_SERVER['HTTP_HOST'];
        $data = $domain . "|" . $item_id;
        parent::png($data, $_SERVER['DOCUMENT_ROOT'] . '/qrcodes/' . $item_id . '.png');
    }
}
