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
use MonduTrade\Util\Logger;
use MonduTrade\Mondu\BuyerStatus;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Checkout provides utility functions for the WooCommerce
 * checkout frontend.
 */
class Checkout {

	/**
	 * Buyer status messages.
	 *
	 * @var array-key
	 */
	const buyer_status_notices = [
		BuyerStatus::UNKNOWN   => [
			'type'    => 'error',
			'message' => 'An unknown error occurred. Please try again later.',
		],
		BuyerStatus::APPLIED   => [
			'type'    => 'notice',
			'message' => "Were just waiting to hear back from Mondu on your application, please refresh the page and try again.",
		],
		BuyerStatus::CANCELLED => [
			'type'    => 'error',
			'message' => 'Your Trade Account application was cancelled, please try again or select a different payment method.',
		],
		BuyerStatus::DECLINED  => [
			'type'    => 'error',
			'message' => 'Your Trade Account has been declined, please use an alternative payment method.',
		],
		BuyerStatus::ACCEPTED  => [
			'type'    => 'success',
			'message' => 'Your Trade Account has been approved.',
		],
		BuyerStatus::PENDING   => [
			'type'    => 'notice',
			'message' => 'Your Trade Account is pending. You will hear back in 48 hours.',
		]
	];

	/**
	 * Adds a WooCommerce notice dependent on the buyers
	 * application status.
	 *
	 * @return void
	 */
	public static function notices(): void {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended

		// Bail if it's a WC endpoint or the user isn't logged in/
		if ( is_wc_endpoint_url() || ! is_user_logged_in() || ! Plugin::has_mondu_trade_query_param() ) {
			return;
		}

		$customer = new Customer( get_current_user_id() );
		if ( ! $customer->is_valid() ) {
			Logger::error( 'Unable to retrieve customer in checkout', [
				'customer' => $customer->get_id(),
			] );

			return;
		}

		$status = $customer->get_mondu_trade_account_status();

		$notice = self::buyer_status_notices[ $status ] ?? [
			'type'    => 'error',
			'message' => 'An unexpected error occurred. Please contact support or try again later.',
		];

		wc_add_notice( $notice['message'], $notice['type'] );

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
		if ( ! is_checkout() || ! is_user_logged_in() || ! Plugin::has_mondu_trade_query_param() ) {
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
