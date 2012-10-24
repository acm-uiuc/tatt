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
 * This file provides provides variables and functions that every script will
 * need.
*/

namespace tatt;

define('IN_TATT' , TRUE);
define('TATT_INCLUDE_PATH'   , 'tatt/');
define('TATT_CONFIGS'        , 'tatt-config/');
define('TATT_LIBRARIES'      , 'third_party/');
define('TATT_TEMPLATES'     , TATT_INCLUDE_PATH . 'smarty/components/');
define('TATT_CLASSES'        , TATT_INCLUDE_PATH . 'classes/');
define('TATT_FUNCTIONS'      , TATT_INCLUDE_PATH . 'functions/');

define('TATT_JAVASCRIPT'     , '/_includes/'); //JS location as from HTML root

$scriptStartTime = microtime(TRUE);

//Load db configs and common functions

require_once TATT_FUNCTIONS . 'global.php'; //code that needs to be run in global namespace.
require_once TATT_FUNCTIONS . 'common.php';
require_once TATT_CONFIGS   . 'dbconfig.php';

//Get db configs.
$db_host = $_SERVER['HTTP_HOST'];
if (!$db_host) {
    $db_host = 'NODE:'.php_uname('n');
}
if (!array_key_exists($db_host, $db_config)) {
    die("Sorry, failed to find DB config for this server. Please mail admin@" . $db_host . " and tell them of this error. Server: $db_host\n");
}


//Use these arrays when creating new db objects
$db_main_config = $db_config[$db_host]['main'];

define('TATT_PREFIX', $db_main_config['table_prefix'] . '_');
 
 //db object construct parameters: $config (array), $is_persistent (bool)
$persistent = true;
$db = new Db($db_main_config,$persistent); //Initialize a db object for persistent db connection to main database.
$hostname = $db_main_config['host'];
$username = $db_main_config['user'];
$password = $db_main_config['pass'];
$database = $db_main_config['db'];


//$db = new \mysqli($hostname,$username,$password,$database);


//No longer need these
unset($db_config, $db_main_config, $hostname, $username, $password, $database);
