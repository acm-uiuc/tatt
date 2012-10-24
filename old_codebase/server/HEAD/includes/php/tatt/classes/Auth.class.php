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

class Auth {

    private $db;

    private $user_id                = NULL;
    private $user_ip                = NULL;
    private $is_moderator           = FALSE;
    private $is_admin               = FALSE;
    private $is_logged_in           = FALSE;
    private $login_check_performed  = FALSE;

    private $default_session_length = 3600; //seconds

    public function __construct($db = NULL){
        if($db == NULL){
            trigger_error('Auth needs a database to query.  Please provide one in the constructor.');
            exit();
        }
        $this->db = $db;

        $this->login_check();
    }

    //TODO: Add get/set functions
    public function get_user_id(){
        if(!$this->login_check_performed)
            return false;
        return $this->user_id;
    }

    public function is_moderator(){
        return $this->is_moderator;
    }

    public function is_admin(){
        return $this->is_admin;
    }
    
    private function login_check(){
        if($this->login_check_performed)
            return; //EARLY_RETURN
        $this->login_check_performed = true;

        //Check not yet performed
        $user_id = Validator::getExtInput('cookie', 'cookie_user_id', 'int');
        $session_id = Validator::getExtInput('cookie', 'cookie_session_id', 'int');
        
        if(!(is_numeric($user_id) && is_numeric($session_id))){
            //Invalid user_id or session
            $this->is_logged_in = false;
            return;  //EARLY RETURN
        }
        
        //Valid cookies at this point.
        $query = "SELECT user_id FROM " . TATT_PREFIX . "sessions WHERE session_id = $session_id AND user_id = $user_id";
        $result = $this->db->query($query);
        $session_count = $result->num_rows;

        if($session_count != 1){
            //No prior session.  Logout to remove cookies
            $this->logout();
            return;  //EARLY RETURN
        }
        
        //User is logged in
        $this->is_logged_in = TRUE;
        $login_info = $result->fetch_object();
        $this->user_id = $login_info->user_id;
        $this->user_ip = $_SERVER['REMOTE_ADDR'];

        //CHECK USER PRIVILEDGES
        $this->check_user_type($this->user_id);
        return;
    }

    /*
     *  Updates the is_admin and is_moderator flags
     *  Assumes $user_id is valid and belongs to this user.
     */
    private function check_user_type($user_id){
        $query = "SELECT account_type FROM " . TATT_PREFIX . "users WHERE user_id = $user_id";
        $result = $this->db->query($query);
        $account_type = $result->fetch_object()->account_type;

        if($account_type == 'admin'){
            $this->is_admin = true;
            $this->is_moderator = true;
        } else if ($account_type == 'moderator'){
            $this->is_moderator = true;
        }
    }


    public function logout(){
        //We need the hostname before .com and after www.
        $domain_array = array_reverse(explode('.',$_SERVER['HTTP_HOST']));
        $domain = $domain_array[1];

        //Expire cookies
        setcookie('cookie_session_id','',time() - 3600,'/', $domain);
        setcookie('cookie_user_id','',time() - 3600,'/', $domain);

        //Remove sessions from db
        if($this->user_id != NULL){
            $query = "DELETE FROM " . TATT_PREFIX . "sessions WHERE user_id = $this->user_id ";
            $this->db->query($query);
        }
        $this->is_logged_in = FALSE;
        $this->user_id = FALSE;
        $this->is_moderator = FALSE;
        $this->is_admin = FALSE;
    }

    public function is_logged_in(){
        if(!$this->login_check_performed)
            $this->login_check(); //EARLY_RETURN
        return $this->is_logged_in;
    }

