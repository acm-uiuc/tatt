/**
 * @author Vinay Hiremath
 * vhiremath4@gmail.com
 * Activity that scans QR codes of items and allows the user to see
 * information about the scanned item and allows them to check out the
 * item.
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

import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpGet;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.Button;
import android.widget.TextView;
import android.widget.Toast;

public class Scan extends Activity{
	
	// the scanned item
	private Item scannedItem = null;
	
	// progress dialog
	private static ProgressDialog gettingItem;
	private static ProgressDialog checkingOutItem;
	private static ProgressDialog returningItem;
	
	// this context
	private Context scanContext = Scan.this;
	
	@Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.scan);
        
        //TODO: CHECK IF QR DROID IS INSTALLED
        
        // launch QR Droid to scan item
        Intent qrDroid = new Intent("la.droid.qr.scan");
        startActivityForResult(qrDroid, 0);
        
        // set up events for buttons
        Button checkout = (Button)findViewById(R.id.checkoutitem);
        Button returnItem = (Button)findViewById(R.id.returnitem);
        Button scan = (Button)findViewById(R.id.scanagain);
        Button back = (Button)findViewById(R.id.backFromScan);
        
        back.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				finish();	
			}
		});
        
        scan.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				scannedItem = null;
				TextView scanItemInfo = (TextView)findViewById(R.id.scaniteminfo);
				scanItemInfo.setText("No item scanned... Please scan another item.");
				Intent qrDroid = new Intent("la.droid.qr.scan");
		        startActivityForResult(qrDroid, 0);
			}
		});
        
        checkout.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				if (scannedItem == null)
					return;
				if (scannedItem.getCheckedOutBy() == Main.user_id){
					Toast.makeText(v.getContext(), "This item is checked out to you already!", 5000).show();
					return;
				}
				// this item is already checked out
				if (scannedItem.getCheckedOutBy() != -1 && scannedItem.getCheckedOutBy() != Main.user_id){
					Toast.makeText(v.getContext(), "This item is checked out by someone else already!", 5000).show();
					return;
				}
				// no one has checked this out, so check out the item
				if (scannedItem.getCheckedOutBy() == -1){
					String item_id = scannedItem.getId();
					checkingOutItem = ProgressDialog.show(scanContext, "", "Checking out item...", true);
					
					String responseString = "";
					try{
						HttpGet httpget = new HttpGet("http://www.trackallthethings.com/mobile-api/check_out_item?user_id="+Main.user_id+"&item_id="+item_id);
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
					    //parse the response
					    responseString = sb.toString();
						in.close();
					} catch(MalformedURLException e){
						e.printStackTrace();
					} catch (IOException e) {
						e.printStackTrace();
					}
					
					if(responseString.contains("SUCCESS")){
						checkingOutItem.dismiss();
						Toast.makeText(v.getContext(), "Item successfully checked out!", 5000).show();
						scannedItem.setCheckedOut(Main.user_id);
						return;
					} else{
						checkingOutItem.dismiss();
						Toast.makeText(v.getContext(), "The item was not succesfully checked out. :-(", 5000).show();
					}
					
				}
			}
		});
        
        returnItem.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				if (scannedItem == null)
					return;
				
				// if it's not checked out
				if (scannedItem.getCheckedOutBy() == -1){
					Toast.makeText(v.getContext(), "This item has already been returned.", 5000).show();
					return;
				}
				
				// otherwise return the item
				String item_id = scannedItem.getId();
				returningItem = ProgressDialog.show(scanContext, "", "Returning item...", true);
				
				String responseString = "";
				try{
					HttpGet httpget = new HttpGet("http://www.trackallthethings.com/mobile-api/return_item?user_id="+Main.user_id+"&item_id="+item_id);
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
				    //parse the response
				    responseString = sb.toString();
					in.close();
				} catch(MalformedURLException e){
					e.printStackTrace();
				} catch (IOException e) {
					e.printStackTrace();
				}
				
				if(responseString.contains("SUCCESS")){
					returningItem.dismiss();
					Toast.makeText(v.getContext(), "Item successfully returned!", 5000).show();
					scannedItem.setCheckedOut(-1);
					return;
				} else{
					returningItem.dismiss();
					Toast.makeText(v.getContext(), "The item was not succesfully returned. :-(", 5000).show();
				}
			}
		});

	}
	
	// how to handle the scan data
	@Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data){
		// show that we're doing shit bro
		gettingItem = ProgressDialog.show(scanContext, "", "Getting item...", true);
		
		// the scan string result
		String result = data.getExtras().getString("la.droid.qr.result");
		Log.d("result: ", result);
		String[] resultSplit = result.split("\\|");
		
		// the case where there is no item_id in the scan result
		if (resultSplit.length < 1){
			gettingItem.dismiss();
			return;
		}
		
		// get the item's information
		for (int i=0; i< resultSplit.length; i++){
			Log.d("resultSplit "+i, resultSplit[i]);
		}
		String item_id = resultSplit[resultSplit.length-1];
		Log.d("item_id: ", item_id);
		String responseString = "";
		try{
			HttpGet httpget = new HttpGet("http://www.trackallthethings.com/mobile-api/search_item?item_id="+item_id);
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
		    //parse the response
		    responseString = sb.toString();
			in.close();
		} catch(MalformedURLException e){
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
		
		// if the response was bad
		if (responseString.equals("") || responseString.contains("INVALID QR SCAN")){
			gettingItem.dismiss();
			return;
		}
		
		// make the item
		try {
			JSONObject itemData = new JSONObject(responseString);
			scannedItem = new Item(itemData);
		} catch (JSONException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
		
		// if the item was made properly, let's display its info
		if (scannedItem != null){
			TextView scanItemInfo = (TextView)findViewById(R.id.scaniteminfo);
			scanItemInfo.setText(scannedItem.toString());
		}
		
		gettingItem.dismiss();
		
	}

}
