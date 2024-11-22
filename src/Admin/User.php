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
		add_action( 'personal_options_update', [ $this, 'prevent_trade_account_update' ] );
		add_action( 'edit_user_profile_update', [ $this, 'prevent_trade_account_update' ] );
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

		$uuid   = $customer->get_mondu_trade_account_uuid();
		$status = $customer->get_mondu_trade_account_status();
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
	 * Prevent unauthorized updates to the Mondu Trade Account data.
	 *
	 * @param int $user_id
	 */
	public function prevent_trade_account_update( int $user_id ) {
		check_admin_referer( 'update-user_' . $user_id );

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to edit this user.', 'mondu-trade-account' ) );
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
