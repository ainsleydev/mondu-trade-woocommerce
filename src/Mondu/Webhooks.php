<?php

/**
 * Mondu Webhooks
 *
 * @package MonduTradeAccount
 */

namespace MonduTrade\Mondu;

use Mondu\Exceptions\MonduException;
use Mondu\Mondu\Models\SignatureVerifier;
use Mondu\Mondu\Support\Helper;
use MonduTrade\Exceptions\MonduTradeException;
use MonduTrade\Plugin;
use MonduTrade\WooCommerce\Customer;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}


class Webhooks extends WP_REST_Controller {
	/**
	 * Mondu Request Wrapper.
	 *
	 * @var RequestWrapper
	 */
	private RequestWrapper $mondu_request_wrapper;

	/**
	 * WooCommerce Mondu Trade Customer.
	 *
	 * @var Customer
	 */
	private Customer $woocommerce_customer;

	/**
	 * Webhooks constructor.
	 */
	public function __construct() {
		$this->mondu_request_wrapper = new RequestWrapper();
		$this->namespace             = 'mondu-trade/v1/webhooks';
	}

	/**
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/trade', [
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'index' ],
				'permission_callback' => '__return_true',
			],
		] );
	}

	/**
	 * Webhooks index
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response
	 */
	public function index( WP_REST_Request $request ): WP_REST_Response {
		$verifier = new SignatureVerifier();
		$params   = $request->get_json_params();
		$topic    = $params['topic'] ?? null;

		$body              = $request->get_body();
		$signature_payload = $request->get_header( 'X-MONDU-SIGNATURE' );
		$signature         = $verifier->create_hmac( $body );

		Helper::log( [
			'webhook_topic' => $topic,
			'params'        => $params,
		] );

		try {
			if ( $signature !== $signature_payload ) {
				throw new MonduException( __( 'Signature mismatch.', 'mondu' ) );
			}

			switch ( $topic ) {
				case 'buyer/accepted':
					$result = $this->handle_buyer_accepted( $params );
					break;
				case 'buyer/pending':
					$result = $this->handle_buyer_pending( $params );
					break;
				case 'buyer/declined':
					$result = $this->handle_buyer_declined( $params );
					break;
				default:
					$result = $this->handle_not_found_topic( $params );
					break;
			}

			$res_body   = $result[0];
			$res_status = $result[1];
		} catch ( \Exception $e ) {
			$this->mondu_request_wrapper->log_plugin_event( $e, 'webhooks', $params );
			$res_body   = [ 'message' => __( 'Something happened on our end.', 'mondu' ) ];
			$res_status = 200;
		}

		return new WP_REST_Response( $res_body, $res_status );
	}

	/**
	 * Example JSON Payload for Buyer Accepted/Pending/Declined
	 *
	 * @see: https://docs.mondu.ai/reference/webhooks-overview#buyer--accepted--pending--declined
	 *
	 * {
	 *     "topic": "buyer/{TOPIC_NAME}",
	 *     "buyer": {
	 *         "uuid": "66e8d234-23b5-1125-9592-d7390f20g01c",
	 *         "state": "accepted",
	 *         "external_reference_id": "DE-1-1000745773",
	 *         "company_name": "2023-02-07T15:14:22.301Z",
	 *         "first_name": "John",
	 *         "last_name": "Smith"
	 *     }
	 * }
	 */

	/**
	 * Handle Buyer Accepted from Trade Account
	 *
	 * @param array $params
	 *
	 * @return array
	 * @throws MonduTradeException
	 */
	private function handle_buyer_accepted( array $params ): array {
		$woocommerce_customer_number = $params['external_reference_id'];
		$buyer_uuid                  = $params['buyer']['uuid'];
		$state                       = $params['buyer']['state'];

		if ( ! $woocommerce_customer_number ) {
			throw new MonduTradeException( __( 'Required params missing.', Plugin::DOMAIN ) );
		}

		$customer = $this->get_customer( $woocommerce_customer_number );
		if ( ! $customer ) {
			return $this->return_not_found();
		}

		$customer->set_mondu_trade_account_uuid( $buyer_uuid );
		$customer->set_mondu_trade_account_status( $state );

		return $this->return_success();
	}

	/**
	 * Handle Buyer Pending from Trade Account
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	private function handle_buyer_pending( array $params ): array {
		return $this->return_success();
	}

	/**
	 * Handle Buyer Declined from Trade Account.
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	private function handle_buyer_declined( array $params ): array {
		return $this->return_success();
	}

	/**
	 * Handle not found topic.
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	private function handle_not_found_topic( $params ): array {
		Helper::log( [
			'not_found_topic' => $params,
		] );

		return $this->return_success();
	}

	/**
	 * Return success.
	 *
	 * @return array
	 */
	private function return_success(): array {
		return [ [ 'message' => 'Ok' ], 200 ];
	}

	/**
	 * Return not found.
	 *
	 * @return array
	 */
	private function return_not_found(): array {
		return [ [ 'message' => __( 'Not Found', 'mondu' ) ], 404 ];
	}

	/**
	 * Obtains the WooCommerce Customer.
	 *
	 * * @return bool|Customer
	 */
	private function get_customer( $id ) {
		try {
			return new Customer( $id );
		} catch ( \Exception $e ) {
			// TODO: log
			return false;
		}
	}
}
