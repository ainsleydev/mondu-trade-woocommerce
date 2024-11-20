/**
 * Handles the submission of the trade account application form.
 * Prevents the default form submission behavior (which is to
 * submit the payment form) and submits to Admin Ajax.,
 *
 * @param {Event} event
 */
function submitTradeAccountApplication(event) {
	event.preventDefault();

	const btn = event.target;
	btn.classList.add('loading');

	clearErrorMessages();

	const localisedData = window.aDevTradeAccountData;
	const formData = new URLSearchParams();

	formData.append('action', 'trade_account_submit');
	formData.append('nonce', localisedData.ajax_nonce);

	// AJAX request to backend (use WordPress ajaxurl if needed)
	fetch(localisedData.ajax_url, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded',
		},
		body: formData,
	})
		.then(response => response.json())
		.then(res => {
			if (res.error) {
				displayErrorMessages(res.data);
			} else if (res?.data?.hosted_page_url) {
				window.location.href = res.data.hosted_page_url;
			} else {
				alert("There was an error processing the form.");
			}
		})
		.catch(error => {
			console.error('Error:', error);
		})
		.finally(() => {
			btn.classList.remove('loading');
		})
}

/**
 * Clears any existing error messages on the form.
 *
 * @returns void
 */
function clearErrorMessages() {
	document.querySelectorAll('.error-label').forEach((label) => label.remove());
}

/**
 * Displays error messages next to the relevant form fields.
 *
 * @param {Record<string, string[]>} errors
 * @returns void
 */
function displayErrorMessages(errors) {
	for (const [field, messages] of Object.entries(errors)) {
		const fieldElement = document.querySelector(`[name="${field}"]`);
		if (!fieldElement) {
			continue;
		}

		const fieldGroup = fieldElement.closest('.form-row')
		if (!fieldGroup) {
			continue
		}

		const errorLabel = document.createElement('span');
		errorLabel.className = 'error-label';
		errorLabel.style.color = 'red';
		errorLabel.textContent = Object.values(messages)[0]
			.replaceAll('-', ' ')
			.replaceAll('The', '');

		fieldGroup.appendChild(errorLabel);
	}
}
