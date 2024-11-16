<?php

/**
 * Admin Settings
 *
 * @package MonduTrade
 * @author ainsley.dev
 */

namespace MonduTrade\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Settings defines the trade_account settings for
 * the Mondu Trade Account.
 */
class Settings {
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'add_settings_section' ], 99 );
		add_action( 'admin_notices', [ $this, 'display_missing_page_notice' ] );
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

		// Add three fields for Account Status Pages
		$this->add_page_select_field( 'page_status_accepted', __( 'Accepted Page', 'ainsley-dev' ) );
		$this->add_page_select_field( 'page_status_pending', __( 'Pending Page', 'ainsley-dev' ) );
		$this->add_page_select_field( 'page_status_declined', __( 'Declined Page', 'ainsley-dev' ) );
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
	 * Helper function to add a page selection field.
	 *
	 * @param string $field_id
	 * @param string $label
	 */
	private function add_page_select_field( string $field_id, string $label ) {
		add_settings_field(
			$field_id,
			$label,
			function () use ( $field_id ) {
				$options  = get_option( 'mondu_trade_account_options' );
				$selected = isset( $options[ $field_id ] ) ? (int) $options[ $field_id ] : 0;
				wp_dropdown_pages( [
					'name'             => "mondu_trade_account_options[$field_id]",
					'selected'         => $selected,
					'show_option_none' => __( 'Select a page', 'ainsley-dev' ),
				] );
			},
			'mondu-trade-account',
			'mondu_trade_account_section'
		);
	}

	/**
	 * Sanitize settings callback.
	 *
	 * @param array $input The input values to be sanitized.
	 *
	 * @return array The sanitized values.
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
	 * Display admin notice if any required page is not set.
	 */
	public function display_missing_page_notice() {
		$options = get_option( 'mondu_trade_account_options' );

		$missing_pages = [];
		if ( empty( $options['page_status_accepted'] ) ) {
			$missing_pages[] = __( 'Accepted Page', 'ainsley-dev' );
		}
		if ( empty( $options['page_status_pending'] ) ) {
			$missing_pages[] = __( 'Pending Page', 'ainsley-dev' );
		}
		if ( empty( $options['page_status_declined'] ) ) {
			$missing_pages[] = __( 'Declined Page', 'ainsley-dev' );
		}

		if ( ! empty( $missing_pages ) ) {
			$message = sprintf(
				__( 'The following Mondu Trade Account pages are not set: %s. Please configure them in the Trade Account settings for the plugin to work.', 'ainsley-dev' ),
				implode( ', ', $missing_pages )
			);
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $message ) . '</p></div>';
		}
	}
}
