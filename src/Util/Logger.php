<?php

/**
 * Logger
 *
 * @package MonduTradeAccount
 * @author ainsley.dev
 */

namespace MonduTrade\Util;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

/**
 * Logger defines a utility class to Log to the
 * WooCommerce logger.
 */
final class Logger {

	/**
	 * Logger Context
	 *
	 * @var string
	 */
	public static string $context = 'mondu-trade';

	/**
	 * Log an info message.
	 *
	 * @param string $message The message to log.
	 * @param array $data Additional data to log.
	 */
	public static function info( string $message, array $data = [] ) {
		self::log( 'info', $message, $data );
	}

	/**
	 * Log a debug message.
	 *
	 * @param string $message The message to log.
	 * @param array $data Additional data to log.
	 */
	public static function debug( string $message, array $data = [] ) {
		self::log( 'debug', $message, $data );
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message The message to log.
	 * @param array $data Additional data to log.
	 */
	public static function error( string $message, array $data = [] ) {
		self::log( 'error', $message, $data );
	}

	/**
	 * Log a message to WooCommerce logs.
	 *
	 * @param string $level One of the following:
	 *      'emergency': System is unusable.
	 *      'alert': Action must be taken immediately.
	 *      'critical': Critical conditions.
	 *      'error': Error conditions.
	 *      'warning': Warning conditions.
	 *      'notice': Normal but significant condition.
	 *      'info': Informational messages.
	 *      'debug': Debug-level messages.
	 * @param string $message The message to log.
	 */
	private static function log( string $level, string $message, array $data = [] ) {
		$logger = wc_get_logger();
		$lines  = [
			'level'   => $level,
			'message' => $message,
		];
		if ( ! empty( $data ) ) {
			$lines = array_merge( $lines, [
				'data' => $data
			] );
		}
		$out = wc_print_r( $lines, true );
		$logger->log( $level, $out, [ 'source' => self::$context ] );

		if ( Environment::is_development() ) {
			error_log( json_encode( $lines ) );
		}
	}
}
