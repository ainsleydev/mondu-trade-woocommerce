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
use MonduTrade\Util\Logger;
use InvalidArgumentException;
use MonduTrade\Mondu\BuyerStatus;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * MonduCustomer extends the WooCommerce Customer to allow for
 * additional fields for the Trade Account.
 *
 * Usage Example:
 *
 * $customer_id = 1;
 * $customer = new Customer($customer_id);
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
	 * Determines if the customer was retrieved successfully.
	 *
	 * @var bool
	 */
	private bool $valid = true;

	/**
	 * Meta key for Mondu Trade Account UUID
	 *
	 * @var string
	 */
	private const META_KEY_TRADE_ACCOUNT_UUID = 'mondu_trade_account_uuid';

	/**
	 * Meta key for Mondu Trade Account Status
	 *
	 * @var string
	 */
	private const META_KEY_TRADE_ACCOUNT_STATUS = 'mondu_trade_account_status';

	/**
	 * Constructor for the extended customer class.
	 *
	 * @param int $id
	 */
	public function __construct( int $id = 0 ) {
		try {
			parent::__construct( $id );
		} catch ( Exception $e ) {
			Logger::error( 'Obtaining the Mondu Trade Customer', [
				'id' => $id,
			] );
			$this->valid = false;
		}
	}

	/**
	 * Determines if the customer was retrieved successfully
	 * by checking for the user ID.
	 *
	 * @return bool
	 */
	public function is_valid(): bool {
		if ( ! $this->valid ) {
			return false;
		}

		if ( $this->get_id() === 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the Mondu Trade Account UUID.
	 *
	 * @return string
	 */
	public function get_mondu_trade_account_uuid(): string {
		return $this->get_meta( self::META_KEY_TRADE_ACCOUNT_UUID, true );
	}

	/**
	 * Set the Mondu Trade Account UUID.
	 *
	 * @param string $uuid Trade Account UUID
	 */
	public function set_mondu_trade_account_uuid( string $uuid ) {
		$this->update_meta_data( self::META_KEY_TRADE_ACCOUNT_UUID, sanitize_text_field( $uuid ) );
	}

	/**
	 * Get the Mondu Trade Account Status.
	 *
	 * @return string
	 */
	public function get_mondu_trade_account_status(): string {
		$status = $this->get_meta( self::META_KEY_TRADE_ACCOUNT_STATUS, true );

		if ( ! $status ) {
			return BuyerStatus::UNKNOWN;
		}

		return $status;
	}

	/**
	 * Set the Mondu Trade Account Status.
	 *
	 * @param string $status Trade Account Status
	 * @throws InvalidArgumentException if the status is invalid
	 */
	public function set_mondu_trade_account_status( string $status ) {
		if ( ! BuyerStatus::is_valid( $status ) ) {
			throw new InvalidArgumentException( 'Invalid status value provided.' );
		}
		$this->update_meta_data( self::META_KEY_TRADE_ACCOUNT_STATUS, sanitize_text_field( $status ) );
	}
}
