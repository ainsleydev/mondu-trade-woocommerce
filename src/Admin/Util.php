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
	 * Validate a nonce for a given action and exits if
	 * the nonce was invalid.
	 *
	 * @param string $action
	 * @param string $query_arg
	 * @return true
	 */
	public static function validate_nonce( string $action, string $query_arg = '' ): bool {
		if ($query_arg === '') {
			$query_arg = $action;
		}

		$is_nonce_valid = check_admin_referer( $action, $query_arg );

		if ( ! $is_nonce_valid ) {
			wp_die(
				esc_html__( 'Invalid security token. Please try again.', 'mondu-trade-account' ),
				esc_html__( 'Bad Request', 'mondu-trade-account' ),
				[ 'response' => 400, 'back_link' => true ]
			);
		}

		return true;
	}
}
