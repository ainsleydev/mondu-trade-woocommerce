<?php

/**
 * Views - Checkout
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

?>

<!-- =====================
	Pending
	===================== -->
<div class="<?php echo esc_attr( apply_filters( 'mondu_trade_account_checkout_class', '' ) ); ?>">
	<div class="woocommerce-info" style="margin-bottom: 0; margin-top: 10px;">
		Your trade account is currently pending, you should be notified within 48 hours of the decision.
	</div>
</div>
