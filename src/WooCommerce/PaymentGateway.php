<?php

/**
 * WooCommerce - Payment Gateway
 *
 * @package     MonduTradeAccount
 * @category    WooCommerce
 * @author      ainsley.dev
 */

namespace MonduTrade\WooCommerce;

use WP_Error;
use WC_Order;
use WC_Data_Exception;
use MonduTrade\Plugin;
use WC_Payment_Gateway;
use Mondu\Mondu\MonduGateway;
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\Mondu\RequestWrapper;
use Mondu\Exceptions\ResponseException;

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
	 * Payment Gateway constructor.
	 */
	public function __construct() {
		$this->id                 = Plugin::PAYMENT_GATEWAY_NAME;
		$this->has_fields         = true;
		$this->method_title       = 'Mondu Trade Account';
		$this->method_description = 'Allows payments using Mondu Trade Account';
		$this->icon               = 'https://checkout.mondu.ai/logo.svg';

		$this->mondu_gateway         = new MonduGateway();
		$this->mondu_request_wrapper = new RequestWrapper();

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled = $this->get_option( 'enabled' ) === 'yes' ? 'yes' : 'no';
		$this->title   = $this->get_option( 'title' );

		add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'thankyou_page' ] );
		add_action( 'woocommerce_email_before_order_table', [ $this, 'email_instructions' ], 10, 3 );
		add_action( 'mondu_trade_account_checkout_class', [ $this, 'view_class_filter' ] );

		$this->register_scripts();

		$this->supports = [
			'products',
			'refunds',
		];
	}

	/**
	 * Adds Payment Fields to the Gateway
	 *
	 * @return void
	 */
	public function payment_fields() {
		wp_enqueue_script( 'a-dev-mondu-checkout-js', MONDU_TRADE_VIEW_PATH . '/checkout/checkout.js', [], null, true );
		parent::payment_fields();

		// We need the user ID in order to obtain the UUID that's
		// associated with the user, this indicates that they
		// haven't signed up yet.
		if ( ! is_user_logged_in() ) {
			include MONDU_TRADE_VIEW_PATH . '/checkout/not-logged-in.php';

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
				include MONDU_TRADE_VIEW_PATH . '/checkout/pending.php';
				break;
			case BuyerStatus::DECLINED:
				include MONDU_TRADE_VIEW_PATH . '/checkout/declined.php';
				break;
			case BuyerStatus::ACCEPTED:
				include MONDU_TRADE_VIEW_PATH . '/checkout/accepted.php';
				break;
			default:
				include MONDU_TRADE_VIEW_PATH . '/checkout/sign-up.php';
		endswitch;
	}

	/**
	 * Add method (called from constructor).
	 *
	 * @param array $methods
	 * @return array
	 */
	public static function add( array $methods ): array {
		array_unshift( $methods, static::class );

		return $methods;
	}

	/**
	 * Establishes the form fields in the backend of WooCommerce.
	 *
	 * @return void
	 */
	public function init_form_fields(): void {
		$this->form_fields = [
			'enabled' => [
				'title'       => 'Enable/Disable',
				'label'       => 'Enable Mondu Trade Account Payment',
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			],
			'title'   => [
				'title'       => 'Title',
				'type'        => 'text',
				'description' => 'This controls the title which the user sees during checkout.',
				'default'     => 'Mondu Trade Account',
				'desc_tip'    => true,
			],
		];
	}

	/**
	 * Process payment.
	 *
	 * @param $order_id
	 * @return array|void
	 * @throws ResponseException|WC_Data_Exception
	 */
	public function process_payment( $order_id ) {
		$order    = wc_get_order( $order_id );
		$customer = new Customer( $order->get_customer_id() );

		// Bail if the customer hasn't been accepted by Mondu, to avoid
		// people placing orders if they haven't been accepted.
		if ( $customer->get_mondu_trade_account_status() !== BuyerStatus::ACCEPTED ) {
			wc_add_notice( __( 'You are not currently permitted to place a Mondu Trade Payment, try another payment method.', Plugin::DOMAIN ), 'error' );

			return;
		}

		// Original code from Mondu, we just call the parent.
		$success_url = $this->get_return_url( $order );
		$mondu_order = $this->mondu_request_wrapper->create_order_with_account( $order, $success_url );

		if ( ! $mondu_order ) {
			wc_add_notice( __( 'Error placing an order. Please try again.', Plugin::DOMAIN ), 'error' );

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
		wp_register_script( $scriptID, MONDU_TRADE_ASSETS_PATH . '/js/checkout.js', [ 'jquery' ], null, true );
		wp_localize_script( $scriptID, 'aDevTradeAccountData', [
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => wp_create_nonce( 'ajax_nonce' ),
			'home_url'   => home_url(),
		] );
		wp_enqueue_script( 'a-dev-mondu-checkout-js' );
	}

	/**
	 * Allows the user to add a custom class to the checkout.
	 *
	 * Example usage:
	 *
	 * add_filter('mondu_trade_account_checkout_class', function ($class) {
	 *     return $class . ' my-class-name';
	 * });
	 *
	 * @param $class
	 * @return string
	 */
	public function view_class_filter( $class ): string {
		return 'mondu-trade mondu-trade-checkout' . $class;
	}
}
