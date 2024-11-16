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
	 * Option name for webhooks registered timestamp.
	 */
	private const OPTION_WEBHOOKS_REGISTERED = '_mondu_trade_webhooks_registered';

	/**
	 * The key that's used to display messages to the user.
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
		Util::validate_nonce( 'mondu_trade_register_webhooks', 'mondu_trade_register_webhooks_nonce' );

		try {
			$webhooks = $this->mondu_request_wrapper->get_webhooks();

			$registered_topics = array_column( $webhooks, 'topic' );
			$required_topic    = 'buyer';

			if ( ! in_array( $required_topic, $registered_topics, true ) ) {
				$this->mondu_request_wrapper->register_buyer_webhooks();
			}

			update_option( self::OPTION_WEBHOOKS_REGISTERED, time() );

			Logger::debug( 'Successfully registered buyer webhooks' );

			wp_redirect( add_query_arg( self::ADMIN_MESSAGE_KEY, 'webhooks_registered', wp_get_referer() ) );
			exit;
		} catch ( \Exception $exception ) {
			delete_option( self::OPTION_WEBHOOKS_REGISTERED );

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
		$uuid = isset( $_POST['uuid'] ) ? sanitize_text_field( $_POST['uuid'] ) : '';
		Util::validate_nonce( 'mondu_trade_delete_webhook_' . $uuid, 'mondu_trade_delete_webhook_nonce' );

		try {
			$this->mondu_request_wrapper->api->delete_webhook( [ 'uuid' => $uuid ] );
			delete_option( self::OPTION_WEBHOOKS_REGISTERED );

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
			'webhooks_registered'     => __( 'Webhooks registered successfully.', Plugin::DOMAIN ),
			'webhook_register_failed' => __( 'Webhooks failed to register.', Plugin::DOMAIN ),
			'webhook_deleted'         => __( 'Webhook deleted successfully.', Plugin::DOMAIN ),
			'webhook_delete_failed'   => __( 'Failed to delete webhook.', Plugin::DOMAIN ),
		];

		$message_key = self::ADMIN_MESSAGE_KEY;
		if ( isset( $_GET[ $message_key ] ) && isset( $messages[ $_GET[ $message_key ] ] ) ) {
			return $messages[ $_GET[ $message_key ] ];
		}

		return null;
	}
}
