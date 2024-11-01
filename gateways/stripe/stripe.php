<?php
	//get our plugin settings
	$options = get_option( 'wpnyd-group' );
	
	//Include strip API, required to be on their server
	wp_enqueue_script( 'stripe', 'https://js.stripe.com/v2/', array( 'jquery' ) );
	wp_register_script( 'key', plugin_dir_url( __FILE__ ) . 'key.js', array( 'jquery' ), '1.0', true );
	
	$pubkey = array();
	if( isset( $options['wpnyd_stripe-mode'] ) && isset( $options['wpnyd_stripe-tpk'] ) ) {
		$pubkey['pubkey'] = trim( esc_attr( $options['wpnyd_stripe-tpk'] ) );
	} elseif( isset( $options['wpnyd_stripe-lpk'] ) ) {
		$pubkey['pubkey'] = trim( esc_attr( $options['wpnyd_stripe-lpk' ] ) );
	}
	wp_localize_script( 'key', 'pubkey', $pubkey );
	wp_enqueue_script( 'key' );
	
	require_once( 'stripe-php-3.4.0/init.php' );
	require_once( 'stripe-php-3.4.0/lib/Stripe.php' );
	  
	$error = '';
	$success = '';
	
	$apikey = '';
	if( isset( $options['wpnyd_stripe-mode'] ) && isset( $options['wpnyd_stripe-tsk'] ) ) {
		$apikey = trim( esc_attr( $options['wpnyd_stripe-tsk'] ) );
	} elseif( isset( $options['wpnyd_stripe-lsk'] ) ) {
		$apikey = trim( esc_attr( $options['wpnyd_stripe-lsk' ] ) );
	}
	
	if ( $_POST && $apikey != '' ) {
	
		\Stripe\Stripe::setApiKey( $apikey );
		//test example: sk_live_31pw13WP31wp13pWp3113wp1
		//live example: sk_test_42wp24WP42wp24WP42pw24wp
		try {
			if ( ! isset( $_POST['stripeToken'] ) )
			    throw new Exception( "The Stripe Token was not generated correctly" );
			\Stripe\Charge::create( array( "amount" => floatval( $_POST['donation']*100 ),
				"currency" => "usd",
				"card" => $_POST['stripeToken'] ) );
			$success = '<h2>Your payment was successful.</h2>';
		}
    	catch ( Exception $e ) {
			$error = '<h2>' . $e->getMessage() . '</h2>';
		}
		echo $success . $error;
		
		//send receipt and notification emails to user and the site admin
		require_once( plugin_dir_path( __FILE__ ) . '../../templates/emails.php' );
		$email = thank_you( $_POST['donation'] );
		send_email( $_POST['email'], $email[0], $email[1], $email[2] );
		$email = new_donation( $_POST['donation'] );
		send_email( get_bloginfo( 'admin_email' ), $email[0], $email[1], $email[2] );
		
	}
	
	function get_form() {
	$form_build = <<< HERE
<form action="" method="POST" id="payment-form">
<span class="payment-errors"></span>
<div class="form-row">
<label>
<span>Your Email</span>
<input type="text" size="20" name="email" data-stripe="email" />
</label>
</div>
<div class="form-row">
<label>
<span>Card Number</span>
<input type="text" size="20" data-stripe="number"/>
</label>
</div>
<div class="form-row">
<label>
<span>CVC</span>
<input type="text" size="4" data-stripe="cvc"/>
</label>
</div>
<div class="form-row">
<label>
<span>Expiration (MM/YYYY)</span>
<input type="text" size="2" data-stripe="exp-month"/ style='width:50px;'>
</label>
<span> / </span>
<input type="text" size="4" data-stripe="exp-year"/ style='width:90px;'>
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