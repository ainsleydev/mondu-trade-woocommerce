<?php

/**
 * Actions - Submit Trade Account
 *
 * @package MonduTrade
 * @author ainsley.dev
 */

namespace MonduTrade\Actions;

use Exception;
use Mondu\Mondu\Support\Helper;
use MonduTrade\Admin\Options;
use MonduTrade\Mondu\API;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

class SubmitTradeAccount extends Form {

	/**
	 * The API to interact with Mondu.
	 *
	 * @var
	 */
	private API $api;

	/**
	 * Admin user defined options.
	 *
	 * @var Options
	 */
	private Options $admin_options;

	/**
	 * Trade Account constructor.
	 */
	public function __construct() {
		$this->action        = 'trade_account_submit';
		$this->api           = new Api();
		$this->admin_options = new Options();
		$this->rules         = [
			'data-protection' => 'required|true',
		];
		parent::__construct();
	}


	/**
	 * {"topic":"buyer/accepted", "buyer":{"uuid":"bb9e3083-59a3-4f31-b34b-577b38f6ad90", "state":"accepted", "external_reference_id":"buyer-02", "first_name":"Ainsley", "last_name":"Clark", "company_name":"123456 ABERDEEN LIMITED"}}
	 */

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
				// TODO, get the wordpress ID (user).
				"external_reference_id" => "buyer-02",
			];

			$response      = $this->api->create_trade_account( $payload );
			$this->respond( 200, $response, 'Successfully created trade account.' );
		} catch ( Exception $e ) {
			Helper::log( [
				'error' => $e->getMessage(),
			] );
			$this->respond( 500, [], $e->getMessage() );
		}

		exit;
	}
}
