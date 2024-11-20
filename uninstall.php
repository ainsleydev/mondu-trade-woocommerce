<?php

/**
 * Uninstall
 *
 * @package     MonduTradeAccount
 * @category    Base
 * @author      ainsley.dev
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin-specific options
delete_option( 'mondu_trade_account_options' );
delete_option( '_mondu_trade_webhooks_registered' );
delete_option( '_mondu_trade_webhooks_secret' );
