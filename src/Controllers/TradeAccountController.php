<?php

/**
 * Controllers - Trade Account Controller
 *
 * @package     MonduTradeAccount
 * @category    Controllers
 * @author      ainsley.dev
 */

namespace MonduTrade\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use MonduTrade\Util\Logger;
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\WooCommerce\Customer;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Trade Account Controller registers a WordPress Route for
 * when Mondu redirects the customer back to WP
 *
 * Example URL: GET /wp-json/mondu-trade/v1/trade-account
 */
class TradeAccountController extends BaseController {

	/**
	 * Query param for the message after a customer has
	 * applied for a Trade Account.
	 *
	 * @var string
	 */
	const QUERY_MESSAGE = 'trade_account_message';

	/**
	 * Query param for the type of notice message.
	 *
	 * @var string
	 */
	const QUERY_NOTICE_TYPE = 'trade_account_notice_type';

	/**
	 * Query param for the buyer status, this is for
	 * when Mondu has sent a webhook.
	 *
	 * @var string
	 */
	const QUERY_BUYER_STATUS = 'trade_account_buyer_status';

	/***
	 * The route of the controller.
	 *
	 * @var string
	 */
	private static string $route = '/trade-account';

	/**
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route( self::$base_namespace, self::$route, [
			[
				'methods'             => 'GET',
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
		return rest_url( self::$base_namespace . self::$route );
	}

	/**
	 * This handler provides the functionality for when Mondu
	 * redirects the user after a trade account application.
	 *
	 * It will redirect to the checkout page with a query
	 * parameter of status.
	 *
	 * For example: /checkout/?redirect_status=succeeded&customer_id
	 *
	 * This will allow the frontend to pick it up and display
	 * a WooCommerce notice to the user.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function index( WP_REST_Request $request ): WP_REST_Response {
		$redirect_status = $request->get_param( 'redirect_status' );
		$customer_id     = $request->get_param( 'customer_id' );
		$return_url      = urldecode( $request->get_param( 'return_url' ) ) ?? wc_get_checkout_url();

		// Bail if there's no customer, as we can't process the request.
		if ( ! $customer_id ) {
			Logger::error( 'Customer ID is missing from request parameters.', [
				'request' => $request,
			] );

			return $this->respond( 'No customer found.', 400 );
		}

		$customer = new Customer( $customer_id );

		// Bail if the customer couldn't be retrieved.
		if ( ! $customer->is_valid() ) {
			Logger::error( 'Unable to retrieve Mondu Trade customer from redirect controller.', [
				'request' => $request,
			] );

			return $this->respond( 'No customer found.', 400 );
		}

		// Obtain the status and UUID so we can utilise it on the frontend.
		$status = $customer->get_mondu_trade_account_status();
		$uuid   = $customer->get_mondu_trade_account_uuid();

		Logger::info( 'Customer has signed up via Mondu, redirecting...', [
			'status' => $status,
			'uuid'   => $uuid,
		] );

		$notice = $this->get_notice_message( $redirect_status, $customer->get_mondu_trade_account_status() );

		// Redirect to the checkout page with both query parameters.
		$redirect_url = add_query_arg(
			[
				self::QUERY_MESSAGE      => esc_attr( $notice['message'] ),
				self::QUERY_NOTICE_TYPE  => esc_attr( $notice['type'] ),
				self::QUERY_BUYER_STATUS => esc_attr( $redirect_status ),
			],
			$return_url,
		);

		wp_safe_redirect( $redirect_url );

		exit;
	}

	/**
	 * Returns the notice messages to redirect too.
	 *
	 * @param string $redirect_status
	 * @param string $buyer_status
	 * @return array
	 */
	private function get_notice_message( string $redirect_status, string $buyer_status ): array {
		// Redirect status handling.
		if ( $redirect_status === 'cancelled' ) {
			return [
				'type'    => 'error',
				'message' => 'Your Trade Account application was cancelled, please try again or select a different payment method.'
			];
		}

		if ( $redirect_status === 'declined' ) {
			return [
				'type'    => 'error',
				'message' => 'Your trade account has been declined, please use an alternative payment method.',
			];
		}

		// Buyer status handling.
		switch ( $buyer_status ) {
			case BuyerStatus::ACCEPTED:
				return [
					'type'    => 'success',
					'message' => 'Your trade account has been approved.',
				];
			case BuyerStatus::PENDING:
				return [
					'type'    => 'notice',
					'message' => 'Your trade account is pending. You will hear back in 48 hours.',
				];
			case BuyerStatus::DECLINED:
				return [
					'type'    => 'error',
					'message' => 'Your trade account has been declined, please use an alternative payment method.',
				];
			case BuyerStatus::APPLIED:
				return [
					'type'    => 'notice',
					'message' => "We're just waiting to hear back from Mondu, please wait and refresh the page.",
				];
			default:
				return [
					'type'    => 'error',
					'message' => 'An unexpected error occurred. Please try again.',
				];
		}
	}
}
