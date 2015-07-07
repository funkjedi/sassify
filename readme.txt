=== Sassify ===
Contributors: funkjedi
Tags: sass, scss, css
Requires at least: 3.5.0
Tested up to: 4.2.2
Version: 1.0.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds support for SCSS stylesheets to wp_enqueue_style.


== Description ==

This plugin adds support for SCSS stylesheets to `wp_enqueue_style`. Just enqueue your styleheet using `wp_enqueue_style` and it will automatically be compiled for you when neccessary.

Variables can be injected using the `sassify_compiler_variables` filter.

= SCSSPHP =

This plugin uses the latest version of [scssphp](https://github.com/leafo/scssphp), modified to be PHP 5.2 compatible.

It implements SCSS 3.2.12. It does not implement the SASS syntax, only the SCSS syntax.

= Bug Submission =
https://github.com/funkjedi/sassify/issues/


== Installation ==

1. Upload `sassify` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress


== Screenshots ==

1. Sassify settings.
2. Injecting variables.


== Changelog ==

= 1.0 =
* Core: Initial release.


== Upgrade Notice ==
