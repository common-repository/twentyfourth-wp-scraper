<?php

/**
 * The admin-specific functionality of the plugin.
 *
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/admin
 */

/**
 *
 * @package    TwentyFourth_WP_Scraper
 * @subpackage TwentyFourth_WP_Scraper/admin
 * @author     Maxwell Mandela <mxmandela@gmail.com>
 */
class TwentyFourth_WP_Scraper_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @access   private
	 * @var      string    $twentyfourth_wp_scraper    The ID of this plugin.
	 */
	private $twentyfourth_wp_scraper;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.6.5
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.6.5
	 * @param      string    $twentyfourth_wp_scraper       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($twentyfourth_wp_scraper, $version)
	{

		$this->twentyfourth_wp_scraper = $twentyfourth_wp_scraper;
		$this->version = $version;

		add_action('init', array($this, 'create_menu'));

		$this->load_dependencies();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.6.5
	 */
	public function enqueue_styles($hook_suffix)
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in TwentyFourth_WP_Scraper_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The TwentyFourth_WP_Scraper_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ($hook_suffix == 'toplevel_page_sss_client') {
			wp_enqueue_style($this->twentyfourth_wp_scraper, plugin_dir_url(__FILE__) . 'css/twentyfourth-wp-scraper-admin.css', array(), $this->version, 'all');

			wp_enqueue_style('{$this->twentyfourth_wp_scraper}-style',  plugin_dir_url(__FILE__) . 'css/app.css', [], false);
			wp_enqueue_style('{$this->twentyfourth_wp_scraper}-styles', plugin_dir_url(__FILE__) . 'css/style.css', [], false);
			wp_enqueue_style('{$this->twentyfourth_wp_scraper}-emoji', "https://emoji-css.afeld.me/emoji.css", [], false);
			wp_enqueue_style('{$this->twentyfourth_wp_scraper}-fa', "https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css", [], false);
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.6.5
	 */
	public function enqueue_scripts($hook_suffix)
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in TwentyFourth_WP_Scraper_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The TwentyFourth_WP_Scraper_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ($hook_suffix == 'toplevel_page_sss_client') {
			wp_enqueue_script($this->twentyfourth_wp_scraper, plugin_dir_url(__FILE__) . 'js/twentyfourth-wp-scraper-admin.js', array('jquery'), $this->version, false);

			wp_enqueue_script('app', plugin_dir_url(__FILE__) . 'js/app.js', ['lodash'],  $this->version, true);
			wp_enqueue_script('sweetalert', 'https://unpkg.com/sweetalert/dist/sweetalert.min.js', [],  $this->version, true);
			wp_enqueue_script('wp', plugin_dir_url(__FILE__) . 'js/wp.js', [],  $this->version, true);

			$title_nonce = wp_create_nonce('title_example');
			wp_localize_script('wp', 'wp_scraper_ajax', array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => $title_nonce,
			));
		}
	}

	public function create_menu()
	{
		$capability = 'administrator';

		add_menu_page(
			'TwentyFourth  WP Scraper - Scraping for Humans!',
			'WP Scraper',
			$capability,
			'sss_client',
			array($this, 'render_admin_page'),
			'dashicons-search',
			20
		);
	}

	public function render_admin_page()
	{
		include_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/twentyfourth-wp-scraper-admin-display.php';
	}

	public function load_dependencies()
	{

		/**
		 * Http Requests handler
		 */
		include_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-twentyfourth-wp-scraper-admin-ajax.php';
	}
}
