<?php

/**
 * Views - Admin
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

$domain = \MonduTrade\Plugin::DOMAIN;

?>

<!-- =====================
	Admin Options
	===================== -->
<div class="wrap">
	<!-- Title -->
	<h1><?php echo esc_html__( 'Mondu Trade Account', $domain ); ?></h1>
	<!-- Settings -->
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mondu_trade_account_settings' );
		do_settings_sections( 'mondu-trade-account' );
		//		submit_button();
		?>
	</form>
	<!-- Register Webhooks -->
	<h2><?php esc_html_e( 'Register Webhooks', $domain ); ?></h2>
	<?php if ( isset( $webhooks_error ) && null !== $webhooks_error ) : ?>
		<p><?php echo esc_html( $webhooks_error ); ?></p>
	<?php endif; ?>
	<?php if ( isset( $webhooks_registered ) && false !== $webhooks_registered ) : ?>
		<p> ✅ <?php esc_html_e( 'Webhooks registered', $domain ); ?>:
			<?php echo esc_html( date_i18n( get_option( 'date_format' ), $webhooks_registered ) ); ?>
		</p>
	<?php endif; ?>
	<form action='<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>' method='post'>
		<input type='hidden' name='action' value='mondu_trade_register_webhooks'/>
		<input type='hidden' name='security'
			   value='<?php echo esc_html( wp_create_nonce( 'mondu-trade-register-webhooks' ) ); ?>'/>
		<?php submit_button( __( 'Register Webhooks', $domain ) ); ?>
	</form>
	<!-- Download Logs -->
	<h2><?php esc_html_e( 'Download Logs', $domain ); ?></h2>
	<form action='<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>' method='post'>
		<input type='hidden' name='action' value='mondu_trade_download_logs'/>
		<input type='hidden' name='security'
			   value='<?php echo esc_html( wp_create_nonce( 'mondu-trade-download-logs' ) ); ?>'/>
		<table class="form-table" role="presentation">
			<tbody>
			<tr>
				<th scope="row">
					<label for="date"><?php esc_html_e( 'Log date', $domain ); ?>:</label>
				</th>
				<td>
					<input type='date' id='date' name='date' value="<?php echo esc_html( gmdate( 'Y-m-d' ) ); ?>"
						   required/>
				</td>
			</tr>
			</tbody>
		</table>
		<?php submit_button( __( 'Download Logs', $domain ) ); ?>
	</form>
</div>
