<?php

/**
 * Views - Submit Trade Account
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

use MonduTrade\Plugin;

$account_url  = get_permalink( wc_get_page_id( 'myaccount' ) );
$current_url  = esc_url( home_url( add_query_arg( null, null ) ) );
$redirect_url = add_query_arg( Plugin::QUERY_PARAM_REDIRECT, $current_url, $account_url );
$is_logged_in = is_user_logged_in();

?>

<!-- =====================
	Trade Account Form
	===================== -->
<div class="mondu-trade-account-form woocommerce">
	<!-- Title & Description -->
	<h3 class="form-title">Sign Up for the Mondu Trade Account</h3>
	<p>
		Gain access to flexible payment options for your business via Mondu.
	</p>
	<!-- Redirect To Login -->
	<?php if ( ! $is_logged_in ) : ?>
		<p>You are not currently logged in, please register or login to apply for a Mondu Trade Account.</p>
		<a href="<?php echo esc_url( $redirect_url ); ?>"
		   class="button alt">
			Register/Login
		</a>
	<!-- Form -->
	<?php else : ?>
		<form id="trade-account-shortcode-signup" method="POST"
			  action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
			<div class="form-wrapper">
				<input type="hidden" name="action" value="trade_account_submit">
				<?php wp_nonce_field( 'trade_account_submit', 'trade_account_submit_nonce' ); ?>
				<p class="mondu-trade-account-form-gdpr">
					<?php
					printf( wp_kses( __( 'Information on the processing of your personal data by <strong>Mondu GmbH</strong> can be found <a href="https://mondu.ai/gdpr-notification-for-buyers" target="_blank">here</a>.', 'mondu-trade-account' ), [
						'a' => [
							'href'   => [],
							'target' => [],
						],
					] ) );
					?>
				</p>
				<div class="form-row">
					<button type="submit" class="button alt">Sign Up</button>
				</div>
			</div>
		</form>
	<?php endif ?>
</div>
