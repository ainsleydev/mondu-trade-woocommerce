<?php

/**
 * WooCommerce - Payment Gateway
 *
 * @package     MonduTradeAccount
 * @category    WooCommerce
 * @author      ainsley.dev
 */

namespace MonduTrade\WooCommerce;

use Exception;
use Mondu\Exceptions\ResponseException;
use Mondu\Mondu\Api;
use Mondu\Mondu\MonduGateway;
use MonduTrade\Admin\Options;
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\Mondu\RequestWrapper;
use MonduTrade\Plugin;
use WC_Order;
use WC_Payment_Gateway;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Payment Gateway - TODO
 */
class PaymentGateway extends WC_Payment_Gateway {

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
		$this->id                 =  Plugin::PAYMENT_GATEWAY_NAME;
		$this->has_fields         = true;
		$this->method_title       = 'Mondu Trade Account';
		$this->method_description = 'Allows payments using Mondu Trade Account';
		$this->icon               = 'https://checkout.mondu.ai/logo.svg';

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

		$status      = BuyerStatus::UNDEFINED;
		$buyer_limit = false;

		if ( ! is_user_logged_in() ) {
			include MONDU_TRADE_ACCOUNT_VIEW_PATH . '/checkout/not-logged-in.php';

			return;
		}

		// If the user is logged in, we can obtain the status to
		// display different messages to the user.
		$user_id  = get_current_user_id();
		$customer = new Customer( $user_id );
		$status   = $customer->get_mondu_trade_account_status();

		// Get different views dependant on the buyer status.
		switch ( $status ):
			case BuyerStatus::PENDING:
				include MONDU_TRADE_ACCOUNT_VIEW_PATH . '/checkout/pending.php';
				break;
			case BuyerStatus::DECLINED:
				include MONDU_TRADE_ACCOUNT_VIEW_PATH . '/checkout/declined.php';
				break;
			case BuyerStatus::ACCEPTED:
				// If the buyer has an accepted status, we can assume that they
				// probably have a buying limit to display to the user.
				try {
					$buyer_limit = $this->mondu_request_wrapper->get_buyer_limit();

					if ( $buyer_limit && isset( $buyer_limit['purchasing_limit']['purchasing_limit_cents'] ) ) {
						$purchasing_limit = $buyer_limit['purchasing_limit']['purchasing_limit_cents'] / 100; // Convert cents to pounds.
						echo '<p>Purchasing Limit: ' . wc_price( $purchasing_limit ) . '</p>';
					}

				} catch ( Exception $e ) {
					// TODO: Probably need to display message to user.
					\MonduTrade\Util\Logger::error('Getting buyer limit', [
						'error' => $e->getMessage(),
					]);
				}
				break;
			default:
				include MONDU_TRADE_ACCOUNT_VIEW_PATH . '/checkout/sign-up.php';
		endswitch;
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
	 * @return bool| WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return $this->mondu_gateway->process_refund( $order_id, $amount, $reason );
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

		// TODO: Localise API Key.
		wp_register_script( $scriptID, MONDU_TRADE_ACCOUNT_ASSETS_PATH . '/js/checkout.js', [ 'jquery' ], null, true );
		wp_localize_script( $scriptID, 'aDevTradeAccountData', [
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'ajax_nonce' ),
			'home_url'   => home_url(),
		] );
		wp_enqueue_script( 'a-dev-mondu-checkout-js' );
	}
}
