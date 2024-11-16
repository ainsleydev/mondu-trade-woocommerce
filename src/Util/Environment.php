<?php

/**
 * Environment
 *
 * @package MonduTradeAccount
 * @author ainsley.dev
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
	 *
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		return getenv( $key ) ?: $default;
	}

	/**
	 * Set the value of an environment variable.
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return void
	 */
	public static function set( string $key, string $value ): void {
		putenv( "$key=$value" );
	}

	/**
	 * Check if the current environment is development.
	 *
	 * @return bool True if APP_ENV is set to 'dev', false otherwise.
	 */
	public static function is_development(): bool {
		return self::get( 'APP_ENV' ) === 'dev';
	}

	/**
	 * Check if the current environment is production.
	 *
	 * @note Returns true if APP_ENV is unset.
	 *
	 * @return bool True if APP_ENV is set to 'production', false otherwise.
	 */
	public static function is_production(): bool {
		$env = self::get( 'APP_ENV' );
		if ( $env === '' ) {
			return true;
		}

		return $env === 'production';
	}
}
