<?php

/**
 * Actions - Submit Trade Account
 *
 * @package MonduTrade
 * @author ainsley.dev
 */

namespace MonduTrade\Actions;

use Mondu\MonduAPI;
use MonduTrade\Admin\Options;
use util\Util;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

class SubmitTradeAccount extends Form {

	private MonduAPI $api;

	private Options $admin_options;

	/**
	 * Trade Account constructor.
	 */
	public function __construct() {
		$this->action        = 'trade_account_submit';
		$this->api           = new MonduAPI();
		$this->admin_options = new Options();
		$this->rules         = [
			'address_line1' => 'required',
			'country_code'  => 'required',
			'city'          => 'required',
			'zip_code'      => 'required',
		];
		parent::__construct();
	}

	/**
	 *
	 *
	 * @return void
	 */
	public function process(): void {
		$this->security_check();
		$this->validate();

		try {
			$payload = [
				"redirect_urls"         => [
					"success_url"  => $this->admin_options->get_redirect_accepted_url(),
					"cancel_url"   => $this->admin_options->get_redirect_declined_url(),
					"declined_url" => $this->admin_options->get_redirect_declined_url(),
				],
				"company_details"       => [
					"registration_address" => $this->get_data(),
				],
				"external_reference_id" => "buyer-01",
			];

			$response      = $this->api->post( '/trade_account', $payload );
			$response_code = wp_remote_retrieve_response_code( $response );

			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );
			if ( $response_code !== 200 && $response_code !== 201 ) {
				Util::log( [
					'error'   => 'Bad Request',
					'details' => $data,
				] );
				$this->respond( 400, $data, 'Bad request. Please check the submitted data.' );

				return;
			}

			$this->respond( 200, $data, 'Successfully created trade account.' );
		} catch ( Exception $e ) {
			Util::log( [
				'error' => $e->getMessage(),
			] );
			$this->respond( 500, [], $e->getMessage() );
		}

		die();
	}

	/**
	 * Returns the registration address used to create
	 * the trade account.
	 *
	 * @return array
	 */
	private function get_data(): array {
		return [
			'country_code'  => $this->clean_vars( $_POST['country_code'] ?? '' ),
			'city'          => $this->clean_vars( $_POST['city'] ?? '' ),
			'zip_code'      => $this->clean_vars( $_POST['zip_code'] ?? '' ),
			'address_line1' => $this->clean_vars( $_POST['address_line1'] ?? '' ),
		];
	}
}
