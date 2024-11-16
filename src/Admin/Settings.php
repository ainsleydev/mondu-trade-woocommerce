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
use MonduTrade\Util\Logger;
use WP_Filesystem_Base;
use MonduTrade\Mondu\RequestWrapper;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Settings defines the trade_account settings for
 * the Mondu Trade Account.
 */
class Settings {
	/**
	 * Mondu Request Wrapper.
	 *
	 * @var RequestWrapper
	 */
	private RequestWrapper $mondu_request_wrapper;


	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->mondu_request_wrapper = new RequestWrapper();

		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'add_settings_section' ], 99 );
		add_action( 'admin_post_mondu_trade_register_webhooks', [ $this, 'register_webhooks' ] );
		add_action( 'admin_post_mondu_trade_download_logs', [ $this, 'download_logs' ] );
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
			__( 'Mondu Trade Account', 'ainsley-dev' ),
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
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}
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
		echo '<p>' . esc_html__( 'Configure the trade account settings for Mondu here.', 'ainsley-dev' ) . '</p>';
	}

	/**
	 * @return void
	 */
	public function register_webhooks() {
		$this->validate_nonce_or_error('mondu-trade-register-webhooks');

		try {
			$this->mondu_request_wrapper->register_buyer_webhooks();
			Logger::debug('Successfully registered buyer webhooks');
		} catch ( \Exception $exception ) {
			Logger::error('Registering buyer webhooks', [
				'error' => $exception->getMessage(),
			]);
		}
	}

	/**
	 * Download Mondu logs.
	 *
	 * @return void
	 * @noinspection DuplicatedCode
	 */
	public function download_logs() {
		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;
		WP_Filesystem();

		$this->validate_nonce_or_error('mondu-trade-download-logs');

		$date = isset( $_POST['date'] ) ? sanitize_text_field( $_POST['date'] ) : null;

		if ( null === $date ) {
			status_header( 400 );
			exit( esc_html__( 'Date is required.' ) );
		}

		$file = $this->get_file( $date );

		if ( null === $file ) {
			status_header( 404 );
			exit(esc_html__( 'Log not found.' ) );
		}

		$filename = 'mondu-trade-' . $date . '.log';

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		echo wp_kses_post(str_replace('>', '', $wp_filesystem->get_contents($file)));
		die;
	}

	/**
	 * Get file.
	 *
	 * @param $date
	 * @return string
	 */
	private function get_file( $date ) {
		$base_dir = WP_CONTENT_DIR . '/uploads/wc-logs/';
		$dir      = opendir( $base_dir );
		if ( $dir ) {
			while ( $file = readdir( $dir ) ) { //phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				if ( str_starts_with( $file, 'mondu-trade-' . $date ) && str_ends_with( $file, '.log' ) ) {
					return $base_dir . $file;
				}
			}
		}
	}

	/**
	 * Validate a nonce for a given action and handle errors.
	 *
	 * @param string $action
	 * @param string $query_arg
	 */
	public function validate_nonce_or_error( string $action, string $query_arg = 'security' ) {
		$is_nonce_valid = check_admin_referer( $action, $query_arg );

		if ( ! $is_nonce_valid ) {
			wp_die(
				esc_html__( 'Invalid security token. Please try again.', 'plugin-domain' ),
				esc_html__( 'Bad Request', 'plugin-domain' ),
				[ 'response' => 400, 'back_link' => true ]
			);
		}
	}
}
