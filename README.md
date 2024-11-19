<div align="center">
<img width="100" src="res/logo.svg" alt="Errors Logo" />

[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![GNU General Public License 3.0](https://img.shields.io/github/license/ainsleyclark/squidge.svg)](https://www.gnu.org/licenses/gpl-3.0.en.html)
[![Twitter](https://img.shields.io/twitter/follow/ainsleydev)](https://twitter.com/ainsleydev)

</div>

# Mondu Trade Account - WooCommerce

Wordpress Plugin for integrating trade accounts into the Resinbound.online Website via the Mondu API

### Sign up

## FAQs

### How do I add custom styling to the payment gateway?

There may be times you want to style the checkout gateway with your own styles. To do this, you can either latch onto
the default class name `mondu-trade` or provide your own using a filter.

An example of this is below:

```php
add_filter('mondu_trade_account_checkout_class', function ($class) {
	return $class . ' my-class-name';
});
```
