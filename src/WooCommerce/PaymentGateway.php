<?php

/**
 * WooCommerce - Payment Gateway
 *
 * @package     MonduTradeAccount
 * @category    WooCommerce
 * @author      ainsley.dev
 */

namespace MonduTrade\WooCommerce;

use Mondu\Exceptions\ResponseException;
use Mondu\Mondu\Api;
use Mondu\Mondu\MonduGateway;
use MonduTrade\Admin\Options;
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\Mondu\RequestWrapper;
use WC_Order;
use WC_Payment_Gateway;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Payment Gateway - TODO
 */
class PaymentGateway extends WC_Payment_Gateway {

	/**
	 * Mondu API
	 *
	 * @var Api
	 */
	private Api $api;

	/**
	 * Base Mondu Gateway found.
	 *
	 * @var MonduGateway
	 */
	private MonduGateway $mondu_gateway;

	/**
	 * Mondu Request Wrapper.
	 *
	 * @var RequestWrapper
	 */
	private RequestWrapper $mondu_request_wrapper;

	/**
	 * Admin user defined options.
	 *
	 * @var Options
	 */
	private Options $admin_options;

	public function __construct() {
		$this->id                 = 'mondu-ainsley-dev-trade-account';
		$this->has_fields         = true;
		$this->method_title       = 'Mondu Trade Account';
		$this->method_description = 'Allows payments using Mondu Trade Account';
		$this->icon               = 'https://checkout.mondu.ai/logo.svg';

		$this->api                   = new Api();
		$this->mondu_gateway         = new MonduGateway();
		$this->mondu_request_wrapper = new RequestWrapper();
		$this->admin_options         = new Options();

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled     = $this->is_enabled() ? 'yes' : 'no';
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );

		add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'thankyou_page' ] );
		add_action( 'woocommerce_email_before_order_table', [ $this, 'email_instructions' ], 10, 3 );

		$this->register_scripts();

		$this->supports = [
			'products',
			'refunds',
		];
	}

	/**
	 * Check if the payment gateway should be enabled.
	 *
	 * @return bool True if the gateway is enabled and all redirect pages are set, false otherwise.
	 */
	public function is_enabled(): bool {
		return 'yes' === $this->get_option( 'enabled' ) && $this->admin_options->has_redirect_pages();
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
	public static function add( array $methods ): array {
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

	/**
	 * Process payment
	 *
	 * @param $order_id
	 *
	 * @return array|void
	 * @throws ResponseException
	 * @throws \WC_Data_Exception
	 */
	public function process_payment( $order_id ) {
		$order       = wc_get_order( $order_id );
		$success_url = $this->get_return_url( $order );
		$mondu_order = $this->mondu_request_wrapper->create_order_with_account( $order, $success_url );

		if ( ! $mondu_order ) {
			wc_add_notice( __( 'Error placing an order. Please try again.', 'mondu' ), 'error' );

			return;
		}

		return [
			'result'   => 'success',
			'redirect' => $mondu_order['hosted_checkout_url'],
		];
	}

	/**
	 * Output for the order received page.
	 */
	public function thankyou_page() {
		$this->mondu_gateway->thankyou_page();
	}

	/**
	 * Add content to the WC emails.
	 *
	 * @param WC_Order $order
	 */
	public function email_instructions( WC_Order $order ) {
		$this->mondu_gateway->email_instructions( $order );
	}

	/**
	 * Checks if the order can be refunded.
	 *
	 * @param WC_Order $order
	 *
	 * @return bool
	 */
	public function can_refund_order( $order ): bool {
		return $this->mondu_gateway->can_refund_order( $order );
	}

	/**
	 * Processes the mondu refund and sends a credit
	 * note to Mondu.
	 *
	 * @param $order_id
	 * @param $amount
	 * @param string $reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return $this->mondu_gateway->process_refund( $order_id, $amount, $reason );
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
	 * Registers Javascript checkout script.
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
		wp_register_script( $scriptID, MONDU_TRADE_ACCOUNT_ASSETS_PATH . '/js/checkout.js', [ 'jquery' ], null, true );
		wp_localize_script( $scriptID, 'aDevTradeAccountData', [
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'ajax_nonce' ),
			'home_url'   => home_url(),
		] );
		wp_enqueue_script( 'a-dev-mondu-checkout-js' );
	}
}
