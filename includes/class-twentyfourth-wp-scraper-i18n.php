<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/maxwellmandela
 * @since      0.6.5
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.6.5
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/includes
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */
class TwentyFourth_WP_Scraper_i18n
{


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    0.6.5
	 */
	public function load_plugin_textdomain()
	{

		load_plugin_textdomain(
			'plugin-name',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);
	}
}
