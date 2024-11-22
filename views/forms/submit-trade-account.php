<?php

/**
 * Views - Submit Trade Account
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

$current_url  = esc_url( home_url( add_query_arg( null, null ) ) );
$is_logged_in = is_user_logged_in();

?>

<!-- =====================
	Trade Account Form
	===================== -->
<div class="mondu-trade-account-form woocommerce" style="margin-bottom: 2rem;">
	<!-- Title & Description -->
	<h3 class="form-title">Sign Up for the Mondu Trade Account</h3>
	<p class="form-description">
		Gain access to flexible payment options for your business via Mondu.
	</p>
	<!-- Redirect To Login -->
	<?php if ( ! $is_logged_in ) : ?>
		<p>You are not currently logged in, please register or login to apply for a Mondu Trade Account.</p>
		<a href="<?php echo esc_url( add_query_arg( 'mondu_trade_redirect_to', $current_url, get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ) ); ?>"
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
				<div class="form-row">
					<button type="submit" class="button alt">Submit</button>
				</div>
			</div>
		</form>
	<?php endif ?>
</div>
