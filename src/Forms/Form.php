<?php

/**
 * Actions - Form
 *
 * @package     MonduTradeAccount
 * @category    Actions
 * @author      ainsley.dev
 */

namespace MonduTrade\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Rakit\Validation\Validator as Validator;

/**
 * Form is a base class for any admin ajax interaction
 * from the front-end.
 *
 * Inheritors must implement the process function.
 */
abstract class Form {

	/**
	 * The action of the form
	 *
	 * @var string
	 */
	protected string $action;

	/**
	 * The validator for form fields.
	 *
	 * @var Validator
	 */
	protected Validator $validator;

	/**
	 * The validator rules.
	 *
	 * @var array
	 */
	protected array $rules = [];

	/**
	 * Add action for form and create a new form validator.
	 */
	public function __construct() {
		// Create validator.
		$this->validator = new Validator();

		// Add action
		add_action( 'wp_ajax_' . $this->action, [ $this, 'process' ] );
		add_action( 'wp_ajax_nopriv_' . $this->action, [ $this, 'process' ] );
	}

	/**
	 * Process function for the form, must be implemented
	 * by child classes.
	 *
	 * @return void
	 */
	abstract protected function process(): void;

	/**
	 * Respond to the front end via AJAX.
	 *
	 * @param int $status
	 * @param $data
	 * @param string $message
	 */
	protected function respond( int $status, $data, string $message ) {
		$data = [
			'status'  => $status,
			'message' => $message,
			'data'    => $data,
			'error'   => ! ( $status >= 200 && $status < 300 ),
		];

		echo wp_json_encode( $data );
		die();
	}

	/**
	 * Obtains the nonce name for the form.
	 *
	 * @return string
	 */
	protected function get_nonce_name(): string {
		return $this->action . '_nonce';
	}

	/**
	 * Determines if a nonce is valid.
	 * Default nonce name is action_nonce.
	 *
	 * @return bool
	 */
	protected function is_nonce_valid(): bool {
		$nonce_name = $this->get_nonce_name();

		$nonce = isset( $_POST[ $nonce_name ] )
			? sanitize_text_field( wp_unslash( $_POST[ $nonce_name ] ) )
			: '';

		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $this->action ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if current request is AJAX
	 */
	protected function is_ajax_request(): bool {
		$requested_with = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_REQUESTED_WITH'] ) )
			: '';

		return wp_doing_ajax() || strtolower( $requested_with ) === 'xmlhttprequest';
	}

	/**
	 * Validate form fields with rules.
	 */
	protected function validate() {
		// Extract the expected fields from the rules.
		$expected_fields = array_keys( $this->rules );

		// Sanitize only the expected fields.
		$sanitized_data = [];
		foreach ( $expected_fields as $field ) {
			if ( isset( $_POST[ $field ] ) ) {
				$sanitized_data[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ) );
			}
		}

		// Perform validation using sanitized data.
		$validation = $this->validator->validate( $sanitized_data, $this->rules );
		if ( $validation->fails() ) {
			$this->respond( 400, $validation->errors()->toArray(), "Validation failed" );
		}
	}

	/**
	 * Strips variables
	 *
	 * @param $text
	 * @return string
	 */
	protected function clean_vars( $text ): string {
		if ( $text == null ) {
			$text = '';
		}

		return preg_replace( "/[^A-Za-z0-9.@ ]/", '', $text );
	}
}
