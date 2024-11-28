<?php

/**
 * Views - Submit Trade Account
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

namespace MonduTrade\Forms;

use MonduTrade\Plugin;
use MonduTrade\WooCommerce\Notices;
use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\Exceptions\MonduTradeException;

/**
 * Renders the trade account view.
 */
class TradeAccountView {

	/**
	 * Render the trade account signup form
	 */
	public static function render() {
		// Prepare URLs and login status.
		$account_url  = get_permalink( wc_get_page_id( 'myaccount' ) );
		$current_url  = esc_url( home_url( add_query_arg( null, null ) ) );
		$redirect_url = add_query_arg( Plugin::QUERY_PARAM_REDIRECT, $current_url, $account_url );
		$is_logged_in = is_user_logged_in();

		// Start output.
		?>
		<div class="mondu-trade-account-form woocommerce">
			<!-- Header -->
			<h3 class="form-title">Sign Up for the Mondu Trade Account</h3>
			<p>Gain access to flexible payment options for your business via Mondu.</p>
			<!-- Form -->
			<?php if ( ! $is_logged_in ) : ?>
				<?php self::render_login_prompt( $redirect_url ); ?>
			<?php else : ?>
				<?php self::render_signup_content(); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render login prompt for non-logged-in users
	 *
	 * @param string $redirect_url URL to redirect to for login/registration
	 */
	private static function render_login_prompt( string $redirect_url ) {
		?>
		<p>You are not currently logged in, please register or login to apply for a Mondu Trade Account.</p>
		<a href="<?php echo esc_url( $redirect_url ); ?>" class="button alt">
			Register/Login
		</a>
		<?php
	}

	/**
	 * Render account signup content for logged-in users
	 */
	private static function render_signup_content() {
		try {
			$user_id = get_current_user_id();
			$status  = mondu_trade_get_buyer_status( $user_id );

			switch ( $status ) {
				case BuyerStatus::UNKNOWN:
					self::render_form();
					break;
				default:
					self::render_status_notice( $status );
					break;
			}
		} catch ( MonduTradeException $e ) {
			self::render_error_message();
		}
	}

	/**
	 * Render the signup form
	 */
	private static function render_form() {
		?>
		<form id="trade-account-shortcode-signup" method="POST"
			  action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
			<div class="form-wrapper">
				<!-- Action -->
				<input type="hidden" name="action" value="trade_account_submit">
				<!-- Nonce -->
				<?php wp_nonce_field( 'trade_account_submit', 'trade_account_submit_nonce' ); ?>
				<!-- GDPR -->
				<p class="mondu-trade-account-form-gdpr">
					<?php self::render_gdpr_notice(); ?>
				</p>
				<!-- Submit -->
				<div class="form-row">
					<button type="submit" class="button alt">Sign Up</button>
				</div>
			</div>
		</form>
		<?php
	}

	/**
	 * Render GDPR notice with safe HTML
	 */
	private static function render_gdpr_notice() {
		printf(
			wp_kses(
				__( 'Information on the processing of your personal data by <strong>Mondu GmbH</strong> can be found <a href="https://mondu.ai/gdpr-notification-for-buyers" target="_blank">here</a>.', 'mondu-trade-account' ),
				[
					'a'      => [
						'href'   => [],
						'target' => [],
					],
					'strong' => [],
				]
			)
		);
	}

	/**
	 * Render status notice based on buyer status
	 *
	 * @param string $status Buyer status
	 */
	private static function render_status_notice( string $status ) {
		$notice = Notices::buyer_status_notices[ $status ] ?? [
			'type'    => 'error',
			'message' => 'An unexpected error occurred. Please contact support or try again later.',
		];

		echo '<p class="' . esc_attr( $notice['type'] ) . '">' . esc_html( $notice['message'] ) . '</p>';
	}

	/**
	 * Render a generic error message
	 */
	private static function render_error_message() {
		echo '<p class="error">Something went wrong. Please try again later.</p>';
	}
}
