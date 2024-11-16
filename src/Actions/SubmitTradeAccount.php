<?php

/**
 * Actions - Submit Trade Account
 *
 * @package     MonduTradeAccount
 * @category    Actions
 * @author      ainsley.dev
 */

namespace MonduTrade\Actions;

use Mondu\Exceptions\ResponseException;
use MonduTrade\Exceptions\MonduTradeException;
use MonduTrade\Mondu\API;
use MonduTrade\Util\Logger;
use WC_Customer;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Submit Trade Account allows the user to sign up to a
 * new trade account within the checkout.
 *
 * If successful, the caller will receive a redirect URL
 * to redirect to Mondu's hosted application form.
 */
class SubmitTradeAccount extends Form {

	/**
	 * The API to interact with Mondu.
	 *
	 * @var API
	 */
	private API $api;

	/**
	 * Submit Trade Account constructor.
	 */
	public function __construct() {
		$this->action = 'trade_account_submit';
		$this->api    = new Api();
		parent::__construct();
	}

	/**
	 * Process submits a trade application to the Mondu API.
	 *
	 * The current user is passed as an external reference, so
	 * it can be looked up in subsequent requests. Applicant
	 * data is sent, if it's available (non-blocking)
	 *
	 * @return void
	 * @throws MonduTradeException
	 * @throws ResponseException
	 * @see https://docs.mondu.ai/reference/post_api-v1-trade-account
	 */
	public function process(): void {
		$this->security_check();

		// Bail if there's no user.
		if ( ! is_user_logged_in() ) {
			$this->respond( 400, [], 'User must be logged in' );
			exit;
		}

		// User ID is required for the external reference.
		$user_id = get_current_user_id();

		// Base Payload for POSTing data to the Trade Account endpoint.
		$payload = [
			"external_reference_id" => (string) $user_id,
			"redirect_urls"         => [
				"success_url"  => $this->get_redirect_url( $user_id, 'succeeded' ),
				"cancel_url"   => $this->get_redirect_url( $user_id, 'cancelled' ),
				"declined_url" => $this->get_redirect_url( $user_id, 'declined' ),
			],
		];

		// Add applicant details if available.
		$applicant_details = $this->get_applicant_details( $user_id );
		if ( ! empty( $applicant_details ) ) {
			$payload['applicant'] = $applicant_details;
		}

		try {
			$response = $this->api->create_trade_account( $payload );
			$this->respond( 200, $response, 'Successfully created trade account.' );
		} catch ( \Exception $e ) {
			Logger::error( 'Error creating trade account', [
				'payload' => $payload,
				'error'   => $e->getMessage(),
			] );
			$this->respond( 500, [], $e->getMessage() );
		}

		exit;
	}

	/**
	 * Get applicant details from WooCommerce customer.
	 *
	 * @param int $user_id
	 * @return array
	 */
	private function get_applicant_details( int $user_id ): array {
		try {
			$customer = new WC_Customer( $user_id );
			$user     = get_userdata( $user_id );

			// TODO: We should get the Wordpress User Data first, and if not resort to Woo's data.

			// Create the initial array with possible empty values.
			$applicant_details = [
				"first_name" => $customer->get_billing_first_name() ?: '',
				"last_name"  => $customer->get_billing_last_name() ?: '',
				"email"      => $user->user_email ?: '',
				"phone"      => $customer->get_billing_phone() ?: '',
			];

			// Filter out empty values from the array.
			return array_filter( $applicant_details, function ( $value ) {
				return $value !== '';
			});

		} catch ( \Exception $e ) {
			Logger::error( 'Error retrieving applicant details', [
				'user_id' => $user_id,
				'error'   => $e->getMessage(),
			] );

			return []; // Return an empty array if there’s an error
		}
	}

	/**
	 * Generate the redirect URL with a specified status and user ID.
	 *
	 * @param int $user_id
	 * @param string $status
	 * @return string
	 */
	private function get_redirect_url( int $user_id, string $status ): string {
		return add_query_arg(
			[
				'status'      => $status,
				'customer_id' => $user_id,
			],
			rest_url( 'mondu-trade/v1/trade-account' )
		);
	}
}
