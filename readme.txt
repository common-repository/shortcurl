=== EZ SHORTCURL Shortcodes to Fetch and Parse External Content ===
Plugin URI: http://wordpress.ieonly.com/category/my-plugins/shortcurl/
Author: Eli Scheetz
Author URI: http://wordpress.ieonly.com/category/my-plugins/
Contributors: scheeeli
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8VWNB5QEJ55TJ
Tags: cURL, shortcode, plugin, wp_remote_get, preg_replace, str_replace, page, post, parse, HTML
Stable tag: 3.17.49
Version: 3.17.49
Requires at least: 2.7
Tested up to: 4.9.1

Use the shortcodes remote_get and preg_replace to fetch external content and parse it to use on your page or post.

== Description ==

Use the shortcode "remote_get" with the parameter "url" to insert the content from that url into your page or post. You can also use parameters like start and stop (or end) to parse out a specific part of the content that you wish to display.

Now you can also wrap any content in the "preg_replace" shortcode to manipulate it into the desired format. Tricky stuff, but very powerful, if you know what you're doing.

Updated December 16th

== Installation ==

1. Download and unzip the plugin into your WordPress plugins directory (usually `/wp-content/plugins/`).
1. Activate the plugin through the 'Plugins' menu in your WordPress Admin.

== Frequently Asked Questions ==

= What do I do after I activate the Plugin? =

Use the shorcode remote_get with a url parameter on a page or post to bring in external content.

= What does a example of the shortcode look like? =

[remote_get url="https://wordpress.org/plugins/shortcurl/stats/" start='&lt;div class="block-content"' stop='!-- block-content--' length="-1" replace="='/extend" with="='http://wordpress.org/extend" replace2="%2Fextend%2F" with2="http%3A%2F%2Fwordpress.org%2Fextend%2F"]

== Changelog ==

= 3.17.49 =
* Fixed more Undefined index Notices.

= 3.17.29 =
* Fixed multiple Undefined index Notices.

= 3.16.29 =
* Added a Shortcode Widget with pop-out position options.

= 3.14.34 =
* Replaced the SESSION code with the use of GLOBALS for broader compatibilty.

= 3.14.53 =
* Fixed the "with" parameter in to remote_get function to accempt empty string.
* Decoded HTML Entities in the URL parameter to improve the handling of GET variables in the URL String.
* Improved the error messages by adding what URL triggered the error.

= 3.04.26 =
* Added a shortcode for str_replace simple string manipulation.
* Added support for arrays in preg_replace shortcode.
* Added an alternate shortcode for running preg_replace on another shortcode vs. on the results of another shortcode.

= 1.3.03.25 =
* Added a shortcode for preg_replace to further manipulate content.

= 1.3.03.15 =
* Fixed error handling on line 71 to report the whole curl error on the admin pages.

= 1.2.12.06 =
* Fixed call to plugin_row_meta.

= 1.2.12.05 =
* Added 24 hour caching to speed up page loads.
* Added admin_notices if fatching or parsing produces any errors.

= 1.2.12.01 =
* First versions uploaded to WordPress.

== Upgrade Notice ==

= 3.17.49 =
Fixed more Undefined index Notices.

= 3.17.29 =
Fixed multiple Undefined index Notices.

= 3.16.29 =
* Added a Shortcode Widget with pop-out position options.

= 3.14.34 =
Replaced the SESSION code with the use of GLOBALS for broader compatibilty.

= 3.14.53 =
Improved the error messages and fixed the parameters in to remote_get function to improve the handling of GET variables and empty strings.

= 3.04.26 =
Added a shortcode for str_replace, support for arrays in preg_replace, and an alternate shortcode for running preg_replace on another shortcode.

= 1.3.03.25 =
Added a shortcode for preg_replace to further manipulate content.

= 1.3.03.15 =
Fixed error handling on line 71 to report the whole curl error on the admin pages.

= 1.2.12.06 =
Fixed call to plugin_row_meta.

= 1.2.12.05 =
Added 24 hour caching to speed up page loads and admin_notices if fatching or parsing produces any errors.

= 1.2.12.01 =
First versions available through WordPress.
