<?php

/**
 * WooCommerce - Customer
 *
 * @package     MonduTradeAccount
 * @category    WooCommerce
 * @author      ainsley.dev
 */

namespace MonduTrade\WooCommerce;

use Exception;
use WC_Customer;
use MonduTrade\Mondu\BuyerStatus;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * MonduCustomer extends the WooCommerce Customer to allow for
 * additional fields for the Trade Account.
 *
 * $customer_id = 1;
 * $customer = new WC_Customer_Extended($customer_id);
 *
 * $customer->set_mondu_trade_account_uuid('sample-uuid-value');
 * $customer->set_mondu_trade_account_status('pending');
 *
 * $customer->save();
 *
 * echo $customer->get_mondu_trade_account_uuid();
 * echo $customer->get_mondu_trade_account_status();
 */
class Customer extends WC_Customer {
	/**
	 * Constructor for the extended customer class.
	 *
	 * @param int $id
	 *
	 * @throws Exception
	 */
	public function __construct( int $id = 0 ) {
		parent::__construct( $id );
	}

	/**
	 * Get the Mondu Trade Account UUID.
	 *
	 * @return string
	 */
	public function get_mondu_trade_account_uuid(): string {
		return $this->get_meta( 'mondu_trade_account_uuid', true );
	}

	/**
	 * Set the Mondu Trade Account UUID.
	 *
	 * @param string $uuid
	 */
	public function set_mondu_trade_account_uuid( string $uuid ) {
		$this->update_meta_data( 'mondu_trade_account_uuid', sanitize_text_field( $uuid ) );
	}

	/**
	 * Get the Mondu Trade Account Status.
	 *
	 * @return string
	 */
	public function get_mondu_trade_account_status(): string {
		return $this->get_meta( 'mondu_trade_account_status', true );
	}

	/**
	 * Set the Mondu Trade Account Status.
	 *
	 * @param string $status
	 */
	public function set_mondu_trade_account_status( string $status ) {
		if (!BuyerStatus::is_valid($status)) {
			throw new \InvalidArgumentException( 'Invalid status value provided.' );
		}
		$this->update_meta_data( 'mondu_trade_account_status', sanitize_text_field( $status ) );
	}
}
