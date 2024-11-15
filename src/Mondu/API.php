<?php
/**
 * Mondu API
 *
 * @package MonduTradeAccount
 */
namespace Mondu;

use util\Util;

/**
 * Class MonduAPI
 *
 * Handles communication with the Mondu API.
 */
class MonduAPI {
	/**
	 * Global settings retrieved from the WordPress options.
	 *
	 * @var array
	 */
	//private $global_settings;

	/**
	 * MonduAPI constructor.
	 */
	public function __construct() {
		//$this->global_settings = get_option('mondu_trade_account_options');
	}


	/**
	 * Sends a POST request to the Mondu API.
	 *
	 * @param string $path API endpoint path.
	 * @param array|null $body Data to be sent in the POST request.
	 * @return array Response from the API.
	 * @throws MonduException
	 * @throws ResponseException
	 */
	public function post($path, array $body = null) {
		return $this->request($path, 'POST', $body);
	}

	/**
	 * Sends a GET request to the Mondu API.
	 *
	 * @param string $path API endpoint path.
	 * @return array Response from the API.
	 * @throws MonduException
	 * @throws ResponseException
	 */
	public function get($path) {
		return $this->request($path, 'GET');
	}

	/**
	 * General request function for sending HTTP requests to the Mondu API.
	 *
	 * @param string $path API endpoint path.
	 * @param string $method HTTP method (GET, POST, PUT, DELETE).
	 * @param array|null $body Data to be sent in the request.
	 * @return array Response from the API.
	 * @throws MonduException
	 * @throws ResponseException
	 */
	protected function request($path, $method = 'GET', $body = null) {
		$url = 'https://api.demo.mondu.ai/api/v1';
		$url .= $path;

		$headers = [
			'Content-Type'     => 'application/json',
			'Api-Token'    => '1GNPFVLDAGDY1CLQ88J4UEZHNALRAN2Q',
			'X-Plugin-Name'    => 'woocommerce-trade-account',
			'X-Plugin-Version' => MONDU_TRADE_ACCOUNT_PLUGIN_VERSION,
		];

		$args = [
			'headers' => $headers,
			'method'  => $method,
			'timeout' => 30,
		];

		if ($body !== null) {
			$args['body'] = wp_json_encode($body);
		}

		// Log the request for debugging
		Util::log([
			'method' => $method,
			'url'    => $url,
			'body'   => $args['body'] ?? null,
		]);

		$response = wp_remote_request($url, $args);
		return $this->validate_response($url, $response);
	}

	/**
	 * Validates the API response from Mondu, throwing errors if needed.
	 *
	 * @param string $url API URL for the request.
	 * @param array|WP_Error $response Response from wp_remote_request.
	 * @return array Validated response.
	 * @throws MonduException
	 * @throws ResponseException
	 */
	private function validate_response($url, $response) {
		if (is_wp_error($response)) {
			throw new Exception($response->get_error_message(), $response->get_error_code());
		}

//		if (empty($response['response']['code']) || strpos((string)$response['response']['code'], '2') !== 0) {
//			$error_message = isset($response['body']) ? json_decode($response['body'], true)['message'] : 'Unexpected API error';
//			throw new Exception($error_message, $response['response']['code']);
//		}

		return $response;
	}
}
