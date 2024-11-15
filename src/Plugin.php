<?php

/**
 * Plugin
 *
 * @package MonduTradeAccount
 */

use MonduTrade\Actions\SubmitTradeAccount;
use MonduTrade\WooCommerce\PaymentGateway;

if (!defined('ABSPATH')) {
	die('Direct access not allowed');
}

class Plugin {

	/**
	 * Initialises the Mondu Trade Account Plugin
	 */
	public function __construct() {
		if ( ! $this->check_dependencies() ) {
			// If dependencies are not met, return early and don't initialize the plugin
			return;
		}

		// Continue with your plugin initialization code here
		$this->init();
	}

	private function init() {
		if (is_admin()) {
			new Settings();
		}
		add_filter( 'woocommerce_payment_gateways', [ PaymentGateway::class, 'add' ] );


		new SubmitTradeAccount();
//		new WooCommerce_Mondu_Payment_Gateway();
	}

	/**
	 * Checks if the following plugins are installed:
	 * - WooCommerce
	 * - Mondu Buy Now pay Later
	 *
	 * @return bool
	 */
	private function check_dependencies() {
		// Load the plugin helper functions if not already loaded.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check the plugins are activated.
//		if (!is_plugin_active('woocommerce/woocommerce.php') ||
//			!is_plugin_active('mondu-buy-now-pay-later/mondu-buy-now-pay-later.php')) {
//			add_action('admin_notices', [$this, 'dependency_error_notice']);
//			return false;
//		}

		return true;
	}

	/**
	 * Adds an error to WP if the dependencies aren't installed.
	 *
	 * @return void
	 */
	public function dependency_error_notice() {
		echo '<div class="notice notice-error">
				<p>Mondu Trade Account requires WooCommerce and Mondu Buy Now Pay Later plugins to be activated.</p>
			</div>';
	}

}
