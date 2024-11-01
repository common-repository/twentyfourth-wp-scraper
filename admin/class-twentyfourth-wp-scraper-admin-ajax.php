<?php

/**
 * Ajax handler
 *
 * @link       https://github.com/maxwellmandela
 * @since      0.6.5
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/admin
 */

/**
 * Ajax handler
 *
 * Ajax handler for all http requests
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/admin
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */


class TwentyFourth_WP_Scraper_Ajax
{

    public function __construct()
    {
        add_action('wp_ajax_save_api_token', array($this, 'save_api_token'));
        add_action('wp_ajax_begin_export', array($this, 'begin_export'));
        add_action('wp_ajax_get_woo_categories', array($this, 'get_woo_categories'));

        // Cron for exporting items
        add_action('cron_schedules', array($this, 'add_custom_schedules'));
        if (!wp_next_scheduled('init_export_schedule')) {
            $time = strtotime(date('Y-m-d H') . ':00:00');
            wp_schedule_event($time, 'once_every_1m', 'init_export_schedule');
        }
        add_action('init_export_schedule', array($this, 'execute_export_schedule'));
    }

    /**
     * Get woo categories
     */
    public function get_woo_categories()
    {
        $taxonomy     = 'product_cat';
        $orderby      = 'name';
        $show_count   = 0;      // 1 for yes, 0 for no
        $pad_counts   = 0;      // 1 for yes, 0 for no
        $hierarchical = 1;      // 1 for yes, 0 for no  
        $title        = '';
        $empty        = 0;

        $args = array(
            'taxonomy'     => $taxonomy,
            'orderby'      => $orderby,
            'show_count'   => $show_count,
            'pad_counts'   => $pad_counts,
            'hierarchical' => $hierarchical,
            'title_li'     => $title,
            'hide_empty'   => $empty
        );

        $all_categories = get_categories($args);

        $product_categories = $this->serialize_categories($all_categories, $args);

        wp_send_json_success($product_categories);
    }

    /**
     * Serialize
     */
    private function serialize_categories($all_categories, $args)
    {
        $product_categories = [];

        foreach ($all_categories as $cat) {
            $category_id = $cat->term_id;

            if ($cat->category_parent == 0) {
                $args2 = array_merge($args, array(
                    'child_of'     => 0,
                    'parent'       => $category_id,
                ));

                $sub_cats = get_categories($args2);

                array_push($product_categories, [
                    'name' => $cat->name,
                    'id' => $category_id,
                ]);

                if ($sub_cats) {
                    foreach ($sub_cats as $sub_category) {
                        array_push($product_categories, [
                            'name' => $sub_category->name,
                            'id' => $sub_category->term_id,
                        ]);
                    }
                }
            }
        }

        return $product_categories;
    }


    /**
     * Save a copy of api token for auth
     */
    public function save_api_token()
    {
        try {
            check_ajax_referer('title_example');
            $token = sanitize_text_field($_POST['api_token']);
            $is_accepted_terms = sanitize_text_field($_POST['accept_terms']);

            if ($is_accepted_terms != 'on') {
                return wp_send_json_error('Please accept the terms and conditions');
            }

            if (get_user_meta(wp_get_current_user()->ID, 'sss_client_service_api_token', true)) {
                delete_user_meta(wp_get_current_user()->ID, 'sss_client_service_api_token');
            }

            add_user_meta(wp_get_current_user()->ID, 'sss_client_service_api_token', $token, true);
            wp_send_json_success("Finished saving configurations");
        } catch (Exception $e) {
            wp_send_json_error("Sorry, an error occured");
        }
    }


    /**
     * Generates featured image for item
     * 
     * @param string $image_url
     * @param string $post_id
     */
    private function generate_featured_image($image_url, $post_id)
    {
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        if (wp_mkdir_p($upload_dir['path']))     $file = $upload_dir['path'] . '/' . $filename;
        else                                    $file = $upload_dir['basedir'] . '/' . $filename;
        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        $res1 = wp_update_attachment_metadata($attach_id, $attach_data);
        $res2 = set_post_thumbnail($post_id, $attach_id);
    }

    /**
     * Get item key helper
     */
    private function get_item_value($current, $value)
    {
        $current = (array)$current;
        $data = isset($current['content']) ? $current['content'] : $current;
        $data = (array)$data;

        return isset($data[$value]) ? $data[$value] : 'no ' . $value;
    }


