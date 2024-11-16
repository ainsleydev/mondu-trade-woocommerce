<?php

/**
 * Encryption - Signature Verifier
 *
 * @package     MonduTradeAccount
 * @category    Encryption
 * @author      ainsley.dev
 */

namespace MonduTrade\Encryption;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Signature Verifier to validate the webhooks to
 * ensure there is no man-in-the-middle attacks.
 */
class SignatureVerifier {

	/**
	 * Webhook secret.
	 *
	 * @var false|mixed|null
	 */
	private $secret;

	/**
	 * Signature constructor.
	 */
	public function __construct() {
		$this->secret = get_option( '_mondu_webhook_secret' );
	}

	/**
	 * Get secret.
	 *
	 * @return string
	 */
	public function get_secret(): string {
		return $this->secret;
	}

	/**
	 * Set secret.
	 *
	 * @param string $secret Secret.
	 * @return SignatureVerifier
	 */
	public function set_secret( string $secret ): SignatureVerifier {
		$this->secret = $secret;

		return $this;
	}

	/**
	 * Create HMAC.
	 *
	 * @param string $payload Payload.
	 * @return string
	 */
	public function create_hmac( $payload ): string {
		return hash_hmac( 'sha256', $payload, $this->secret );
	}

	/**
	 * Verify signature.
	 *
	 * @param $signature
	 * @return bool
	 */
	public function verify( $signature ): bool {
		return $this->secret === $signature;
	}
}
