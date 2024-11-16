<?php

/**
 * Controllers - Base Controller
 *
 * @package     MonduTradeAccount
 * @category    Controllers
 * @author      ainsley.dev
 */

namespace MonduTrade\Controllers;

use WP_REST_Request;
use WP_REST_Response;
use MonduTrade\Plugin;
use WP_REST_Controller;
use MonduTrade\Util\Environment;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Base Controller is used as a common class
 * for common REST functionality.
 */
abstract class BaseController extends WP_REST_Controller {
	/**
	 * API namespace for REST routes.
	 *
	 * @var string
	 */
	protected $namespace = 'mondu-trade/v1';

	/**
	 * Utility function to send a JSON response with a message.
	 *
	 * @param string $message
	 * @param int $status
	 * @return WP_REST_Response
	 */
	protected function respond( string $message, int $status ): WP_REST_Response {
		return new \WP_REST_Response( [ 'message' => $message ], $status );
	}

	/**
	 * Return success.
	 *
	 * @return WP_REST_Response
	 */
	protected function return_success(): WP_REST_Response {
		return $this->respond( 'OK', 200 );
	}

	/**
	 * Return not found.
	 *
	 * @return WP_REST_Response
	 */
	protected function return_not_found(): WP_REST_Response {
		return $this->respond( __( 'Not Found', Plugin::DOMAIN ), 404 );
	}

	/**
	 * Return internal error.
	 *
	 * @return WP_REST_Response
	 */
	protected function return_internal_error(): WP_REST_Response {
		return $this->respond( __( 'Something happened on our end.', Plugin::DOMAIN ), 500 );
	}

	/**
	 * Validate if the request has come from Mondu's IP Range.
	 *
	 * @param WP_REST_Request $request
	 * @return bool
	 * @see https://docs.mondu.ai/reference/webhook-security
	 */
	protected function validate_mondu_ip( WP_REST_Request $request ): bool {
		if ( Environment::is_development() ) {
			return true;
		}

		$allowed_ips = [
			// Sandbox
			'3.67.101.172',
			'3.69.55.142',
			'3.72.30.70',
			// Production
			'3.68.36.187',
			'3.127.195.5',
			'18.194.230.169'
		];

		$ip = $request->get_header( 'X-Forwarded-For' ) ?? $request->get_header( 'REMOTE_ADDR' );

		return in_array( $ip, $allowed_ips, true );
	}
}
