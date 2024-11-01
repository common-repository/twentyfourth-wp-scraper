<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/maxwellmandela
 * @since      0.2.1
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/admin
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */


add_action('wp_ajax_save_api_token', 'sssclient_save_api_token');
function sssclient_save_api_token()
{
    try {
        check_ajax_referer('title_example');
        $token = sanitize_text_field($_POST['api_token']);
        $is_accepted_terms = sanitize_text_field($_POST['accept_terms']);

        if ($is_accepted_terms != 'on') {
            return wp_send_json_error('Please the terms and conditions');
        }

        add_user_meta(wp_get_current_user()->ID, 'sss_client_service_api_token', $token, true);
        wp_send_json_success("Finished saving configurations");
    } catch (Exception $e) {
        wp_send_json_error("Sorry, an error occured");
    }
}

add_action('wp_ajax_begin_export', 'sssclient_begin_export');
function sssclient_begin_export()
{
    try {
        check_ajax_referer('title_example');

        $filters = array(
            "form" => array(
                "flags" => FILTER_FORCE_ARRAY,
            ),
            "items"   => array(
                "flags" => FILTER_REQUIRE_ARRAY,
            ),
        );

        $request_data = filter_input_array(INPUT_POST, $filters);

        /**
         * The form with data_map, preset_data_map arrays
         * @var array
         * 
         * */
        $data =  $request_data['form'];

        /**
         * The items to be saved/exported
         * @var array
         * 
         * */
        $items = $request_data['items'];

        // count saved items progress
        $saved = 0;

        for ($i = 0; $i < count($items); $i++) {
            $item = [];
            $current = $items[$i];

            // map keys to values
            foreach ($data['data_map'] as $key => $value) {
                $item[$key] = isset($current['content']) ?  $current['content'][$value] : $current[$value];
            }

            // for data that has custom values e.g post_type, 
            // for example when exporting as `page`
            // map keys to values
            foreach ($data['preset_data_map'] as $key => $value) {
                $item[$key] = $current[$value];
            }

            if ($data['entity'] == 'category') {
                wp_insert_category($item);
                $saved++;
            } elseif ($data['entity'] == 'post') {
                wp_insert_post($item, true);
                $saved++;
            }
        }


        wp_send_json_success($saved . " items saved");
    } catch (Exception $e) {
        wp_send_json_error("Sorry, an error occured whie exporting");
    }
}
