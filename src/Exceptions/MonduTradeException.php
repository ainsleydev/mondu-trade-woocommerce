<?php

/**
 * Exceptions - Mondu Trade Exception
 *
 * @package     MonduTradeAccount
 * @category    Exceptions
 * @author      ainsley.dev
 */

namespace MonduTrade\Exceptions;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Direct access not allowed' );
}

use Exception;

/**
 * Mondu Trade Exception is a generic exception for
 * when an error occurred within the plugin.
 */
class MonduTradeException extends Exception {}
