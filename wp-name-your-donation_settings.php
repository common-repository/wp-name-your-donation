<?php
	/*
	
	* WP Name Your Donation Settings *
	
	Admin can set api keys and sandbox modes for different payment gateways
	
	*/
?>
<div class="wrap">
	<h2>WP Name Your Donation</h2>
    <?php settings_errors(); ?>
	<form action='options.php' method='POST'>
	<?php
		settings_fields( 'wpnyd-group' );
		do_settings_sections( 'wp-name-your-donation-options' );
		submit_button(); ?>
	</table>
	</form>
</div>