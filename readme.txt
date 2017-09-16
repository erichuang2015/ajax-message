=== Ajax Message ===
Contributors: keksus
License: GPL 3
Donate link: http://keksus.com/donate.html
Tags: ajax, contact, message, form, email, feedback
Requires at least: 3.6
Tested up to: 4.8.1
Requires PHP: 5.3
Stable tag: 4.8.1
License: GPLv3 
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin hide update popup text into administration part of WordPress 

== Description ==

This plugin allows you send messages to email from a page, post or from any other location that you specify in template. It can be used as a shortcode or a widget. 
Has a admin settings page where you can change the names of fields, enable and disable captcha, add custom CSS code. 

### Live Demo

* [http://keksus.com/wordpress-plugins/ae.html](http://keksus.com/wordpress-plugins/ae.html)


== Installation ==

Unzip plugin files and upload them under your '/wp-content/plugins/' directory.
Activate plugin at "Plugins" administration page.

### Usage

If you want to use Ajax message form on the page or post, add this shortcode inside the text editor:
	[ae_message]
Also you can use this shortcode in the widget "Ajax message".

If you want to use Ajax message form in the theme code, add this code to your template:
	<?php>do_shortcode('[ae_message]');?>

== Upgrade Notice ==

Upgrade normally

== Screenshots ==

1. Admin settings page
2. Ajax message form

== Changelog ==

= 0.0.1 =
* Initial release
