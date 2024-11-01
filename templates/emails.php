<?php
	/*
	
	* Email Templates *
	
	*/
	
	function send_email( $to, $subject, $message, $success_msg ) {
	
		$options = get_option( 'wpnyd-group' );
		$email_method = 'html';
		if( $email_method == 'html' ) {
			$headers = array('Content-Type: text/html; charset=UTF-8');
		}
		wp_mail( $to, $subject, $message, $headers );
		echo $success_msg;
		
	}
	
	function thank_you( $amount ) {
		

		$options = get_option( 'wpnyd-group' );
		$email_method = 'html';
		$email_body = "";
		if( $email_method == 'html' ) {
			$email_body = '<h2>Thank You For Your Donation To ' . get_bloginfo() . '</h2>';
			$emailbody .= '<table border="1" cellpadding="10">';
			$emailbody .= '<tr><td><strong>Donation Received:</strong></td><td>' . date("Y/m/d h:i:sa") . '</td></tr>';
			$emailbody .= '<tr><td><strong>Donation Amount:</strong></td><td>' . $amount . '</td></tr>';
			$emailbody .= '</table>';
			$emailbody .= '<p><a href="' . site_url() . '">' . site_url() . '</a></p>';
		} else {
			$emailbody .= "Thank You For Your Donation To " . get_bloginfo() . "\n";
			$emailbody .= "Donation Received: " . date("Y/m/d h:i:sa") . "\n";
			$emailbody .= "Donation Amount: $" . $amount . "\n";
			$emailbody .= site_url();
		}
		
		return array( "Thank You For Your Donation", $emailbody, '<p>An email has been sent to you for your records.</p>' );
		
	}
	
	function new_donation( $amount) {
		
		$options = get_option( 'wpnyd-group' );
		$email_method = 'html';
		$email_body = "";
		if( $email_method == 'html' ) {
			$email_body = '<h2>New Donation At ' . get_bloginfo() . '</h2>';
			$emailbody .= '<table border="1" cellpadding="10">';
			$emailbody .= '<tr><td><strong>Donation Received:</strong></td><td>' . date("Y/m/d h:i:sa") . '</td></tr>';
			$emailbody .= '<tr><td><strong>Donation Amount:</strong></td><td>' . $amount . '</td></tr>';
			$emailbody .= '</table>';
			$emailbody .= '<p><a href="' . site_url() . '">' . site_url() . '</a></p>';
		} else {
			$emailbody .= "New Donation At " . get_bloginfo() . "\n";
			$emailbody .= "Donation Received: " . date("Y/m/d h:i:sa") . "\n";
			$emailbody .= "Donation Amount: $" . $amount . "\n";
			$emailbody .= site_url();
		}
		
		return array( 'You Have Received A New Donation', $emailbody, '' );
		
	}
?>