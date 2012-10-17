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

class Attribute {
	private $item_id = NULL;
	private $attribute_id = NULL;
	private $name = NULL;
	private $value = NULL;

	public function __construct($item_id = NULL, $attribute_id = NULL)
	{
		if($item_id == NULL && $attribute_id == NULL)
			return; //Return unitialized object

		if($item_id == NULL || $attribute_id == NULL || !is_numeric($item_id) || !is_numeric($attribute_id))
		{
			trigger_error('Item_id or Attribute_id invalid in Attribute class constructor');
			return;
		}
		$this->item_id = (int)$item_id;
		$this->attribute_id = (int)$attribute_id;
		$this->load_attribute_data();
	}

	public function get_name()
	{
		return $this->name;
	}

	public function get_value()
	{
		return $this->value;
	}

	public function set_value($new_value = NULL)
	{
		global $db;
		if($new_value == NULL){
			trigger_error('Parameter for set_value in Attribute object was NULL');
		}
		
		$new_value = $db->escape_string($new_value);
		$query = 'INSERT INTO ' . TATT_PREFIX . "attribute_values (attribute_id, item_id, value) VALUES ({$this->attribute_id}, {$this->item_id}, '$new_value') ON DUPLICATE KEY UPDATE value = '$new_value'";
		$db->query($query);
		//Reload data
		$this->load_attribute_data();
	}

	public function delete_value()
	{
		global $db;

		$query = 'DELETE FROM ' . TATT_PREFIX . "attribute_values WHERE attribute_id = {$this->attribute_id} AND item_id = {$this->item_id}";
		$db->query($query);
		//TODO Handle failure
		$this->value = NULL;
	}

	public function to_array() {
	    return get_object_vars($this);
	}

	////////////  STATIC FUNCTIONS  //////////////

	static public function create($name, $item_type_id)
	{
		global $db;
		if($item_type_id == NULL || $name == NULL || !is_numeric($item_type_id))
			trigger_error('Incorrect parameters given to create function in Attributes');

        //TODO check that owner exists in db.
        $item_type_id = (int)$item_type_id;
        $name = $db->escape_string($name);
        $query = 'INSERT INTO ' . TATT_PREFIX . "attributes (item_type_id, name) VALUES ($item_type_id, '$name')";
        $result = $db->query($query);
        if($db->affected_rows > 0)
            return $db->insert_id;
        return 0;
	}

	static public function exists($item_id, $attribute_id) {
		global $db;

		if ($item_id == NULL || $attribute_id == NULL)
			trigger_error('Incorrect parameters given to exists function in Attributes');

		$query = 'SELECT * FROM ' . TATT_PREFIX ."attributes WHERE item_id = $item_id AND attribute_id = $attribute_id";
		$result = $db->query($query);
		if ($result->num_rows > 0) {
			return true;
		}
		else {
			return false;
		}
	}

	static public function delete($attribute_id){
	    //TODO/Warning: Attribute_values table depends on the the attributes table. Delete those or throw error if related attribute values exist.
		global $db;
		if($attribute_id == NULL || !is_numeric($attribute_id))
 				trigger_error('Invalid attribute_id parameter for delete function in Attribute class');
		
        $attribute_id = (int)$attribute_id;
        $query = 'DELETE FROM '. TATT_PREFIX . "attributes WHERE attribute_id = $attribute_id";
        $db->query($query);
        //TODO Check for success.
	}

	static public function set_name($attribute_id = NULL, $new_name = NULL)
	{
		global $db;
		//TODO Make sure attribute already exists
		if($attribute_id == NULL || $new_name == NULL || !is_numeric($attribute_id))
		{
			trigger_error('Invalid parameters to static set_name function in attribute class. Item_id should be a number and new_name should be a string');
			return;
		}
		
		$attribute_id = (int)$attribute_id;
		$new_name = $db->escape_string($new_name);
		$query = 'UPDATE ' . TATT_PREFIX . "attributes SET name = '$new_name' WHERE attribute_id = $attribute_id";
		$db->query($query);
	}

	static public function get_attributes_by_item_id($item_id = NULL)
	{
		global $db;
		if($item_id == NULL || !is_numeric($item_id))
		{
			trigger_error('$item_id is either NULL or not a number in get_attributes_by_item_id function in Attributes class');
			return;
		}

		$item_id = (int)$item_id;

		$query = 'SELECT * FROM ' . TATT_PREFIX . 'attributes attr JOIN ' . TATT_PREFIX . "attribute_values attr_val USING (attribute_id) WHERE attr_val.item_id = $item_id ORDER BY attr.name ASC";
		$result = $db->query($query);

		if($result->num_rows > 0){
			while($attribute = $result->fetch_object()){
				$attr = new Attribute();
				$attr->item_id = (int)$item_id;
				$attr->attribute_id = (int)$attribute->attribute_id;
				$attr->name = $attribute->name;
				$attr->value = $attribute->value;
				$attributes_array[$attr->attribute_id] = $attr;

			}
			return $attributes_array; //EARLY RETURN
		}
		//No attributes found.
		return NULL;
	}

	//Returns an array of attribute_id and attribute names.
	static public function get_attribute_info_by_item_type($item_type){
	    global $db;
	    $query = 'SELECT * FROM ' . TATT_PREFIX . "attributes WHERE item_type_id = $item_type";
	    $result = $db->query($query);
	    if($result->num_rows == 0)
		return NULL; //EARLY RETURN
	    $attributes = array();
	    while($attribute = $result->fetch_assoc()){
		$attributes[] = $attribute;
	    }
	    return $attributes;
	}

// Private

	private function load_attribute_data()
	{
		//TODO Handle case where attribute does not exist
		global $db;
		$query = 'SELECT attr.name as name, attr_val.value as value FROM ' . TATT_PREFIX . 'attributes attr JOIN ' . TATT_PREFIX . "attribute_values attr_val USING (attribute_id) WHERE attr_val.attribute_id = {$this->attribute_id} AND attr_val.item_id = {$this->item_id} ORDER BY name ASC";
		$result = $db->query($query);
		if($result->num_rows > 0){
			$attribute_info = $result->fetch_object();
			$this->name = $attribute_info->name;
			$this->value = $attribute_info->value;
		}
	}
}
