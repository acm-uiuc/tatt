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

class User {
    private $users_data_loaded = false;
    private $user_id;
    private $account_type;
    private $username;
    private $email = NULL;

    private $error = NULL;

    public function __construct ($user_id = NULL){
        global $db;

        if($user_id == NULL)
            trigger_error("A user class constructor parameter is NULL");

        $this->user_id = $user_id;
    }

    private function load_user_data(){
        global $db;
        if($this->users_data_loaded)
            return; //EARLY RETURN
        $query = "SELECT * FROM " . TATT_PREFIX . "users WHERE user_id = {$this->user_id}";
        $result = $db->query($query);
        $user_data = $result->fetch_object();
        $this->username = $user_data->username;
        $this->account_type = $user_data->account_type;
        $this->email = $user_data->email_address;
    }

    public function get_username(){
        $this->load_user_data();
        return $this->username;
    }

    /*
     * set_username
     * Returns true on sucess, false of error
     */
    public function set_username($new_username = NULL){
        global $db;

        $this->load_user_data();
        if($new_username == NULL)
            trigger_error("set_username requires a parameter");

        $status = Validator::is_username($new_username);
        if($status !== true){
            $this->error = "Username invalid";
            return false;
        }
        $new_username = $db->escape_string($new_username);
        $query = "UPDATE " . TATT_PREFIX . "users SET username = '$new_username' WHERE user_id = $this->user_id";
        $db->query($query);
        $this->username = $new_username;
        return true;
    }

    public function get_email(){
        $this->load_user_data();
        return $this->email;
    }

    public function to_array(){
            $this->load_user_data();
        return get_object_vars($this);
    }

    /*
     * Returns a set of item_id's that have been checked out by a user_id
     */
    static public function get_user_history($user_id){
        //XXX UNTESTED
        global $db;
        $query = 'SELECT item_id ' .
                 'FROM ' . TATT_PREFIX . 'checkouts ' .
                 "WHERE user_id = $user_id ORDER BY checkout_time DESC";
        $result = $db->query($query);
        //TODO return a sensible result
        return $result;
    }

    /*
     * Returns the item_ids for a user_id that have not been returned
     */
    static public function get_user_checked_out_items($user_id) {
        //XXX UNTESTED
        global $db;
        $query = 'SELECT item_id ' .
                 'FROM ' . TATT_PREFIX . 'checkouts ' .
                 "WHERE return_time = '0000-00-00 00:00:00' AND user_id = $user_id ORDER BY checkout_time DESC";
        $result = $db->query($query);
        return $result;
    }
}
