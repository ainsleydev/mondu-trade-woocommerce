<?php

/**
 * Views - Admin
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
	User
	===================== -->
<h3>Mondu Trade Account</h3>
<p>This section displays the unique identifier (UUID) and current status of the user's Mondu Trade Account.</p>
<table class="form-table">
	<!-- UUID -->
	<tr>
		<th>
			<label for="mondu_trade_uuid">UUID</label>
		</th>
		<td>
			<input type="text"
			       id="mondu_trade_uuid"
			       readonly
				   value="<?php echo isset( $uuid ) ? esc_attr( $uuid ) : ''; ?>" class="regular-text"
			/>
		</td>
	</tr>
	<!-- Status -->
	<tr>
		<th><label for="mondu_trade_status">Status</label></th>
		<td>
			<input type="text"
			       id="mondu_trade_status"
			       readonly
				   value="<?php echo isset( $status ) ? esc_attr( $status ) : ''; ?>" class="regular-text"
			/>
		</td>
	</tr>
</table>
