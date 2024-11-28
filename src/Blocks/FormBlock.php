<?php

/**
 * Blocks - Form
 *
 * @package     MonduTradeAccount
 * @category    Blocks
 * @author      ainsley.dev
 */

namespace MonduTrade\Blocks;

/**
 * Form Block adds block support for the Block/CTA
 */
class FormBlock {

	/**
	 * Form Block constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_block' ] );
	}

	/**
	 * Registers block the block type.
	 *
	 * @return void
	 */
	public function register_block() {
		// Ensure block editor scripts are only loaded when necessary.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register the block type.
		register_block_type( 'mondu-trade/trade-account-form', [
			'render_callback' => [ $this, 'render_block' ],
			'editor_script'   => 'mondu-trade-account-block-editor',
		] );
	}

	/**
	 * Outputs the block content.
	 *
	 * @param array $attributes Block attributes.
	 * @param string $content Block content.
	 * @return string
	 */
	public function render_block( array $attributes, string $content ): string {
		ob_start();
		echo do_shortcode( '[mondu_trade_account_form]' );

		return ob_get_clean();
	}
}
