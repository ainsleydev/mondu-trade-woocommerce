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
	Not Logged In
	===================== -->
<div class="<?php apply_filters( 'mondu_trade_account_checkout_class', '' ); ?>">
	<a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>">Log in</a>
	to register to Pay using a Mondu Trade Account.
</div>

