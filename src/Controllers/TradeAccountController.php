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
	 * Query param for where Mondu has redirected us.
	 * It can be one of three states:
	 *
	 * - succeeded
	 * - cancelled
	 * - declined
	 *
	 * @var string
	 */
	const QUERY_REDIRECT_STATUS = 'trade_account_redirect_status';

	/**
	 * Query param for the buyer status, this is for
	 * when Mondu has sent a webhook.
	 *
	 * @var string
	 */
	const QUERY_BUYER_STATUS = 'trade_account_buyer_status';

	/**
	 * Query param for when the customer ID could not
	 * be retrieved from the URL.
	 *
	 * @var string
	 */
	const QUERY_ERROR = 'trade_account_error';

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

		// Bail if there's no customer, as we can't process the request.
		if ( ! $customer_id ) {
			$this->handle_error( 'Customer ID is missing from request parameters.' );

			return $this->respond( 'No customer found.', 400 );
		}

		$customer = new Customer( $customer_id );

		// Bail if the customer couldn't be retrieved.
		if ( ! $customer->is_valid() ) {
			$this->handle_error( 'Unable to retrieve Mondu Trade customer from redirect controller.' );

			return $this->respond( 'No customer found.', 400 );
		}

		// Obtain the status and UUID so we can utilise it on the frontend.
		$status = $customer->get_mondu_trade_account_status();
		$uuid   = $customer->get_mondu_trade_account_uuid();

		Logger::info( 'Customer has signed up via Mondu, redirecting...', [
			'status' => $status,
			'uuid'   => $uuid,
		] );

		// Redirect to the checkout page with both query parameters.
		$redirect_url = add_query_arg(
			[
				self::QUERY_REDIRECT_STATUS => esc_attr( $redirect_status ),
				self::QUERY_BUYER_STATUS    => esc_attr( $customer->get_mondu_trade_account_status() ),
			],
			wc_get_checkout_url()
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Handle error coming back from Mondu (with no status)
	 *
	 * @param string $log_message
	 * @return void
	 */
	private function handle_error( string $log_message ): void {
		Logger::error( $log_message );

		$redirect_url = add_query_arg( self::QUERY_ERROR, esc_attr( 'true' ), wc_get_checkout_url() );

		wp_safe_redirect( $redirect_url );
	}
}
