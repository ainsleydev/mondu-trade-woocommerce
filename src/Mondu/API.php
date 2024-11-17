<?php

/**
 * Mondu - API
 *
 * @package     MonduTradeAccount
 * @category    Mondu
 * @author      ainsley.dev
 */

namespace MonduTrade\Mondu;

use Mondu\Plugin;
use MonduTrade\Util\Logger;
use Mondu\Mondu\Support\Helper;
use Mondu\Exceptions\ResponseException;
use MonduTrade\Exceptions\MonduTradeException;
use MonduTrade\Exceptions\MonduTradeResponseException;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * API handles communication with the Mondu API by
 * extending the base API class.
 */
class API extends \Mondu\Mondu\Api {
	/**
	 * Global settings.
	 *
	 * @var array
	 */
	private $global_settings;

	/**
	 * API constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->global_settings = get_option( Plugin::OPTION_NAME );
	}

	/**
	 * Creates an order via a trade account.
	 *
	 * @param array $params
	 * @return mixed
	 * @throws MonduTradeException
	 * @throws ResponseException
	 * @see https://docs.mondu.ai/reference/get_api-v1-orders
	 */
	public function create_trade_account( array $params ) {
		$result = $this->request( '/trade_account', 'POST', $params );

		return json_decode( $result['body'], true );
	}

	/**
	 * @param string $uuid
	 * @return mixed
	 * @throws ResponseException
	 * @throws MonduTradeException
	 */
	public function get_buyer_limit( array $params ) {
		$result = $this->request( sprintf( '/buyers/%s/purchasing_limit', $params['uuid'] ) );

		return json_decode( $result['body'], true );
	}

	public function delete_webhook( array $params ) {
		$result = $this->request( sprintf( '/webhooks/%s', $params['uuid'] ), 'DELETE' );

		return json_decode( $result['body'], true );
	}

	/**
	 * Send Request.
	 *
	 * @param $path
	 * @param $method
	 * @param $body
	 * @return array
	 * @throws ResponseException|MonduTradeException
	 */
	private function request( $path, $method = 'GET', $body = null ) {
		$url = Helper::is_production() ? MONDU_PRODUCTION_URL : MONDU_SANDBOX_URL;
		$url .= $path;

		$headers = [
			'Content-Type'     => 'application/json',
			'Api-Token'        => $this->global_settings['api_token'],
			'X-Plugin-Name'    => 'woocommerce',
			'X-Plugin-Version' => MONDU_PLUGIN_VERSION,
		];

		$args = [
			'headers' => $headers,
			'method'  => $method,
			'timeout' => 30,
		];

		if ( null !== $body ) {
			$args['body'] = wp_json_encode( $body );
		}

		Logger::info( 'Performing request to Mondu API', [
			'method' => $method,
			'url'    => $url,
			'body'   => $args['body'] ?? null,
		] );

		return $this->validate_remote_result( $url, wp_remote_request( $url, $args ) );
	}

	/**
	 * Validate Result
	 *
	 * @param $url
	 * @param $result
	 * @return array
	 * @throws MonduTradeResponseException
	 * @throws MonduTradeException
	 */
	private function validate_remote_result( $url, $result ): array {
		if ( $result instanceof \WP_Error ) {
			throw new MonduTradeException( $result->get_error_message(), $result->get_error_code() );
		} else {
			Logger::info( 'Result from Mondu API', [
				'code'     => $result['response']['code'] ?? null,
				'url'      => $url,
				'response' => $result['body'] ?? null,
			] );
		}

		if ( ! is_array( $result ) || ! isset( $result['response'], $result['body'] ) || ! isset( $result['response']['code'], $result['response']['message'] ) ) {
			throw new MonduTradeException( __( 'Unexpected API response format.', 'mondu' ) );
		}

		if ( strpos( $result['response']['code'], '2' ) !== 0 ) {
			$message = $result['response']['message'];
			if ( isset( $result['body']['errors'], $result['body']['errors']['title'] ) ) {
				$message = $result['body']['errors']['title'];
			}

			throw new \MonduTrade\Exceptions\MonduTradeException( $message, $result['response']['code'], json_decode( $result['body'], true ) );
		}

		return $result;
	}
}
