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
	Declined
	===================== -->
<div class="<?php echo esc_attr( apply_filters( 'mondu_trade_account_checkout_class', '' ) ); ?>">
	You’ve been declined for a Mondu Trade Account. Please contact
	<a href="https://www.mondu.ai/contact/" target="_blank">support</a> for assistance.
</div>
