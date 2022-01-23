<?php

if (!defined('ABSPATH')) {
    exit;
}

class WCFMmp_Gateway_Razor_Pay extends WCFMmp_Abstract_Gateway {

	public $id;
	public $message = array();
	public $gateway_title;
	public $payment_gateway;
	public $withdrawal_id;
	public $vendor_id;
	public $withdraw_amount = 0;
	public $currency;
	public $transaction_mode;
	private $reciver_email;
	public $test_mode = false;
	public $client_id;
	public $client_secret;
	
	public function __construct() {
		
		$this->id = WCFMpgmp_GATEWAY;
		$this->gateway_title = __( WCFMpgmp_GATEWAY_LABEL, 'wcfm-pg-razorpay' );
		$this->payment_gateway = $this->id;
	}
	
	public function gateway_logo() { global $WCFMmp; return $WCFMmp->plugin_url . 'assets/images/'.$this->id.'.png'; }
	
	public function process_payment( $withdrawal_id, $vendor_id, $withdraw_amount, $withdraw_charges, $transaction_mode = 'auto' ) {
	
		global $WCFM, $WCFMmp;
		
		$this->withdrawal_id    = $withdrawal_id;
		$this->vendor_id        = $vendor_id;
		$this->withdraw_amount  = $withdraw_amount;
		$this->currency         = get_woocommerce_currency();
		$this->transaction_mode = $transaction_mode;
		$this->reciver_email    = $WCFMmp->wcfmmp_vendor->get_vendor_payment_account( $this->vendor_id, $this->id );
		
		$withdrawal_test_mode   = isset( $WCFMmp->wcfmmp_withdrawal_options['test_mode'] ) ? 'yes' : 'no';
		
		//$this->client_id        = isset( $WCFMmp->wcfmmp_withdrawal_options[$this->id.'_client_id'] ) ? $WCFMmp->wcfmmp_withdrawal_options[$this->id.'_client_id'] : '';
		//$this->client_secret    = isset( $WCFMmp->wcfmmp_withdrawal_options[$this->id.'_secret_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options[$this->id.'_secret_key'] : '';
		
		//if ( $withdrawal_test_mode == 'yes') {
			$this->test_mode     = true;
			//$this->client_id     = isset( $WCFMmp->wcfmmp_withdrawal_options[$this->id.'_test_client_id'] ) ? $WCFMmp->wcfmmp_withdrawal_options[$this->id.'_test_client_id'] : '';
			//$this->client_secret = isset( $WCFMmp->wcfmmp_withdrawal_options[$this->id.'_test_secret_key'] ) ? $WCFMmp->wcfmmp_withdrawal_options[$this->id.'_test_secret_key'] : '';
		//}
		
		if ( $this->validate_request() ) {
			// Updating withdrawal meta
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'withdraw_amount', $this->withdraw_amount );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'currency', $this->currency );
			$WCFMmp->wcfmmp_withdraw->wcfmmp_update_withdrawal_meta( $this->withdrawal_id, 'reciver_email', $this->reciver_email );
			return array( 'status' => true, 'message' => __( 'New transaction has been initiated', 'wc-multivendor-marketplace' ) );
		} else {
			return $this->message;
		}
	}
	
	public function validate_request() {
		global $WCFMmp;
		return true;
	}
}