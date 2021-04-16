<?php
/**
 * WooCommerce custom tab for plugin settings.
 *
 * @version 1.0.0
 * @package Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/admin/partials/
 */

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WOOPI_Settings_Tab', false ) ) {
	return new WOOPI_Settings_Tab();
}

/**
 * WC_Settings_Products.
 */
class WOOPI_Settings_Tab extends WC_Settings_Page {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id    = 'woobox';
		$this->label = __( 'WooBox', 'woopi' );

		// Parent constructor.
		parent::__construct();
	}

	/**
	 * Get sections.
	 *
	 * @return array
	 */
	public function get_sections() {
		$sections = array(
			''    => __( 'PostNord', 'woopi' ),
			'gls' => __( 'GLS', 'woopi' ),
		);

		return apply_filters( "woopi_wc_get_sections_{$this->id}", $sections );
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;
		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		WC_Admin_Settings::save_fields( $settings );

		if ( $current_section ) {
			do_action( "woocommerce_update_options_{$this->id}_{$current_section}" );
		}
	}

	/**
	 * Get settings array.
	 *
	 * @param string $current_section Current section name.
	 * @return array
	 */
	public function get_settings( $current_section = '' ) {
		if ( 'gls' === $current_section ) {
			$settings = apply_filters(
				'woopi_wc_gls_settings',
				array()
			);
		} else {
			$settings = apply_filters(
				'woopi_wc_postnord_settings',
				array()
			);
		}

		return apply_filters( "woopi_wc_get_settings_{$this->id}", $settings, $current_section );
	}
}

return new WOOPI_Settings_Tab();
