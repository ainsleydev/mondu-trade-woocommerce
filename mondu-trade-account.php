<?php /** @noinspection ALL */

/**
 * Plugin Name:     Mondu Trade Account
 * Plugin URI:      https://ainsley.dev
 * Description:     WooCommerce Payment Gateway for integrating the Mondu Digital Trade Account.
 * Author:          ainsley.dev LTD
 * Author URI:      https://ainsley.dev
 * Text Domain:     mondu-trade
 * Version:         1.0.0
 *
 * Requires at least: 6.7
 * Requires PHP: 7.4
 * WC requires at least: 9.4
 *
 * Copyright 2024 ainsley.dev
 *
 * @package         MonduTradeAccount
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

// Mondu Trade Constants.
define( 'MONDU_TRADE_PLUGIN_VERSION', '1.0.0' );
define( 'MONDU_TRADE_PLUGIN_FILE', __FILE__ );
define( 'MONDU_TRADE_PLUGIN_PATH', __DIR__ );
define( 'MONDU_TRADE_PLUGIN_BASENAME', plugin_basename( MONDU_TRADE_PLUGIN_FILE ) );
define( 'MONDU_TRADE_PUBLIC_PATH', plugin_dir_url( MONDU_TRADE_PLUGIN_FILE ) );
define( 'MONDU_TRADE_ASSETS_PATH', plugins_url( 'assets', MONDU_TRADE_PLUGIN_FILE ) );
define( 'MONDU_TRADE_VIEW_PATH', MONDU_TRADE_PLUGIN_PATH . '/views' );

// Require Composer.
require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Plugin.
add_action( 'plugins_loaded', [ new \MonduTrade\Plugin(), 'init' ] );

// Declare HPOS compatibility.
add_action('before_woocommerce_init', function() {
	if (class_exists(FeaturesUtil::class)) {
		FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
		FeaturesUtil::declare_compatibility('remote_logging', __FILE__, true);
	}
});
