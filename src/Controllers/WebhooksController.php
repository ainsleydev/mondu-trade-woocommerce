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

	/***
	 * The route of the controller.
	 *
	 * @var string
	 */
	public static string $route = '/webhooks';

	/**
	 * Register routes.
	 */
	public function register_routes() {
		register_rest_route( self::$base_namespace, self::$route, [
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'index' ],
				'permission_callback' => '__return_true',
			],
		] );
	}

	/**
	 * Get the full REST URL for the Webhooks endpoint.
	 *
	 * @return string
	 */
	public static function get_full_rest_url(): string {
		if ( Environment::is_production() ) {
			return rest_url( self::$base_namespace . self::$route );
		}

		$base = Environment::get( 'MONDU_WEBHOOKS_URL', get_home_url() );

		return $base . '/wp-json/' . self::$base_namespace . self::$route;
	}

	/**
	 * Webhooks index
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function index( WP_REST_Request $request ): WP_REST_Response {
		$params = $request->get_json_params();

		// The referer needs to be in the valid IP range.
		if ( ! $this->validate_mondu_ip( $request ) ) {
			return $this->respond( 'Unauthorised', 403 );
		}

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

		Logger::info( 'Webhook received from Mondu', [
			'payload' => $params,
		] );

		try {
			if ( $signature !== $signature_payload ) {
				Logger::error( 'Signature mismatch in Webhooks Controller' );
				throw new MonduTradeException( __( 'Signature mismatch.', 'mondu-digital-trade-account' ) );
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
				'error'  => $e->getMessage(),
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
	 * @param string $buyer_status
	 * @param array $params
	 * @return WP_REST_Response
	 * @see: https://docs.mondu.ai/reference/webhooks-overview#buyer--accepted--pending--declined
	 */
	private function update_customer_state( string $buyer_status, array $params ): WP_REST_Response {
		$woocommerce_customer_number = $params['external_reference_id'];
		$buyer_uuid                  = $params['uuid'];
		$state                       = $params['state'];

		if ( ! $woocommerce_customer_number || ! is_numeric( $woocommerce_customer_number ) ) {
			Logger::error( 'Invalid customer number in webhook request, external reference ID: ' . $woocommerce_customer_number );

			return $this->respond(
				sprintf(
				// translators: %s is the customer number that is invalid.
					__( 'Invalid customer number provided: %s', 'mondu-digital-trade-account' ),
					$woocommerce_customer_number
				),
				400
			);
		}

		$customer = $this->get_customer( (int) $woocommerce_customer_number );
		if ( ! $customer ) {
			Logger::error( 'Customer ID not found from external reference ID: ' . $woocommerce_customer_number );

			return $this->return_not_found();
		}

		$customer->set_mondu_trade_account_uuid( $buyer_uuid );
		$customer->set_mondu_trade_account_status( $state );
		$customer->save();

		Logger::info( 'Successfully updated customer status from buyer webhook', [
			'state'       => $state,
			'uuid'        => $params['uuid'],
			'customer_id' => $woocommerce_customer_number,
		] );

		try {
			$this->perform_action( $buyer_status, (int) $woocommerce_customer_number, $params );
		} catch ( \Exception $e ) {
			Logger::error( 'Error performing action (triggering user defined hook)', [
				'buyer_status' => $buyer_status,
				'params'       => $params,
				'error'        => $e->getMessage(),
			] );
		}

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
			if ( ! $customer->is_valid() ) {
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
	 * Performs an action so any user can latch onto the
	 * Webhook Request.
	 *
	 * @param string $state
	 * @param int $customer_id
	 * @param array $buyer
	 * @return void
	 */
	private function perform_action( string $state, int $customer_id, array $buyer ) {
		do_action( 'mondu_trade_buyer_webhook_received', $state, $customer_id, $buyer );

		switch ( $state ) {
			case BuyerStatus::ACCEPTED:
				do_action( 'mondu_trade_buyer_accepted', $customer_id, $buyer );
				break;
			case BuyerStatus::PENDING:
				do_action( 'mondu_trade_buyer_pending', $customer_id, $buyer );
				break;
			case BuyerStatus::DECLINED:
				do_action( 'mondu_trade_buyer_declined', $customer_id, $buyer );
				break;
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
