<?php

/**
 * Admin - Log Management
 *
 * @package     MonduTradeAccount
 * @category    Admin
 * @author      ainsley.dev
 */

namespace MonduTrade\Admin;

use MonduTrade\Plugin;
use WP_Filesystem_Base;

/**
 * Log Manager is responsible for downloading logs
 * from the plugin.
 */
class LogManager {

	/**
	 * Log Manager constructor.
	 */
	public function __construct() {
		add_action( 'admin_post_mondu_trade_download_logs', [ $this, 'download' ] );
	}

	/**
	 * Download Mondu Trade logs.
	 *
	 * @return void
	 * @noinspection DuplicatedCode
	 */
	public function download() {
		Util::validate_user_permissions();

		// Check if nonce is set and sanitize it before verification.
		$nonce = isset( $_POST['mondu_trade_download_logs_nonce'] )
			? sanitize_text_field( wp_unslash( $_POST['mondu_trade_download_logs_nonce'] ) )
			: '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'mondu_trade_download_logs' ) ) {
			Util::die_after_security_check();

			return;
		}

		/**
		 * @var WP_Filesystem_Base $wp_filesystem
		 */
		global $wp_filesystem;
		WP_Filesystem();

		$date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : null;

		if ( null === $date ) {
			status_header( 400 );
			exit( esc_html__( 'Date is required.', 'mondu-digital-trade-account' ) );
		}

		$file = $this->get_file( $date );

		if ( null === $file ) {
			status_header( 404 );
			exit( esc_html__( 'Log not found.', 'mondu-digital-trade-account' ) );
		}

		$filename = Plugin::LOG_CONTEXT . '-' . $date . '.log';

		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '";' );
		echo wp_kses_post( str_replace( '>', '', $wp_filesystem->get_contents( $file ) ) );
		die;
	}

	/**
	 * Get file for a specific date.
	 *
	 * @param string $date
	 * @return string|null
	 */
	private function get_file( string $date ): ?string {
		$upload_dir = wp_upload_dir();
		$base_dir   = trailingslashit( $upload_dir['basedir'] ) . 'mondu-digital-trade-account/';

		// Ensure the directory exists before trying to read files.
		if ( ! is_dir( $base_dir ) ) {
			return null;
		}

		$dir = opendir( $base_dir );
		if ( $dir ) {
			while ( $file = readdir( $dir ) ) { //phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
				if ( str_starts_with( $file, Plugin::LOG_CONTEXT . '-' . $date ) && str_ends_with( $file, '.log' ) ) {
					closedir( $dir );

					return $base_dir . $file;
				}
			}
			closedir( $dir );
		}

		return null;
	}
}
