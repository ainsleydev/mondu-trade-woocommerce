<?php

/**
 * User
 *
 * @package     MonduTradeAccount
 * @category    Admin
 * @author      ainsley.dev
 */

namespace MonduTrade\Admin;

use WP_User;
use MonduTrade\WooCommerce\Customer;

class User {

	/**
	 * User constructor.
	 */
	public function __construct() {
		// Display Mondu Trade Account section
		add_action( 'show_user_profile', [ $this, 'display_mondu_trade_account' ] );
		add_action( 'edit_user_profile', [ $this, 'display_mondu_trade_account' ] );

		// Add validation to prevent saving changes
		add_action( 'personal_options_update', [ $this, 'prevent_trade_account_update' ] );
		add_action( 'edit_user_profile_update', [ $this, 'prevent_trade_account_update' ] );
	}

	/**
	 * Display Mondu Trade Account details in the user profile.
	 *
	 * @param WP_User $user The user object.
	 */
	public function display_mondu_trade_account( WP_User $user ) {
		$customer = new Customer( $user->ID );
		if ( ! $customer->is_valid() ) {
			echo '<h3>Mondu Trade Account</h3><p>No trade account data available.</p>';

			return;
		}

		$uuid   = $customer->get_mondu_trade_account_uuid();
		$status = $customer->get_mondu_trade_account_status();

		include MONDU_TRADE_ACCOUNT_VIEW_PATH . '/admin/user.php';
	}

	/**
	 * Prevent unauthorized updates to the Mondu Trade Account data.
	 *
	 * @param int $user_id
	 */
	public function prevent_trade_account_update( int $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		// Fetch original customer data to ensure no changes are made,
		$customer        = new Customer( $user_id );
		$original_uuid   = $customer->get_mondu_trade_account_uuid();
		$original_status = $customer->get_mondu_trade_account_status();

		// If POST data has altered these fields, reset them,
		if ( isset( $_POST['mondu_trade_uuid'] ) && $_POST['mondu_trade_uuid'] !== $original_uuid ) {
			$_POST['mondu_trade_uuid'] = $original_uuid;
		}
		if ( isset( $_POST['mondu_trade_status'] ) && $_POST['mondu_trade_status'] !== $original_status ) {
			$_POST['mondu_trade_status'] = $original_status;
		}
	}
}
