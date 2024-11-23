<?php

/**
 * Mondu - Redirect Status
 *
 * @package     MonduTradeAccount
 * @category    Mondu
 * @author      ainsley.dev
 */

namespace MonduTrade\Mondu;

if (!defined('ABSPATH')) {
	die('Direct access not allowed');
}

/**
 * Redirect Status defines the statues for when Modnu redirect
 * the user after a Trade Account application
 *
 * @see: https://docs.mondu.ai/docs/mondu-digital-trade-account
 */
final class RedirectStatus
{

	/**
	 * Success can either be pending or successful.
	 * Why?
	 *
	 * @var string
	 */
	const SUCCEEDED = 'succeeded';

	/**
	 * Declined is when Mondu refused the application.
	 *
	 * @var string
	 */
	const DECLINED = 'declined';

	/**
	 * Cancelled is when the buyer exited the application.
	 *
	 * @var string
	 */
	const CANCELLED = 'cancelled';
}



