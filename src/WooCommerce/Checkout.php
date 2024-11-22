<?php

/**
 * WooCommerce - Checkout
 *
 * @package     MonduTradeAccount
 * @category    WooCommerce
 * @author      ainsley.dev
 */

namespace MonduTrade\WooCommerce;

use MonduTrade\Plugin;
use MonduTrade\Controllers\TradeAccountController;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Checkout provides utility functions for the WooCommerce
 * checkout frontend.
 */
class Checkout {

	/**
	 * Adds a WooCommerce notice dependent on the buyers
	 * application status.
	 *
	 * @return void
	 */
	public static function notices(): void {
		// phpcs: disable WordPress.Security.NonceVerification.Recommended

		// Bail if it's not the checkout.
		if ( ! is_checkout() || is_wc_endpoint_url() ) {
			return;
		}

		// Validate that both keys exist before accessing them.
		if (
			isset( $_GET[ TradeAccountController::QUERY_NOTICE_TYPE ] ) &&
			isset( $_GET[ TradeAccountController::QUERY_MESSAGE ] )
		) {
			$type    = sanitize_text_field( wp_unslash( $_GET[ TradeAccountController::QUERY_NOTICE_TYPE ] ) );
			$message = sanitize_text_field( wp_unslash( $_GET[ TradeAccountController::QUERY_MESSAGE ] ) );

			wc_add_notice( $message, $type );
		}

		// phpcs:enable
	}

	/**
	 * Selects the Trade Account gateway if the status
	 * is succeeded.
	 *
	 * @param $available_gateways
	 * @return mixed
	 */
	public static function select_default_gateway( $available_gateways ) {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! is_checkout() ) {
			return $available_gateways;
		}

		$status = isset( $_GET[ TradeAccountController::QUERY_BUYER_STATUS ] ) ?
			sanitize_text_field( wp_unslash( $_GET[ TradeAccountController::QUERY_BUYER_STATUS ] ) ) : '';

		if ( $status !== 'succeeded' ) {
			return $available_gateways;
		}

		foreach ( $available_gateways as $gateway_id => $gateway ) {
			if ( $gateway_id === Plugin::PAYMENT_GATEWAY_NAME ) {
				$gateway->chosen = true;
				continue;
			}
			$gateway->chosen = false;
		}

		return $available_gateways;

		// phpcs:enable
	}
}
