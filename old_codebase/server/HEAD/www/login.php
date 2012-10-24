<?php
namespace tatt;
require_once 'tatt/webcommon.php';

if(isset($_GET['url'])){
	$redirect_url = encode_decode_redirect_url($_GET['url']);
} else {
	$redirect_url = '';
}


if(isset($_GET['action'])){
	if($_GET['action'] == 'logout'){
		$auth->logout();
		redirect_to_url('/' . $redirect_url);
	}

	$username = $_POST['username'];
	$password = $_POST['password'];

	$auth->login($username,$password);
	if($auth->is_logged_in()){
		redirect_to_url('/' . $redirect_url);
	}
}

//TODO Bad login, display login pagei
$page->assign('redirect_url',$redirect_url);
$page->assign('page_title', 'Login Failed');
$page->display('login.tpl');
