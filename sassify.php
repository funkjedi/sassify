<?php
/*
Plugin Name: Sassify
Plugin URI: https://github.com/funkjedi/sassify
Description: Adds support for SCSS stylesheets to wp_enqueue_style.
Version: 1.0.0
Author: funkjedi
Author URI: http://funkjedi.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

define('SASSIFY_PLUGIN',     __FILE__);
define('SASSIFY_PLUGIN_DIR', plugin_dir_path(SASSIFY_PLUGIN));

require_once SASSIFY_PLUGIN_DIR . 'class-sassify-plugin.php';
new sassify_plugin;
