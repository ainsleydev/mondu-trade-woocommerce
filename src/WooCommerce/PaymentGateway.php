<?php

/**
 * WooCommerce - Payment Gateway
 *
 * @package     MonduTradeAccount
 * @category    WooCommerce
 * @author      ainsley.dev
 */

namespace MonduTrade\WooCommerce;

use MonduTrade\Controllers\TradeAccountController;
use WP_Error;
use WC_Order;
use Exception;
use WC_Data_Exception;
use MonduTrade\Plugin;
use WC_Payment_Gateway;
use MonduTrade\Util\Logger;
use Mondu\Mondu\MonduGateway;
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\Mondu\RequestWrapper;
use MonduTrade\Exceptions\MonduTradeResponseException;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Payment Gateway is the main WooCommerce payment gateway
 * for Mondu Digital Trade Accounts.
 *
 * It deals with the functionality of when a user hit's pay
 * on the checkout.
 *
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
		$this->icon               = MONDU_TRADE_ASSETS_PATH . '/images/logo.svg';

		$this->mondu_gateway         = new MonduGateway();
		$this->mondu_request_wrapper = new RequestWrapper();

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled = $this->get_option( 'enabled' ) === 'yes' ? 'yes' : 'no';
		$this->title   = $this->get_option( 'title' );

		/**
		 * Add default hooks.
		 */
		add_action( 'woocommerce_thankyou_' . $this->id, [ $this, 'thankyou_page' ] );
		add_action( 'woocommerce_email_before_order_table', [ $this, 'email_instructions' ], 10, 3 );
		add_action( 'mondu_trade_account_checkout_class', [ $this, 'view_class_filter' ] );

		/**
		 * Pre-selects the Trade Account if it's succeeded.
		 */
		add_filter( 'woocommerce_available_payment_gateways', [ $this, 'select_default_gateway' ] );

		$this->supports = [
			'products',
			'refunds',
		];
	}

	/**
	 * Adds Payment Fields to the Gateway.
	 * This is what the user sees under 'Mondu Trade Account'
	 *
	 * @return void
	 */
	public function payment_fields(): void {
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
			case BuyerStatus::CANCELLED:
				include MONDU_TRADE_VIEW_PATH . '/checkout/sign-up.php';
				break;
			case BuyerStatus::APPLIED:
				include MONDU_TRADE_VIEW_PATH . '/checkout/applied.php';
				break;
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
	 * Buyer States:
	 * Unknown   -> The customer will be redirected to create a Trade Account.
	 * Applied   -> The customer has been redirected to the hosted Trade Account application form.
	 * Pending   -> The customer will be displayed a message to indicate they will be notified within 48 hours.
	 * Declined  -> The customer will be displayed a message to show they cannot purchase with a Trade Account.
	 * Accepted  -> Spending limit is checked and displayed, and then redirected to Mondu's self-hosted checkout.
	 * Cancelled -> The customer has cancelled the transaction.
	 *
	 * @param $order_id
	 * @return array|void
	 * @throws WC_Data_Exception|MonduTradeResponseException
	 */
	public function process_payment( $order_id ) {
		$order       = wc_get_order( $order_id );
		$customer_id = $order->get_customer_id();
		$customer    = new Customer( $customer_id );
		$status      = $customer->get_mondu_trade_account_status();
		$return_url  = is_wc_endpoint_url( 'order-pay' ) ? $order->get_checkout_payment_url() : wc_get_checkout_url();

		// Applied means we haven't received the webhook, so we
		// need to wait for them to send us the info.
		if ( $status === BuyerStatus::APPLIED ) {
			wc_add_notice( "We're just waiting to hear back from Mondu, please wait and refresh the page.", 'notice' );

			return;
		}

		// If the buyer status is unknown, it would indicate we need to
		// create the trade account as the webhook hasn't fired for this
		// customer yet.
		//
		// If it's cancelled, they should be able to retry.
		if ( $status === BuyerStatus::UNKNOWN || $status === BuyerStatus::CANCELLED ) {

			$response = $this->mondu_request_wrapper->create_trade_account( $customer_id, $return_url, [
				'first_name' => $order->get_billing_first_name(),
				'last_name'  => $order->get_billing_last_name(),
				'email'      => $order->get_billing_email(),
				'phone'      => $order->get_billing_phone(),
			] );

			if ( empty( $response['hosted_page_url'] ) ) {
				$this->exit_from_payment();

				return;
			}

			$redirect_url = $response['hosted_page_url'];

			Logger::info( 'Redirecting user to hosted checkout page', [
				'url' => $redirect_url,
			] );

			return [
				'result'   => 'success',
				'redirect' => $redirect_url,
			];
		}

		// Bail if the customer hasn't been accepted by Mondu (PENDING/DECLINED)
		// to avoid people placing orders if they haven't been accepted.
		if ( $status === BuyerStatus::PENDING || $status === BuyerStatus::DECLINED ) {
			$this->exit_from_payment( 'You are not currently permitted to place a Mondu Trade Payment, try another payment method.' );

			return;
		}

		// If the buyer doesn't have enough credit's in their account,
		// we can't process the payment. We do this by comparing the
		// current balance in Mondu and the WooCommerce total amount.
		try {
			$response = $this->mondu_request_wrapper->get_buyer_limit( $customer_id );

			$max_purchase_value_cents = $response['purchasing_limit']['max_purchase_value_cents'];
			$order_total              = $order->get_total();
			$order_total_pence        = intval( $order_total * 100 );

			if ( $order_total_pence > $max_purchase_value_cents ) {
				$this->exit_from_payment( 'Your order exceeds the allowed purchase limit. Please reduce your order amount or try another payment method.' );

				return;
			}
		} catch ( Exception $e ) {
			$this->exit_from_payment();

			return;
		}

		// Original code from Mondu, we just call the parent.
		$success_url = $this->get_return_url( $order );
		$mondu_order = $this->mondu_request_wrapper->create_order_with_account( $order, $success_url );

		if ( ! $mondu_order ) {
			$this->exit_from_payment();

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
	 * @return bool| WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		return $this->mondu_gateway->process_refund( $order_id, $amount, $reason );
	}

	/**
	 * Exits from the payment and adds a session variable.
	 *
	 * @param string $notice_message
	 * @return void
	 */
	private function exit_from_payment( string $notice_message = '' ): void {
		if ( $notice_message === '' ) {
			$notice_message = 'Error placing an order. Please try again.';
		}

		// translators: %s is the error message displayed to the user.
		wc_add_notice( sprintf( __( 'Error: %s', 'mondu-digital-trade-account' ), esc_html( $notice_message ) ), 'error' );
	}

	/**
	 * Selects the Trade Account gateway if the status
	 * is succeeded.
	 *
	 * @param $available_gateways
	 * @return mixed
	 */
	public static function select_default_gateway( $available_gateways ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! is_checkout() || ! is_user_logged_in() || ! Plugin::has_mondu_trade_query_param() ) {
			return $available_gateways;
		}

		foreach ( $available_gateways as $gateway_id => $gateway ) {
			if ( $gateway_id === Plugin::PAYMENT_GATEWAY_NAME ) {
				$gateway->chosen = true;
				continue;
			}
			$gateway->chosen = false;
		}

		return $available_gateways;

		// phpcs:enable
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
