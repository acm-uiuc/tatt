/**
 * @author Vinay Hiremath
 * vhiremath4@gmail.com
 * Activity that acts as the app's main menu. Acts as an Activity launcher.
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

import java.util.ArrayList;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.ListView;

public class Menu extends Activity{
	
	//variables that make up the main menu
	ListView mainMenu;
	ArrayAdapter<String> menuAdapter;//adapter that will bind the data to the ListView
	ArrayList<String> menuItems = new ArrayList<String>();
	String selectedMenuItem;//stores the selected menu item and fires up the appropriate Activity
	
	@Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.menu);
        
        //initialize some variables that will be of importance to the main menu
        mainMenu = (ListView)findViewById(R.id.mainmenu);
        int layoutID = android.R.layout.simple_list_item_1;
        menuAdapter = new ArrayAdapter<String>(this, layoutID, menuItems);//bind the menuItems ArrayList to the adapter
        mainMenu.setAdapter(menuAdapter);//bind the adapter to the ListView
        String[] menuEntries = new String[]{
        	"Inventory",
        	"Items on Loan",
        	"Scan Item",
        	"Login as Different User"
        };//items that will populate the menu
        
        //populate the ListView
        for (int i = 0; i < menuEntries.length; i++)
        	menuItems.add(menuEntries[i]);
        menuAdapter.notifyDataSetChanged();//notify the adapter of data change - not necessary but precautionary
        
        //fire up separate Activites
        mainMenu.setOnItemClickListener(new AdapterView.OnItemClickListener() {
			public void onItemClick(AdapterView<?> av, View v, int index,
					long arg3) {
				//get the selected menu item
				selectedMenuItem = menuItems.get(index);
				
				//check to see which item has been selected and then fire up the necessary Activity
				if (selectedMenuItem.equals("Inventory")){
					Intent intent = new Intent(Menu.this, MyItems.class);
					startActivity(intent);
				} else if (selectedMenuItem.equals("Items on Loan")){
					Intent intent = new Intent(Menu.this, ItemsOnLoan.class);
					startActivity(intent);
				} else if (selectedMenuItem.equals("Scan Item")){
					Intent intent = new Intent(Menu.this, Scan.class);
					startActivity(intent);
				} else if(selectedMenuItem.equals("Login as Different User")){
					finish();
				}
			}
		});
    
	}

}
