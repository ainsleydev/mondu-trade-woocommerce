<?php

/**
 * Mondu - Buyer Status
 *
 * @package     MonduTradeAccount
 * @category    Mondu
 * @author      ainsley.dev
 */

namespace MonduTrade\Mondu;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Buyer Status defines the statues for a Trade Account
 * buyer via Mondu.
 *
 * @see: https://docs.mondu.ai/docs/mondu-digital-trade-account
 */
final class BuyerStatus {

	/**
	 * Unknown is the default buyer status when none is defined.
	 *
	 * @var string
	 */
	const UNKNOWN = 'unknown';

	/**
	 * Applied status is when a customer has tried to apply for
	 * a Trade Account, but the webhook hasn't been triggered.
	 *
	 * @var string
	 */
	const APPLIED = 'applied';

	/**
	 * Accepted is the state in which a customer has been
	 * approved a Trade Account, and they should have a
	 * buyer limit.
	 *
	 * @var string
	 */
	const ACCEPTED = 'accepted';

	/**
	 * Pending is the state in which a customer is waiting to
	 * hear from Mondu if their account has been accepted.
	 * (Maximum of 48 hours).
	 *
	 *
	 * @var string
	 */
	const PENDING = 'pending';

	/**
	 * Declined is the state in which a customer has been flat
	 * out refused credit from Mondu.
	 *
	 * @var string
	 */
	const DECLINED = 'declined';

	/**
	 * Obtains the Buyer Status values.
	 *
	 * @return string[]
	 */
	public static function get_values(): array {
		return [
			self::UNKNOWN,
			self::APPLIED,
			self::ACCEPTED,
			self::PENDING,
			self::DECLINED,
		];
	}

	/**
	 * Determines if an Buyer Status is valid.
	 *
	 * @param string $value
	 * @return bool
	 */
	public static function is_valid( string $value ): bool {
		return in_array( $value, self::get_values(), true );
	}
}



