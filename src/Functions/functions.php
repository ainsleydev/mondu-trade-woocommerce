<?php

/**
 * Functions
 *
 * @package     MonduTradeAccount
 * @category    Functions
 * @author      ainsley.dev
 */

use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\Mondu\RequestWrapper;
use MonduTrade\WooCommerce\Customer;
use MonduTrade\Exceptions\MonduTradeException;

if ( ! function_exists( 'mondu_trade_get_buyer_status' ) ) {

	/**
	 * Obtains the current status for a customer.
	 *
	 * Throws an error if the customer ID is not valid.
	 *
	 * @param int $customer_id
	 * @return string
	 * @throws MonduTradeException
	 */
	function mondu_trade_get_buyer_status( int $customer_id ): string {
		$customer = new Customer( $customer_id );

		if ( ! $customer->is_valid() ) {
			throw new MonduTradeException( 'Invalid Mondu Trade customer ID: ' . esc_html( $customer_id ) );
		}

		return $customer->get_mondu_trade_account_status();
	}
}

if ( ! function_exists( 'mondu_trade_get_buyer_limit' ) ) {

	/**
	 * Obtains the buyer limit for a customer.
	 *
	 * Throws an error if the customer ID is not valid or the
	 * buyer status is not accepted.
	 *
	 * @param int $customer_id
	 * @return array
	 * @throws MonduTradeException
	 */
	function mondu_trade_get_buyer_limit( int $customer_id ): array {
		$api      = new RequestWrapper();
		$customer = new Customer( $customer_id );

		if ( ! $customer->is_valid() ) {
			throw new MonduTradeException( 'Invalid Mondu Trade customer ID: ' . esc_html( $customer_id ) );
		}

		$status = $customer->get_mondu_trade_account_status();
		if ( $status !== BuyerStatus::ACCEPTED ) {
			throw new MonduTradeException( 'Customer has not been accepted by Mondu for a Digital Trade Account, customer ID: ' . esc_html( $customer_id ) );
		}

		return $api->get_buyer_limit( $customer_id );
	}
}


