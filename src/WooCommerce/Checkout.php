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
	public static function notices() {

		if ( ! is_checkout() || is_wc_endpoint_url() ) {
			return;
		}

		// Check for trade account error query param.
		if ( isset( $_GET[ TradeAccountController::QUERY_ERROR ] ) && 'true' === sanitize_text_field( $_GET[ TradeAccountController::QUERY_ERROR ] ) ) {
			wc_add_notice( __( 'Unfortunately your trade account could not be processed at this time, please try again.', Plugin::DOMAIN ), 'error' );

			return;
		}

		// Check for buyer status and redirect status.
		$buyer_status    = isset( $_GET[ TradeAccountController::QUERY_BUYER_STATUS ] ) ? sanitize_text_field( $_GET[ TradeAccountController::QUERY_BUYER_STATUS ] ) : '';
		$redirect_status = isset( $_GET[ TradeAccountController::QUERY_REDIRECT_STATUS ] ) ? sanitize_text_field( $_GET[ TradeAccountController::QUERY_REDIRECT_STATUS ] ) : '';

		// Display notices based on the query parameters.
		if ( $redirect_status === 'cancelled' ) {
			wc_add_notice( __( 'Your Trade Account application was cancelled, please try again.', Plugin::DOMAIN ), 'error' );

			return;
		}

		// If we've got this far, it means we've received the webhook
		// from Mondu and can accurately determine where the buyer
		// is in their application.
		switch ( $buyer_status ) {
			case BuyerStatus::ACCEPTED:
				wc_add_notice( __( 'Your trade account has been approved.', Plugin::DOMAIN ), 'success' );
				break;
			case BuyerStatus::PENDING:
				wc_add_notice( __( 'Your trade account is pending. You will hear back in 48 hours.', Plugin::DOMAIN ), 'notice' );
				break;
			case BuyerStatus::DECLINED:
				wc_add_notice( __( 'Your trade account has been declined, please use an alternative payment method.', Plugin::DOMAIN ), 'error' );
				break;
			default:
				wc_add_notice( __( `We couldn't process your trade account application, please try again or reach out to support.`, Plugin::DOMAIN ), 'error' );
				break;
		}
	}

	/**
	 * Selects the Trade Account gateway if the status
	 * is succeeded.
	 *
	 * @param $available_gateways
	 * @return mixed
	 */
	public static function select_default_gateway( $available_gateways ) {
		if ( ! is_checkout() ) {
			return $available_gateways;
		}

		$status = isset( $_GET[ TradeAccountController::QUERY_BUYER_STATUS ] ) ? sanitize_text_field( $_GET[ TradeAccountController::QUERY_BUYER_STATUS ] ) : '';

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
	}
}
