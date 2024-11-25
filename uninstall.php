<?php

/**
 * Uninstall
 *
 * @package     MonduTradeAccount
 * @category    Base
 * @author      ainsley.dev
 */

use MonduTrade\Plugin;

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin-specific options
delete_option( 'mondu_trade_account_options' );
delete_option( Plugin::OPTION_WEBHOOKS_REGISTERED );
delete_option( Plugin::OPTION_WEBHOOKS_SECRET );
