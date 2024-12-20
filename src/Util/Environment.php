<?php

/**
 * Util - Environment
 *
 * @package     MonduTradeAccount
 * @category    Util
 * @author      ainsley.dev
 */

namespace MonduTrade\Util;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Environment defines a utility class to manage environment
 * variables and check if the app is in production/dev.
 */
final class Environment {

	/**
	 * Get the value of an environment variable.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		return $_ENV[ $key ] ?? getenv( $key ) ?: $default; // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	/**
	 * Set the value of an environment variable.
	 *
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public static function set( string $key, string $value ): void {
		putenv( "$key=$value" );
	}

	/**
	 * Check if the current environment is development.
	 * Returns true if MONDU_TRADE_ENV is set to 'dev', false otherwise.
	 *
	 * @return bool
	 */
	public static function is_development(): bool {
		return self::get( 'MONDU_TRADE_ENV' ) === 'dev';
	}

	/**
	 * Check if the current environment is production.
	 * Returns true if MONDU_TRADE_ENV is set to 'production' or an
	 * empty string, false otherwise.
	 *
	 * @return bool
	 */
	public static function is_production(): bool {
		$env = self::get( 'MONDU_TRADE_ENV' );
		if ( $env === '' ) {
			return true;
		}

		return $env === 'production';
	}
}
