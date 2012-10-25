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
require_once TATT_LIBRARIES . 'Smarty_3_1_0/libs/Smarty.class.php';

//TODO: Write Documentation for this class
class Page extends \Smarty{
    private $page_title = '';
    private $css_override = '';
    private $javascript_files;
    private $javascript_texts;

    public function __construct() {
        parent::__construct();
        $this->javascript_files = array();
        $this->javascript_texts = array();

        $file_path = dirname(__FILE__) . '/';
        $smarty_dir = $file_path . '../smarty/';
        parent::setTemplateDir( $smarty_dir . 'templates/');
        parent::setCompileDir( $smarty_dir . 'dev/template_c/');
        parent::setCacheDir( $smarty_dir . 'dev/cache/');
        parent::setConfigDir( $smarty_dir . 'configs/');

        $smarty_install_path = $file_path . '../../third_party/Smarty_3_1_0/';
        parent::setPluginsDir($smarty_install_path . 'libs/plugins' );
        parent::addPluginsDir($smarty_install_path . 'libs/sysplugins');
    }

    public function get_css_override() {
        return $this->css_override;
    }

    public function load_javascript_text($text) {
        $this->javascript_texts[] = $text;
    }

    public function load_javascript_include($filename) {
        $this->javascript_files[] = TATT_JAVASCRIPT . $filename;
    }

    public function load_external_javascript_include($url) {
        $this->javascript_files[] = $url;
    }

    public function load_override_css($text) {
        $this->css_override = $text;
    }

    public function display($template = null, $cache_id = null, $compile_id = null, $parent = null) {
        $this->assign('javascript_files',$this->javascript_files);
        $this->assign('javascript_texts',$this->javascript_texts);
        parent::display($template, $cache_id, $compile_id, $parent);
    }

    public static function query_log_color($query_time, $shortest_query_time, $average_query_time, $longest_query_time) {
        $max_red = 160;
        $max_green = 160;

        if($query_time < $average_query_time) {
            $green = $max_green;
            $red = round($max_red*(($query_time - $shortest_query_time)/($average_query_time - $shortest_query_time)));
        } elseif ($query_time > $average_query_time) {
            $red = $max_red;
            $green = round($max_green*(($longest_query_time - $query_time)/($longest_query_time - $average_query_time)));
        } else {
            $red = $max_red;
            $green = $max_green;
        }
        $red_hex = str_pad(dechex($red), 2, '0', STR_PAD_LEFT);
        $greenhex = str_pad(dechex($green), 2, '0', STR_PAD_LEFT);
        $rgb_hex_string = '#' . $red_hex . $green_hex . '00';
        return $rgb_hex_string;
    }
}
