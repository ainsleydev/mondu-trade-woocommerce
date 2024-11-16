<?php

/**
 * Admin - Options
 *
 * @package     MonduTradeAccount
 * @category    Admin
 * @author      ainsley.dev
 */

namespace MonduTrade\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

class Options {

	/**
	 * Check if all redirect pages (accepted, pending, declined) are set.
	 *
	 * @return bool
	 */
	public function has_redirect_pages(): bool {
		return $this->get_redirect_accepted_id() && $this->get_redirect_pending_id() && $this->get_redirect_declined_id();
	}

	/**
	 * Get the page ID for the accepted status page.
	 *
	 * @return int
	 */
	private function get_redirect_accepted_id(): int {
		$options = get_option( 'mondu_trade_account_options', [] );
		return $options['page_status_accepted'] ?? 0;
	}

	/**
	 * Get the URL for the accepted status page.
	 *
	 * @return string
	 */
	public function get_redirect_accepted_url(): string {
		$page_id = $this->get_redirect_accepted_id();
		return $page_id ? get_permalink( $page_id ) : '';
	}

	/**
	 * Get the page ID for the pending status page.
	 *
	 * @return int
	 */
	private function get_redirect_pending_id(): int {
		$options = get_option( 'mondu_trade_account_options', [] );
		return $options['page_status_pending'] ?? 0;
	}

	/**
	 * Get the URL for the pending status page.
	 *
	 * @return string
	 */
	public function get_redirect_pending_url(): string {
		$page_id = $this->get_redirect_pending_id();
		return $page_id ? get_permalink( $page_id ) : '';
	}

	/**
	 * Get the page ID for the declined status page.
	 *
	 * @return int
	 */
	private function get_redirect_declined_id(): int {
		$options = get_option( 'mondu_trade_account_options', [] );
		return $options['page_status_declined'] ?? 0;
	}

	/**
	 * Get the URL for the declined status page.
	 *
	 * @return string
	 */
	public function get_redirect_declined_url(): string {
		$page_id = $this->get_redirect_declined_id();
		return $page_id ? get_permalink( $page_id ) : '';
	}
}
