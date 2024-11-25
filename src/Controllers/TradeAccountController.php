<?php

/**
 * Controllers - Trade Account Controller
 *
 * @package     MonduTradeAccount
 * @category    Controllers
 * @author      ainsley.dev
 */

namespace MonduTrade\Controllers;

use MonduTrade\Mondu\RedirectStatus;
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
	 * Query param to determine if the user has signed
	 * up for a Trade Account.
	 *
	 * @var string
	 */
	const QUERY_APPLIED = 'mondu_trade_account_applied';

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
		$params          = $request->get_json_params();
		$redirect_status = $request->get_param( 'redirect_status' );
		$customer_id     = $request->get_param( 'customer_id' );
		$return_url      = urldecode( $request->get_param( 'return_url' ) ) ?? wc_get_checkout_url();

		// The referer needs to be in the valid IP range.
		if ( ! $this->validate_mondu_ip( $request ) ) {
			Logger::error( 'Unauthorised: Mondu IP is not valid', [
				'params' => $params,
			] );

			return $this->respond( 'Unauthorised', 403 );
		}

		Logger::debug( 'Received request in Trade Account Controller', [
			'customer_id'     => $customer_id,
			'return_url'      => $return_url,
			'redirect_status' => $redirect_status,
		] );

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
				'params' => $request->get_params(),
			] );

			return $this->respond( 'No customer found.', 400 );
		}

		// Update the status of the customer as best we can
		// with no pending state.
		switch ( $redirect_status ) {
			case RedirectStatus::DECLINED:
				$customer->set_mondu_trade_account_status( BuyerStatus::DECLINED );
				break;
			case RedirectStatus::CANCELLED:
				$customer->set_mondu_trade_account_status( BuyerStatus::CANCELLED );
		}

		// Obtain the status and UUID so we can utilise it on the frontend.
		$status = $customer->get_mondu_trade_account_status();
		$uuid   = $customer->get_mondu_trade_account_uuid();

		if ( $status === BuyerStatus::UNKNOWN || $status === BuyerStatus::APPLIED ) {
			// Momentary sleep, so we can ensure the webhook as fired.
			sleep( 2 );

			Logger::error( 'Customer has been redirected before a webhook as fired', [
				'params' => $request->get_params(),
				'uuid'   => $uuid,
			] );
		}

		$redirect_url = add_query_arg( [ self::QUERY_APPLIED => esc_attr( 'true' ), ], $return_url, );

		Logger::info( 'Customer has signed up via Mondu, redirecting...', [
			'status' => $status,
			'uuid'   => $uuid,
			'url'    => $redirect_url,
		] );

		wp_safe_redirect( $redirect_url );

		exit;
	}
}