    public function login($username = NULL,$password = NULL, $remember_me = FALSE){
        if( !(is_string($username) && is_string($password)) ){
            //Invalid user/pass
            trigger_error('Error: The username or password parameters are not strings in Auth->login().');
            return FALSE;  //EARLY RETURN
        }

        $username = $this->db->escape_string($username);
        $password = $this->db->escape_string($password);
        //TODO: Get password salt if user_id exists

        $query = "SELECT user_id, account_type, password_salt, password FROM " . TATT_PREFIX . "users WHERE username = '$username'";
        $result = $this->db->query($query);
        if($result->num_rows != 1)
            return false; //EARLY RETURN

        $login_info = $result->fetch_object();
        $salt = $login_info->password_salt;
        $salt_length = strlen($salt);
        $pwd = md5( substr($salt,0,$salt_length/3) . $password . substr($salt, $salt_length/3) );   

        if($pwd != $login_info->password)
            return false; //EARLY RETURN

        //User exists and login valid
        $query = "DELETE FROM " . TATT_PREFIX . "sessions WHERE user_id = $login_info->user_id";
        $this->db->query($query);

        $session_length = $this->default_session_length;
        while(true){
            $session_id = mt_rand();
            $query = "INSERT IGNORE INTO " . TATT_PREFIX . "sessions (session_id, user_id, session_expiration_time) VALUES ($session_id, $login_info->user_id, FROM_UNIXTIME(" . (time() + $session_length) . ") )";
            $this->db->query($query);
            if($this->db->affected_rows == 1)
                break;
        }

        //Get domain name for setting cookies.
        //We need the hostname before .com and after www.
        $domain_array = array_reverse(explode('.',$_SERVER['HTTP_HOST']));
        $domain = $domain_array[1];
        //set cookies
        if($remember_me)
            $session_length += 3600 * 24 * 365; //Stay logged in for a year.
        setcookie('cookie_session_id',"$session_id",time() + $session_length,'/');// ,$domain);
        setcookie('cookie_user_id',"$login_info->user_id",time() + $session_length,'/');//, $domain);

        //UPDATE OBJECT STATE
        $this->login_check_performed = true;
        $this->user_id = $login_info->user_id;
        $this->is_logged_in = true;
        $this->user_ip = $_SERVER['REMOTE_ADDR'];
        $this->check_user_type($this->user_id);
        return true;
    }

    public function set_password($new_pw){
        $salt = $this->create_salt();
        $salt_length = strlen($salt);

        $pwd = md5( substr($salt,0,$salt_length/3) . $new_pw . substr($salt, $salt_length/3) ); 

        $query = 'UPDATE ' . TATT_PREFIX . "users SET password_salt = '$salt', password = '$pwd' WHERE user_id = {$this->user_id}";
        $this->db->query($query);
    }

    private function create_salt(){
        //TODO: Replace with a better salt
        return md5(time());
    }

    public function register_user($username, $password, $email){
        //TODO validate input and make sure no username/emails exist already.

        $query = 'INSERT INTO ' . TATT_PREFIX . "users (user_id, account_type, username, password, password_salt, email_address) VALUES (NULL,'user', '$username', 'a','b', '$email' )";

        $result = $this->db->query($query);

        $user_id = $this->db->insert_id;
        $this->user_id = $user_id;
        $this->set_password($password);
                
    }

    

    /*
     * When this method is called, the user will be booted to the login page if
     * they are not logged in, or, if the optional parameter is set to "moderator",
     * if they are not moderators or admins.
    */
    public function require_login($userlvl="user") {
        switch ($userlvl) {
            case "user":
                $allowed = $this->is_logged_in;
                break;
            case "moderator":
                $allowed = $this->is_moderator;
                break;
            case "admin":
                $allowed = $this->is_admin;
                break;
            default:
                $allowed = FALSE;
                break;
        }

        if (!$allowed && !$this->is_logged_in) {
            //Redirect to login page
            $urlnew = encode_decode_redirect_url($_SERVER["REQUEST_URI"]);
            if ($urlnew == "") {
                redirect_to_url('/login.php');
        } else {
                redirect_to_url('/login.php?url=' . urlencode($urlnew));
        }
    } else if (!$allowed && $this->is_logged_in) {
        //They Shouldn't be here. Redirect to homepage.
        redirect_to_url('');

    } else{
            return TRUE;
        }
    }
}
