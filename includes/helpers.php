<?php


/**
 * Register all helpers for the plugin
 *
 * @link       https://github.com/maxwellmandela
 * @since      0.6.5
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/includes
 */



if (!function_exists('is_woocommerce_activated')) {
    /**
     * Check if WooCommerce is activated
     * @return bool
     */
    function tw_wp_scraper_is_woocommerce_activated()
    {
        return class_exists('WooCommerce') ? true : false;
    }
}
