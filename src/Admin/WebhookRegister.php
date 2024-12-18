<?php

/**
 * Admin - Webhook Register
 *
 * @package     MonduTradeAccount
 * @category    Admin
 */

namespace MonduTrade\Admin;

use MonduTrade\Plugin;
use MonduTrade\Util\Logger;
use MonduTrade\Util\Environment;
use MonduTrade\Mondu\RequestWrapper;
use Mondu\Exceptions\ResponseException;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Webhook Register handles all the webhook-related
 * functionality to register and delete Webhooks
 * via the Mondu API.
 */
class WebhookRegister {

	/**
	 * The key that's used to display messages to the user.
	 *
	 * @var string
	 */
	private const ADMIN_MESSAGE_KEY = "mondu_trade_message";

	/**
	 * Mondu Request Wrapper.
	 *
	 * @var RequestWrapper
	 */
	private RequestWrapper $mondu_request_wrapper;

	/**
	 * Initialize the class and set its properties.
	 */
	public function __construct() {
		$this->mondu_request_wrapper = new RequestWrapper();

		add_action( 'admin_post_mondu_trade_register_webhooks', [ $this, 'register' ] );
		add_action( 'admin_post_mondu_trade_delete_webhook', [ $this, 'delete' ] );
	}

	/**
	 * Registers the buyer webhooks.
	 *
	 * @return void
	 */
	public function register() {
		Util::validate_user_permissions();

		// Validate and sanitize the nonce input.
		$nonce = isset( $_POST['mondu_trade_register_webhooks_nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['mondu_trade_register_webhooks_nonce'] ) )
			: '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'mondu_trade_register_webhooks' ) ) {
			Util::die_after_security_check();

			return;
		}

		try {
			$webhooks = $this->mondu_request_wrapper->get_webhooks();

			$registered_topics = array_column( $webhooks, 'topic' );
			$required_topic    = 'buyer';

			if ( ! in_array( $required_topic, $registered_topics, true ) ) {
				$this->mondu_request_wrapper->register_buyer_webhooks();
			}

			update_option( Plugin::OPTION_WEBHOOKS_REGISTERED, time() );
			Logger::debug( 'Successfully registered buyer webhooks' );

			$secret = $this->mondu_request_wrapper->webhook_secret();
			update_option( Plugin::OPTION_WEBHOOKS_SECRET, $secret );
			Logger::debug( 'Successfully updated webhook secret' );

			wp_redirect( add_query_arg( self::ADMIN_MESSAGE_KEY, 'webhooks_registered', wp_get_referer() ) );
			exit;
		} catch ( \Exception $exception ) {
			delete_option( Plugin::OPTION_WEBHOOKS_REGISTERED );

			Logger::error( 'Registering buyer webhooks failed', [
				'error' => $exception->getMessage(),
			] );

			wp_redirect( add_query_arg( self::ADMIN_MESSAGE_KEY, 'webhook_register_failed', wp_get_referer() ) );
			exit;
		}
	}

	/**
	 * Get webhooks.
	 *
	 * @return array
	 * @throws ResponseException
	 */
	public function get(): array {
		return $this->mondu_request_wrapper->get_webhooks();
	}

	/**
	 * Handle webhook deletion.
	 *
	 * @return void
	 */
	public function delete() {
		Util::validate_user_permissions();

		if ( Environment::is_production() ) {
			exit;
		}

		// Validate and sanitize the UUID input.
		$uuid = isset( $_POST['uuid'] )
			? sanitize_text_field( wp_unslash( $_POST['uuid'] ) )
			: '';

		// Validate and sanitize the nonce input.
		$nonce = isset( $_POST['mondu_trade_delete_webhook_nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['mondu_trade_delete_webhook_nonce'] ) )
			: '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'mondu_trade_delete_webhook_' . $uuid ) ) {
			Util::die_after_security_check();

			return;
		}

		try {
			$this->mondu_request_wrapper->api->delete_webhook( [ 'uuid' => $uuid ] );
			delete_option( Plugin::OPTION_WEBHOOKS_REGISTERED );

			wp_redirect( add_query_arg( self::ADMIN_MESSAGE_KEY, 'webhook_deleted', wp_get_referer() ) );
			exit;
		} catch ( \Exception $e ) {
			Logger::error( 'Deleting webhook failed', [
				'uuid'  => $uuid,
				'error' => $e->getMessage(),
			] );

			wp_redirect( add_query_arg( self::ADMIN_MESSAGE_KEY, 'webhook_delete_failed', wp_get_referer() ) );
			exit;
		}
	}

	/**
	 * Get the message based on the 'mondu_trade_message' GET parameter.
	 *
	 * @return string|null
	 */
	public function get_message(): ?string {
		$messages = [
			'webhooks_registered'     => __( 'Webhooks registered successfully.', 'mondu-digital-trade-account' ),
			'webhook_register_failed' => __( 'Webhooks failed to register.', 'mondu-digital-trade-account' ),
			'webhook_deleted'         => __( 'Webhook deleted successfully.', 'mondu-digital-trade-account' ),
			'webhook_delete_failed'   => __( 'Failed to delete webhook.', 'mondu-digital-trade-account' ),
		];

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$message_key = self::ADMIN_MESSAGE_KEY;
		$message     = isset( $_GET[ $message_key ] )
			? sanitize_text_field( wp_unslash( $_GET[ $message_key ] ) )
			: '';
		// phpcs:enable

		if ( $message && isset( $messages[ $message ] ) ) {
			return $messages[ $message ];
		}

		return null;
	}
}
