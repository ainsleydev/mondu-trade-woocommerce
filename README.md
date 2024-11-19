<div align="center">
<img width="100" height="25" src="res/logo.svg" alt="Errors Logo"/>
<h3 align="center">Mondu Trade Account - WooCommerce</h3>

[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://GitHub.com/Naereen/StrapDown.js/graphs/commit-activity)
[![GNU General Public License 3.0](https://img.shields.io/github/license/ainsleyclark/squidge.svg)](https://www.gnu.org/licenses/gpl-3.0.en.html)
[![Twitter](https://img.shields.io/twitter/follow/ainsleydev)](https://twitter.com/ainsleydev)

</div>

## Overview

WordPress Plugin for TODO.

## Installation

You can either download a the zip file from the releases section or locate it on the Wordpress Plugin install page.

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

## Copyright

All rights reserved. This plugin and its code are proprietary to ainsley.dev LTD. Unauthorized copying, distribution,
transmission, or storage of this plugin, its code, or content, in whole or in part, in any form or by any means, is
strictly prohibited without prior written permission.

This plugin is licensed for use by end-users on their WordPress sites but may not be copied, shared, modified, or
redistributed in any form, except with explicit written permission from ainsley.dev LTD.

## Licence

Code Copyright 2024 ainsley.dev LTD. Code released under the [BSD-3 Clause](LICENSE).
