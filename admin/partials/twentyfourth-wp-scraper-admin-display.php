<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/maxwellmandela
 * @since      0.6.5
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/admin/partials
 */

$plugin = new TwentyFourth_WP_Scraper();
$plugin_name = $plugin->get_display_name();
$plugin_version = $plugin->get_version();

$api_token = get_user_meta(wp_get_current_user()->ID, 'sss_client_service_api_token', true);
$notified = get_user_meta(wp_get_current_user()->ID, 'sss_client_notified', true);
$has_woocommerce = tw_wp_scraper_is_woocommerce_activated();
?>

<div class="wp-scraper--header">
    <h6> <?php echo esc_html($plugin_name) . ' - v' . esc_html($plugin_version); ?>
    </h6>

    <small id="blurp" style="margin-right: 150px;">
        <?php echo esc_html('Made with') ?> <i class="fa fa-heart text-red"></i> <?php echo esc_html('by Max(@24th)') ?>
    </small>
</div>

<script>
    // window.api_base = "http://127.0.0.1:5000"
    window.api_base = "https://p2pwoocommerce-demo.herokuapp.com"
    // window.api_service_url = "http://sss-client.test:3000"
    window.api_service_url = "https://twentyfourthwebscraper.herokuapp.com"
    window.can_make_configs_public = false
</script>

<div class="sss-client">

    <!-- Pre flight check -->
    <?php
    if (!$api_token) {
    ?>
        <div id="app" class="wp-scraper--container">
            <div class="card">
                <div class="card-body">
                    <p> <?php echo esc_html('Hi new comer!!') ?> <i class="em em-smile"></i> <?php echo esc_html('Lets get you all setup and blah!') ?></p>
                    <p class="alert alert-success"> <i class="fa fa-star"></i> <?php echo esc_html("If you've gone ahead and chosen a plan already, don't worry, it's not going anywhere") ?> <i class="em em-wink"></i> </p>

                    <h4> <i class="fa fa-exclamation-circle"></i> <?php echo esc_html("Terms of Use") ?> </h4>
                    <p><?php echo esc_html("We require email addresses to complete the signup, this could be the email you used to signup for this website but
                        you can change it in the form below.") ?>
                    </p>
                    <p><?php echo esc_html("We do not share your email address with any third pary organizations or businesses") ?> </p>
                    <br>

                    <p><?php echo esc_html("When you're ready, click this") ?> <i class="em em-point_down"></i></p>
                    <form method="post" id="request-token--form">
                        <label for="email"><?php echo esc_html("Email") ?></label>
                        <input id="email" style="margin-bottom: 10px;" name="email" class='form-control' placeholder='Enter email address' value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>">

                        <label for="">
                            <?php echo esc_html("I accept these terms of use") ?>
                            <input type="checkbox" required name="accept-terms" id="accept-terms">
                            <strong class="help-block"><?php echo esc_html("(check this box to affirm strongly!)") ?></strong>
                        </label>

                        <br>

                        <button class="btn btn-primary"><?php echo esc_html("Click to Load Scraper Now!") ?></button>
                    </form>

                    <div id="feedback-details" style="margin-top: 15px;"></div>
                </div>
            </div>
        </div>
    <?php
    } else {
        if (sct_fs()->is_not_paying() && !$notified) {
            echo '<div class="upgrade-features alert alert-info"><h5>' . __('You are missing some awesome premium features', 'sss-client') . ' <i class="fa fa-star"></i></h5>';
            echo '<ul>';
            echo '<li>Scrape more items(upto 10k per day)</li>';
            echo '<li>Follow item link for details</li>';
            echo '<li>Add users</li>';
            echo '<li>..and even more business features before v1.0!</li>';
            echo '</ul>';
            echo '<a href="' . sct_fs()->get_upgrade_url() . '">' .
                __('Upgrade Now!', 'sss-client') .
                '</a>';
            echo '<br><br><form method="post" action id="remove-upgrade-notification"> <button type="submit" id="upgrade-notif-btn" class="btn btn-default btn-sm">' .
                __('Upgrade later', 'sss-client') .
                ' <i class="fa fa-times"></i> </button><input type="hidden" name="upgrade-notif" value="yes"></form>';
            echo '
        </div>';
        }

        if (isset($_POST['upgrade-notif'])) {
            add_user_meta(wp_get_current_user()->ID, 'sss_client_notified', sanitize_text_field($_POST['upgrade-notif']), true);
        }
    ?>

        <!-- Application -->
        <script>
            window.api_token = "<?php echo $api_token; ?>"
        </script>

        <div id="app" class="is-wp">
            <sss-client is_wp="yes" is_premium="<?php echo sct_fs()->can_use_premium_code() ? true : false ?>" has_woocommerce="<?php echo $has_woocommerce; ?>" />
        </div>
</div>

<?php
    }
