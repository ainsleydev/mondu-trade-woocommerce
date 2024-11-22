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
use MonduTrade\Mondu\BuyerStatus;
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
		// phpcs:disable WordPress.Security.NonceVerification.Recommended

		// Bail if it's not the checkout.
		if ( ! is_checkout() || is_wc_endpoint_url() ) {
			return;
		}

		// Check for trade account error query param.
		if ( isset( $_GET[ TradeAccountController::QUERY_ERROR ] ) &&
		     'true' === sanitize_text_field( wp_unslash( $_GET[ TradeAccountController::QUERY_ERROR ] ) ) ) {
			wc_add_notice( __( 'Unfortunately your trade account could not be processed at this time, please try again.', 'mondu-trade-account' ), 'error' );

			return;
		}

		// Check for buyer status and redirect status.
		$buyer_status    = isset( $_GET[ TradeAccountController::QUERY_BUYER_STATUS ] ) ? sanitize_text_field( wp_unslash( $_GET[ TradeAccountController::QUERY_BUYER_STATUS ] ) ) : '';
		$redirect_status = isset( $_GET[ TradeAccountController::QUERY_REDIRECT_STATUS ] ) ? sanitize_text_field( wp_unslash( $_GET[ TradeAccountController::QUERY_REDIRECT_STATUS ] ) ) : '';

		// Display notices based on the query parameters.
		if ( $redirect_status === 'cancelled' ) {
			wc_add_notice( __( 'Your Trade Account application was cancelled, please try again.', 'mondu-trade-account' ), 'error' );

			return;
		}

		// If we've got this far, it means we've (probably) received
		// the webhook from Mondu and can accurately determine where
		// the buyer is in their application.
		switch ( $buyer_status ) {
			case BuyerStatus::ACCEPTED:
				wc_add_notice( __( 'Your trade account has been approved.', 'mondu-trade-account' ), 'success' );
				break;
			case BuyerStatus::PENDING:
				wc_add_notice( __( 'Your trade account is pending. You will hear back in 48 hours.', 'mondu-trade-account' ), 'notice' );
				break;
			case BuyerStatus::DECLINED:
				wc_add_notice( __( 'Your trade account has been declined, please use an alternative payment method.', 'mondu-trade-account' ), 'error' );
				break;
			case BuyerStatus::APPLIED:
				wc_add_notice( __( "We're just waiting to hear back from Mondu, please refresh the page.", 'mondu-trade-account' ), 'notice' );
				break;
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
