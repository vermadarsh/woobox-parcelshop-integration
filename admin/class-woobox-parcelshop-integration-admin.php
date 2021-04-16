<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://woobox.dk/
 * @since      1.0.0
 *
 * @package    Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/admin
 * @author     Parth Patel <info@woobox.dk>
 */
class Woobox_Parcelshop_Integration_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function woopi_enqueue_admin_assets() {
		$post = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		if ( isset( $post ) && ! empty( $post ) && 'shop_order' === get_post_type( $post ) ) {
			wp_enqueue_style(
				$this->plugin_name,
				WOOPI_PLUGIN_URL . 'admin/css/woobox-parcelshop-integration-admin.css',
				array(),
				filemtime( WOOPI_PLUGIN_PATH . 'admin/css/woobox-parcelshop-integration-admin.css' )
			);
		}

		// Enqueue script.
		wp_enqueue_script(
			$this->plugin_name,
			WOOPI_PLUGIN_URL . 'admin/js/woobox-parcelshop-integration-admin.js',
			array( 'jquery' ),
			filemtime( WOOPI_PLUGIN_PATH . 'admin/js/woobox-parcelshop-integration-admin.js' ),
			true
		);
		wp_localize_script(
			$this->plugin_name,
			'WOOPI_Admin_JS_Obj',
			array(
				'woopi_ajax_url'                        => admin_url( 'admin-ajax.php' ),
				'woopi_nonce'                           => wp_create_nonce( 'woopi-nonce' ),
				'loader_url'                            => includes_url( 'images/spinner-2x.gif' ),
				'woopi_remove_custom_shipping_rate_row' => __( 'Are you sure to remove the selected rates?', 'woopi' ),
				'waiting_modal_header_text'             => __( 'Please wait...', 'woopi' ),
			)
		);

	}

	/**
	 * Filter added to enable shipping methods.
	 *
	 * @param array $methods Holds the array of shipping methods with their class names.
	 *
	 * @return array
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_add_shipping_methods( $methods ) {
		// PostNord Shipping Methods.
		$shop_origin      = woopi_get_shop_origin();
		$shipping_methods = woopi_get_available_shipping_methods( $shop_origin );
		if ( ! empty( $shipping_methods ) ) {
			$methods = array_merge( $methods, $shipping_methods );
		}

		// GLS Shipping Methods.
		$gls_shipping_methods = woopi_get_gls_shipping_methods();
		if ( ! empty( $gls_shipping_methods ) ) {
			$methods = array_merge( $methods, $gls_shipping_methods );
		}

		return $methods;
	}

	/**
	 * Defining the class here to initiate the shipping method class.
	 *
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_wc_shipping_init() {
		// Introducing PostNord Shipping Methods.
		$shop_origin               = woopi_get_shop_origin();
		$postnord_shipping_methods = woopi_get_available_shipping_methods( $shop_origin );
		$origins                   = array( 'FI', 'DK', 'SE', 'NO' );
		foreach ( $origins as $origin ) {

			if ( ! empty( $postnord_shipping_methods ) && is_array( $postnord_shipping_methods ) ) {
				foreach ( $postnord_shipping_methods as $method_slug => $shipping_method_title ) {
					$file = WOOPI_PLUGIN_PATH . "admin/partials/postnord-shipping-methods/shipping-{$origin}/class-{$method_slug}.php";
					woopi_include_file_if_exists( $file );
				}
			}
		}

		// Introducing GLS Shipping Methods.
		$gls_shipping_methods = woopi_get_gls_shipping_methods();

		if ( ! empty( $gls_shipping_methods ) && is_array( $gls_shipping_methods ) ) {
			foreach ( $gls_shipping_methods as $method_slug => $gls_shipping_method ) {
				$file = WOOPI_PLUGIN_PATH . "admin/partials/gls-shipping-methods/class-{$method_slug}.php";
				woopi_include_file_if_exists( $file );
			}
		}
	}

	/**
	 * Hooking in to add custom meta box on shop order edit page.
	 *
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_meta_boxes() {
		// Pickup metabox for pickup by PostNord.
		add_meta_box(
			'woopi-display-pickup',
			__( 'PostNord Pickup', 'woopi' ),
			array( $this, 'woopi_postnord_order_pickup_metabox_callback' ),
			'shop_order',
			'side'
		);

		// GLS carrier metabox.
		add_meta_box(
			'woopi-generate-gls-label',
			__( 'GLS Carrier', 'woopi' ),
			array( $this, 'woopi_gls_carrier_metabox_callback' ),
			'shop_order',
			'side'
		);
	}

	/**
	 * The callback function to display the pickup location.
	 *
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_postnord_order_pickup_metabox_callback() {
		$file = __DIR__ . '/partials/metaboxes/postnord-order-pickup.php';
		woopi_include_file_if_exists( $file );
	}

	/**
	 * Callback to display the label printing option from GLS.
	 *
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_gls_carrier_metabox_callback() {
		$file = __DIR__ . '/partials/metaboxes/gls-order-carrier.php';
		woopi_include_file_if_exists( $file );
	}

	/**
	 * Filter hooked to manage the extra HTML on shipping methods.
	 *
	 * @param array $methods Holds the shipping methods array with settings.
	 * @param array $raw_methods Holds the list of shipping methods.
	 * @return array
	 *
	 * @since 1.0.0
	 * @author Parth Patel <info@woobox.dk>
	 */
	public function woopi_woocommerce_shipping_zone_shipping_methods( $methods, $raw_methods ) {
		$shop_origin               = woopi_get_shop_origin();
		$postnord_shipping_methods = woopi_get_available_shipping_methods( $shop_origin );
		$gls_shipping_methods      = woopi_get_gls_shipping_methods();
		$shipping_methods          = array_merge( $postnord_shipping_methods, $gls_shipping_methods );

		foreach ( $raw_methods as $raw_method ) {
			$instance_id = $raw_method->instance_id;
			$method_id   = $raw_method->method_id;

			if ( ! empty( $shipping_methods ) && array_key_exists( $method_id, $shipping_methods ) ) {
				$should_be_called_class                  = $shipping_methods[ $method_id ];
				$shipping_class_obj                      = new $should_be_called_class();
				$html                                    = $shipping_class_obj->woopi_add_custom_rates_html();
				$methods[ $instance_id ]->settings_html .= $html;
			}
		}

		return $methods;
	}

	/**
	 * Overridden the hook: woocommerce_get_settings_pages
	 * to include our custom tab.
	 *
	 * @param array $settings Holds the WooCommerce settings array.
	 * @return array
	 */
	public function woopi_wc_settings_pages( $settings ) {
		$settings = include __DIR__ . '/partials/class-woopi-settings-tab.php';

		return $settings;
	}

	/**
	 * Admin settings for PostNord.
	 *
	 * @param array $fields Holds the PostNord settings fields array.
	 * @return array
	 */
	public function woopi_wc_postnord_settings_fields( $fields ) {
		$developer_api_link = '<a href="https://developer.postnord.com/login/" target="_blank" title="' . __( 'PostNord Developer API', 'woopi' ) . '">' . __( 'here', 'woopi' ) . '</a>';
		$gmaps_api_link     = '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key/" target="_blank" title="' . __( 'Google Maps API', 'woopi' ) . '">' . __( 'here', 'woopi' ) . '</a>';
		$shop_base_address  = wc_get_base_location();
		$base_country       = ( isset( $shop_base_address['country'] ) && ! empty( $shop_base_address['country'] ) ) ? $shop_base_address['country'] : '';
		$fields             = array(
			'section_title'       => array(
				'name' => __( 'PostNord Integration', 'woopi' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_woobox-postnord_section_title',
			),
			'consumer-id'         => array(
				'name'     => __( 'Consumer ID', 'woopi' ),
				'type'     => 'text',
				/* translators: 1: %s: postnord developer account link. */
				'desc'     => sprintf( __( 'You can get one from %1$s.', 'woopi' ), $developer_api_link ),
				'desc_tip' => __( 'This field contains the Consumer ID that you get from the Developer API.', 'woopi' ),
				'id'       => 'woopi-api-consumer-id',
			),
			'google-maps-api-key' => array(
				'name'     => __( 'GMaps API Key', 'woopi' ),
				'type'     => 'text',
				/* translators: 1: %s: google maps api key link. */
				'desc'     => sprintf( __( 'You can get one from %1$s.', 'woopi' ), $gmaps_api_link ),
				'desc_tip' => __( 'This field contains the Google Maps API Key. Provide the key if you wish to enable maps for pickup on checkout.', 'woopi' ),
				'id'       => 'woopi-google-maps-api-key',
			),
			'shop-origin'         => array(
				'name'     => __( 'Shop Origin', 'woopi' ),
				'type'     => 'select',
				'options'  => array(
					$base_country => __( 'WC Settings Default', 'woopi' ),
					'DK'          => __( 'Denmark', 'woopi' ),
					'SE'          => __( 'Sweden', 'woopi' ),
					'NO'          => __( 'Norway', 'woopi' ),
					'FI'          => __( 'Finland', 'woopi' ),
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'Select the custom shop origin.', 'woopi' ),
				'default'  => '',
				'id'       => 'woopi-shop-origin',
			),
			'section_end'         => array(
				'type' => 'sectionend',
				'id'   => 'wc_woobox-postnord_section_end',
			),
		);

		return $fields;
	}

	/**
	 * Admin settings for GLS.
	 *
	 * @param array $fields Holds the GLS settings fields array.
	 * @return array
	 */
	public function woopi_wc_gls_settings_fields( $fields ) {
		$gmaps_api_link = '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key/" target="_blank" title="' . __( 'Google Maps API', 'woopi' ) . '">' . __( 'here', 'woopi' ) . '</a>';
		$fields         = array(
			'section_title'                          => array(
				'name' => __( 'GLS Integration', 'woopi' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_woobox-gls_section_title',
			),
			'webservice-login'                       => array(
				'name'     => __( 'Webservice Login', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the webservice username.', 'woopi' ),
				'id'       => 'woopi-api-webservice-login',
			),
			'webservice-password'                    => array(
				'name'     => __( 'Webservice Password', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the webservice password.', 'woopi' ),
				'id'       => 'woopi-api-webservice-password',
			),
			'gls-agency-code'                        => array(
				'name'     => __( 'GLS Agency Code', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the webservice agency code.', 'woopi' ),
				'id'       => 'woopi-api-gls-agency-code',
			),
			'google-maps-api-key'                    => array(
				'name'     => __( 'GMaps API Key', 'woopi' ),
				'type'     => 'text',
				/* translators: 1: %s: google maps api key link. */
				'desc'     => sprintf( __( 'You can get one from %1$s.', 'woopi' ), $gmaps_api_link ),
				'desc_tip' => __( 'This field contains the Google Maps API Key. Provide the key if you wish to enable maps for pickup on checkout.', 'woopi' ),
				'id'       => 'woopi-google-maps-api-key',
			),
			'section_end'                            => array(
				'type' => 'sectionend',
				'id'   => 'wc_woobox-postnord_section_end',
			),
			'section-title-label-printing'           => array(
				'name' => __( 'Label Printing', 'woopi' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_woobox-gls_label_printing_section_title',
			),
			'webservice-login-label-printing'        => array(
				'name'     => __( 'Webservice Login', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the webservice username.', 'woopi' ),
				'id'       => 'woopi-api-webservice-login-label-printing',
			),
			'webservice-password-label-printing'     => array(
				'name'     => __( 'Webservice Password', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the webservice password.', 'woopi' ),
				'id'       => 'woopi-api-webservice-password-label-printing',
			),
			'customer-id-label-printing'             => array(
				'name'     => __( 'Customer ID', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the webservice customer ID. The same as webservice username.', 'woopi' ),
				'id'       => 'woopi-api-customer-id-label-printing',
			),
			'contact-id-label-printing'              => array(
				'name'     => __( 'Contact ID', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the webservice contact ID.', 'woopi' ),
				'id'       => 'woopi-api-contact-id-label-printing',
			),
			'delivery-label-format-label-printing'   => array(
				'name'     => __( 'Delivery Label Format', 'woopi' ),
				'type'     => 'select',
				'options'  => array(
					'A4' => 'A4',
					'A5' => 'A5',
					'A6' => 'A6',
				),
				'class'    => 'wc-enhanced-select',
				'desc_tip' => __( 'This field contains the label format.', 'woopi' ),
				'default'  => 'A6',
				'id'       => 'woopi-api-delivery-label-format-label-printing',
			),
			'shop-return-service'                    => array(
				'name' => __( 'Enable Shop Return Service', 'woopi' ),
				'type' => 'checkbox',
				'desc' => __( 'Check this checkbox to enable shop return service.', 'woopi' ),
				'id'   => 'woopi-api-shop-return-service-label-printing',
			),
			'shop-return-notification-email'         => array(
				'name' => __( 'Enable Shop Return Notification Email', 'woopi' ),
				'type' => 'checkbox',
				'desc' => __( 'Check this checkbox to enable shop return notification email.', 'woopi' ),
				'id'   => 'woopi-api-shop-return-notification-email-label-printing',
			),
			'section_end-label-printing'             => array(
				'type' => 'sectionend',
				'id'   => 'wc_woobox-postnord_section_end-label-printing',
			),
			'section-title-shipping-address'         => array(
				'name' => __( 'Shipping Address', 'woopi' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wc_woobox-gls_shipping_address_section_title',
			),
			'return-contact-name'                    => array(
				'name'     => __( 'Return Contact Name', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the name used when printing labels.', 'woopi' ),
				'id'       => 'woopi-api-return-contact-name',
			),
			'address1-shipping-address'              => array(
				'name'     => __( 'Address Line 1', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the address line 1 used when printing labels.', 'woopi' ),
				'id'       => 'woopi-api-address-1-shipping-address',
			),
			'address2-shipping-address'              => array(
				'name'     => __( 'Address Line 2', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the address line 2 used when printing labels.', 'woopi' ),
				'id'       => 'woopi-api-address-2-shipping-address',
			),
			'postal-code-shipping-address'           => array(
				'name'     => __( 'Postal Code', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the postcode used when printing labels.', 'woopi' ),
				'id'       => 'woopi-api-postal-code-shipping-address',
			),
			'city-shipping-address'                  => array(
				'name'     => __( 'City', 'woopi' ),
				'type'     => 'text',
				'desc_tip' => __( 'This field contains the city used when printing labels.', 'woopi' ),
				'id'       => 'woopi-api-city-shipping-address',
			),
			'delivery-label-format-shipping-address' => array(
				'name'     => __( 'Country / State', 'woopi' ),
				'type'     => 'single_select_country',
				'default'  => get_option( 'woocommerce_default_country' ),
				'autoload' => false,
				'desc_tip' => __( 'This field contains the country & state used when printing labels.', 'woopi' ),
				'id'       => 'woopi-api-country-state-shipping-address',
			),
			'section_end-shipping-address'           => array(
				'type' => 'sectionend',
				'id'   => 'wc_woobox-postnord_section_end-shipping-address',
			),
		);

		return $fields;
	}

	/**
	 * Adding assets in admin footer.
	 *
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_admin_footer_assets() {
		$post = filter_input( INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT );

		if ( isset( $post ) && ! empty( $post ) && 'shop_order' === get_post_type( $post ) ) {
			$file = __DIR__ . '/partials/modals/woopi-gls-carrier.php';
			woopi_include_file_if_exists( $file );
		}
	}

	/**
	 * AJAX served to fetch label print modal content.
	 *
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_fetch_print_label_modal_html() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'woopi_fetch_print_label_modal_html' !== $action ) {
			echo 0;
			wp_die();
		}

		$orderid          = filter_input( INPUT_POST, 'orderid', FILTER_SANITIZE_NUMBER_INT );
		$date             = gmdate( 'Y-m-d' );
		$shipping_methods = woopi_get_gls_shipping_methods();
		ob_start();
		?>
		<table class="form-table woopi-print-label-input-data-tbl">
			<tbody>
			<tr>
				<th scope="row"><label
							for="woopi-package-weight"><?php esc_html_e( 'Package', 'woopi' ); ?></label>
				</th>
				<td>
					<input type="text" name="woopi-package-weight" id="woopi-package-weight"
							placeholder="E.g.: x,y,z...">
					<p class="description"><?php esc_html_e( 'This holds the package requirement based upon product\'s weight. For multiple packages, provide comma separated values. E.g.: 5,10,15,20, etc...', 'woopi' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label
							for="woopi-gls-service"><?php esc_html_e( 'GLS Service', 'woopi' ); ?></label>
				</th>
				<td>
					<select id="woopi-gls-service" name="woopi-gls-service">
						<?php
						if ( ! empty( $shipping_methods ) && is_array( $shipping_methods ) ) {
							foreach ( $shipping_methods as $slug => $shipping_method ) {
								$shipping_method = str_replace( '_', ' ', $shipping_method );
								?>
								<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $shipping_method ); ?></option>
								<?php
							}
						}
						?>
					</select>
					<p class="description"><?php esc_html_e( 'This holds the appropriate GLS shipping service.', 'woopi' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label
							for="woopi-shipping-date"><?php esc_html_e( 'Shipping Date', 'woopi' ); ?></label>
				</th>
				<td>
					<input type="text" name="woopi-shipping-date" id="woopi-shipping-date"
							value="<?php echo esc_attr( $date ); ?>"
							placeholder="YYYY-MM-DD">
					<p class="description"><?php esc_html_e( 'This holds the order\'s shipping date.', 'woopi' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label
							for="woopi-incoterm"><?php esc_html_e( 'Incoterm', 'woopi' ); ?></label>
				</th>
				<td>
					<select name="woopi-incoterm" id="woopi-incoterm">
						<option value="10"><?php esc_html_e( 'DDP - Delivered goods, all costs paid including export and import duties and taxes.', 'woopi' ); ?></option>
						<option value="20"><?php esc_html_e( 'DDP - Goods delivered, unpaid clearance, unpaid taxes.', 'woopi' ); ?></option>
						<option value="30"><?php esc_html_e( 'DDP - Goods delivered, export and import duties paid, unpaid taxes.', 'woopi' ); ?></option>
						<option value="40"><?php esc_html_e( 'DDP - Goods delivered, no clearance, no taxes.', 'woopi' ); ?></option>
						<option value="50"><?php esc_html_e( 'DDP - Goods delivered, export and import duties paid, low value exemption free authorization.', 'woopi' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'This holds the incoterm.', 'woopi' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label
							for="woopi-supp-reference-1"><?php esc_html_e( 'Supp Reference 1', 'woopi' ); ?></label>
				</th>
				<td>
					<input type="text" name="woopi-supp-reference-1" id="woopi-supp-reference-1"
							value="<?php echo esc_attr( $orderid ); ?>"
							placeholder="E.g.: 000">
					<p class="description"><?php esc_html_e( 'This holds the supp reference 1.', 'woopi' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row"><label
							for="woopi-supp-reference-2"><?php esc_html_e( 'Supp Reference 2', 'woopi' ); ?></label>
				</th>
				<td>
					<input type="text" name="woopi-supp-reference-2" id="woopi-supp-reference-2"
							placeholder="E.g.: 000">
					<p class="description"><?php esc_html_e( 'This holds the supp reference 2.', 'woopi' ); ?></p>
				</td>
			</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="button" class="button button-primary woopi-submit-print-label-data"
					data-orderid="<?php echo esc_attr( $orderid ); ?>"
					value="<?php esc_html_e( 'Proceed', 'woopi' ); ?>">
		</p>
		<?php
		$html = ob_get_clean();
		/* translators: 1: %d: order ID. */
		$header_text = sprintf( __( 'Print label for #%1$d', 'woopi' ), $orderid );

		$response = array(
			'message'     => 'gls-print-label-modal-html-fetched',
			'html'        => $html,
			'header_text' => $header_text,
		);
		wp_send_json_success( $response );
		wp_die();
	}

	/**
	 * Printing the label.
	 *
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_generate_label() {
		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'woopi_generate_label' !== $action ) {
			echo 0;
			wp_die();
		}

		$orderid         = filter_input( INPUT_POST, 'orderid', FILTER_SANITIZE_NUMBER_INT );
		$packages        = filter_input( INPUT_POST, 'packages', FILTER_SANITIZE_STRING );
		$gls_service     = filter_input( INPUT_POST, 'gls_service', FILTER_SANITIZE_STRING );
		$shipping_date   = filter_input( INPUT_POST, 'shipping_date', FILTER_SANITIZE_STRING );
		$incoterm        = filter_input( INPUT_POST, 'incoterm', FILTER_SANITIZE_STRING );
		$supp_reference1 = filter_input( INPUT_POST, 'supp_reference1', FILTER_SANITIZE_STRING );
		$supp_reference2 = filter_input( INPUT_POST, 'supp_reference2', FILTER_SANITIZE_STRING );
		$_order          = wc_get_order( $orderid );

		$references = array();
		if ( isset( $supp_reference1 ) && ! empty( $supp_reference1 ) ) {
			array_push( $references, $supp_reference1 );
		}
		if ( isset( $supp_reference2 ) && ! empty( $supp_reference2 ) ) {
			array_push( $references, $supp_reference2 );
		}

		if ( false !== stripos( $packages, ',' ) ) {
			$packages = array_map( 'trim', explode( ',', trim( $packages ) ) );
		} else {
			$packages = array_map( 'trim', array( $packages ) );
		}

		$parcels = array();
		if ( ! empty( $packages ) && is_array( $packages ) ) {
			foreach ( $packages as $package ) {
				$parcels[] = array(
					'weight' => $package,
				);
			}
		}

		$api_url               = 'https://api.gls-group.eu/public/v1/';
		$resource              = 'shipments';
		$api_url               = $api_url . $resource;
		$webservice_login      = get_option( 'woopi-api-webservice-login-label-printing' );
		$webservice_contact_id = get_option( 'woopi-api-contact-id-label-printing' );
		$webservice_password   = get_option( 'woopi-api-webservice-password-label-printing' );
		$credentials           = '';
		if ( ! empty( $webservice_login ) && ! empty( $webservice_password ) ) {
			$credentials = "{$webservice_login}:{$webservice_password}";
		}

		$label_size = get_option( 'woopi-api-delivery-label-format-label-printing' );

		$country_state = get_option( 'woopi-api-country-state-shipping-address' );
		if ( false !== stripos( $country_state, ':' ) ) {
			$country_state = explode( ':', $country_state );
			$country       = $country_state[0];
		} else {
			$country = $country_state;
		}

		$response = wp_remote_post(
			'http://api.gls.dk/ws/DK/V1/CreateShipment',
			array(
				'method' => 'POST',
				'body'   => array(
					'UserName'     => '2080060960',
					'Password'     => 'API1234',
					'Customerid'   => '2080060960',
					'Contactid'    => '208a144Uoo',
					'ShipmentDate' => '20200505',
					'Reference'    => 'Customers reference number',
					'Addresses'    => array(
						'Delivery'           => array(
							'Name1'      => 'Name1',
							'Name2'      => 'Name2',
							'Name3'      => 'Name3',
							'Street1'    => 'Street',
							'CountryNum' => '208',
							'ZipCode'    => '6000',
							'City'       => 'Kolding',
							'Contact'    => 'Contact person',
							'Email'      => 'adarsh.srmcem@gmail.com',
							'Phone'      => '+917318216218',
							'Mobile'     => '+917318216218',
						),
						'AlternativeShipper' => array(
							'Name1'      => 'Name1',
							'Name2'      => 'Name2',
							'Name3'      => 'Name3',
							'Street1'    => 'Street',
							'CountryNum' => '208',
							'ZipCode'    => '6000',
							'City'       => 'Kolding',
							'Contact'    => 'Contact person',
							'Email'      => 'adarsh.srmcem@gmail.com',
							'Phone'      => '+917318216218',
							'Mobile'     => '+917318216218',
						),
					),
					'Parcels'      => array(
						array(
							'Weight'                => 2.5,
							'Reference'             => 'Parcel specific reference',
							'Comment'               => 'Comment',
							'Cashservice'           => 0.0,
							'AddOnLiabilityService' => 0.0,
						),
					),
					'Services'     => array(
						'ShopDelivery'      => '96600',
						'NotificationEmail' => 'adarsh.srmcem@gmail.com',
					),
				),
			)
		);

		echo $response_code = wp_remote_retrieve_response_code( $response );
		$response_body = wp_remote_retrieve_body( $response );
		debug( $response_body ); die;

		$response = array(
			'message'     => 'gls-print-label-modal-html-fetched',
			'html'        => $html,
			'header_text' => $header_text,
		);
		wp_send_json_success( $response );
		wp_die();
	}
}
