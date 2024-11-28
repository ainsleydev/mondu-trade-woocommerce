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
		$file_path = MONDU_TRADE_ASSETS_DIR . $path;
		$url_path  = MONDU_TRADE_ASSETS_PATH . $path;
		$id        = self::get_id( $id );

		wp_register_script(
			$id,
			$url_path,
			$deps,
			self::get_version( $file_path ),
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
		$file_path = MONDU_TRADE_ASSETS_DIR . $path;
		$url_path  = MONDU_TRADE_ASSETS_PATH . $path;
		$id        = self::get_id( $id );

		wp_register_style(
			$id,
			$url_path,
			$deps,
			self::get_version( $file_path ),
			'all',
		);

		wp_enqueue_style( $id );
	}


	/**
	 * Obtains version for cache busting.
	 *
	 * @param string $file_path
	 * @return false|int
	 */
	private static function get_version( string $file_path ) {
		return file_exists( $file_path ) ? filemtime( $file_path ) : false;
	}

	/**
	 * Obtains the ID of the asset.
	 *
	 * @param string $id
	 * @return string
	 */
	private static function get_id( string $id ): string {
		return 'mondu-trade-account-' . $id;
	}
}
