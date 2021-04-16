<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://woobox.dk/
 * @since      1.0.0
 *
 * @package    Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/includes
 * @author     Parth Patel <info@woobox.dk>
 */
class Woobox_Parcelshop_Integration_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woobox-parcelshop-integration',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
