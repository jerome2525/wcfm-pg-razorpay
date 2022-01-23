<?php
/**
 * Plugin Name: WCFM Marketplace Vendor Payment - Razor Pay
 * Plugin URI: https://wclovers.com/product/woocommerce-multivendor-membership
 * Description: WCFM Marketplace razor pay vendor payment gateway 
 * Author: WC Lovers
 * Version: 1.0.0
 * Author URI: https://wclovers.com
 *
 * Text Domain: wcfm-pg-razorpay
 * Domain Path: /lang/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.4.0
 *
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

if(!defined('WCFM_TOKEN')) return;
if(!defined('WCFM_TEXT_DOMAIN')) return;

if ( ! class_exists( 'WCFMpgmp_Dependencies' ) )
	require_once 'helpers/class-wcfm-pg-razorpay-dependencies.php';

if( !WCFMpgmp_Dependencies::woocommerce_plugin_active_check() )
	return;

if( !WCFMpgmp_Dependencies::wcfm_plugin_active_check() )
	return;

if( !WCFMpgmp_Dependencies::wcfmmp_plugin_active_check() )
	return;

require_once 'helpers/wcfm-pg-razorpay-core-functions.php';
require_once 'wcfm-pg-razorpay-config.php';

if(!class_exists('WCFM_PG_Razor_Pay')) {
	include_once( 'core/class-wcfm-pg-razorpay.php' );
	global $WCFM, $WCFMpgmp, $WCFM_Query;
	$WCFMpgmp = new WCFM_PG_Razor_Pay( __FILE__ );
	$GLOBALS['WCFMpgmp'] = $WCFMpgmp;
}