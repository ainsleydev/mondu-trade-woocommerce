<?php if ( ! is_user_logged_in() ): ?>
	<div class="woocommerce-error" style="margin-bottom: 0; margin-top: 10px;">
		<a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>">Log in</a> to register to Pay using a
		Mondu Trade Account.
	</div>
<?php //elseif (true): ?>
<!--	<h4>You have 2 grand in your account</h4>-->
<?php else: ?>
	<fieldset id="wc-a-dev-trade-account" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
		<div class="form-row form-row-wide">
			<p>You're not currently registered for a Trade Account, compelete the form below to be redirected to
				Mondu</p>
		</div>
		<div class="form-row form-row-wide">
			<!-- Legal -->
			<div class="form-row form-row-wide" >
				<div style="display: flex; align-items: flex-start;">
					<input id="a-dev-data-protection" type="checkbox" name="data-protection" style="transform: translateY(6px);">
					<label for="a-dev-data-protection">I agree for Mondu to process my <a href="https://www.mondu.ai/en-gb/gdpr-notification-for-buyers-uk/">personal data</a> outlined in their protection regulation.</label>
				</div>
			</div>
			<!-- Clear -->
			<div class="clear"></div>
			<!-- Submit Button, Note: Have to use a div here to prevent checkout submission -->
			<div class="form-row form-row-wide">
				<div id="a-dev-submit-trade-application" class="button alt"
					 onclick="submitTradeAccountApplication(event)">Apply Now
				</div>
			</div>
		</div>
	</fieldset>
<?php endif; ?>
