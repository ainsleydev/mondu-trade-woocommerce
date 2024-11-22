<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Outputs a trade account signup form.
 */
function output_trade_account_form() {
	?>
	<form id="trade-account-signup" method="POST" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
		<form id="trade-account-signup" method="POST" action="<?php echo admin_url('admin-ajax.php'); ?>">
			<input type="hidden" name="action" value="trade_account_submit">
			<?php wp_nonce_field('trade_account_submit', 'trade_account_submit_nonce'); ?>
			<button type="submit">Submit</button>
		</form>
	</form>
	<?php
}

// Register a shortcode to display the form on any page
add_shortcode( 'trade_account_form', 'output_trade_account_form' );
