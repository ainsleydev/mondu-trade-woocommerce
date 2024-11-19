<?php

/**
 * Views - Admin
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

use MonduTrade\Plugin;
use MonduTrade\Util\Environment;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

$domain = Plugin::DOMAIN;
$is_development = Environment::is_development()

?>

<!-- =====================
	Admin Options
	===================== -->
<div class="wrap">
	<!-- Title -->
	<h1><?php echo esc_html__( 'Mondu Trade Account', $domain ); ?></h1>
	<!-- Messages -->
	<?php if ( ! empty( $message ) ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
	<?php endif; ?>
	<!-- Settings -->
	<form method="post" action="options.php">
		<?php
		settings_fields( 'mondu_trade_account_settings' );
		do_settings_sections( 'mondu-trade-account' );
		//		submit_button();
		?>
	</form>
	<!-- =====================
		Register Webhooks
		===================== -->
	<h2><?php esc_html_e( 'Register Webhooks', $domain ); ?></h2>
	<p>Register the associated webhooks for the
		<a href="https://docs.mondu.ai/docs/mondu-digital-trade-account" target="_blank">Digital Trade Account</a>.
	</p>
	<!-- Register -->
	<?php if ( isset( $webhooks_registered ) && false !== $webhooks_registered ) : ?>
		<p>✅ <?php esc_html_e( 'Webhooks registered', $domain ); ?>:
			<?php echo esc_html( date_i18n( get_option( 'date_format' ), $webhooks_registered ) ); ?>
		</p>
	<?php endif; ?>
	<!-- Button -->
	<form action='<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>' method='post'>
		<input type='hidden' name='action' value='mondu_trade_register_webhooks'/>
		<?php
		wp_nonce_field( 'mondu_trade_register_webhooks', 'mondu_trade_register_webhooks_nonce' );
		submit_button( __( 'Register Webhooks', $domain ) );
		?>
	</form>
	<!-- =====================
		Download Logs
		===================== -->
	<h2><?php esc_html_e( 'Download Logs', $domain ); ?></h2>
	<p>Downloads all logs from the mondu-trade domain.</p>
	<form action='<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>' method='post'>
		<input type='hidden' name='action' value='mondu_trade_download_logs'/>
		<?php wp_nonce_field( 'mondu_trade_download_logs', 'mondu_trade_download_logs_nonce' ); ?>
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
	<!-- =====================
		Webhooks
		===================== -->
	<h2><?php esc_html_e( 'Current Webhooks', $domain ); ?></h2>
	<!-- Table -->
	<?php if ( ! empty( $webhooks ) ) : ?>
		<p>The registered webhooks are shown below.</p>
		<table class="wp-list-table widefat fixed striped">
			<thead>
			<tr>
				<th><?php esc_html_e( 'UUID', $domain ); ?></th>
				<th><?php esc_html_e( 'Topic', $domain ); ?></th>
				<th><?php esc_html_e( 'Address', $domain ); ?></th>
				<?php if ($is_development): ?>
					<th><?php esc_html_e( 'Actions', $domain ); ?></th>
				<?php endif; ?>
			</tr>
			</thead>
			<tbody>
			<?php foreach ( $webhooks as $webhook ) : ?>
				<tr>
					<td><?php echo esc_html( $webhook['uuid'] ); ?></td>
					<td><?php echo esc_html( $webhook['topic'] ); ?></td>
					<td><?php echo esc_html( $webhook['address'] ); ?></td>
					<?php if ($is_development): ?>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'mondu_trade_delete_webhook_' . $webhook['uuid'], 'mondu_trade_delete_webhook_nonce' ); ?>
								<input type="hidden" name="action" value="mondu_trade_delete_webhook">
								<input type="hidden" name="uuid" value="<?php echo esc_attr( $webhook['uuid'] ); ?>">
								<?php submit_button( __( 'Delete', $domain ), 'delete', 'submit', false ); ?>
							</form>
						</td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php else : ?>
		<p><?php esc_html_e( 'No webhooks found.', $domain ); ?></p>
	<?php endif; ?>
</div>

