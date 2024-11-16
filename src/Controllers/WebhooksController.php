<?php

/**
 * Controllers - Webhooks
 *
 * @package     MonduTradeAccount
 * @category    Controllers
 * @author      ainsley.dev
 */

namespace MonduTrade\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use MonduTrade\Plugin;
use MonduTrade\Util\Logger;
use MonduTrade\Util\Environment;
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\WooCommerce\Customer;
use MonduTrade\Mondu\RequestWrapper;
use MonduTrade\Encryption\SignatureVerifier;
use MonduTrade\Exceptions\MonduTradeException;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Webhook Controller registers WordPress Routes so that Mondu
 * is able to post back updates about buyer status's.
 *
 * Example URL: POST /wp-json/mondu-trade/v1/webhooks
 */
class WebhooksController extends BaseController {
	/**
	 * Mondu Request Wrapper.
	 *
	 * @var RequestWrapper
	 */
	private RequestWrapper $mondu_request_wrapper;

	/**
	 * Webhooks constructor.
	 */
	public function __construct() {
		$this->mondu_request_wrapper = new RequestWrapper();
	}

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/webhooks', [
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
	 * @return WP_REST_Response
	 */
	public function index( WP_REST_Request $request ): WP_REST_Response {
		if ( ! $this->validate_mondu_ip( $request ) ) {
			return $this->respond( 'Unauthorised', 403 );
		}

		$params = $request->get_json_params();

		// Handle case where params are null (i.e. empty JSON payload).
		if ( is_null( $params ) ) {
			Logger::error( 'Webhook request received with null params' );

			return $this->respond( 'Invalid request, no parameters provided', 400 );
		}

		// Check if the "buyer" information is present.
		if ( empty( $params['buyer'] ) ) {
			Logger::error( 'Missing buyer information in webhook request', [ 'params' => $params ] );

			return new WP_REST_Response( [ 'message' => 'Invalid request, missing buyer information' ], 400 );
		}

		$verifier          = new SignatureVerifier();
		$topic             = $params['topic'] ?? null;
		$body              = $request->get_body();
		$signature_payload = $request->get_header( 'X-MONDU-SIGNATURE' );
		$signature         = $verifier->create_hmac( $body );

		Logger::info( 'Webhook recieved from Mondu', [
			'webhook_topic' => $topic,
			'params'        => $params,
		] );

		try {
			if ( Environment::is_production() && $signature !== $signature_payload ) {
				throw new MonduTradeException( __( 'Signature mismatch.', 'mondu' ) );
			}

			$buyer = $params['buyer'];
			switch ( $topic ) {
				case 'buyer/accepted':
					$result = $this->update_customer_state( BuyerStatus::ACCEPTED, $buyer );
					break;
				case 'buyer/pending':
					$result = $this->update_customer_state( BuyerStatus::PENDING, $buyer );
					break;
				case 'buyer/declined':
					$result = $this->update_customer_state( BuyerStatus::DECLINED, $buyer );
					break;
				default:
					$result = $this->handle_not_found_topic( $params );
					break;
			}
		} catch ( \Exception $e ) {
			Logger::error( 'Webhook error from Mondu', [
				'params' => $params,
			] );
			$this->mondu_request_wrapper->log_plugin_event( $e, 'webhooks', $params );

			return $this->return_internal_error();
		}

		return $result;
	}

	/**
	 * Handle Buyer Declined from Trade Account.
	 *
	 * Example JSON Payload for Buyer Accepted/Pending/Declined:
	 *
	 *  {
	 *      "topic": "buyer/{TOPIC_NAME}",
	 *      "buyer": {
	 *          "uuid": "66e8d234-23b5-1125-9592-d7390f20g01c",
	 *          "state": "accepted",
	 *          "external_reference_id": "DE-1-1000745773",
	 *          "company_name": "2023-02-07T15:14:22.301Z",
	 *          "first_name": "John",
	 *          "last_name": "Smith"
	 *      }
	 *  }
	 *
	 *
	 * @param string $state
	 * @param array $params
	 * @return WP_REST_Response
	 * @see: https://docs.mondu.ai/reference/webhooks-overview#buyer--accepted--pending--declined
	 */
	private function update_customer_state( string $state, array $params ): WP_REST_Response {
		$woocommerce_customer_number = $params['external_reference_id'];
		$buyer_uuid                  = $params['uuid'];
		$state                       = $params['state'];

		if ( ! is_numeric( $woocommerce_customer_number ) ) {
			return $this->respond( __( 'Invalid customer number provided: ' . $woocommerce_customer_number, Plugin::DOMAIN ), 400 );
		}

		if ( ! $woocommerce_customer_number ) {
			return $this->respond( __( 'Customer not found: ' . $woocommerce_customer_number, Plugin::DOMAIN ), 404 );
		}

		$customer = $this->get_customer( (int) $woocommerce_customer_number );
		if ( ! $customer ) {
			return $this->return_not_found();
		}

		$customer->set_mondu_trade_account_uuid( $buyer_uuid );
		$customer->set_mondu_trade_account_status( $state );
		$customer->save();

		return $this->return_success();
	}

	/**
	 * Obtains the WooCommerce Customer.
	 *
	 * @return bool|Customer
	 */
	private function get_customer( $id ) {
		try {
			$customer = new Customer( $id );
			if ( $customer->get_id() === 0 ) {
				throw new \Exception( "Customer not found" );
			}

			return $customer;
		} catch ( \Exception $e ) {
			Logger::error( 'Customer not found from webhook', [
				'id'    => $id,
				'error' => $e->getMessage(),
			] );

			return false;
		}
	}

	/**
	 * Handle not found topic.
	 *
	 * @param array $params
	 * @return WP_REST_Response
	 */
	private function handle_not_found_topic( array $params ): WP_REST_Response {
		Logger::error( 'Webhook not found', [
			'not_found_topic' => $params,
		] );

		return $this->return_not_found();
	}
}
