<?php

/**
 * WCFM PG Mobile Pay plugin core
 *
 * Plugin intiate
 *
 * @author 		WC Lovers
 * @package 	wcfm-pg-razorpay
 * @version   1.0.0
 */
 
class WCFM_PG_Razor_Pay {
	
	public $plugin_base_name;
	public $plugin_url;
	public $plugin_path;
	public $version;
	public $token;
	public $text_domain;
	
	public function __construct($file) {

		$this->file = $file;
		$this->plugin_base_name = plugin_basename( $file );
		$this->plugin_url = trailingslashit(plugins_url('', $plugin = $file));
		$this->plugin_path = trailingslashit(dirname($file));
		$this->token = WCFMpgmp_TOKEN;
		$this->text_domain = WCFMpgmp_TEXT_DOMAIN;
		$this->version = WCFMpgmp_VERSION;
		
		add_action( 'wcfm_init', array( &$this, 'init' ), 10 );
	}
	
	function init() {
		global $WCFM, $WCFMre;
		
		// Init Text Domain
		$this->load_plugin_textdomain();
		
		add_filter( 'wcfm_marketplace_withdrwal_payment_methods', array( &$this, 'wcfmmp_custom_pg' ) );
		
		//add_filter( 'wcfm_marketplace_settings_fields_withdrawal_payment_keys', array( &$this, 'wcfmmp_custom_pg_api_keys' ), 50, 2 );
		
		//add_filter( 'wcfm_marketplace_settings_fields_withdrawal_payment_test_keys', array( &$this, 'wcfmmp_custom_pg_api_test_keys' ), 50, 2 );
		
		add_filter( 'wcfm_marketplace_settings_fields_withdrawal_charges', array( &$this, 'wcfmmp_custom_pg_withdrawal_charges' ), 50, 3 );
		
		add_filter( 'wcfm_marketplace_settings_fields_billing', array( &$this, 'wcfmmp_custom_pg_vendor_setting' ), 50, 2 );
		
		// Load Gateway Class
		require_once $this->plugin_path . 'gateway/class-wcfmmp-gateway-razor-pay.php';
		
	}
	
	function wcfmmp_custom_pg( $payment_methods ) {
		$payment_methods[WCFMpgmp_GATEWAY] = __( WCFMpgmp_GATEWAY_LABEL, 'wcfm-pg-razorpay' );
		return $payment_methods;
	}
	
