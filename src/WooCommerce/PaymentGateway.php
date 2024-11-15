<?php

/**
 * Mondu Trade Payment Gateway
 *
 * @package MonduTrade
 * @author ainsley.dev
 *
 */
namespace MonduTrade\WooCommerce;

use WC_Payment_Gateway;

if (!defined('ABSPATH')) {
	die('Direct access not allowed');
}

class PaymentGateway extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = 'mondu-ainsley-dev-trade-account';
		$this->has_fields         = true;
		$this->method_title       = 'Mondu Trade Account';
		$this->method_description = 'Allows payments using Mondu Trade Account';
		$this->icon               = 'https://checkout.mondu.ai/logo.svg';

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled     = $this->get_option( 'enabled' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		$this->register_scripts();

		$this->supports = [
			'products',
			'refunds',
		];
	}

	/**
	 * Adds Payment Fields to the Gateway
	 * TODO: If conditional if already registered.
	 *
	 * @return void
	 */
	public function payment_fields() {

		wp_enqueue_script( 'a-dev-mondu-checkout-js', MONDU_TRADE_ACCOUNT_VIEW_PATH . '/checkout/checkout.js', [], null, true );
		parent::payment_fields();
		include MONDU_TRADE_ACCOUNT_VIEW_PATH . '/checkout.php';
	}

	/**
	 * Add method
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public static function add( array $methods ) {
		array_unshift( $methods, static::class );
		return $methods;
	}

	public function init_form_fields() {
		$this->form_fields = [
			'enabled'     => [
				'title'       => 'Enable/Disable',
				'label'       => 'Enable Mondu Trade Account Payment',
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			],
			'title'       => [
				'title'       => 'Title',
				'type'        => 'text',
				'description' => 'This controls the title which the user sees during checkout.',
				'default'     => 'Mondu Trade Account',
				'desc_tip'    => true,
			],
			'description' => [
				'title'       => 'Description',
				'type'        => 'textarea',
				'description' => 'This controls the description which the user sees during checkout.',
				'default'     => 'Pay using Mondu Trade Account.',
				'desc_tip'    => true,
			],
		];
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$order->update_status( 'on-hold', 'Awaiting Mondu payment' );
		wc_reduce_stock_levels( $order_id );
		WC()->cart->empty_cart();

		return [
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		];
	}

	public function receipt_page( $order_id ) {
		echo '<p>Click the button below to proceed with Mondu payment:</p>';
		echo '<button onclick="alert(\'Proceeding with Mondu payment\')">Pay with Mondu</button>';
	}

	/**
	 * Stubs a function called submission_status
	 *
	 * @return string
	 */
	public function submission_status() {
		// Here you could implement real submission logic, but for now, we're stubbing it.
		// Possible return values: 'pending', 'success', 'declined'
		return 'pending'; // Replace this with appropriate logic later.
	}

	/**
	 * Registers
	 *
	 * @return void
	 */
	public function register_scripts() {

		// We only need JavaScript to process a token only on cart/checkout pages.
		if ( ! is_cart() && ! is_checkout() ) {
			return;
		}

		// If payment gateway is disabled, we do not have to enqueue JS too.
		if ( 'no' === $this->enabled ) {
			return;
		}

		$scriptID = 'a-dev-mondu-checkout-js';

		// TODO: Localise API Key
		wp_register_script( $scriptID, MONDU_TRADE_ACCOUNT_ASSETS_PATH . '/js/checkout.js', ['jquery'], null, true );
		wp_localize_script($scriptID, 'aDevTradeAccountData', [
			'ajax_url'           => admin_url('admin-ajax.php'),
			'ajax_nonce'         => wp_create_nonce('ajax_nonce'),
			'home_url'           => home_url(),
		]);
		wp_enqueue_script('a-dev-mondu-checkout-js');
	}
}
