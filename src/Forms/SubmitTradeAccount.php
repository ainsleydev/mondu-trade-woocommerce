<?php

/**
 * Actions - Submit Trade Account
 *
 * @package     MonduTradeAccount
 * @category    Actions
 * @author      ainsley.dev
 */

namespace MonduTrade\Forms;

use WC_Customer;
use MonduTrade\Util\Logger;
use MonduTrade\Mondu\RequestWrapper;
use MonduTrade\Controllers\TradeAccountController;

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
	 * Mondu Request Wrapper.
	 *
	 * @var RequestWrapper
	 */
	private RequestWrapper $mondu_request_wrapper;

	/**
	 * Submit Trade Account constructor.
	 */
	public function __construct() {
		$this->action                = 'trade_account_submit';
		$this->mondu_request_wrapper = new RequestWrapper();

		add_shortcode( 'mondu_trade_account_form', [ $this, 'output_trade_account_form' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

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
	 * @see https://docs.mondu.ai/reference/post_api-v1-trade-account
	 */
	public function process(): void {

		// Bail if there is no nonce (trade_account_submit_nonce).
		if ( ! $this->is_nonce_valid() ) {
			$this->respond( 403, [], 'Invalid nonce' );

			return;
		}

		// Bail if there's no user.
		if ( ! is_user_logged_in() ) {
			$this->respond( 400, [], 'User must be logged in' );

			return;
		}

		$user_id    = get_current_user_id();
		$return_url = home_url() . wp_get_referer();

		try {
			$response = $this->mondu_request_wrapper->create_trade_account(
				$user_id,
				$return_url,
				$this->get_applicant_details( $user_id ),
			);

			$this->respond( 200, $response, 'Trade account submitted' );
		} catch ( \Exception $e ) {
			Logger::error( 'Error creating Trade Account from form', [
				'user_id' => $user_id,
				'error'   => $e->getMessage(),
			] );

			wc_add_notice( 'Trade account submission failed: ' . $e->getMessage(), 'error' );

			$this->respond( 500, [], $e->getMessage() );
		}

		exit;
	}

	/**
	 * Outputs the form as a shortcode, won't output if there
	 * is a query param (indicating they've already signed)
	 * up.
	 *
	 * @return void
	 */
	public function output_trade_account_form(): void {
		$query_buyer_status = isset( $_GET[ TradeAccountController::QUERY_APPLIED ] ) ? // phpcs:disable WordPress.Security.NonceVerification.Recommended
			sanitize_text_field( wp_unslash( $_GET[ TradeAccountController::QUERY_APPLIED ] ) ) : '';

		if ( ! empty( $query_buyer_status ) ) {
			return;
		}

		include MONDU_TRADE_VIEW_PATH . '/forms/submit-trade-account.php';
	}

	/**
	 * Get applicant details from WooCommerce customer.
	 *
	 * @param int $user_id
	 * @return array
	 */
	private function get_applicant_details( int $user_id ): array {
		try {
			$user     = get_userdata( $user_id );
			$customer = new WC_Customer( $user_id );

			// Create the initial array with WordPress user data first.
			$applicant_details = [
				"first_name" => $user->first_name ?? '',
				"last_name"  => $user->last_name ?? '',
				"email"      => $user->user_email ?? '',
				"phone"      => '',
			];

			// If WordPress data is missing, fall back to WooCommerce data.
			$applicant_details['first_name'] = $applicant_details['first_name'] ?: $customer->get_billing_first_name();
			$applicant_details['last_name']  = $applicant_details['last_name'] ?: $customer->get_billing_last_name();
			$applicant_details['phone']      = $customer->get_billing_phone() ?: '';

			// Filter out empty values from the array.
			return array_filter( $applicant_details, function ( $value ) {
				return $value !== '';
			} );
		} catch ( \Exception $e ) {
			Logger::error( 'Error retrieving applicant details', [
				'user_id' => $user_id,
				'error'   => $e->getMessage(),
			] );

			return []; // Return an empty array if there’s an error
		}
	}

	/**
	 * Enqueue the JavaScript file for the trade account form
	 *
	 * @return void
	 */
	public function enqueue_scripts(): void {
		$id = 'mondu-trade-account-form';
		wp_register_script( $id, MONDU_TRADE_ASSETS_PATH . '/js/form.js', [ 'jquery' ], false, true );
		wp_enqueue_script( $id );
	}
}
