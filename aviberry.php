<?php
/*  Copyright 2012 Movavi (email : support@movavi.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * @package aviberryPlugin
 * @author Aviberry
 * @link http://www.aviberry.com
 * @version 2.4.1
 */

/*
Plugin Name: Aviberry Plugin
Plugin URI: http://www.aviberry.com/download-wordpress-plugin.html?source=wppanel
Description: Intergation of Aviberry Converter with Wordpress Media
Version: 2.4.1
Author: Aviberry
Author URI: http://www.aviberry.com/plugin-pricing.html?source=wppanel
License: GPL2
*/

/*
 * Definitions 
 */

// Pre-2.6 compatibility
defined('WP_CONTENT_URL')
	|| define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
defined('WP_CONTENT_DIR')
	|| define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
defined('WP_PLUGIN_URL')
	|| define('WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins');
defined('WP_PLUGIN_DIR')
	|| define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

//
// Requirements
//
require_once 'lib/aviberry/aviberryPlugin/aviberryPluginFactory.class.php';
require_once 'lib/aviberry/aviberryAction/aviberryActionFactory.class.php';
require_once 'lib/aviberry/aviberryShortCode/aviberryShortCodeFactory.class.php';
require_once 'lib/aviberry/aviberryFilter/aviberryFilterFactory.class.php';

/*
 * Actions
 */
aviberryActionFactory::create($wp_version, $pagenow);

/*
 * Shortcodes
 */
aviberryShortCodeFactory::create($wp_version);

/*
 * Filters
 */
aviberryFilterFactory::create($wp_version);

/*
 * I18n
 */
load_plugin_textdomain('aviberry', 'wp-content/plugins/' . AVIBERRY_PLUGIN_DIR, AVIBERRY_PLUGIN_DIR);

?>
