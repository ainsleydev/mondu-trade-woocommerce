<?php

/**
 * Plugin
 *
 * @package     MonduTradeAccount
 * @category    Plugin
 * @author      ainsley.dev
 * @copyright   2024 ainsley.dev
 * @link        https://github.com/ainsleydev/woocommerce-mondu-trade-gateway
 */

namespace MonduTrade;

use Dotenv\Dotenv;
use MonduTrade\Admin\Settings;
use MonduTrade\Actions\SubmitTradeAccount;
use MonduTrade\Admin\User;
use MonduTrade\Util\Environment;
use MonduTrade\WooCommerce\PaymentGateway;
use MonduTrade\Controllers\TradeAccountController;
use MonduTrade\Controllers\WebhooksController;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Plugin defines the main entry point of the Mondu -> WooCommerce
 * Payment Gateway.
 */
class Plugin {

	/**
	 * Text domain of the plugin.
	 *
	 * @var string
	 */
	const DOMAIN = 'mondu-trade-account';

	/**
	 * Name of the payment gateway for WooCommerce.
	 *
	 * @var string
	 */
	const PAYMENT_GATEWAY_NAME = 'mondu-trade-account';

	/**
	 * Log context  for the Woocommerce logger.
	 *
	 * @var string
	 */
	const LOG_CONTEXT = 'mondu-trade';

	/**
	 * Initialises the Mondu Trade Account Plugin
	 */
	public function __construct() {
		// If dependencies are not met, return early and don't initialize the plugin.
		if ( ! $this->check_dependencies() ) {
			return;
		}

		// Continue with initialisation.
		$this->init();
	}

	/**
	 *
	 *
	 * @return void
	 */
	private function init() {
		/**
		 * Bootstrap classes, filters & actions.
		 */
		add_action( 'init', function () {
			// Register forms (actions).
			new SubmitTradeAccount();

			// Add the admin options in the sidebar and display
			// user information in the /user-edit page.
			if ( is_admin() ) {
				new Settings();
				new User();
			}
		} );

		/**
		 * Require hooks & actions.
		 */
		require_once __DIR__ . '/Frontend/checkout.php';

		/**
		 * We should use the .env file in the plugin base dir
		 * if we're in dev. Safe Load doesn't throw an
		 * exception if it's not found.
		 */
		$dotenv = Dotenv::createImmutable( MONDU_TRADE_ACCOUNT_PLUGIN_PATH );
		$dotenv->safeLoad();

		/**
		 * Adds the REST controller routes.
		 */
		add_action( 'rest_api_init', function () {
			$trade_account = new TradeAccountController();
			$trade_account->register_routes();
			$webhooks = new WebhooksController();
			$webhooks->register_routes();
		} );

		/**
		 * Load the main Mondu Trade Gateway.
		 */
		add_filter( 'woocommerce_payment_gateways', [ PaymentGateway::class, 'add' ] );
	}

	/**
	 * Checks if the following plugins are installed:
	 *
	 * - WooCommerce
	 * - Mondu Buy Now pay Later
	 *
	 * @return bool
	 */
	private function check_dependencies(): bool {
		// Load the plugin helper functions if not already loaded.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Check the plugins are activated.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ||
		     ! is_plugin_active( 'mondu-buy-now-pay-later/mondu-buy-now-pay-later.php' ) ) {
			add_action( 'admin_notices', [ $this, 'dependency_error_notice' ] );

			return false;
		}

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