	function wcfmmp_custom_pg_api_keys( $payment_keys, $wcfm_withdrawal_options ) {
		$gateway_slug  = WCFMpgmp_GATEWAY;
		$gateway_label = __( WCFMpgmp_GATEWAY_LABEL, 'wcfm-pg-razorpay' ) . ' ';
		
		$withdrawal_brain_tree_client_id = isset( $wcfm_withdrawal_options[$gateway_slug.'_client_id'] ) ? $wcfm_withdrawal_options[$gateway_slug.'_client_id'] : '';
		$withdrawal_brain_tree_secret_key = isset( $wcfm_withdrawal_options[$gateway_slug.'_secret_key'] ) ? $wcfm_withdrawal_options[$gateway_slug.'_secret_key'] : '';
		$payment_brain_tree_keys = array(
																		"withdrawal_".$gateway_slug."_client_id" => array('label' => $gateway_label . __('Client ID', 'wcfm-pg-razorpay'), 'name' => 'wcfm_withdrawal_options['.$gateway_slug.'_client_id]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_live withdrawal_mode_'.$gateway_slug, 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_live withdrawal_mode_'.$gateway_slug, 'value' => $withdrawal_brain_tree_client_id ),
																		"withdrawal_".$gateway_slug."_secret_key" => array('label' => $gateway_label . __('Secret Key', 'wcfm-pg-razorpay'), 'name' => 'wcfm_withdrawal_options['.$gateway_slug.'_secret_key]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_live withdrawal_mode_'.$gateway_slug, 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_live withdrawal_mode_'.$gateway_slug, 'value' => $withdrawal_brain_tree_secret_key )
																		);
		$payment_keys = array_merge( $payment_keys, $payment_brain_tree_keys );
		return $payment_keys;
	}
	
	function wcfmmp_custom_pg_api_test_keys( $payment_test_keys, $wcfm_withdrawal_options ) {
		$gateway_slug  = WCFMpgmp_GATEWAY;
		$gateway_label = __( WCFMpgmp_GATEWAY_LABEL, 'wcfm-pg-razorpay' ) . ' ';
		
		$withdrawal_brain_tree_test_client_id = isset( $wcfm_withdrawal_options[$gateway_slug.'_test_client_id'] ) ? $wcfm_withdrawal_options[$gateway_slug.'_test_client_id'] : '';
		$withdrawal_brain_tree_test_secret_key = isset( $wcfm_withdrawal_options[$gateway_slug.'_test_secret_key'] ) ? $wcfm_withdrawal_options[$gateway_slug.'_test_secret_key'] : '';
		$payment_brain_tree_test_keys = array(
																					"withdrawal_".$gateway_slug."_test_client_id" => array('label' => $gateway_label . __('Client ID', 'wcfm-pg-razorpay'), 'name' => 'wcfm_withdrawal_options['.$gateway_slug.'_test_client_id]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_test withdrawal_mode_'.$gateway_slug, 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_test withdrawal_mode_'.$gateway_slug, 'value' => $withdrawal_brain_tree_test_client_id ),
																					"withdrawal_".$gateway_slug."_test_secret_key" => array('label' => $gateway_label . __('Secret Key', 'wcfm-pg-razorpay'), 'name' => 'wcfm_withdrawal_options['.$gateway_slug.'_test_secret_key]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele withdrawal_mode withdrawal_mode_test withdrawal_mode_'.$gateway_slug, 'label_class' => 'wcfm_title withdrawal_mode withdrawal_mode_test withdrawal_mode_'.$gateway_slug, 'value' => $withdrawal_brain_tree_test_secret_key )
																					);
		$payment_test_keys = array_merge( $payment_test_keys, $payment_brain_tree_test_keys );
		return $payment_test_keys;
	}
	
	function wcfmmp_custom_pg_withdrawal_charges( $withdrawal_charges, $wcfm_withdrawal_options, $withdrawal_charge ) {
		$gateway_slug  = WCFMpgmp_GATEWAY;
		$gateway_label = __( WCFMpgmp_GATEWAY_LABEL, 'wcfm-pg-razorpay' ) . ' ';
		
		$withdrawal_charge_brain_tree = isset( $withdrawal_charge[$gateway_slug] ) ? $withdrawal_charge[$gateway_slug] : array();
		$payment_withdrawal_charges = array(  "withdrawal_charge_".$gateway_slug => array( 'label' => $gateway_label . __('Charge', 'wcfm-pg-razorpay'), 'type' => 'multiinput', 'name' => 'wcfm_withdrawal_options[withdrawal_charge]['.$gateway_slug.']', 'class' => 'withdraw_charge_block withdraw_charge_'.$gateway_slug, 'label_class' => 'wcfm_title wcfm_ele wcfm_fill_ele withdraw_charge_block withdraw_charge_'.$gateway_slug, 'value' => $withdrawal_charge_brain_tree, 'custom_attributes' => array( 'limit' => 1 ), 'options' => array(
																					"percent" => array('label' => __('Percent Charge(%)', 'wcfm-pg-razorpay'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_percent withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																					"fixed" => array('label' => __('Fixed Charge', 'wcfm-pg-razorpay'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'label_class' => 'wcfm_title wcfm_ele withdraw_charge_field withdraw_charge_fixed withdraw_charge_percent_fixed', 'attributes' => array( 'min' => '0.1', 'step' => '0.1') ),
																					"tax" => array('label' => __('Charge Tax', 'wcfm-pg-razorpay'), 'type' => 'number', 'class' => 'wcfm-text wcfm_ele', 'label_class' => 'wcfm_title wcfm_ele', 'attributes' => array( 'min' => '0.1', 'step' => '0.1'), 'hints' => __( 'Tax for withdrawal charge, calculate in percent.', 'wcfm-pg-razorpay' ) ),
																					) ) );
		$withdrawal_charges = array_merge( $withdrawal_charges, $payment_withdrawal_charges );
		return $withdrawal_charges;
	}
	
	function wcfmmp_custom_pg_vendor_setting( $vendor_billing_fileds, $vendor_id ) {
		$gateway_slug  = WCFMpgmp_GATEWAY;
		$gateway_label = __( WCFMpgmp_GATEWAY_LABEL, 'wcfm-pg-razorpay' ) . ' ';
		
		$vendor_data = get_user_meta( $vendor_id, 'wcfmmp_profile_settings', true );
		if( !$vendor_data ) $vendor_data = array();
		$brain_tree = isset( $vendor_data['payment'][$gateway_slug]['email'] ) ? esc_attr( $vendor_data['payment'][$gateway_slug]['email'] ) : '' ;
		$vendor_brain_tree_billing_fileds = array(
		$gateway_slug => array('label' => $gateway_label . __('Account', 'wcfm-pg-razorpay'), 'name' => 'payment['.$gateway_slug.'][email]', 'type' => 'text', 'class' => 'wcfm-text wcfm_ele paymode_field paymode_'.$gateway_slug, 'label_class' => 'wcfm_title wcfm_ele paymode_field paymode_'.$gateway_slug, 'value' => $brain_tree ),
		);
		$vendor_billing_fileds = array_merge( $vendor_billing_fileds, $vendor_brain_tree_billing_fileds );
		return $vendor_billing_fileds;
	}

	
	/**
	 * Load Localisation files.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present
	 *
	 * @access public
	 * @return void
	 */
	public function load_plugin_textdomain() {
		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'wcfm-pg-razorpay' );
		
		//load_plugin_textdomain( 'wcfm-tuneer-orders' );
		//load_textdomain( 'wcfm-pg-razorpay', WP_LANG_DIR . "/wcfm-pg-razorpay/wcfm-pg-razorpay-$locale.mo");
		load_textdomain( 'wcfm-pg-razorpay', $this->plugin_path . "lang/wcfm-pg-razorpay-$locale.mo");
		load_textdomain( 'wcfm-pg-razorpay', ABSPATH . "wp-content/languages/plugins/wcfm-pg-razorpay-$locale.mo");
	}
	
	public function load_class($class_name = '') {
		if ('' != $class_name && '' != $this->token) {
			require_once ('class-' . esc_attr($this->token) . '-' . esc_attr($class_name) . '.php');
		} // End If Statement
	}
}