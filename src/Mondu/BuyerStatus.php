<?php

/**
 * Mondu Buyer Status
 *
 * @package MonduTradeAccount
 */

namespace MonduTrade\Mondu;

/**
 * Buyer Status defines the statues for a Trade Account
 * buyer via Mondu.
 *
 * @see: https://docs.mondu.ai/docs/mondu-digital-trade-account
 */
final class BuyerStatus
{
	const ACCEPTED = 'accepted';
	const PENDING = 'pending';
	const DECLINED = 'declined';

	/**
	 * Obtains the Buyer Status values.
	 *
	 * @return string[]
	 */
	public static function get_values(): array
	{
		return [
			self::ACCEPTED,
			self::PENDING,
			self::DECLINED,
		];
	}

	/**
	 * Determines if an Buyer Status is valid.
	 *
	 * @param string $value
	 *
	 * @return bool
	 */
	public static function is_valid(string $value): bool
	{
		return in_array($value, self::get_values(), true);
	}
}



