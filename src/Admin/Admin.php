<?php

/**
 * Settings
 *
 * @package MonduTradeAccount
 */
if (!defined('ABSPATH')) {
	die('Direct access not allowed');
}

/**
 * Settings defines the extended settings for
 * the Mondu Trade Account.
 */
class Settings
{
	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct()
	{
		add_action('admin_init', [$this, 'register_settings']);
		add_action('admin_menu', [$this, 'add_settings_section'], 99);
	}

	/**
	 * Register the settings section and fields.
	 */
	public function register_settings()
	{
		// Register a new setting
		register_setting(
			'mondu_extended_settings',
			'mondu_extended_options',
			[
				'type' => 'array',
				'description' => 'Mondu Extended Settings',
				'sanitize_callback' => [$this, 'sanitize_settings'],
				'default' => []
			]
		);

		// Add a new section to the Mondu settings page
		add_settings_section(
			'mondu_extended_section',
			__('Extended Settings', 'ainsley-dev'),
			[$this, 'section_callback'],
			'mondu-trade-account'
		);
	}

	/**
	 * Add submenu to existing Mondu menu
	 */
	public function add_settings_section()
	{
		add_submenu_page(
			'mondu-settings-account',
			__('Trade Account', 'ainsley-dev'),
			__('Trade Account', 'ainsley-dev'),
			'manage_options',
			'mondu-trade-account',
			[$this, 'render'],
		);
	}

	/**
	 * Render the Trade Account page content.
	 */
	public function render()
	{
		echo '<div class="wrap">';
		echo '<h1>' . esc_html__('Mondo Trade Account', 'ainsley-dev') . '</h1>';
		echo '<form method="post" action="options.php">';
		settings_fields('mondu_extended_settings');
		do_settings_sections('mondu-trade-account');
		submit_button();
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Section callback
	 */
	public function section_callback()
	{
		echo '<p>' . esc_html__('Configure the extended settings for Mondu here.', 'ainsley-dev') . '</p>';
	}

	/**
	 * Initialize the class
	 */
	public static function init()
	{
		$instance = new self();
		return $instance;
	}
}
