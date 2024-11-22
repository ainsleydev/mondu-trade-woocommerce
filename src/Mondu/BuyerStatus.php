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
final class BuyerStatus
{
	const UNKNOWN = 'unknown';
	const ACCEPTED = 'accepted';
	const PENDING = 'pending';
	const DECLINED = 'declined';

	const APPLIED = 'applied';

	/**
	 * Obtains the Buyer Status values.
	 *
	 * @return string[]
	 */
	public static function get_values(): array
	{
		return [
			self::UNKNOWN,
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
	public static function is_valid(string $value): bool
	{
		return in_array($value, self::get_values(), true);
	}
}



