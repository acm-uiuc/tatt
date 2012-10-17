/**
 * @author Vinay Hiremath
 * vhiremath4@gmail.com
 * Main Activity that handles the login for Track all the Things CS411 Project.
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
import org.apache.http.client.HttpClient;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.impl.client.DefaultHttpClient;

import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;

public class Main extends Activity {
	
	// progress dialog
	private static ProgressDialog loggingIn;
	
	//reuse the same static httpclient so that the cookies are valid across all Activities
	public static HttpClient httpclient = new DefaultHttpClient();
	
	//AsyncTask object that will keep track of the login event
	public doLogin loginTask;
	
	//successfull username and password (if login is successful)
	public static String userName = null;
	public static String passWrd = null;
	public static int user_id;
	
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main);
        
        //onClickListener to handle the login event on the login button
        Button submit = (Button)findViewById(R.id.loginsubmit);
        submit.setOnClickListener(new View.OnClickListener() {
			@Override
			public void onClick(View v) {
				//get the text from the username and password fields
				EditText username = (EditText)findViewById(R.id.username);
				EditText password = (EditText)findViewById(R.id.password);
				String user = username.getText().toString();
				String pass = password.getText().toString();
				Main.userName = user;
				Main.passWrd = pass;
				
				//if the user has entered nothing, don't bother trying to contact
				//the server - a safeguard
				if (user.equals("") || pass.equals(""))
					return;
				
				//otherwise we will contact the server to login
				//set up and execute the login asynctask
				
				//show the loading dialog
				loggingIn = ProgressDialog.show(Main.this, "", "Logging in...", true);
				loginTask = new doLogin(Main.this, "http://www.trackallthethings.com/mobile-api/login.php?userName="+user+"&passWrd="+pass);
				loginTask.execute();
			}
		});
    }
    
    class doLogin extends AsyncTask<String, Void, String>{
		
		String URL;//URL location that will be parsed for logging in
		private boolean loginSuccessful = false;//whether or not the login was successful
		private Context mainContext;
		
		//constructor
		public doLogin(Context main, String url){
			URL = url;
			mainContext = main;
		}
		
		protected String doInBackground(String... params){
			//safeguard
			if (URL==null || URL.equals(""))
				return null;
			
			String responseString = ""; //the response string

			//the actual code that parses the URL and tries to login
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
			    //parse the response
			    responseString = sb.toString();
				in.close();
			} catch(MalformedURLException e){
				e.printStackTrace();
			} catch (IOException e) {
				e.printStackTrace();
			}
			return responseString;
		}
		
		//the String from doInBackground gets passed here for
		//checking if the login was successful
		protected void onPostExecute(String response){
			//if unsuccessful
			if (response.equals("") || response.contains("FAILURE LOGIN")){
				Main.userName = null;
				Main.passWrd = null;
				Main.loggingIn.dismiss();
				Toast.makeText(mainContext, "Could not log in, please check your username and password.", 5000).show();
				return;
			}
			
			//if successful
			if (response.contains("SUCCESSFUL LOGIN")){
					loginSuccessful = true;
					String[] responseSplit = response.split("\n");
					String userId = responseSplit[responseSplit.length-1];
					user_id = Integer.parseInt(userId);
					Main.loggingIn.dismiss();
					Intent intent = new Intent(mainContext, Menu.class);
					startActivity(intent);
			}
		}
		
		//get whether the login was successful
		boolean wasSuccessfulLogin(){
			return loginSuccessful;
		}
	}
}
