<?php

/**
 * Admin - Settings
 *
 * @package     MonduTradeAccount
 * @category    Admin
 * @author      ainsley.dev
 */

namespace MonduTrade\Admin;

use MonduTrade\Plugin;
use MonduTrade\Util\Environment;
use WP_Filesystem_Base;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Settings defines the trade_account settings for
 * the Mondu Trade Account.
 */
class Settings {
	/**
	 * Webhook Register.
	 *
	 * @var WebhookRegister
	 */
	private WebhookRegister $webhook_register;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->webhook_register = new WebhookRegister();
		new LogManager();

		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'add_settings_section' ], 99 );
	}

	/**
	 * Register the settings section and fields.
	 */
	public function register_settings() {
		// Register a new setting
		register_setting(
			'mondu_trade_account_settings',
			'mondu_trade_account_options',
			[
				'type'              => 'array',
				'description'       => 'Mondu Trade Account Settings',
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'default'           => []
			]
		);

		// Add a new section to the Mondu settings page
		add_settings_section(
			'mondu_trade_account_section',
			__( 'Settings', Plugin::DOMAIN ),
			[ $this, 'section_callback' ],
			'mondu-trade-account'
		);
	}

	/**
	 * Add submenu to existing Mondu menu
	 */
	public function add_settings_section() {
		add_submenu_page(
			'mondu-settings-account',
			__( 'Trade Account', 'ainsley-dev' ),
			__( 'Trade Account', 'ainsley-dev' ),
			'manage_options',
			'mondu-trade-account',
			[ $this, 'render' ],
		);
	}

	/**
	 * Render the Trade Account page content.
	 */
	public function render() {
		Util::validate_user_permissions();

		$webhooks_list_error = null;

		// Fetch the current webhooks to display to the
		// user (just for debugging).
		if (Environment::is_development()) {
			try {
				$webhooks = $this->webhook_register->get();
			} catch ( \Exception $e ) {
				$webhooks            = [];
				$webhooks_list_error = $e->getMessage();
			}
		}

		$message = $this->webhook_register->get_message();
		$webhooks_registered = get_option( '_mondu_trade_webhooks_registered' );

		include MONDU_TRADE_ACCOUNT_VIEW_PATH . '/admin.php';
	}

	/**
	 * Sanitize settings callback.
	 *
	 * @param array $input
	 * @return array
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = [];
		$fields    = [ 'page_status_accepted', 'page_status_pending', 'page_status_declined' ];
		foreach ( $fields as $field ) {
			$sanitized[ $field ] = isset( $input[ $field ] ) ? (int) $input[ $field ] : 0;
		}

		return $sanitized;
	}

	/**
	 * Section callback
	 */
	public function section_callback() {
		echo '<p>' . esc_html__( 'Configure the trade account settings for Mondu here.', Plugin::DOMAIN ) . '</p>';
	}
}
