<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://sss-client.herokuapp.com
 * @since             0.6.5
 * @package           TwentyFourth_WP_Scraper
 *
 * @wordpress-plugin
 * Plugin Name:       TwentyFourth WP Scraper
 * Plugin URI:        https://sss-client.herokuapp.com
 * Description:       Scrape any website, export as anything including posts, pages, categories, products, etc.
 * Version:           0.6.5
 * Author:            Maxwell Mandela
 * Author URI:        https://github.com/maxwellmandela
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       twentyfourth-wp-scraper
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 0.6.5 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('TWENTYFOURTH_WP_SCRAPER_VERSION', '0.6.5');

require_once plugin_dir_path(__FILE__) . 'freemius.php';


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-twentyfourth-wp-scraper-activator.php
 */
function activate_twentyfourth_wp_scraper()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-twentyfourth-wp-scraper-activator.php';
	TwentyFourth_WP_Scraper_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-twentyfourth-wp-scraper-deactivator.php
 */
function deactivate_twentyfourth_wp_scraper()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-twentyfourth-wp-scraper-deactivator.php';
	TwentyFourth_WP_Scraper_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_twentyfourth_wp_scraper');
register_deactivation_hook(__FILE__, 'deactivate_twentyfourth_wp_scraper');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-twentyfourth-wp-scraper.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.6.5
 */
function run_twentyfourth_wp_craper()
{

	$plugin = new TwentyFourth_WP_Scraper();
	$plugin->run();
}

run_twentyfourth_wp_craper();
