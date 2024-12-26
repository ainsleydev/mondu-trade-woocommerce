<?php

/**
 * Views - Admin
 *
 * @package     MonduTradeAccount
 * @category    Views
 * @author      ainsley.dev
 */

use MonduTrade\Mondu\BuyerStatus;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

$is_admin = current_user_can( 'administrator' );

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
				   name="mondu_trade_uuid"
				<?php if ( ! $is_admin ) {
					echo 'readonly';
				} ?>
				   class="regular-text"
				   value="<?php echo isset( $uuid ) ? esc_attr( $uuid ) : ''; ?>"
			/>
		</td>
	</tr>
	<!-- Status -->
	<tr>
		<th><label for="mondu_trade_status">Status</label></th>
		<td>
			<select id="mondu_trade_status" name="mondu_trade_status"
					class="regular-text" <?php echo ! $is_admin ? 'disabled' : ''; ?>>
				<?php foreach ( BuyerStatus::get_values() as $buyer_status ) : ?>
					<option
						value="<?php echo esc_attr( $buyer_status ); ?>" <?php echo ( $status ?? '' ) === $buyer_status ? 'selected' : ''; ?>>
						<?php echo esc_html( ucfirst( $buyer_status ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<!-- Buyer Limit -->
	<?php if ( isset( $buyer_limit ) && is_array( $buyer_limit ) ): ?>
		<!-- Purchasing Limit Cents -->
		<?php if ( isset( $buyer_limit['purchasing_limit_cents'] ) ): ?>
			<tr>
				<th>
					<label for="mondu_trade_purchasing_limit_cents">Purchasing Limit:</label>
				</th>
				<td>
					<input type="text"
						   readonly
						   id="mondu_trade_purchasing_limit_cents"
						   class="regular-text"
						   value="<?php echo esc_html( number_format( $buyer_limit['purchasing_limit_cents'] / 100, 2 ) ); ?>"
					/>
				</td>
			</tr>
		<?php endif; ?>
		<!-- Balance Cents -->
		<?php if ( isset( $buyer_limit['balance_cents'] ) ): ?>
			<tr>
				<th>
					<label for="mondu_trade_balance_cents">Balance:</label>
				</th>
				<td>
					<input type="text"
						   readonly
						   id="mondu_trade_balance_cents"
						   class="regular-text"
						   value="<?php echo esc_html( number_format( $buyer_limit['balance_cents'] / 100, 2 ) ); ?>"
					/>
				</td>
			</tr>
		<?php endif; ?>
		<!-- Max Purchase Value Cents -->
		<?php if ( isset( $buyer_limit['max_purchase_value_cents'] ) ): ?>
			<tr>
				<th>
					<label for="mondu_trade_max_purchase_value_cents">Max Purchase Value:</label>
				</th>
				<td>
					<input type="text"
						   readonly
						   id="mondu_trade_max_purchase_value_cents"
						   class="regular-text"
						   value="<?php echo esc_html( number_format( $buyer_limit['max_purchase_value_cents'] / 100, 2 ) ); ?>"
					/>
				</td>
			</tr>
		<?php endif; ?>
		<!-- Max Collections State -->
		<?php if ( isset( $buyer_limit['max_collections_state'] ) ): ?>
			<tr>
				<th>
					<label for="mondu_trade_max_collections_state">Max Collections State:</label>
				</th>
				<td>
					<input type="text"
						   readonly
						   id="mondu_trade_max_collections_state"
						   class="regular-text"
						   value="<?php echo esc_html( ucfirst( $buyer_limit['max_collections_state'] ) ); ?>"
					/>
				</td>
			</tr>
		<?php endif; ?>
	<?php endif; ?>
</table>
