<?php

/**
 * Admin Options
 *
 * @package MonduTrade
 * @author ainsley.dev
 */

namespace MonduTrade\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

class Options {

	/**
	 * Check if all redirect pages (accepted, pending, declined) are set.
	 *
	 * @return bool True if all pages are set, false otherwise.
	 */
	public function has_redirect_pages() {
		return $this->get_redirect_accepted_id() && $this->get_redirect_pending_id() && $this->get_redirect_declined_id();
	}

	/**
	 * Get the page ID for the accepted status page.
	 *
	 * @return int Page ID for the accepted page, or 0 if not set.
	 */
	private function get_redirect_accepted_id() {
		$options = get_option( 'mondu_trade_account_options', [] );
		return $options['page_status_accepted'] ?? 0;
	}

	/**
	 * Get the URL for the accepted status page.
	 *
	 * @return string URL for the accepted page, or an empty string if not set.
	 */
	public function get_redirect_accepted_url() {
		$page_id = $this->get_redirect_accepted_id();
		return $page_id ? get_permalink( $page_id ) : '';
	}

	/**
	 * Get the page ID for the pending status page.
	 *
	 * @return int Page ID for the pending page, or 0 if not set.
	 */
	private function get_redirect_pending_id() {
		$options = get_option( 'mondu_trade_account_options', [] );
		return $options['page_status_pending'] ?? 0;
	}

	/**
	 * Get the URL for the pending status page.
	 *
	 * @return string URL for the pending page, or an empty string if not set.
	 */
	public function get_redirect_pending_url() {
		$page_id = $this->get_redirect_pending_id();
		return $page_id ? get_permalink( $page_id ) : '';
	}

	/**
	 * Get the page ID for the declined status page.
	 *
	 * @return int Page ID for the declined page, or 0 if not set.
	 */
	private function get_redirect_declined_id() {
		$options = get_option( 'mondu_trade_account_options', [] );
		return $options['page_status_declined'] ?? 0;
	}

	/**
	 * Get the URL for the declined status page.
	 *
	 * @return string URL for the declined page, or an empty string if not set.
	 */
	public function get_redirect_declined_url() {
		$page_id = $this->get_redirect_declined_id();
		return $page_id ? get_permalink( $page_id ) : '';
	}
}
