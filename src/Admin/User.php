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
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\Mondu\RequestWrapper;
use MonduTrade\WooCommerce\Customer;
use MonduTrade\Util\Logger;

/**
 * User allows admins to view information about the
 * Trade Account such as the UUID and status
 * associated with Mondu.
 */
class User {

	/**
	 * Mondu Request Wrapper.
	 *
	 * @var RequestWrapper
	 */
	private RequestWrapper $mondu_request_wrapper;

	/**
	 * User constructor.
	 */
	public function __construct() {
		$this->mondu_request_wrapper = new RequestWrapper();

		// Display Mondu Trade Account section
		add_action( 'show_user_profile', [ $this, 'display_mondu_trade_account' ] );
		add_action( 'edit_user_profile', [ $this, 'display_mondu_trade_account' ] );

		// Add validation to prevent saving changes
		add_action( 'personal_options_update', [ $this, 'trade_account_update' ] );
		add_action( 'edit_user_profile_update', [ $this, 'trade_account_update' ] );
	}

	/**
	 * Display Mondu Trade Account details in the user profile.
	 *
	 * @param WP_User $user The user object.
	 */
	public function display_mondu_trade_account( WP_User $user ) {
		if ( ! is_admin() ) {
			return;
		}

		$user_id  = $user->ID;
		$customer = new Customer( $user_id );

		if ( ! $customer->is_valid() ) {
			echo '<h3>' . esc_html__( 'Mondu Trade Account', 'mondu-trade-account' ) . '</h3>';
			echo '<p>' . esc_html__( 'No trade account data available.', 'mondu-trade-account' ) . '</p>';

			return;
		}

		$uuid        = $customer->get_mondu_trade_account_uuid();
		$status      = $customer->get_mondu_trade_account_status();
		$buyer_limit = false;

		if ( $status === BuyerStatus::ACCEPTED ) {
			try {
				$buyer_limit = $this->mondu_request_wrapper->get_buyer_limit( $user_id );

				if ( ( $buyer_limit['purchasing_limit'] ) !== null ) {
					$buyer_limit = $buyer_limit['purchasing_limit'];
				}
			} catch ( \Exception $e ) {
				Logger::error( 'Error obtaining buyer limit in admin', [
					'user_id' => $user_id,
					'error'   => $e->getMessage(),
				] );
			}
		}

		include MONDU_TRADE_VIEW_PATH . '/admin/user.php';
	}

	/**
	 * Update or prevent unauthorized updates to the
	 * Mondu Trade Account data.
	 *
	 * @param int $user_id
	 */
	public function trade_account_update( int $user_id ) {
		check_admin_referer( 'update-user_' . $user_id );

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to edit this user.', 'mondu-trade-account' ) );
		}

		// Fetch original customer data.
		$customer        = new Customer( $user_id );
		$original_uuid   = $customer->get_mondu_trade_account_uuid();
		$original_status = $customer->get_mondu_trade_account_status();

		// Prevent UUID tampering.
		if ( isset( $_POST['mondu_trade_uuid'] ) && $_POST['mondu_trade_uuid'] !== $original_uuid ) {
			$_POST['mondu_trade_uuid'] = $original_uuid;
		}

		// Allow admins to update the status field.
		if ( isset( $_POST['mondu_trade_status'] ) && current_user_can( 'administrator' ) ) {
			$new_status = sanitize_text_field( wp_unslash( $_POST['mondu_trade_status'] ) );

			// Ensure the new status is valid.
			if ( BuyerStatus::is_valid( $new_status ) && $new_status !== $original_status ) {
				$customer->set_mondu_trade_account_status( $new_status );
			}
		} else {
			// Prevent unauthorized status change
			if ( isset( $_POST['mondu_trade_status'] ) && $_POST['mondu_trade_status'] !== $original_status ) {
				$_POST['mondu_trade_status'] = $original_status;
			}
		}
	}
}
