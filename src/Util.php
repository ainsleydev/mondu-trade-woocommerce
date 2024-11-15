<?php


namespace util;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

class Util {

	/**
	 * Log
	 *
	 * @param array $message
	 * @param string $level
	 */
	public static function log( array $message, $level = 'DEBUG' ) {
		$logger = wc_get_logger();
		$logger->log( $level, wc_print_r( $message, true ), [ 'source' => 'mondu-trade-account' ] );
	}
}

