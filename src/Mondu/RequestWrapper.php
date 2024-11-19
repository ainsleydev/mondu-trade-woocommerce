<?php

/**
 * Mondu - Request Wrapper
 *
 * @package     MonduTradeAccount
 * @category    Mondu
 * @author      ainsley.dev
 */

namespace MonduTrade\Mondu;

use WC_Order;
use Exception;
use Mondu\Plugin;
use WC_Data_Exception;
use MonduTrade\Util\Logger;
use MonduTrade\Util\Environment;
use Mondu\Mondu\Support\OrderData;
use Mondu\Mondu\MonduRequestWrapper;
use MonduTrade\WooCommerce\Customer;
use Mondu\Exceptions\ResponseException;
use MonduTrade\Exceptions\MonduTradeException;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Request Wrapper is an extension of the Mondu API to
 * provide functionality to interact with Mondu via
 * Woocommerce & WordPress.
 */
class RequestWrapper extends MonduRequestWrapper {

	/**
	 * Mondu API
	 *
	 * @var Api
	 */
	public API $api;

	/**
	 * MonduRequestWrapper constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->api = new API();
	}

	/**
	 * Creates a new trade account.
	 *
	 * @param int $user_id
	 * @param array $applicant_details
	 * @return array
	 * @throws ResponseException
	 * @see https://docs.mondu.ai/reference/post_api-v1-trade-account
	 */
	public function create_trade_account( int $user_id, array $applicant_details ): array {
		$trade_data = [
			"external_reference_id" => (string) $user_id,
			"redirect_urls"         => [
				"success_url"  => $this->get_trade_redirect_url( $user_id, 'succeeded' ),
				"cancel_url"   => $this->get_trade_redirect_url( $user_id, 'cancelled' ),
				"declined_url" => $this->get_trade_redirect_url( $user_id, 'declined' ),
			],
		];

		if ( ! empty( $applicant_details ) ) {
			$trade_data['applicant'] = $applicant_details;
		}

		return $this->wrap_with_mondu_log_event( 'create_trade_account', [ $trade_data ] );
	}

	/**
	 * Create Order with Account (WP User) via the billing statement method.
	 *
	 * This just calls the parent but with the added UUID and payment method
	 * set to billing statement.
	 *
	 * @return mixed|void
	 * @throws ResponseException
	 * @throws WC_Data_Exception
	 * @see https://docs.mondu.ai/docs/mondu-digital-trade-account
	 */
	public function create_order_with_account( WC_Order $order, $success_url ) {
		$customer = new Customer( $order->get_customer_id() );

		// Set a temporary payment method to avoid PHP warning.
		$order->set_payment_method( Plugin::PAYMENT_METHODS['invoice'] );

		$order_data                   = OrderData::create_order( $order, $success_url );
		$order_data['payment_method'] = 'billing_statement';
		$order_data['buyer']['uuid']  = $customer->get_mondu_trade_account_uuid();

		Logger::debug( 'Sending order to Mondu', [
			'order' => $order_data,
		] );

		$response = $this->wrap_with_mondu_log_event( 'create_order', [ $order_data ] );

		$mondu_order = $response['order'];

		$order->update_meta_data( Plugin::ORDER_ID_KEY, $mondu_order['uuid'] );
		$order->save();

		return $mondu_order;
	}

	/**
	 * Obtains the Buyer Limit for a given user.
	 *
	 * @return mixed
	 * @throws ResponseException
	 * @throws MonduTradeException
	 * @see https://docs.mondu.ai/reference/get_api-v1-buyers-uuid-purchasing-limit
	 */
	public function get_buyer_limit() {
		$user_id = get_current_user_id();

		try {
			$customer = new Customer( $user_id );
		} catch ( Exception $e ) {
			Logger::error( 'Obtaining customer to get Buyer Limit', [
				'user_id' => $user_id,
				'error'   => $e->getMessage(),
			] );
		}

		if ( ! isset( $customer ) || ! $customer || ! $customer->is_valid() ) {
			throw new MonduTradeException( 'Cannot buyer limit, invalid customer' );
		}

		$params = [
			'uuid' => $customer->get_mondu_trade_account_uuid(),
		];

		return $this->wrap_with_mondu_log_event( 'get_buyer_limit', [ $params ] );
	}

	/**
	 * Register Buyer Webhooks.
	 *
	 * @return mixed
	 * @throws ResponseException
	 */
	public function register_buyer_webhooks() {
		$path = '/wp-json/mondu-trade/v1/webhooks';
		$base = rest_url();

		if ( Environment::is_development() ) {
			$base = Environment::get( 'MONDU_WEBHOOKS_URL' );
		}

		$params = [
			'topic'   => 'buyer',
			'address' => $base . $path,
		];

		$response = $this->wrap_with_mondu_log_event( 'register_webhook', [ $params ] );

		return $response['webhooks'] ?? null;
	}

	/**
	 * Wrap the call to the Mondu API with a try/catch block and log if an error occurs.
	 *
	 * @param string $action
	 * @param array $params
	 *
	 * @return mixed
	 * @throws ResponseException
	 * @throws Exception
	 */
	private function wrap_with_mondu_log_event( string $action, array $params = [] ) {
		try {
			return call_user_func_array( [ $this->api, $action ], $params );
		} catch ( ResponseException $e ) {
			$this->log_plugin_event( $e, $action, $e->getBody() );
			throw $e;
		} catch ( Exception $e ) {
			$this->log_plugin_event( $e, $action );
			throw $e;
		}
	}

	/**
	 * Generate the redirect URL with a specified status and user ID.
	 *
	 * @param int $user_id
	 * @param string $status
	 * @return string
	 */
	private function get_trade_redirect_url( int $user_id, string $status ): string {
		return add_query_arg(
			[
				'status'      => $status,
				'customer_id' => $user_id,
			],
			rest_url( 'mondu-trade/v1/trade-account' )
		);
	}
}