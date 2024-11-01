<?php
	$options = get_option( 'wpnyd-group' );
	
	if( $_POST ) {
		$post_url = "https://secure.authorize.net/gateway/transact.dll ";
		$testmode = 'no';
		if( isset( $options['wpnyd_authnet-mode'] ) ) {
			$testmode = 'yes';
			$post_url = "https://test.authorize.net/gateway/transact.dll ";
		}

		$post_values = array(
			
			// the API Login ID and Transaction Key must be replaced with valid values
			"x_login"			=> $options['wpnyd_authnet-login'],
			"x_tran_key"		=> $options['wpnyd_authnet-trans'],

			"x_version"			=> "3.1",
			"x_delim_data"		=> "TRUE",
			"x_delim_char"		=> "|",
			"x_relay_response"	=> "FALSE",
			"x_test_request"	=> $testmode,

			"x_type"			=> "AUTH_CAPTURE",
			"x_method"			=> "CC",
			"x_card_num"		=> $_POST['card_number'],
			"x_exp_date"		=> $_POST['card_mm'] . $_POST['card_yyyy'],

			"x_amount"			=> $_POST['donation'],
			"x_description"		=> "Online Donation: " . time(),

			"x_first_name"		=> $_POST['bill_first'],
			"x_last_name"		=> $_POST['bill_last'],
			"x_address"			=> $_POST['bill_add'],
			"x_state"			=> $_POST['bill_state'],
			"x_zip"				=> $_POST['bill_zip']
			// Additional fields can be added here as outlined in the AIM integration
			// guide at: http://developer.authorize.net
			
		);

		// This section takes the input fields and converts them to the proper format
		// for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
		$post_string = "";
		foreach( $post_values as $key => $value ) { 
			$post_string .= "$key=" . urlencode( $value ) . "&";
		}
		$post_string = rtrim( $post_string, "& " );

		// This code uses the CURL library for php to establish a connection,
		// submit the post, and record the response.
		// If you receive an error, you may want to ensure that you have the curl
		// library enabled in your php configuration
		$request = curl_init( $post_url ); // initiate curl object
		curl_setopt( $request, CURLOPT_HEADER, 0 ); // set to 0 to eliminate header info from response
		curl_setopt( $request, CURLOPT_RETURNTRANSFER, 1 ); // Returns response data instead of TRUE(1)
		curl_setopt( $request, CURLOPT_POSTFIELDS, $post_string ); // use HTTP POST to send form data
		curl_setopt( $request, CURLOPT_SSL_VERIFYPEER, FALSE ); // uncomment this line if you get no gateway response.
		$post_response = curl_exec( $request ); // execute curl post and store results in $post_response
		curl_close( $request ); // close curl object

		// This line takes the response and breaks it into an array using the specified delimiting character
		$response_array = explode($post_values["x_delim_char"],$post_response);
		
		$result_text;
		if( $response_array[0] == "1" ) {
		
			$result_text = 'Card Approved';		
			//send receipt and notification emails to user and the site admin
			require_once( plugin_dir_path( __FILE__ ) . '../../templates/emails.php' );
			$email = thank_you( $_POST['donation'] );
			send_email( $_POST['email'], $email[0], $email[1], $email[2] );
			$email = new_donation( $_POST['donation'] );
			send_email( get_bloginfo( 'admin_email' ), $email[0], $email[1], $email[2] );
			
		} else {
		
			$result_text = "Authorize.net.\nResponse reason text: " . $response_array[2] . "\n";
			
		}		
		echo '<h3>' . $result_text . '</h3>';
	}
	
	function get_form() {
		$form_build = <<< HERE
<form action="" method="POST" id="payment-form">
<span class="payment-errors"></span>
<div class="form-row">
<label>
<span>Email</span>
<input type="text" name="email" />
</label>
</div>
<div class="form-row">
<label>
<span>Billing First Name</span>
<input type="text" name="bill_first" />
</label>
</div>
<div class="form-row">
<label>
<span>Billing Last Name</span>
<input type="text" name="bill_last" />
</label>
</div>
<div class="form-row">
<label>
<span>Billing Address</span>
<input type="text" name="bill_add" />
</label>
</div>
<div class="form-row">
<label>
<span>Billing City</span>
<input type="text" name="bill_city" />
</label>
</div>
<div class="form-row">
<label>
<span>Billing State/Province</span>
<input type="text" name="bill_state" />
</label>
</div>
<div class="form-row">
<label>
<span>Billing Zip</span>
<input type="text" name="bill_zip" />
</label>
</div>
<div class="form-row">
<label>
<span>Card Number</span>
<input type="text" name="card_number" />
</label>
</div>
<div class="form-row">
<label>
<span>CVC</span>
<input type="text" size="4" name="cvc" />
</label>
</div>
<div class="form-row">
<label>
<span>Expiration (MM/YYYY)</span>
<input type="text" size="2" name="card_mm" />
</label>
<span> / </span>
<input type="text" size="4" name="card_yyyy" />
</div>
<div class="form-row">
<label>
<span>Donation Amount</span>
<input type="text" name="donation" size="5" />
</label>
</div>
<button type="submit">Submit Payment</button>
</form>
HERE;
		return $form_build;
	}