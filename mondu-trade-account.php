<?php

/**
 * Plugin Name:     Mondu Trade Account
 * Plugin URI:      https://ainsley.dev
 * Description:     Wordpress Plugin for integrating trade accounts into the Resinbound.online Website via the Mondu API.
 * Author:          ainsley.dev LTD
 * Author URI:      https://ainsley.dev
 * Text Domain:     mondu-trade-account
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Mondu_Trade_Account
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

require_once __DIR__ . '/vendor/autoload.php';

define( 'MONDU_TRADE_ACCOUNT_PLUGIN_VERSION', '1.0.0' );
define( 'MONDU_TRADE_ACCOUNT_PLUGIN_FILE', __FILE__ );
define( 'MONDU_TRADE_ACCOUNT_PLUGIN_PATH', __DIR__ );
define( 'MONDU_TRADE_ACCOUNT_PLUGIN_BASENAME', plugin_basename( MONDU_TRADE_ACCOUNT_PLUGIN_FILE ) );
define( 'MONDU_TRADE_ACCOUNT_PUBLIC_PATH', plugin_dir_url( MONDU_TRADE_ACCOUNT_PLUGIN_FILE ) );
define( 'MONDU_TRADE_ACCOUNT_ASSETS_PATH', plugins_url( 'assets', MONDU_TRADE_ACCOUNT_PLUGIN_FILE ) );
define( 'MONDU_TRADE_ACCOUNT_VIEW_PATH', MONDU_TRADE_ACCOUNT_PLUGIN_PATH . '/views' );

new Plugin();
