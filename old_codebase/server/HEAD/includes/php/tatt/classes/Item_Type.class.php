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

class Item_Type {

    private $name;
    private $attr_list;     // attr - short for attribute
    private $id;
    private $db;


    public function __construct ($db = NULL, $name = NULL){
        if($name == NULL || $db == NULL)
            trigger_error("A item_type class constructor parameter is NULL");
        $this->name = $name;
        $this->db = $db;

        $query = 'INSERT INTO ' . TATT_PREFIX . 'item_types '.
            "VALUES($name)";
        $result = $db->query($query);
        //TODO Check result for error?
    }

    // Creates a new item type and returns its id
    static public function create($name, $owner_id) {
        global $db;
        //TODO check that owner exists in db.
        if($name == NULL || $owner_id == NULL || !is_numeric($owner_id))
            trigger_error('Invalid parameter for create_item_type function');
        $owner_id = (int)$owner_id;
        $name = $db->escape_string($name);
        $query = 'INSERT INTO ' . TATT_PREFIX . "item_types (name, owner_id) VALUES ('$name', $owner_id)";
        $result = $db->query($query);
        if($db->affected_rows > 0)
            return $db->insert_id;
        return 0;
    }

    static public function delete($item_type_id) {
        global $db;
        //TODO Items and attributes depend on this. Either throw error or delete related items, attributes and attribute values before deleting this.
        if( $item_type_id == NULL || !is_numeric($item_type_id))
            trigger_error('Item_type_id invalid in delete function');
        $item_type_id = (int)$item_type_id;
        $query = 'DELETE FROM '. TATT_PREFIX . "item_types WHERE item_type_id = $item_type_id";
        $db->query($query);
        //TODO Check for success.
    }

    static public function get_item_types_by_owner_id($owner_id) {
        global $db;
        $query = 'SELECT * FROM ' . TATT_PREFIX . "item_types WHERE owner_id = $owner_id ORDER BY name ASC";
        $result = $db->query($query);
        if($result->num_rows == 0)
            return NULL; //EARLY RETURN
        $item_types = array();
        while($item_type = $result->fetch_assoc()){
            $item_types[] = $item_type;
        }
        return $item_types;
    }

    static public function exhists($item_type_name, $owner){
        global $db;
        $query = 'SELECT * ' .
                 'FROM ' . TATT_PREFIX . 'item_types ' .
                 "WHERE name = '$item_type_name' AND owner_id = '$owner'";
        $result = $db->query($query);

        if($result->num_rows > 0){
            return true;
        }
        else return false;
    }
    public function get_name(){
        return $this->name;
    }

    public function get_id(){
        return $this->id;
    }

    public function get_attributes(){
        return $this->attr_list;
    }

    public function add_item_type_attr($attr){
        // Create and add the relation of the attribute to the item type
        $query = "INSERT INTO " . TATT_PREFIX . "attributes " . "(item_type_id, name) " .
                 "VALUES($this->id , $attr)";
        $result = $this->db->query($query);
        //TODO Check result for error?
    }

    public function remove_item_type_attr($attr){
        //TODO Check this for correctness:
        $query = 'DELETE FROM ' . TATT_PREFIX . 'attributes ' .
                 'WHERE (name.' . TATT_PREFIX . "attributes = $attr
                  AND item_type_id." . TATT_PREFIX . "attributes = $this->id)";
        $result = $db->query($query);
        //TODO Check result for error?
    }

    public function edit_item_type_attr($old_attr, $new_attr){
        $query = 'UPDATE ' . TATT_PREFIX . 'attributes ' .
            "SET name = $new_attr " .
            "WHERE ( item_type_id." .TATT_PREFIX . "attributes = $this->id)";
        $result = $db->query($query);
        //TODO Check result for error?
    }

}
