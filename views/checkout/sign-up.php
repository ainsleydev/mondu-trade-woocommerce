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
	Sign Up
	===================== -->
<div class="<?php echo esc_attr( apply_filters( 'mondu_trade_account_checkout_class', '' ) ); ?>">
	<div class="form-row form-row-wide">
		<p style="margin-bottom: 10px;">
			You're not currently registered for a Trade Account, place the order, and you will be redirected to Mondu, to create an account.
		</p>
		<p>
			<?php
			printf( wp_kses( __( 'Information on the processing of your personal data by <strong>Mondu GmbH</strong> can be found <a href="https://mondu.ai/gdpr-notification-for-buyers" target="_blank">here</a>.', 'mondu-digital-trade-account' ), [
				'a' => [
					'href'   => [],
					'target' => [],
				],
			] ) );
			?>
		</p>
	</div>
</div>
