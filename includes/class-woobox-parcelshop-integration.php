<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://woobox.dk/
 * @since      1.0.0
 *
 * @package    Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/includes
 * @author     Parth Patel <info@woobox.dk>
 */
class Woobox_Parcelshop_Integration {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woobox_Parcelshop_Integration_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WOOPI_PLUGIN_VERSION' ) ) {
			$this->version = WOOPI_PLUGIN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woobox-parcelshop-integration';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woobox_Parcelshop_Integration_Loader. Orchestrates the hooks of the plugin.
	 * - Woobox_Parcelshop_Integration_I18n. Defines internationalization functionality.
	 * - Woobox_Parcelshop_Integration_Admin. Defines all hooks for the admin area.
	 * - Woobox_Parcelshop_Integration_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once 'class-woobox-parcelshop-integration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once 'class-woobox-parcelshop-integration-i18n.php';

		/**
		 * The class responsible for defining all admin static functions
		 * side of the site.
		 */
		require_once 'woobox-parcelshop-integration-functions.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once __DIR__ . '/../admin/class-woobox-parcelshop-integration-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once __DIR__ . '/../public/class-woobox-parcelshop-integration-public.php';

		$this->loader = new Woobox_Parcelshop_Integration_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woobox_Parcelshop_Integration_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Woobox_Parcelshop_Integration_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Woobox_Parcelshop_Integration_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'woopi_enqueue_admin_assets' );
		$this->loader->add_filter( 'woocommerce_shipping_methods', $plugin_admin, 'woopi_add_shipping_methods' );
		$this->loader->add_action( 'woocommerce_shipping_init', $plugin_admin, 'woopi_wc_shipping_init' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'woopi_meta_boxes' );
		$this->loader->add_filter( 'woocommerce_shipping_zone_shipping_methods', $plugin_admin, 'woopi_woocommerce_shipping_zone_shipping_methods', 10, 4 );
		$this->loader->add_filter( 'woocommerce_get_settings_pages', $plugin_admin, 'woopi_wc_settings_pages' );
		$this->loader->add_filter( 'woopi_wc_postnord_settings', $plugin_admin, 'woopi_wc_postnord_settings_fields' );
		$this->loader->add_filter( 'woopi_wc_gls_settings', $plugin_admin, 'woopi_wc_gls_settings_fields' );
		$this->loader->add_action( 'admin_footer', $plugin_admin, 'woopi_admin_footer_assets' );
		$this->loader->add_action( 'wp_ajax_woopi_fetch_print_label_modal_html', $plugin_admin, 'woopi_fetch_print_label_modal_html' );
		$this->loader->add_action( 'wp_ajax_woopi_generate_label', $plugin_admin, 'woopi_generate_label' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Woobox_Parcelshop_Integration_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'woopi_enqueue_public_assets' );
		$this->loader->add_action( 'woocommerce_after_checkout_billing_form', $plugin_public, 'woopi_pickup_integration' );
		$this->loader->add_action( 'wp_ajax_woopi_fetch_pickuppoints_html', $plugin_public, 'woopi_fetch_pickuppoints_html' );
		$this->loader->add_action( 'wp_ajax_nopriv_woopi_fetch_pickuppoints_html', $plugin_public, 'woopi_fetch_pickuppoints_html' );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'woopi_add_selected_pickup_point' );
		/*$this->loader->add_filter( 'woocommerce_get_order_item_totals', $plugin_public, 'woopi_order_row_pickup_data_email', 99, 2 );*/
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Woobox_Parcelshop_Integration_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}
