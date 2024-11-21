<?php

/**
 * Nonce
 *
 * @package     MonduTradeAccount
 * @category    Util
 * @author      ainsley.dev
 */

namespace MonduTrade\Admin;

use MonduTrade\Plugin;

/**
 * Admin Util defines utility functions for admin
 * functions and options.
 */
final class Util {

	/**
	 * Checks if the user can edit options and exits if they can't.
	 *
	 * @return void
	 */
	public static function validate_user_permissions(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You do not have sufficient permissions to access this page.', 'mondu-trade-account' )
			);
		}
	}

	/**
	 * Dies WP if the nonce was invalid.
	 */
	public static function die_after_security_check(): void {
		wp_die(
			esc_html__( 'Invalid security token. Please try again.', 'mondu-trade-account' ),
			esc_html__( 'Bad Request', 'mondu-trade-account' ),
			[ 'response' => 400, 'back_link' => true ]
		);
	}
}
