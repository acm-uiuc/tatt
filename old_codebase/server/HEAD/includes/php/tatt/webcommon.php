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

/*
 * This file provides provides variables and functions that every web
 * accessable script will need.
*/

namespace tatt;
require_once 'tatt/common.php';
if (!defined('IN_TATT')) {
    exit;
}


//TODO: REMOVE THIS LINE BEFORE SITE GOES LIVE
/****************** DEBUG LINE *********************/
  $_GET['pagedebug'] = 'true';
 /********************* END ********************/


//Establish session
session_start();


//------- COMMON VARIABLES
$page = new Page();
$auth = new Auth($db); //Checks Auth at object creation

$user = NULL;
$page->assign('logged_in', $auth->is_logged_in());
$page->assign('redirect_url', encode_decode_redirect_url($_SERVER['REQUEST_URI']));

$page->load_javascript_include('jquery-1.6.4.min.js');

if ($auth->is_logged_in()) {
   $user_id = $auth->get_user_id();
   $user = new User($user_id);
   $page->assign('username', $user->get_username());
   $page->assign('user_id', $user_id);
}


//Query logging
$db->enable_query_logging(FALSE);
$page_debugging = FALSE;
if(isset($_GET['pagedebug']) && $_GET['pagedebug'] == 'true' && $auth->is_moderator()) {

    ini_set('display_errors','On');
    error_reporting(E_ALL | E_STRICT);
    $page_debugging = TRUE;
    $db->enable_query_logging(TRUE);      //Logs queries for debugging puroses.
    $db->enable_query_backtracing(TRUE);  //Provides location and line numbers for the logging.
}
