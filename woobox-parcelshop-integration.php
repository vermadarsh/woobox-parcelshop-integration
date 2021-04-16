<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://woobox.dk/
 * @since             1.0.0
 * @package           Woobox_Parcelshop_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       Woobox Parcelshop Integration
 * Plugin URI:        https://woobox.dk/
 * Description:       This plugin adds a shipping options available for Scandinavian countries.
 * Version:           1.0.0
 * Author:            Parth Patel
 * Author URI:        https://woobox.dk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woopi
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOPI_PLUGIN_VERSION', '1.0.0' );

// Plugin URL.
if ( ! defined( 'WOOPI_PLUGIN_URL' ) ) {
	define( 'WOOPI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin path.
if ( ! defined( 'WOOPI_PLUGIN_PATH' ) ) {
	define( 'WOOPI_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woobox-parcelshop-integration-activator.php
 */
function activate_woobox_parcelshop_integration() {
	require_once 'includes/class-woobox-parcelshop-integration-activator.php';
	Woobox_Parcelshop_Integration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woobox-parcelshop-integration-deactivator.php
 */
function deactivate_woobox_parcelshop_integration() {
	require_once 'includes/class-woobox-parcelshop-integration-deactivator.php';
	Woobox_Parcelshop_Integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woobox_parcelshop_integration' );
register_deactivation_hook( __FILE__, 'deactivate_woobox_parcelshop_integration' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woobox_parcelshop_integration() {

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require 'includes/class-woobox-parcelshop-integration.php';
	$plugin = new Woobox_Parcelshop_Integration();
	$plugin->run();

}

/**
 * Check plugin requirement on plugins loaded, this plugin requires WooCommerce to be installed and active.
 *
 * @since    1.0.0
 */
function woopi_plugins_loaded_callback() {

	$wc_active = in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ), true );

	// Check if WooCommerce is active or not.
	if ( current_user_can( 'activate_plugins' ) && true !== $wc_active ) {
		add_action( 'admin_notices', 'woopi_admin_notices_callback' );
	} else {
		run_woobox_parcelshop_integration();
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woopi_plugin_action_links__callback' );
	}

}

add_action( 'plugins_loaded', 'woopi_plugins_loaded_callback' );

/**
 * Show admin notice in case of Gravity Forms plugin is missing.
 *
 * @since    1.0.0
 */
function woopi_admin_notices_callback() {

	$woopi_plugin = __( 'Woobox Parcelshop Integration', 'woopi' );
	$wc_plugin    = __( 'WooCommerce', 'woopi' );
	?>
	<div class="error">
		<p>
			<?php
			/* translators: 1: %s: string tag open, 2: %s: strong tag close, 3: %s: this plugin, 4: %s: woocommerce plugin */
			echo wp_kses_post( sprintf( __( '%1$s%3$s%2$s is ineffective as it requires %1$s%4$s%2$s to be installed and active. Click %5$shere%6$s to install or activate it.', 'woopi' ), '<strong>', '</strong>', $woopi_plugin, $wc_plugin, '<a target="_blank" href="' . admin_url( 'plugin-install.php?s=woocommerce&tab=search&type=term' ) . '">', '</a>' ) );
			?>
		</p>
	</div>
	<?php

}

/**
 * Settings link on plugin listing page.
 *
 * @param array $links Holds the list of plugin links.
 * @return array
 * @since 1.0.0
 */
function woopi_plugin_action_links__callback( $links ) {
	$this_plugin_links = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=woobox' ) . '">' . __( 'Settings', 'woopi' ) . '</a>',
	);

	return array_merge( $links, $this_plugin_links );
}
