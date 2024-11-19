<?php

/**
 * Views - Checkout
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

use MonduTrade\Util\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

$purchasing_limit = [];

// Obtain the buyer purchasing limit from the API.
try {
	$buyer_limit = $this->mondu_request_wrapper->get_buyer_limit();
	if ( ! isset( $buyer_limit['purchasing_limit'] ) ) {
		throw new Exception( 'Undefined purchasing limit' );
	}
	$purchasing_limit = $buyer_limit['purchasing_limit'];
} catch ( Exception $e ) {
	Logger::error( 'Getting buyer limit', [
		'error' => $e->getMessage(),
	] );
	echo '<p>Unable to retrieve purchasing limit information at this time.</p>';
}

// Obtain the purchasing limit in cents.
$purchasing_limit_cents   = $purchasing_limit['purchasing_limit_cents'] ?? 0;
$balance_cents            = $purchasing_limit['balance_cents'] ?? 0;
$max_purchase_value_cents = $purchasing_limit['max_purchase_value_cents'] ?? 0;

// Convert cents to pounds.
$purchasing_limit   = $purchasing_limit_cents / 100;
$balance            = $balance_cents / 100;
$max_purchase_value = $max_purchase_value_cents / 100;

?>

<!-- =====================
	Accepted
	===================== -->
<div class="<?php echo esc_attr( apply_filters( 'mondu_trade_account_checkout_class', '' ) ); ?>">
	<p>Balance: <?php echo wc_price( $balance ); ?> </p>
	<p>Maximum Purchase Value: <?php echo wc_price( $max_purchase_value ); ?> </p>
	<p>Purchasing Limit: <?php echo wc_price( $purchasing_limit ); ?> </p>
</div>
