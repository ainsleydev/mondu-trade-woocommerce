<?php

/**
 * Util - Assets
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
 * Assets is a helper class for registering scripts
 * and styles.
 */
final class Assets {

	/**
	 * Registers a script to WordPress.
	 *
	 * @param string $id
	 * @param string $path
	 * @param array $deps
	 * @return void
	 */
	public static function register_script( string $id, string $path, array $deps ): void {
		$id = self::get_id( $id );

		wp_register_script(
			$id,
			MONDU_TRADE_ASSETS_PATH . $path,
			$deps,
			MONDU_TRADE_PLUGIN_VERSION,
			true,
		);

		wp_enqueue_script( $id );
	}

	/**
	 * Registers a style to WordPress.
	 *
	 * @param string $id
	 * @param string $path
	 * @param array $deps
	 * @return void
	 */
	public static function register_style( string $id, string $path, array $deps ): void {
		$id = self::get_id( $id );

		wp_register_style(
			$id,
			MONDU_TRADE_ASSETS_PATH . $path,
			$deps,
			MONDU_TRADE_PLUGIN_VERSION,
		);

		wp_enqueue_style( $id );
	}

	/**
	 * Obtains the ID of the asset.
	 *
	 * @param string $id
	 * @return string
	 */
	private static function get_id( string $id ): string {
		return 'mondu-digital-trade-account-' . $id;
	}
}
