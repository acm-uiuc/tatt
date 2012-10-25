<?php
/*
The MIT License

Copyright (c) 2011 Drew Cross, Eric Parsons

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

class Item {

    private $name;
    private $id;
    private $type_id;
    private $type_name;
    private $owner_id;
    private $owner;
    private $location;
    private $last_accounted_for;
    private $has_photo;
    private $due_date;
    private $checked_out_by;
    private $attributes;

    public function __construct($item_id = NULL) {
        global $db;

        if($item_id == NULL)
            return;  //Return empty object.

        //TODO: Handle case where $item_id is invalid
        $query = 'SELECT *, items.name as name, items.owner_id as owner_id, UNIX_TIMESTAMP(last_accounted_for) as last_accounted_for_unix, UNIX_TIMESTAMP(due_date) as due_date_unix, item_types.name as type_name ' .
         'FROM ' . TATT_PREFIX . 'items items JOIN ' . TATT_PREFIX . 'item_types item_types USING (item_type_id)' .
         "WHERE item_id = $item_id";
        $result = $db->query($query);
        if($result->num_rows == 1) {
            $item = $result->fetch_object();

            $this->id                 = $item_id;
            $this->name               = $item->name;
            $this->type_id            = (int)$item->item_type_id;
            $this->type_name          = $item->type_name;
            $this->owner_id           = (int)$item->owner_id;
            $this->owner              = new User($this->owner_id);
            $this->location           = $item->location;
            $this->last_accounted_for = (int)$item->last_accounted_for_unix;
            $this->has_photo          = str_to_bool($item->has_photo);
            $this->due_date           = (int)$item->due_date_unix;
            if($item->checked_out_by != NULL) {
                $this->checked_out_by = new User((int)$item->checked_out_by);
            }

            $this->attributes = Attribute::get_attributes_by_item_id($item_id);
        }
    }

    /*
     * Returns the entire item catalog, useful for statistics
     */
    static public function get_all_items() {
        global $db;

        $query = 'SELECT *, items.name as name, items.owner_id as owner_id, UNIX_TIMESTAMP(last_accounted_for) as last_accounted_for_unix, UNIX_TIMESTAMP(due_date) as due_date_unix, item_types.name as type_name ' .
                 'FROM ' . TATT_PREFIX . 'items as items JOIN ' . TATT_PREFIX . "item_types item_types USING (item_type_id) ";

        $result = $db->query($query);

        $items_array = array();

        while($item_data = $result->fetch_object()) {
            $item = new Item();

            $item->id                 = $item_data->item_id;
            $item->name               = $item_data->name;
            $item->type_id            = (int)$item_data->item_type_id;
            $item->type_name          = $item_data->type_name;
            $item->owner_id           = (int)$item_data->owner_id;
            $item->owner              = new User($item->owner_id);
            $item->location           = $item_data->location;
            $item->last_accounted_for = (int)$item_data->last_accounted_for_unix;
            $item->has_photo          = str_to_bool($item_data->has_photo);
            $item->due_date           = (int)$item_data->due_date_unix;
            if($item_data->checked_out_by != NULL) {
                $item->checked_out_by = new User((int)$item_data->checked_out_by);
            }

            $item->attributes = Attribute::get_attributes_by_item_id($item_data->item_id);

            $items_array[] = $item;
        }
        return $items_array;
    }

    static public function get_items_by_user_id($user_id, $start_index = 1, $items_per_page = 50) {
        global $db;
        //TODO: Validate input

        $query = 'SELECT *, items.name as name, items.owner_id as owner_id, UNIX_TIMESTAMP(last_accounted_for) as last_accounted_for_unix, UNIX_TIMESTAMP(due_date) as due_date_unix, item_types.name as type_name ' .
                 'FROM ' . TATT_PREFIX . 'items items JOIN ' . TATT_PREFIX . "item_types item_types USING (item_type_id) " .
                 "WHERE items.owner_id = $user_id ORDER BY items.name ASC";
        $result = $db->query($query);

        $items_array = array();

        while($item_data = $result->fetch_object()) {
            $item = new Item();

            $item->id                 = $item_data->item_id;
            $item->name               = $item_data->name;
            $item->type_id            = (int)$item_data->item_type_id;
            $item->type_name          = $item_data->type_name;
            $item->owner_id           = (int)$item_data->owner_id;
            $item->owner              = new User($item->owner_id);
            $item->location           = $item_data->location;
            $item->last_accounted_for = (int)$item_data->last_accounted_for_unix;
            $item->has_photo          = str_to_bool($item_data->has_photo);
            $item->due_date           = (int)$item_data->due_date_unix;
            if($item_data->checked_out_by != NULL) {
                $item->checked_out_by = new User((int)$item_data->checked_out_by);
            }

            $item->attributes = Attribute::get_attributes_by_item_id($item_data->item_id);

            $items_array[] = $item;
        }
        return $items_array;
    }


    /**
     * Adds a new item to the database, returns 0 on failure
     */
    static public function create($name, $type_id, $owner_id) {
        global $db;

        //TODO Check that owner_id, and item_type_id exist in database before insert.
        $variables = 'name, owner_id, item_type_id, has_photo';
        $values = "'$name', $owner_id, $type_id, FALSE";
        $query = Item::insert_items_db($variables, $values);
        $result = $db->query($query);
        if($db->affected_rows > 0)
            return $db->insert_id;
        return 0;
    }

    // Deletes an item.  Only call this function if logged in user is allow to perform this action.
    static public function delete($item_id) {
        global $db;
        $query = 'DELETE FROM ' . TATT_PREFIX . "attribute_values WHERE item_id = $item_id";
        $db->query($query);

        $query = 'DELETE FROM ' . TATT_PREFIX . "items WHERE item_id = $item_id";
        $db->query($query);

    }

    /********************
            Setters
    ********************/

    //NOTE: I purposely left out set id, owner_id, and item_id as they shouldn't be changed
    public function set_location($location) {
        global $db;
        $this->location = $location;
        $query = Item::update_items_db($this->id, 'location', "'$location'");
        $result = $db->query($query);
        //TODO Check result for errors?
    }

    static public function set_location_by_id($item_id, $location) {
        global $db;
        $query = Item::update_items_db($item_id, 'location', "'$location'");
        $result = $db->query($query);
        //TODO Check result for errors?
    }

    public function set_name($name) {
        global $db;
        $this->name = $name;
        $query = Item::update_items_db($this->id, 'name', "'$name'");
        $result = $db->query($query);
    }

    //XXX: not sure we can/want to have the funcitonality to set the date since
    //     it is normally an autoupdated field
    public function set_last_accounted_for($date) {
        global $db;
        $this->last_accounted_for = $date;
        $query = Item::update_items_db($this->id, 'last_accounted_for', "'$date'");
        $result = $db->query($query);
        //TODO Check result for errors?
    }

    public function update_last_accounted_for() {
        global $db;
        $this->last_accounted_for = date('Y-m-d');
        $query = Item::update_items_db($this->id, 'last_accounted_for', "'$this->last_accounted_for'");

    }

    public function set_photo($photo) {
        global $db;
        $this->has_photo = true;
        $query = Item::update_items_db($this->id, 'has_photo', 1);
        $resupt = $db->query($query);
        //TODO add photo to database?
    }

    public function set_due_date($date) {
        global $db;
        $this->due_date = $date;
        $query = Item::update_items_db($this->id, 'due_date', "'$date'");
        $results = $db->query($query);
        //TODO Check result for errors?
    }

    public function set_checked_out_by($user_id) {
        global $db;
        $query = Item::update_items_db($this->id, 'checked_out_by', $user_id);
        $result = $db->query($query);

        $this->checked_out_by = new User($user_id);
        //TODO Check result for errors?
    }

    /********************
            Getters
    ********************/

    public function get_attributes() {
        return $this->attributes;
    }
    public function get_name() {
        return $this->name;
    }
    public function get_id() {
        return $this->id;
    }
    public function get_type_id() {
        return $this->type_id;
    }
    public function get_type() {
        global $db;
        $query = 'SELECT name FROM ' . TATT_PREFIX . "item_types WHERE item_type_id = {$this->type_id}";
        $result = $db->query($query);
        $item_type = $result->fetch_object();
        return $item_type->name;
    }
    public function get_owner_id() {
        return $this->owner_id;
    }
    public function get_owner_name() {
        global $db;
        $query = 'SELECT username FROM ' . TATT_PREFIX . "users WHERE user_id = {$this->owner_id}";
        $result = $db->query($query);
        $user = $result->fetch_object();
        return $user->username;
    }
    public function get_location() {
        return $this->location;
    }
    public function get_last_accounted_for() {
        return $this->last_accounted_for;
    }
    public function get_has_photo() {
        return $this->has_photo;
    }
    public function get_due_date() {
        return $this->due_date;
    }
    public function get_checked_out_by() {
        return $this->checked_out_by->get_user_id();
    }

    public function to_array() {
        $item_array = get_object_vars($this);
        if($item_array['owner'] != NULL)
            $item_array['owner'] = $item_array['owner']->to_array();

        if($item_array['checked_out_by'] != NULL)
            $item_array['checked_out_by'] = $item_array['checked_out_by']->to_array();

        if($item_array['attributes'] != NULL) {
            foreach($item_array['attributes'] as $attribute) {
                $attributes_array[] = $attribute->to_array();
            }
            $item_array['attributes'] = $attributes_array;
        }
        return $item_array;
    }

    /*
     * Returns a set of user_id's that have checked out a certain item
     */
    static public function get_item_history($item_id) {
        global $db;
        $query = 'SELECT user_id ' .
                 'FROM ' . TATT_PREFIX . 'checkouts ' .
                 "WHERE item_id = $item_id";
        $result = $db->query($query);
        //TODO return a sensible result
        return $result;
    }

    /*
     * Checkout an item, based on the item and user id's passed in as args
     */
    static public function checkout_by_ids($item_id, $user_id) {
        global $db;
        $query = 'INSERT INTO ' . TATT_PREFIX . 'checkouts (item_id, user_id) ' .
                 "VALUES ($item_id, $user_id)";
        $checkouts_result = $db->query($query);

        $query = 'UPDATE ' . TATT_PREFIX . 'items ' .
                 "SET checked_out_by = $user_id ".
                 "WHERE item_id = $item_id";
        $items_result = $db->query($query);
        //TODO check results
    }

    /*
     * Checkout count (for statistical analysis/graphs) based on an item id
     * passed in as an argument
     */
   static public function checkout_count_by_id($item_id) {
       global $db;

       $query = 'SELECT COUNT(*) AS CHECKOUTCOUNT ' .
                'FROM ' . TATT_PREFIX . 'checkouts ' .
                "WHERE item_id = $item_id";
       $result = $db->query($query)->fetch_object();
       return $result->CHECKOUTCOUNT;
   }

    /*
     * Checkout count for day X days from today (based on parameter)
     */
    static public function checkout_count_by_day($daysago) {
       //XXX: UNTESTED
       global $db;

       $query = 'SELECT COUNT(*) AS CHECKOUTCOUNT ' .
                'FROM ' . TATT_PREFIX . 'checkouts ' .
                "WHERE DATE(checkout_time) = (DATE(NOW()) - INTERVAL $daysago DAY)";
       $result = $db->query($query)->fetch_object();
       return $result->CHECKOUTCOUNT;
    }
    /*
     * Checkin count for day X days from today (based on parameter)
     */
    static public function return_count_by_day($daysago) {
       //XXX: UNTESTED
       global $db;

       $query = 'SELECT COUNT(*) AS CHECKINCOUNT ' .
                'FROM ' . TATT_PREFIX . 'checkouts ' .
                "WHERE DATE(return_time) = (DATE(NOW()) - INTERVAL $daysago DAY)";
       $result = $db->query($query)->fetch_object();
       return $result->CHECKINCOUNT;
    }

    /*
     * Checkout this item object based on a user's id.
     */
    public function checkout_item($user_id) {
        checkout_by_id($this->id, $user_id);
    }

    static public function return_by_id($item_id, $user_id) {
        global $db;
        $query = 'UPDATE ' . TATT_PREFIX . 'items ' .
                 'SET checked_out_by = NULL ' .
                 "WHERE item_id = $item_id";
        $result = $db->query($query);

        $query = 'UPDATE ' . TATT_PREFIX . 'checkouts ' .
                 'SET return_time = NOW() '.
                 "WHERE item_id = $item_id AND user_id = $user_id AND return_time = '0000-00-00 00:00:00'";
        $result = $db->query($query);
    }

    /*
     * Searches all items returning all objects that have a name or attribute match
     */
    static public function search($search_string) {
        global $db;
        $search_string = $db->escape_string($search_string);
        //$query = 'SELECT *, items.name as name, items.owner_id as owner_id, UNIX_TIMESTAMP(last_accounted_for) as last_accounted_for_unix, UNIX_TIMESTAMP(due_date) as due_date_unix ' .
        //         'FROM ' . TATT_PREFIX . 'attributes attr JOIN ' . TATT_PREFIX . 'items items ' .
        //         "WHERE items.name LIKE '%$search_string%' AND attr.item_id = items.item_id";
        //$result = $db->query($query);
        $query = 'SELECT *, items.name as name, items.owner_id as owner_id, UNIX_TIMESTAMP(last_accounted_for) as last_accounted_for_unix, UNIX_TIMESTAMP(due_date) as due_date_unix, item_types.name as type_name ' .
                 'FROM ' . TATT_PREFIX . 'items items JOIN ' . TATT_PREFIX . "item_types item_types USING (item_type_id) " .
                 "WHERE items.name LIKE '%$search_string%'";
        $result2 = $db->query($query);

        $items_array = array();
        while($item_data = $result2->fetch_object() /*|| $item_data = $result->fetch_object() */) {
            $item = new Item();

            $item->id                 = $item_data->item_id;
            $item->name               = $item_data->name;
            $item->type_id            = (int)$item_data->item_type_id;
            $item->type_name          = $item_data->type_name;
            $item->owner_id           = (int)$item_data->owner_id;
            $item->owner              = new User($item->owner_id);
            $item->location           = $item_data->location;
            $item->last_accounted_for = (int)$item_data->last_accounted_for_unix;
            $item->has_photo          = str_to_bool($item_data->has_photo);
            $item->due_date           = (int)$item_data->due_date_unix;
            if($item_data->checked_out_by != NULL) {
                $item->checked_out_by = new User((int)$item_data->checked_out_by);
            }

            $item->attributes = Attribute::get_attributes_by_item_id($item_data->item_id);

            $items_array[] = $item;
        }
        return $items_array;
    }

    /*
     * Returns a set of item_id reccomendations based on other users
     */
    static public function reccomendations($user_id){

    }

    /********************
       Query Factories
    ********************/

    static private function insert_items_db($variables, $values) {
        $query = 'INSERT INTO ' . TATT_PREFIX . "items ( $variables ) " .
            "VALUES ( $values )";
        return $query;
    }

    static private function update_items_db($item_id, $var_name, $value) {
        $query = 'UPDATE ' . TATT_PREFIX . 'items ' .
            "SET $var_name = $value " .
            "WHERE item_id = $item_id";
        return $query;
    }
    static private function delete_items_db($item_id) {
        $query = 'DELETE ' . TATT_PREFIX . 'items ' .
                 "WHERE item_id = $item_id";
        return $query;
    }

}
