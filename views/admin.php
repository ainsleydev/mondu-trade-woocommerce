<!-- =====================
	Admin Options
	===================== -->
<div class="wrap">
	<h1><?php echo esc_html__( 'Mondu Trade Account', 'ainsley-dev' ); ?></h1>
	<!-- Settings -->
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mondu_trade_account_settings' );
		do_settings_sections( 'mondu-trade-account' );
		submit_button();
		?>
	</form>
</div>
