<?php

/**
 * Mondu - Request Wrapper
 *
 * @package     MonduTradeAccount
 * @category    Mondu
 * @author      ainsley.dev
 */

namespace MonduTrade\Mondu;

use MonduTrade\Util\Environment;
use WC_Order;
use Exception;
use Mondu\Plugin;
use MonduTrade\Util\Logger;
use Mondu\Mondu\Support\OrderData;
use Mondu\Mondu\MonduRequestWrapper;
use MonduTrade\WooCommerce\Customer;
use Mondu\Exceptions\ResponseException;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

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
	 * Create Order with Account (WP User) via the
	 * billing statement method.
	 *
	 * @see https://docs.mondu.ai/docs/mondu-digital-trade-account
	 *
	 * @return mixed|void
	 * @throws ResponseException
	 * @throws \WC_Data_Exception
	 */
	public function create_order_with_account( WC_Order $order, $success_url ) {
		// Temporary to avoid warning.
		$order->set_payment_method( Plugin::PAYMENT_METHODS['invoice'] );

		$order_data                   = OrderData::create_order( $order, $success_url );
		$order_data['payment_method'] = 'billing_statement';
		$order_data['buyer']['uuid']  = 'bb9e3083-59a3-4f31-b34b-577b38f6ad90';

		error_log( json_encode( $order_data ) );
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
		$path    = '/wp-json/mondu-trade/v1/webhooks';
		$base = rest_url();

		if ( Environment::is_development() ) {
			$base = Environment::get( 'WEBHOOK_ADDRESS' );
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
}
