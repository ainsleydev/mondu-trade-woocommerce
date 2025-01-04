=== Mondu Trade Account ===
Contributors: ainsleydev
Tags: woocommerce, trade accounts, checkout, mondu, api
Requires at least: 6.7
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.2.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Integrates Mondu's Digital Trade Account functionality into WooCommerce, enabling customers to apply for trade accounts during checkout.

== Description ==

The Mondu Trade Account - WooCommerce plugin integrates Mondu's Digital Trade Account functionality into your WooCommerce store, allowing your customers to apply for and manage trade accounts directly during checkout.

**Features:**
- **Trade Account Applications**: Let customers apply for a trade account while completing their order.
- **Webhook Integration**: Automatically update customer statuses (e.g., accepted, pending, declined) when a customer has applied for a Digital Trade Account.
- **Custom Styling and Actions**: Easily extend and customize the checkout experience with hooks and filters. Allows you to run actions when a buyer has been accepted or declined.
- **Admin Management Tools**: Access customer trade account information, logs, and webhook settings from the WordPress admin panel.
- **Secure and Compliant**: Fully supports WooCommerce standards and uses secure connections for API communication.

**Useful Links:**
- [Official GitHub Repository](https://github.com/mondu-ai/bnpl-checkout-woocommerce)
- [Changelog](https://github.com/mondu-ai/bnpl-checkout-woocommerce/blob/main/changelog.txt)

== Installation ==

You can either download the zip file from the releases section or locate it on the WordPress Plugin install page.

== Prerequisites ==

Before installing and using the Mondu Trade Account - WooCommerce plugin, ensure your environment meets the following requirements:

- **WordPress**: Requires at least version `6.7`
- **PHP**: Requires at least version `7.4`
- **WooCommerce**: Requires at least version `9.4`
- **Mondu Plugin**: Needs to be installed, tested version `3.0.3`

== Backwards Compatibility ==

This plugin has a direct dependency on the `Mondu Plugin`. Before updating both plugins, always test the new version on a staging environment to ensure compatibility with your customizations, integrations, and WooCommerce setup.

== Buyer States ==

There are a total of 6 buyer states that a user can be in. If you are the admin of the WordPress install, you can change these states, but it's recommended not to.

| State       | Explanation                                                                                      |
|:------------|:-------------------------------------------------------------------------------------------------|
| `unknown`   | The default status, when the customer hasn't signed up yet.                                      |
| `applied`   | Customer has tried to apply for a Trade Account, but the webhook hasn't been triggered.          |
| `accepted`  | Customer has been approved for a Trade Account and should have a buyer limit.                    |
| `pending`   | Customer is waiting to hear from Mondu if their account has been accepted (Maximum of 48 hours). |
| `declined`  | Customer has been flat-out refused credit from Mondu.                                            |
| `cancelled` | Customer exited out of the Mondu Trade Application form.                                         |

== Sandbox ==

If you have sandbox mode set on the `Mondu` plugin, you can test the following states with the email addresses listed below. Note that if an email does not follow this convention, the trade application will fail.

- **`Accepted`**: accepted.good.{random-string}@example.com
- **`Pending`**: pending.pending-brc.{random-string}@example.com
- **`Declined`**: declined.bad.{random-string}@example.com

Note that emails are sent via the Mondu internal inbox in sandbox mode. To see them, it's best to reach out to Mondu.

== Webhooks ==

Mondu sends buyer webhooks when a user has applied for a Trade Account. The following payload will be provided via this webhook once buyer onboarding requested is processed. See [here](https://docs.mondu.ai/reference/webhooks-overview) for more details.

If the webhook fails, Mondu will send the webhook in increasing intervals until the `WebhookController` returns an `OK` response. Mondu will send `6` attempts every minute for all `buyer` and `order` topics until the handler responds with an OK status code.

**Example Payload**:

`
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
`

== Frequently Asked Questions ==

= Is the plugin compatible with the WP block editor? =
The Payment Gateway is not compatible, but the form is. See below for more details.

= How do I allow for signups outside the checkout? =
This plugin adds native support for allowing users to sign up to a Trade Account outside of the checkout. Perfect for CTA's or banners.

**Note**: The user must be logged in to perform this action, as a customer ID is required when Mondu responds to the Trade Account request. Before displaying the form, ensure your users are logged in by using the WordPress `is_user_logged_in()` function.

**Block**:
You can add the `Mondu Trade Account Form Block` to the block editor, which will output a dynamic form the user can use to sign up for the Trade Account.

**Shortcode**:
If you want to programmatically output the shortcode, you can do so by placing the following code in your page template:

`echo do_shortcode('[mondu_trade_account_form]');`

= How do I know what the status is of a customer? =
Simply go to the user's account on WordPress by navigating to `Users` and click on a customer. From there, you should see the following fields:

- `uuid` -> The external UUID that's been assigned from Mondu.
- `status` -> The buyer status which can be one of `unknown`, `accepted`, `pending`, or `declined`.

= When will Mondu send emails to the customer? =
- After the buyer authorizes the onboarding process in the hosted checkout page.
- Status change to accepted triggers a webhook attempt, and the email communication is sent.
- Status change to declined triggers a webhook, and the declined email is sent.

**It's recommended you still add email triggers for webhooks**, as described below.

= How do I add custom styling to the payment gateway? =
There may be times you want to style the checkout gateway with your own styles. To do this, you can either latch onto the default class name `mondu-trade` or provide your own using a filter.

An example of this is below:

`
/**
 * Adds a custom class to the Mondu Trade Account checkout gateway.
 *
 * @param string $class The existing CSS class for the Mondu Trade Account checkout gateway.
 * @return string The modified CSS class with the custom class name appended.
 */
function add_custom_mondu_trade_account_class($class) {
    return $class . ' my-class-name';
}

add_filter('mondu_trade_account_checkout_class', 'add_custom_mondu_trade_account_class');
`

= How can I get a buyer status of a user? =
You can use the `mondu_trade_get_buyer_status` function to fetch the current status of a customer. This function will throw a `MonduTradeException` if the provided customer ID is invalid. Ensure to handle exceptions properly when calling this function.

**Parameters:**
- `$customer_id (int)`: The ID of the customer whose status you want to retrieve.

**Returns**:
- `string`: The current Mondu Trade Account status of the customer.

**Throws**:
- `MonduTradeException`: If the customer ID is not valid.

**Example**:
`status = mondu_trade_get_buyer_status($customer_id);`

= How can I get a buyer limit of a user? =
You can use the `mondu_trade_get_buyer_limit` function to fetch the buyer limit for a customer. This function will throw a `MonduTradeException` if the provided customer ID is invalid or if the buyer status is not `accepted`. Ensure to handle exceptions properly when calling this function.

**Parameters:**
- `$customer_id (int)`: The ID of the customer whose buyer limit you want to retrieve.

**Returns**:
- `array`: An associative array containing the buyer limit details.

**Throws**:
- `MonduTradeException`: If the customer ID is not valid or the buyer status is not `accepted`.

**Example**:
` $buyer_limit = mondu_trade_get_buyer_limit($customer_id);`

**Sample Output**:

`
Array (
    [purchasing_limit] => Array (
        [purchasing_limit_cents] => 100000
        [balance_cents] => 10000
        [max_purchase_value_cents] => 90000
        [max_collections_state] => no
    )
)
`

= How can I run actions when a buyer status has changed? =
There are 4 different actions you can latch onto when Mondu replies with an update after a customer has applied for a Digital Trade Account. Below is a list of available actions.

**Actions**:
- `mondu_trade_buyer_webhook_received`
- `mondu_trade_buyer_accepted`
- `mondu_trade_buyer_pending`
- `mondu_trade_buyer_declined`

**Example Payload**:

`
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
`

**Example for `mondu_trade_buyer_accepted`**:

`
add_action('mondu_trade_buyer_accepted', function ($customer_id, $buyer) {
    // Handle accepted status
});
`

== Copyright ==

All rights reserved. This plugin and its code are proprietary to ainsley.dev LTD. Unauthorized copying, distribution, transmission, or storage of this plugin, its code, or content, in whole or in part, in any form or by any means, is strictly prohibited without prior written permission.

This plugin is licensed for use by end-users on their WordPress sites but may not be copied, shared, modified, or redistributed in any form, except with explicit written permission from ainsley.dev LTD.
