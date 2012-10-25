/**
 * @author Vinay Hiremath
 * vhiremath4@gmail.com
 * Activity that displays the user's inventory items.
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

import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.MalformedURLException;
import java.util.ArrayList;

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpGet;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.os.AsyncTask;
import android.os.Bundle;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ListView;
import android.widget.Toast;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;

public class MyItems extends Activity {
	
	// fields for the feed
	private static ArrayAdapter<Item> myItemsAdapter;
	private static ArrayList<Item> myItems = new ArrayList<Item>();
	
	// progress dialog
	private static ProgressDialog loading;
	
	public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.myitems);
        
        // bind the ArrayList of Item Objects to the Array Adapter
        // and then bind the Array Adapter to the ListView
        ListView myItemsFeed = (ListView) findViewById(R.id.myitemsfeed);
        myItemsAdapter = new ArrayAdapter<Item>(this, R.layout.item, myItems);
        myItemsFeed.setAdapter(myItemsAdapter);
        
        // fire up an AsyncTask to get the user's items
        //show the loading dialog
		loading = ProgressDialog.show(MyItems.this, "", "Getting your items...", true);
        getMyItems task = new getMyItems(MyItems.this, "http://www.trackallthethings.com/mobile-api/display_user_inventory_items.php");
        task.execute();
        
        Button back = (Button)findViewById(R.id.backfrommyitems);
        back.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				if (myItemsAdapter != null)
					myItemsAdapter.clear();
				finish();
			}
		});
	}
	
	// adds an Item Object to the feed
	public static void addNewEntry(Item toBeAdded){
		myItems.add(toBeAdded);
		myItemsAdapter.notifyDataSetChanged();
	}
	
	// AsyncTask that will get the user's items and display them on the feed
	class getMyItems extends AsyncTask<String, Void, JSONArray>{
		String URL;//URL location that will be parsed for the items
		Context myItemsContext;
		
		//constructor
		public getMyItems(Context v, String url){
			URL = url;
			myItemsContext = v;
		}
		
		protected JSONArray doInBackground(String... params){
			//safeguard
			if (URL==null || URL.equals(""))
				return null;
			
			JSONArray returnObject = null;

			//the actual code that parses the URL and tries to get the user's items
			try{
				HttpGet httpget = new HttpGet(URL);
				HttpResponse response;
				
				response = Main.httpclient.execute(httpget);
				HttpEntity entity = response.getEntity();
				InputStream in = entity.getContent();

				BufferedReader reader = new BufferedReader(new InputStreamReader(in));
				StringBuilder sb = new StringBuilder();
					
				String input = null;
			    try {
			        while ((input = reader.readLine()) != null) {
			        sb.append(input + "\n");
			        }
			    } catch (IOException e) {
			            e.printStackTrace();
			    } finally {
			        try {
			            in.close();
			        } catch (IOException e) {
			            e.printStackTrace();
			        }
			    }
			    String responseString = sb.toString();
			    try {
					returnObject = new JSONArray(responseString);
				} catch (JSONException e) {
					e.printStackTrace();
				}
				in.close();
			} catch(MalformedURLException e){
				e.printStackTrace();
			} catch (IOException e) {
				e.printStackTrace();
			}
			return returnObject;
		}
		
		//the JSONObject from doInBackground gets passed here for
		//updating the ListView
		protected void onPostExecute(JSONArray myItems){
			// make sure the data is valid
			if (myItems == null || myItems.length() == 0){
				MyItems.loading.dismiss();
				Toast.makeText(myItemsContext, "Could not get any items!", 5000).show();
				return;
			}
			
			// traverse through JSONObject to set up Items
			for (int i = 0; i < myItems.length(); i++){
				try {
					String input = (String)myItems.get(i);
					JSONObject inputObject = new JSONObject(input);
					Item toBeAdded = new Item(inputObject);
					addNewEntry(toBeAdded);
				} catch (JSONException e) {
					e.printStackTrace();
				}
			}
			MyItems.loading.dismiss();
		}
	}
}
