<?php

/**
 * Controllers - Trade Account Controller
 *
 * @package     MonduTradeAccount
 * @category    Controllers
 * @author      ainsley.dev
 */

namespace MonduTrade\Controllers;

use MonduTrade\Util\Logger;
use WP_REST_Request;
use WP_REST_Response;

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
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/trade-account', [
			[
				'methods'             => 'GET',
				'callback'            => [ $this, 'index' ],
				'permission_callback' => '__return_true',
			],
		] );
	}

	/**
	 * This handler provides the functionality for when Mondu
	 * redirects the user after a trade account application.
	 *
	 * It will redirect to the checkout page with a query
	 * parameter of status.
	 *
	 * For example: /checkout/?trade_account_status=accepted
	 *
	 * This will allow the frontend to pick it up and display
	 * a WooCommerce notice to the user.
	 *
	 * @param WP_REST_Request $request
	 * @return WP_REST_Response
	 */
	public function index( WP_REST_Request $request ): WP_REST_Response {
		$status       = $request->get_param( 'status' );
		$customer_id  = $request->get_param( 'customer_id' );
		$checkout_url = wc_get_checkout_url();

		// Bail if there's no status.
		if ( ! $status || ! $customer_id ) {
			Logger::error( 'Trade account status parameter is missing in the request.', [
				'query' => $request->get_params(),
			] );
			wp_safe_redirect( $checkout_url );
			exit;
		}

		// Redirect to the checkout page with status parameter.
		$redirect_url = add_query_arg( 'trade_account_status', esc_attr( $status ), wc_get_checkout_url() );

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
