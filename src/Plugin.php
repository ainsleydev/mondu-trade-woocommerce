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
use MonduTrade\Admin\User;
use MonduTrade\Admin\Settings;
use MonduTrade\Forms\SubmitTradeAccount;
use MonduTrade\WooCommerce\Checkout;
use MonduTrade\WooCommerce\PaymentGateway;
use MonduTrade\Controllers\WebhooksController;
use MonduTrade\Controllers\TradeAccountController;

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
	 * Option name for webhooks registered timestamp.
	 *
	 * @var string
	 */
	const OPTION_WEBHOOKS_REGISTERED = '_mondu_trade_webhooks_registered';

	/**
	 * Options name for the secret used in the Signature Verifier.
	 *
	 * @var string
	 */
	const OPTION_WEBHOOKS_SECRET = '_mondu_trade_webhooks_secret';

	/**
	 * Initialises Mondu Trade.
	 *
	 * @return void
	 */
	public function init() {
		// If dependencies are not met, return early and don't initialize the plugin.
		if ( ! $this->check_dependencies() ) {
			return;
		}

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
		 * We should use the .env file in the plugin base dir
		 * if we're in dev. Safe Load doesn't throw an
		 * exception if it's not found.
		 */
		$dotenv = Dotenv::createImmutable( MONDU_TRADE_PLUGIN_PATH );
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

		/*
		 * Show action links on the plugin screen.
		 */
		add_filter( 'plugin_action_links_' . MONDU_TRADE_PLUGIN_BASENAME, [ $this, 'add_action_links' ] );

		/*
		 * Adds meta information about the Mondu Trade Plugin.
		 */
		add_filter( 'plugin_row_meta', [ $this, 'add_row_meta' ], 10, 2 );

		/**
		 * Adds checkout notices when the buyer has submitted a Trade application.
		 */
		add_filter( 'template_redirect', [ Checkout::class, 'notices' ] );

		/**
		 * Pre-selects the Trade Account if it's succeeded.
		 */
		add_filter( 'woocommerce_available_payment_gateways', [ Checkout::class, 'select_default_gateway' ] );
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

	/**
	 * Allows the user to go to the Settings page from the Plugins page.
	 *
	 * @param $links
	 * @return array|string[]
	 */
	public static function add_action_links( $links ): array {
		$action_links = [
			'settings' => '<a href="' . admin_url( 'admin.php?page=mondu-trade-account' ) . '" aria-label="' . esc_attr__( 'View Mondu settings', 'mondu-trade-account' ) . '">' . esc_html__( 'Settings', 'mondu-trade-account' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file Plugin Base file.
	 * @return array
	 * @noinspection DuplicatedCode
	 */
	public static function add_row_meta( $links, $file ): array {
		if ( MONDU_TRADE_PLUGIN_BASENAME !== $file ) {
			return $links;
		}

		$row_meta = [
			'github' => '<a target="_blank" href="' . esc_url( 'https://github.com/ainsleydev/mondu-trade-woocommerce' ) . '" aria-label="' . esc_attr__( 'Visit Github Repo', 'mondu-trade-account' ) . '">' . esc_html__( 'GitHub', 'mondu-trade-account' ) . '</a>',
			'faq'    => '<a target="_blank" href="' . esc_url( esc_attr__( 'https://mondu.ai/faq', 'mondu-trade-account' ) ) . '" aria-label="' . esc_attr__( 'View FAQ', 'mondu-trade-account' ) . '">' . esc_html__( 'FAQ', 'mondu-trade-account' ) . '</a>',
		];

		return array_merge( $links, $row_meta );
	}
}


