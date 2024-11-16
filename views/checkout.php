<?php

/**
 * Views - Checkout
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

use MonduTrade\Mondu\BuyerStatus;
use MonduTrade\WooCommerce\Customer;

$status      = BuyerStatus::UNDEFINED;
$buyer_limit = false;

if ( is_user_logged_in() ) {
	// If the user is logged in, we can obtain the status to
	// display different messages to the user.
	$user_id  = get_current_user_id();
	$customer = new Customer( $user_id );
	$status   = $customer->get_mondu_trade_account_status();

	// If the buyer has an accepted status, we can assume that they
	// probably have a buying limit to display to the user.
	if ( $status === BuyerStatus::ACCEPTED ) {
		$buyer_limit = $this->mondu_request_wrapper->get_buyer_limit();
	}
}

?>

<!-- =====================
	Not Logged In
	===================== -->
<?php if ( ! is_user_logged_in() ): ?>
	<div class="woocommerce-error" style="margin-bottom: 0; margin-top: 10px;">
		<a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>">Log in</a>
		to register to Pay using a Mondu Trade Account.
	</div>
<!-- =====================
	Accepted
	===================== -->
<?php elseif ( $status === BuyerStatus::ACCEPTED ):
	if ( $buyer_limit && isset( $buyer_limit['purchasing_limit']['purchasing_limit_cents'] ) ) {
		$purchasing_limit = $buyer_limit['purchasing_limit']['purchasing_limit_cents'] / 100; // Convert cents to pounds.
		echo '<p>Purchasing Limit: ' . wc_price( $purchasing_limit ) . '</p>';
	}
?>
<!-- =====================
	Pending
	===================== -->
<?php elseif ( $status === BuyerStatus::PENDING ): ?>
	<div class="woocommerce-info" style="margin-bottom: 0; margin-top: 10px;">
		Your trade account is currently pending, you should be notified within 48 hours of the decision.
	</div>
<!-- =====================
	Declined
	===================== -->
<?php elseif ( $status === BuyerStatus::DECLINED ): ?>
	<div class="woocommerce-error" style="margin-bottom: 0; margin-top: 10px;">
		You’ve been declined for a Mondu Trade Account. Please contact
		<a href="https://www.mondu.ai/contact/" target="_blank">support</a> for assistance.
	</div>
<!-- =====================
	Not Signed Up
	===================== -->
<?php else: ?>
	<fieldset id="wc-a-dev-trade-account" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
		<!-- Info -->
		<div class="form-row form-row-wide">
			<p>
				You're not currently registered for a Trade Account, compelete the form below to be redirected to
				Mondu
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
			<div id="a-dev-submit-trade-application" class="button alt"
				 onclick="submitTradeAccountApplication(event)">Apply Now
			</div>
		</div>
	</fieldset>
<?php endif; ?>
