<?php

/**
 * Frontend - Checkout Hooks & Actions
 *
 * @package     MonduTradeAccount
 * @category    Frontend
 * @author      ainsley.dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Adds a WooCommerce notice dependent on the buyers
 * application status.
 *
 * @return void
 */
function mondu_trade_checkout_account_notice() {
	if ( ! is_checkout() || is_wc_endpoint_url() ) {
		return;
	}

	$status = isset( $_GET['trade_account_status'] ) ? sanitize_text_field( $_GET['trade_account_status'] ) : '';

	if ( ! $status ) {
		return;
	}

	switch ( $status ) {
		case 'succeeded':
			wc_add_notice( 'Your trade account has been approved.', 'success' );
			break;

		case 'cancelled':
			wc_add_notice( 'Your trade account is pending. You will hear back in 48 hours.', 'notice' );
			break;

		case 'declined':
			wc_add_notice( 'Your trade account has been declined, please use an alternative payment method.', 'error' );
			break;

		default:
			wc_add_notice( sprintf( 'Trade Account Status: %s', esc_html( $status ) ), 'notice' );
	}
}

add_action( 'template_redirect', 'mondu_trade_checkout_account_notice' );
