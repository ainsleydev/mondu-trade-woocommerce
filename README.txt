=== Mondu Trade Account ===
Contributors: ainsleydev
Tags: woocommerce, trade accounts, checkout, mondu, api
Requires at least: 6.7
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.0.7
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Integrates Mondu's Digital Trade Account functionality into WooCommerce, enabling customers to apply for trade accounts during checkout.

== Description ==

The Mondu Trade Account - WooCommerce plugin integrates Mondu's Digital Trade Account functionality into your WooCommerce store, allowing your customers to apply for and manage trade accounts directly during checkout.

**Features:**
- **Trade Account Applications**: Let customers apply for a trade account while completing their order.
- **Webhook Integration**: Automatically update customer statuses (e.g., accepted, pending, declined).
- **Custom Styling and Actions**: Extend and customize the checkout experience with hooks and filters.
- **Admin Management Tools**: Access customer trade account information, logs, and webhook settings from the WordPress admin panel.
- **Secure and Compliant**: Fully supports WooCommerce standards and uses secure connections for API communication.

Useful Links:
- [GitHub Repository](https://github.com/mondu-ai/bnpl-checkout-woocommerce)
- [Changelog](https://github.com/mondu-ai/bnpl-checkout-woocommerce/blob/main/changelog.txt)

== Installation ==

1. Download the plugin ZIP file from the releases section or the WordPress plugin directory.
2. Upload the ZIP file via the WordPress admin panel under "Plugins > Add New."
3. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How do I allow for signups outside the checkout? =
This plugin supports signup forms outside the checkout. Ensure users are logged in before submitting the form.

Example form:

**HTML**:

```html
<form id="trade-account-signup" method="POST" action="<?php echo admin_url('admin-ajax.php'); ?>">
  <input type="hidden" name="action" value="trade_account_submit">
  <?php wp_nonce_field('trade_account_submit', 'trade_account_nonce'); ?>
  <button type="submit">Submit</button>
</form>

**JavaScript**:

```js
document.querySelector('form').addEventListener('submit', function (event) {
  event.preventDefault();

  const form = event.target;
  const formData = new FormData(form);

  fetch(form.action, {
    method: 'POST',
    body: formData,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Server responded with status ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (!data.error) {
        alert('Trade Account application submitted successfully.');
      } else {
        alert(`Error: ${data.message || 'An error occurred while submitting the application.'}`);
      }
    })
    .catch((error) => {
      alert(`An error occurred: ${error.message}`);
    });
});
```

= How do I know the status of a customer? =
Go to "Users" in the WordPress admin panel and select the desired customer. Relevant fields include:
- `uuid`: External UUID assigned by Mondu.
- `status`: Customer's trade account status (`unknown`, `accepted`, `pending`, or `declined`).

= How do I add custom styling to the payment gateway? =
Use the `mondu_trade_account_checkout_class` filter to add custom styles. Example:
add_filter('mondu_trade_account_checkout_class', function ($class) {
    return $class . ' my-class-name';
});

= How can I run actions when a buyer status changes? =

There are 4 different actions you can latch onto when Mondu replies with an update after a customer has applied for a
Digital Trade Account. Below is a list of available actions.

**Example Payload**

Below is an example of a buyer and topic sent via the actions, see
the [Mondu Webhooks Overview](https://docs.mondu.ai/reference/webhooks-overview) for more information

```json
{
  "topic": "buyer/{TOPIC_NAME}",
  "buyer": {
    "uuid": "66e8d234-23b5-1125-9592-d7390f20g01c",
    "state": "accepted",
    "external_reference_id": "DE-1-1000745773",
    "company_name": "2023-02-07T15:14:22.301Z",
    "first_name": "John",
    "last_name": "Smith"
  }
}
```

**Actions**:

- `mondu_trade_buyer_webhook_received`
- `mondu_trade_buyer_accepted`
- `mondu_trade_buyer_pending`
- `mondu_trade_buyer_declined`

Example for `mondu_trade_buyer_accepted`:
add_action('mondu_trade_buyer_accepted', function ($customer_id, $buyer) {
    // Handle accepted status
});

== License ==

This plugin is licensed under the [GPLv3](https://www.gnu.org/licenses/gpl-3.0.html).
