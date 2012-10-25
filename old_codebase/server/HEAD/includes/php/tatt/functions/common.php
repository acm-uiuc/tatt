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

/*
 * If a user views a page that needs login credentials to use, we redirect them to the
 * login page.  After logining in they are redirected to that original page.  This encodes
 * and decodes the url durring this process so it can be passed via GET requests.
 */
function encode_decode_redirect_url($urlnew) {
    if (strstr(urldecode($urlnew), "\n") || strstr(urldecode($urlnew), "\r") || strstr(urldecode($urlnew), ';url')) {
        $urlnew = "";
    } else {
        $urlnew = preg_replace('#^\/?(.*?)\/?$#', '/\1', trim($urlnew));
    }
    return ltrim($urlnew, "/");
}

// Redirect user to new page and end execution of current script.
function redirect_to_url($url){
    print '<meta HTTP-EQUIV="Refresh" CONTENT="0; URL=' . $url . '" />'; 
    exit();
}

/*
 * Returns the just file extension of a file
 */
function get_ext($string) {
    $filename = $string;
    if(stripos($filename,".")) {
        $name = explode(".", $filename);
        $extension = $db->escape_string(array_pop($name));
    } else {
        $extension = 'no ext';
    }
    return $extension;
}

/*
 * Converts the strings "false" and "true" to boolean values.  Type casting
 * using (boolean)"false" does not do this.
 *
 * If string is neither "true" or "false" false is returned.
*/
function str_to_bool($string) {
    $string = strToLower($string);
    if($string == 'true' || $string === true || (is_numeric($string) && (int)$string != 0)) {
        $boolean = true;
    } elseif ($string == "false" || $string == '0' || $string === false) {
        $boolean = false;
    } else {
        $boolean = false;
    }
    return $boolean;
}
