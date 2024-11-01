<?php
	function get_form() {
		$options = get_option( 'wpnyd-group' );
	
		$form_build =  '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="wp_accept_pp_button_form_classic">';
		$form_build .= '<input type="hidden" name="cmd" value="_xclick" />';
		$form_build .= '<input type="hidden" name="business" value="' . $options['wpnyd_paypal-email'] . '" />';
		$form_build .= '<input type="hidden" name="item_name" value="Site Donation" />';
		$form_build .= '<input type="hidden" name="currency_code" value="USD" />';
		$form_build .= '$ <input type="text" id="amount" name="amount" class="">';
		$form_build .= ' <input type="hidden" name="no_shipping" value="0" />
        <input type="hidden" name="no_note" value="1" />
        <input type="hidden" name="bn" value="TipsandTricks_SP" />';
		$form_build .= '<input type="image" src="' . plugins_url() . '/wp-name-your-donation/res/img/Paypal-Donate-Button.png" name="submit" alt="Make payments with payPal - it\'s fast, free and secure!" />';
		$form_build .= '</form>';
		return $form_build;
	}