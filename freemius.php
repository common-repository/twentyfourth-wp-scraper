<?php

if (!function_exists('sct_fs')) {
    // Create a helper function for easy SDK access.
    function sct_fs()
    {
        global  $sct_fs;

        if (!isset($sct_fs)) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';
            $sct_fs = fs_dynamic_init(array(
                'id'             => '6513',
                'slug'           => 'sss-client',
                'premium_slug'   => 'twentyfourth-wp-scraper-premium',
                'type'           => 'plugin',
                'public_key'     => 'pk_b3a0e417f82f7f5e412ef430ca357',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 7,
                    'is_require_payment' => false,
                ),
                'menu'           => array(
                    'slug'    => 'sss_client',
                    'support' => false,
                ),
                'is_live'        => true,
            ));
        }

        return $sct_fs;
    }

    // Init Freemius.
    sct_fs();
    // Signal that SDK was initiated.
    do_action('sct_fs_loaded');
}
