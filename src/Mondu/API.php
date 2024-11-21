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
	 * @throws MonduTradeResponseException|MonduTradeException
	 * @see https://docs.mondu.ai/reference/post_api-v1-trade-account
	 */
	public function create_trade_account( array $params ) {
		$result = $this->request( '/trade_account', 'POST', $params );

		return json_decode( $result['body'], true );
	}

	/**
	 * Obtains the buyer limit for the customer.
	 *
	 * @param array $params
	 * @return mixed
	 * @throws MonduTradeResponseException|MonduTradeException
	 * @see https://docs.mondu.ai/reference/get_api-v1-buyers-uuid-purchasing-limit
	 */
	public function get_buyer_limit( array $params ) {
		$result = $this->request( sprintf( '/buyers/%s/purchasing_limit', $params['uuid'] ) );

		return json_decode( $result['body'], true );
	}

	/**
	 * Deletes a webhook that's already registered.
	 * Should only be used in dev.
	 *
	 * @param array $params
	 * @return mixed
	 * @throws MonduTradeResponseException|MonduTradeException
	 * @see https://docs.mondu.ai/reference/delete_api-v1-webhooks-uuid
	 */
	public function delete_webhook( array $params ) {
		$result = $this->request( sprintf( '/webhooks/%s', $params['uuid'] ), 'DELETE' );

		return json_decode( $result['body'], true );
	}

	/**
	 * Send Request.
	 *
	 * @param string $path
	 * @param string $method
	 * @param $body
	 * @return array
	 * @throws MonduTradeResponseException|MonduTradeException
	 * @noinspection DuplicatedCode
	 */
	private function request( string $path, string $method = 'GET', $body = null ): array {
		$url = Helper::is_production() ? MONDU_TRADE_API_PRODUCTION_URL : MONDU_TRADE_API_SANDBOX_URL;
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
	 * Validate Result.
	 *
	 * @param $url
	 * @param $result
	 * @return array
	 * @throws MonduTradeResponseException|MonduTradeException
	 */
	private function validate_remote_result( $url, $result ): array {
		if ( $result instanceof \WP_Error ) {
			throw new MonduTradeException(
				esc_html( $result->get_error_message() ),
				esc_html( $result->get_error_code() )
			);
		} else {
			Logger::info( 'Result from Mondu API', [
				'code'     => esc_html( $result['response']['code'] ?? '' ),
				'url'      => esc_url( $url ),
				'response' => esc_html( $result['body'] ?? '' ),
			] );
		}

		if ( ! is_array( $result ) || ! isset( $result['response'], $result['body'] ) || ! isset( $result['response']['code'], $result['response']['message'] ) ) {
			throw new MonduTradeException(
				esc_html__( 'Unexpected API response format.', 'mondu-trade-account' )
			);
		}

		if ( strpos( $result['response']['code'], '2' ) !== 0 ) {
			$message = $result['response']['message'];
			if ( isset( $result['body']['errors'], $result['body']['errors']['title'] ) ) {
				$message = $result['body']['errors']['title'];
			}

			throw new MonduTradeResponseException(
				esc_html( $message ),
				esc_html( $result['response']['code'] ),
				wp_json_encode( $result['body'] ),
			);
		}

		return $result;
	}
}
