<?php
/**
 * Plugin Name: WP Name Your Donation
 * Plugin URI: http://whoischris.com
 * Description: Allow users to set a donation price and donate using multiple gateways include stripe, authorize.net and USAePay
 * Version: 1.1.0
 * Author: Chris Flannagan
 * Author URI: http://whoischris.com
 * License: GPL2
 */
 
if( ! class_exists( 'WP_Name_Your_Donation' ) ) {

    class WP_Name_Your_Donation {
	
		const PLUGIN_SLUG = 'wp-name-your-donation';
		const PLUGIN_ABBR = 'wpnyd';
		
		//General settings
		public $global_field_options = array(
			'global-email-text' => array( 'label' => 'Send Text Emails Instead of HTML', 'type' => 'checkbox', 'size' => '', 'admin' => 'checkbox', 'extra' => '' ),
		);
		
		//Fields used to set our API keys for different gateways
		public $stripe_field_options = array(
			'stripe-label' => array( 'label' => 'Stripe Shortcode', 'type' => 'admin', 'size' => '', 'admin' => 'row', 'extra' => '[name-your-donation gateway="stripe"]' ),
			'stripe-mode' => array( 'label' => 'Stripe Test Mode', 'type' => 'checkbox', 'size' => '', 'admin' => 'checkbox', 'extra' => '' ),
			'stripe-tsk' => array( 'label' => 'Stripe Test Secret Key', 'type' => 'text', 'size' => '30', 'admin' => 'text', 'extra' => '' ),
			'stripe-tpk' => array( 'label' => 'Stripe Test Public Key', 'type' => 'text', 'size' => '30', 'admin' => 'text', 'extra' => '' ),
			'stripe-lsk' => array( 'label' => 'Stripe Live Secret Key', 'type' => 'text', 'size' => '30', 'admin' => 'text', 'extra' => '' ),
			'stripe-lpk' => array( 'label' => 'Stripe Live Public Key', 'type' => 'text', 'size' => '30', 'admin' => 'text', 'extra' => '' ),
		);
		
		public $authnet_field_options = array(
			'authnet-label' => array( 'label' => 'Authorize.net Shortcode', 'type' => 'admin', 'size' => '', 'admin' => 'row', 'extra' => '[name-your-donation gateway="authnet"]' ),
			'authnet-mode' => array( 'label' => 'Authorize.net Test Mode', 'type' => 'checkbox', 'size' => '', 'admin' => 'checkbox', 'extra' => '' ),
			'authnet-login' => array( 'label' => 'Authorize.net Login ID', 'type' => 'text', 'size' => '30', 'admin' => 'text', 'extra' => '' ),
			'authnet-trans' => array( 'label' => 'Authorize.net Trans Key', 'type' => 'text', 'size' => '30', 'admin' => 'text', 'extra' => '' ),
		);
		
		public $paypal_field_options = array(
			'paypal-label' => array( 'label' => 'PayPal Shortcode', 'type' => 'admin', 'size' => '', 'admin' => 'row', 'extra' => '[name-your-donation gateway="paypal"]' ),
			'paypal-email' => array( 'label' => 'PayPal Account Email', 'type' => 'hidden', 'size' => '', 'admin' => 'text', 'extra' => '' ),
		);
    
        /**
         * Construct the plugin object
         */
        public function __construct() {
		
            // register actions
			add_action( 'admin_init', array( &$this, 'admin_init' ) );
			add_action( 'admin_menu', array( &$this, 'add_settings' ) );
			
			// register shortcode
			add_shortcode( 'name-your-donation', array( $this, 'donation_sc_func' ) );
			
        } // END public function __construct
    
        /**
         * Activate the plugin
         */
        public static function activate()
        {
		
            // Do nothing
			
        } // END public static function activate
    
        /**
         * Deactivate the plugin
         */     
        public static function deactivate()
        {
		
            // Do nothing
			
        } // END public static function deactivate
		
		public function add_settings() {
		
			//Place a link to our settings page under the Wordpress "Settings" menu
			add_options_page( 'WP Name Your Donations', 'Name Your Donations', 'manage_options', self::PLUGIN_SLUG . '-options', array( $this, 'settings_page' ) );
			
		}
		
		public function settings_page() {
		
		//Include our settings page template
			include(sprintf("%s/%s_settings.php", dirname(__FILE__), self::PLUGIN_SLUG));  
		
		}
		
		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init() {
		
			// Here we create our settings fields to be contained in the setting group 'wpfh-group'
			register_setting( self::PLUGIN_ABBR . '-group', self::PLUGIN_ABBR . '-group' );
			add_settings_section ( self::PLUGIN_SLUG . '_main_section' , 'Primary Settings', array( $this, 'settings_callback'), self::PLUGIN_SLUG . '-options' );
			
			//loop through our defined fields and add as a setting field option
			foreach ( $this->global_field_options as $field => $args ) {
				add_settings_field( self::PLUGIN_ABBR . '_' . $field, $args['label'], array( $this, $args['admin'] . '_callback' ), self::PLUGIN_SLUG . '-options', self::PLUGIN_SLUG . '_main_section', array( $field, $args['extra'] ) );
			}
			foreach ( $this->stripe_field_options as $field => $args ) {
				add_settings_field( self::PLUGIN_ABBR . '_' . $field, $args['label'], array( $this, $args['admin'] . '_callback' ), self::PLUGIN_SLUG . '-options', self::PLUGIN_SLUG . '_main_section', array( $field, $args['extra'] ) );
			}
			foreach ( $this->authnet_field_options as $field => $args ) {
				add_settings_field( self::PLUGIN_ABBR . '_' . $field, $args['label'], array( $this, $args['admin'] . '_callback' ), self::PLUGIN_SLUG . '-options', self::PLUGIN_SLUG . '_main_section', array( $field, $args['extra'] ) );
			}
			foreach ( $this->paypal_field_options as $field => $args ) {
				add_settings_field( self::PLUGIN_ABBR . '_' . $field, $args['label'], array( $this, $args['admin'] . '_callback' ), self::PLUGIN_SLUG . '-options', self::PLUGIN_SLUG . '_main_section', array( $field, $args['extra'] ) );
			}
		
		} // END public static function activate
		
		public function settings_callback() {
		
			echo 'Set your gateway API settings here.';
			if ( ! is_ssl() ) {
				echo '<p><strong>No SSL detected, please be sure the page you accept donations is secured via an SSL Certificate and https.</strong></p>';
			}
			
		}
		
		//display information
		public function row_callback( $args ) {
		
			echo $args[1];
			
		}
		
		//create text field options
		public function text_callback( $args ) {
		
			$options = get_option( self::PLUGIN_ABBR . '-group' );
			$value = '';
			
			//define value of option if has been set previously
			if ( isset( $options[ self::PLUGIN_ABBR . '_' . $args[0] ] ) ) {
				$value = $options[ self::PLUGIN_ABBR . '_' . $args[0] ];
			}
			echo '<input type="text" name="' . self::PLUGIN_ABBR . '-group[' . self::PLUGIN_ABBR . '_' . $args[0] . ']" value="' . esc_attr( $value ) . '" />';
			
		}
		
		//create checkbox field options
		public function checkbox_callback( $args ) {
		
			$options = get_option( self::PLUGIN_ABBR . '-group' );
			$checked = '';
			
			//select the option if has been saved previously
			if ( isset( $options[ self::PLUGIN_ABBR . '_' . $args[0] ] ) ) {
				$checked = ' checked="checked"';
			}
			echo '<input type="checkbox"' . $checked . ' name="' . self::PLUGIN_ABBR . '-group[' . self::PLUGIN_ABBR . '_' . $args[0] . ']" value="1" />';
			
		}

		//[name-your-donation gateway='*****'] shortcode function, default to stripe
		public function donation_sc_func( $atts ) {

			$a = shortcode_atts( array(
				'gateway' => 'stripe',
			), $atts );
			
			if( isset( $options[ self::PLUGIN_ABBR . '_email-text' ] ) ) {
				$email_method = 'text';
			}
			
			require_once( 'gateways/' . $a['gateway'] . '/' . $a['gateway'] . '.php' );
			
			//wp_enqueue_style( 'formcss', plugin_dir_url( __FILE__ ) . 'gateways/stripe/form.css' );
			
			$returnform = get_form();
			return $returnform;
			
		}
		
	}
	
} // END if(!class_exists('WP_Name_Your_Donation'));

// Add a link to the settings page onto the plugin page
if(class_exists('WP_Name_Your_Donation')) {			

	// Installation and uninstallation hooks
	register_activation_hook(__FILE__, array('WP_Name_Your_Donation', 'activate'));
	register_deactivation_hook(__FILE__, array('WP_Name_Your_Donation', 'deactivate'));

	// instantiate the plugin class
	$WP_Name_Your_Donation = new WP_Name_Your_Donation();
	
}