    /**
     * Export
     * 
     * Entity is the item we want such as post, category, product etc that the plugin 
     * supports
     */
    public function begin_export()
    {
        try {
            check_ajax_referer('title_example');

            $filters = array(
                "form" => array(
                    "flags" => FILTER_FORCE_ARRAY,
                ),
                "schedule" => array(
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


            // schedule config
            if (isset($request_data['schedule']) && isset($request_data['schedule']['schedule_start_page'])) {
                $schedule_ids = $this->start_scheduling($data, $request_data);
                wp_send_json_success($schedule_ids);
            } else {

                /**
                 * The items to be saved/exported
                 * @var array
                 * 
                 * */
                $items = $request_data['items'];

                // end for loop
                $saved = $this->run_export($items, $data);
                wp_send_json_success($saved . " items saved");
            }
        } catch (Exception $e) {
            wp_send_json_error("Sorry, an error occured whie exporting");
        }
    }

    /**
     * Schedule export, add custom interval: every minute
     */
    public function add_custom_schedules($schedules)
    {
        $schedules['once_every_1m'] = [
            'interval' => 15,
            'display' => 'Once every minute'
        ];
        return $schedules;
    }

    public function execute_export_schedule()
    {
        $posts = query_posts([
            'post_title' => '24th scraper schedule',
            'post_type' => 'schedule',
            'post_status' => 'draft',
            'posts_per_page' => 1
        ]);

        foreach ($posts as $post) {
            $term = (object)json_decode(get_post_meta($post->ID, 'schedule_details', true));
            $res = wp_remote_get($term->url);
            $result = (object)json_decode($res['body']);

            $items = $result->data;
            $data = $term->data;

            if (count($items)) {
                error_log('Run a cron job found ' . count($items) . ' items from link ' . $term->url);
                $this->run_export($items, $data);
            } else {
                error_log('Run a cron job, found no items from  ' . $term->url);
            }

            wp_delete_post($post->ID);
        }
    }

    /**
     * Schedule exporting
     * @param $data - the config form
     * @param $request_data - the request payload for query
     */
    private function start_scheduling($data, $request_data)
    {
        $schedule_form =  $request_data['schedule'];
        $config = [
            'site' => $schedule_form['site'],
            'page' => $schedule_form['schedule_start_page'],
            'perPage' => $schedule_form['schedule_items_per_page'],
            'api' => $schedule_form['api'],
            'api_token' => $schedule_form['api_token'],
        ];

        $pages = [];

        for ($i = $schedule_form['schedule_start_page']; $i < $schedule_form['schedule_end_page'] + 1; $i++) {
            $url = $config['api'] . '/api/data-objects?site=' . $config['site'] . '&page=' . $i . '&perPage=' . $config['perPage'] . '&api_token=' . $config['api_token'];

            $PID = wp_insert_post([
                'post_title' => '24th scraper schedule',
                'post_type' => 'schedule',
            ]);

            $schedule_details = json_encode([
                'url' => $url,
                'data' => $data,
            ]);
            add_post_meta($PID, 'schedule_details', $schedule_details);

            array_push($pages, $PID);
        }

        return $pages;
    }


    private function run_export($items, $data)
    {
        $data = (array)$data;

        /**
         * Count saved items progress
         * @var int
         */
        $saved = 0;

        for ($i = 0; $i < count($items); $i++) {
            $post_id = null;
            $current_item = (array)$items[$i];

            if (isset($current_item) && isset($current_item['title'])) {

                /**
                 * check if already saved
                 * @var int the post ID or 0 if doesnt exist
                 */
                try {
                    $found_post = post_exists($current_item['title'], '', '', '');
                } catch (\Throwable $th) {
                    $q = query_posts([
                        'post_title' => $current_item['title'],
                        'posts_per_page' => 1
                    ]);
                    if (count($q)) {
                        $found_post = $q[0]->ID;
                    } else {
                        $found_post = false;
                    }
                }

                if ($found_post && isset($current_item['is_dropped'])) {
                    wp_delete_post($found_post);
                    break;
                }
            }

            $item = [];
            $current = $current_item;

            // map keys to values
            foreach ($data['data_map'] as $key => $value) {
                $item[$key] = $this->get_item_value($current, $value);
            }


            // for data that has custom values e.g post_type, 
            // for example when exporting as `page`
            // map keys to values
            foreach ($data['preset_data_map'] as $key => $value) {
                foreach ($value as $key => $value) {
                    $item[$key] = $value;
                }
            }


            if ($data['entity'] == 'category') {
                $post_id = wp_insert_category($item);
                $saved++;
            }

            // if is exporting products
            if ($data['entity'] == 'product') {
                $post_id = $this->export_as_product($data, $item, $current);
                $saved++;
            }

            if ($data['entity'] == 'post' || $data['entity'] == 'page') {
                $post_id = wp_insert_post($item, true);
                $saved++;
            }

            if (isset($data['attachment']) && $post_id) {
                foreach ($data['attachment'] as $key => $value) {
                    $this->generate_featured_image($this->get_item_value($current, $value), $post_id);
                }
            }
        }

        return $saved;
    }

    private function export_as_product($data, $item, $current)
    {
        $post_id = wp_insert_post($item);

        // add object_terms
        if (isset($data['object_term'])) {
            foreach ($data['object_term'] as $key => $value) {
                if (is_numeric($value)) {
                    $value = (int)$value;
                }
                wp_set_object_terms($post_id, $value, $key, true);
            }
        }

        // add post_meta
        if (isset($data['post_meta'])) {
            foreach ($data['post_meta'] as $key => $value) {
                $val = $this->get_item_value($current, $value);

                // just fixing value if the field is price-ish
                if (strpos($val, 'price') !== false) {
                    $val = (float)$val;
                }

                update_post_meta($post_id, $key, $val);
            }
        }


        // add custom data 
        if (isset($data['custom'])) {
            if ($data['custom']['type'] == 'object_term') {
                try {
                    wp_set_object_terms($post_id, $data['custom']['term'], $data['custom']['taxonomy'], true);
                } catch (\Throwable $th) {
                    // throw $th;
                }
            }

            if ($data['custom']['type'] == 'post_meta') {
                update_post_meta($post_id, $data['custom']['key'], $data['custom']['value']);
            }
        }

        return $post_id;
    }
}


$ajax = new TwentyFourth_WP_Scraper_Ajax();
