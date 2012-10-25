/**
 * @author Vinay Hiremath
 * vhiremath4@gmail.com
 * Class that represents an inventory item.
 */

/*
The MIT License

Copyright (c) 2011 Vinay Hiremath

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

package com.cs411.trackallthethings;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class Item {
	// Item information
	private String name;
	private String id;
	private String type_name;
	private String location;
	/* owner is of the form:
	 * {
	 * 		users_data_loaded: bool
	 * 		user_id: int
	 * 		account_type: String
	 * 		username: String
	 * 		email: String
	 * 		error: null
	 * 	}	
	 */
	private JSONObject owner;
	private int due_date;
	private int last_accounted_for;
	private int checked_out_by = -1;
	/* attributes is of the form:
	 * [
	 * 	{
	 * 		item_id: int
	 * 		attribute_id: int
	 * 		name: String
	 * 		value: String
	 * 	},
	 * 	... and so on
	 * ]
	 */
	private JSONArray attributes;
	
	// constructor that takes in a JSONObject and creates the Item Object
	public Item(JSONObject data){
		try {
			name = (String) data.get("name");
			id = (String) data.get("id");
			type_name = (String) data.get("type_name");
			owner = data.getJSONObject("owner");
			due_date = data.getInt("due_date");
			location = data.getString("location");
			last_accounted_for = data.getInt("last_accounted_for");
			attributes = data.getJSONArray("attributes");
			if (data.get("checked_out_by") != null)
				checked_out_by = data.getInt("checked_out_by");
		} catch (JSONException e) {
			e.printStackTrace();
		}
	}
	
	// getters
	public String getName(){
		return name;
	}
	public String getId(){
		return id;
	}
	public String getTypeName(){
		return type_name;
	}
	public JSONObject getOwner(){
		return owner;
	}
	public int getDueDate(){
		return due_date;
	}
	public String getLocation(){
		return location;
	}
	public int getLastAccountedFor(){
		return last_accounted_for;
	}
	public int getCheckedOutBy(){
		return checked_out_by;
	}
	public JSONArray getAttributes(){
		return attributes;
	}
	public void setCheckedOut(int new_user_id){
		checked_out_by = new_user_id;
	}
	
	
	// the toString that will be called for display on the ListView
	public String toString(){
		return "Name: "+name+"\n"+
				"Type: "+type_name;
	}

}
