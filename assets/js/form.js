/**
 * Mondu Trade Form
 *
 * @author ainsley.dev
 * @package Mondu Trade
 *
 */

/**
 * Handles the submission of the trade account application form.
 * Prevents the default form submission behavior (which is to
 * submit the payment form) and submits to Admin Ajax.,
 *
 * @param {HTMLFormElement} form
 * @param {HTMLButtonElement} button
 */
function submitTradeAccountApplication(form, button) {
	console.info('Submitting Trade Account form...');

	button.classList.add('loading');

	fetch(form.getAttribute('action'), {
		method: 'POST',
		body: new FormData(form),
	})
		.then((res) => res.json())
		.then((res) => {
			console.info(`Mondu Trade - Received message: ${res.message}`);
			const data = res.data;
			if (!data['hosted_page_url']) {
				throw new Error(`No hosted URL page`);
			}
			window.location = data['hosted_page_url'];
		})
		.catch((error) => {
			console.error(`Mondu Trade - An error occurred: ${error.message}`);
			displayWooCommerceError(error.message);
		})
		.finally(() => {
			button.classList.remove('loading');
		})
}

/**
 * Displays a WooCommerce-style error notice in a form
 *
 * @param {HTMLFormElement} form - The form to display the error in
 * @param {string} [message='An unexpected error occurred'] - The error message to display
 */
function displayWooCommerceError(form, message = 'An unexpected error occurred') {
	// Clear any existing notices
	const existingNotices = form.querySelectorAll('.woocommerce-error, .woocommerce-message');
	existingNotices.forEach(notice => notice.remove());

	// Create error notice
	const errorNotice = document.createElement('div');
	errorNotice.className = 'woocommerce-error';
	errorNotice.setAttribute('role', 'alert');
	errorNotice.textContent = message;

	// Insert at the top of the form
	form.insertBefore(errorNotice, form.firstChild);
}

/**
 * Adds the event listener to the form and triggers
 * the AJAX handler.
 */
document.addEventListener('DOMContentLoaded', function () {
	const form = document.querySelector('form#trade-account-shortcode-signup');
	if (!form) {
		console.error('Mondu - Trade Form not found');
		return;
	}
	const btn = form.querySelector('button[type="submit"]');
	if (!btn) {
		console.error('Mondu Trade - Button not found');
		return;
	}
	form.addEventListener('submit', function (event) {
		event.preventDefault();
		submitTradeAccountApplication(form, btn);
	});
});
