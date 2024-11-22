<?php

/**
 * Views - Checkout
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

use MonduTrade\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

$account_url  = get_permalink( wc_get_page_id( 'myaccount' ) );
$redirect_url = add_query_arg( Plugin::QUERY_PARAM_REDIRECT, wc_get_checkout_url(), $account_url );

?>

<!-- =====================
	Not Logged In
	===================== -->
<div class="<?php apply_filters( 'mondu_trade_account_checkout_class', '' ); ?>">
	<a href="<?php echo esc_url( $redirect_url ); ?>">Log in</a>
	to register to Pay using a Mondu Trade Account.
</div>

