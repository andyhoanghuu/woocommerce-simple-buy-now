=== WooCommerce Simple Buy Now ===
Contributors: ndoublehwp
Tags: woocommerce, woocommerce addon, woocommerce simple buy now, woo simple buy now, woocommerce checkout in product page
Requires at least: 4.6
Tested up to: 5.0.3
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Simple Buy Now helps you to add to cart and checkout only one step in the single product page.

== Description ==

WooCommerce Simple Buy Now helps you to add to cart and checkout only one step in the single product page.
You only need to create one button `Buy Now` in `WooCommerce > Settings > WC Simple Buy Now` at before / after or replace Add to cart button. A pop-up will be displayed with the content of the checkout page.
New*: You can select to Redirect to checkout page from v1.0.5


= More Information =

* For help use [wordpress.org](http://wordpress.org/support/plugin/woo-simple-buy-now/) or create issues on [Github](https://github.com/ndoublehwp/woocommerce-simple-buy-now/)
* Fork or contribute on [GitHub](https://github.com/ndoublehwp/woocommerce-simple-buy-now/), I would be happy if you leave a star for this GitHub repository
* Follow me [Twitter](https://twitter.com/andyhoanghuu)
* View my other [WordPress Plugins](http://profiles.wordpress.org/ndoublehwp/)
* Contact me and hire me [https://wpismylife.com](https://wpismylife.com/)
* I would be happy if you leave a review :) [Review](https://wordpress.org/support/plugin/woo-simple-buy-now/reviews/). It's free!

= Settings =
Go to `Dashboard > WooCommerce > Settings > WC Simple Buy Now` tab
General:

* Enable Simple Buy Now: Activate Buy Now feature
* Redirect: Use a *Pop-up* or *Redirect to the checkout page (skip the cart page)*
* Simple Buy Now Button Position: Set a position for Buy Now button
* Simple Buy Button Title: the title default is *Buy Now*
* Reset Cart before Buy Now: Apcept reset cart before Buy Now
* Remove Quantity input: Hide quantity input by CSS code

Customize:

* Button style: Enable customizer settings if you choose *Customize*
* Normal colors
* Hover colors
* Dimensions
* Size
* Additional CSS

= Shortcode for developer =
`[woocommerce_simple_buy_now_button]`
Arguments: *title* - *class*
If you are a developer, you can use this shortcode at `content-single-product` template or its child. It should be written on the inside of the form cart.
For example,
`<?php do_shortcode( '[woocommerce_simple_buy_now_button title="Buy Now" class="wsb-button"]' ); ?>`

== Installation ==

= Minimum Requirements =

* PHP version 5.4 or greater (PHP 5.6 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)
* WooCommerce 3.0
* WordPress 4.6

Visit the [WooCommerce Simple Buy Now repository](https://github.com/ndoublehwp/woocommerce-simple-buy-now/) for a detailed list of server requirements.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of WooCommerce Simple Buy Now, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “WooCommerce Simple Buy Now” and click Search Plugins. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading my plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).
[WooCommerce Simple Buy Now repository](https://github.com/ndoublehwp/woocommerce-simple-buy-now/).

== Screenshots ==

1. WP Admin > WooCommerce > Settings > WC Simple Buy Now.
2. Single Product page with Buy Now button.
3. Pop-up 1.
4. Pop-up 2.
5. Order detail page after checkout.

== Changelog ==

= 2.0.0 – 2019-01-28 =
* Feature - Customize button.
* Fix – Compatible with WooCommerce add-ons.

= 1.0.7 - 2018-11-02 =
* Add - Add button positions.
* Add - Support a shortcode for developer.

= 1.0.6 - 2018-09-10 =
* Fix - Variable product when selected.
* Add - Add the loading icon when clicked.

= 1.0.5 - 2018-07-28 =
* Feature - Add redirect setttings (use pop-up or redirect to the checkout page).

= 1.0.4 - 2018-03-17 =
* Fix - Checkout java scripts

= 1.0.3 - 2017-10-05 =
* Dev - Add PHP hook

= 1.0.2 - 2017-10-03 =
* Dev - Add JS hook

= 1.0.1 - 2017-10-03 =
* Feature - Reset Cart before Buy Now.
* Feature - Remove Quantity input.

= 1.0.0 - 2017-10-02 =
* Feature - Add WooCommerce Settings.

[See changelog for all versions](https://github.com/ndoublehwp/woocommerce-simple-buy-now/blob/master/changelog.txt).
