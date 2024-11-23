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
use MonduTrade\Util\Logger;
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\Mondu\RequestWrapper;
use MonduTrade\WooCommerce\Customer;

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
		add_action( 'personal_options_update', [ $this, 'user_update_trade_account' ] );
		add_action( 'edit_user_profile_update', [ $this, 'user_update_trade_account' ] );
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
	public function user_update_trade_account( int $user_id ) {
		check_admin_referer( 'update-user_' . $user_id );

		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to edit this user.', 'mondu-trade-account' ) );
		}

		$customer = new Customer( $user_id );

		// Updates the Customer Status (if admin).
		$this->update_customer_field(
			$customer,
			'mondu_trade_uuid',
			'get_mondu_trade_account_uuid',
			'set_mondu_trade_account_uuid'
		);

		// Updates the Customer UUID (if admin).
		$this->update_customer_field(
			$customer,
			'mondu_trade_status',
			'get_mondu_trade_account_status',
			'set_mondu_trade_account_status',
			[ BuyerStatus::class, 'is_valid' ]
		);

		$this->update_uuid( $customer );
		$this->update_uuid( $customer );
	}

	/**
	 * Updates a field in the Customer object.
	 *
	 * @param Customer $customer The customer object.
	 * @param string $field The field name in the $_POST array.
	 * @param string $getter The method name to get the current value.
	 * @param string $setter The method name to set the new value.
	 * @param callable|null $validation Optional validation callback for the new value.
	 * @return void
	 */
	private function update_customer_field(
		Customer $customer,
		string $field,
		string $getter,
		string $setter,
		callable $validation = null
	): void {
		$original_value = $customer->$getter();

		// Prevent unauthorized changes.
		if ( ! $this->is_admin() ) {
			if ( isset( $_POST[ $field ] ) && $_POST[ $field ] !== $original_value ) {  // phpcs:disable WordPress.Security.NonceVerification.Missing
				$_POST[ $field ] = $original_value;
			}

			return;
		}

		// Skip if the field is not set in the POST data.
		if ( ! isset( $_POST[ $field ] ) ) {
			return;
		}

		$new_value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );

		// Skip if the value has not changed.
		if ( $new_value === $original_value ) {
			return;
		}

		// Validate the new value if a validation callback is provided.
		if ( $validation && ! $validation( $new_value ) ) {
			return;
		}

		// Update the field in the Customer object.
		$customer->$setter( $new_value );
	}

	/**
	 * Determines if the current user is an administrator.
	 *
	 * @return bool
	 */
	private function is_admin(): bool {
		return current_user_can( 'administrator' );
	}
}
