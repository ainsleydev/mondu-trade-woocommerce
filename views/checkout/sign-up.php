<?php

/**
 * Views - Checkout
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

?>

<!-- =====================
	Sign Up
	===================== -->
<div class="<?php echo esc_attr( apply_filters( 'mondu_trade_account_checkout_class', '' ) ); ?>">
	<fieldset id="wc-a-dev-trade-account" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
		<!-- Info -->
		<div class="form-row form-row-wide">
			<p style="margin-bottom: 10px;">
				You're not currently registered for a Trade Account, complete the form below to be redirected to Mondu.
			</p>
			<p>
				<?php
				printf( wp_kses( __( 'Information on the processing of your personal data by <strong>Mondu GmbH</strong> can be found <a href="https://mondu.ai/gdpr-notification-for-buyers" target="_blank">here</a>.', 'mondu' ), [
					'a' => [
						'href'   => [],
						'target' => [],
					],
				] ) );
				?>
			</p>
		</div>
		<!-- Submit Button, Note: Have to use a div here to prevent checkout submission -->
		<div class="form-row form-row-wide">
			<div id="a-dev-submit-trade-application" class="button alt btn btn-primary"
				 onclick="submitTradeAccountApplication(event)">Apply Now
			</div>
		</div>
	</fieldset>
</div>
