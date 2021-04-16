<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://woobox.dk/
 * @since      1.0.0
 *
 * @package    Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/public
 * @author     Parth Patel <info@woobox.dk>
 */
class Woobox_Parcelshop_Integration_Public {

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
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function woopi_enqueue_public_assets() {
		wp_enqueue_style(
			$this->plugin_name,
			WOOPI_PLUGIN_URL . 'public/css/woobox-parcelshop-integration-public.css',
			array(),
			filemtime( WOOPI_PLUGIN_PATH . 'public/css/woobox-parcelshop-integration-public.css' )
		);

		$postcode     = '';
		$country_code = '';

		if ( is_user_logged_in() ) {
			$customer_id  = get_current_user_id();
			$customer     = new WC_Customer( $customer_id );
			$postcode     = $customer->get_shipping_postcode();
			$country_code = $customer->get_shipping_country();
		} else {
			$cart_session_data = WC()->session->get_session_data();

			if ( ! empty( $cart_session_data['customer'] ) ) {
				$customer_data = maybe_unserialize( $cart_session_data['customer'] );

				if ( $customer_data ) {
					$postcode     = ( isset( $customer_data['postcode'] ) && ! empty( $customer_data['postcode'] ) ) ? $customer_data['postcode'] : '';
					$country_code = ( isset( $customer_data['country'] ) && ! empty( $customer_data['country'] ) ) ? $customer_data['country'] : '';
				}
			}
		}

		if ( empty( $postcode ) && ! empty( $country_code ) ) {
			$postcode = woopi_get_default_postcodes( $country_code );
		}

		wp_enqueue_script(
			$this->plugin_name,
			WOOPI_PLUGIN_URL . 'public/js/woobox-parcelshop-integration-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Localized variables.
		wp_localize_script(
			$this->plugin_name,
			'WOOPI_Public_JS_Obj',
			array(
				'woopi_ajax_url'             => admin_url( 'admin-ajax.php' ),
				'woopi_nonce'                => wp_create_nonce( 'woopi-nonce' ),
				'loader_url'                 => includes_url( 'images/spinner-2x.gif' ),
				'is_checkout_page'           => is_checkout() ? 'yes' : 'no',
				'customer_shipping_postcode' => $postcode,
				'customer_shipping_country'  => $country_code,
			)
		);

	}

	/**
	 * Action added to integrate pickup map.
	 *
	 * @author   Parth Patel <info@woobox.dk>
	 * @since    1.0.0
	 */
	public function woopi_pickup_integration() {
		$apikey = woopi_get_google_maps_api_key();
		?>
		<div class="woopi-pickup-integration">
			<?php echo woopi_get_pickup_html( $apikey ); ?>
			<div class="woopi-pickup-ajax-loader">
				<img alt="loader" src="<?php echo esc_url( includes_url( 'images/spinner-2x.gif' ) ); ?>">
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX served to fetch pickup points HTML.
	 *
	 * @author   Parth Patel <info@woobox.dk>
	 * @since    1.0.0
	 */
	public function woopi_fetch_pickuppoints_html() {

		// Check ajax security nonce.
		check_ajax_referer( 'woopi-nonce', 'security' );

		$action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

		if ( empty( $action ) || 'woopi_fetch_pickuppoints_html' !== $action ) {
			echo 0;
			wp_die();
		}

		$customer_id                = woopi_get_consumer_id();
		$customer_shipping_postcode = filter_input( INPUT_POST, 'customer_shipping_postcode', FILTER_SANITIZE_STRING );
		$customer_shipping_country  = filter_input( INPUT_POST, 'customer_shipping_country', FILTER_SANITIZE_STRING );
		$pickup_points              = woopi_get_pickupoints( $customer_id, $customer_shipping_postcode, $customer_shipping_country );
		$html                       = woopi_get_pickup_points_html( $pickup_points );
		$coordinates                = woopi_get_pickup_points_coordinates( $pickup_points );

		wp_send_json_success(
			array(
				'message'     => 'woopi-pickpoints-html-fetched',
				'html'        => $html,
				'coordinates' => $coordinates,
			)
		);
		wp_die();
	}

	/**
	 * Action hooked to save the selected pickup address.
	 *
	 * @param int $order_id Holds the order ID.
	 * @author   Parth Patel <info@woobox.dk>
	 * @since    1.0.0
	 */
	public function woopi_add_selected_pickup_point( $order_id ) {
		$service_point_id = filter_input( INPUT_POST, 'woopi-pickup-point', FILTER_SANITIZE_STRING );
		$customer_id      = woopi_get_consumer_id();
		$wc_order         = wc_get_order( $order_id );
		$billing_country  = $wc_order->get_billing_country();
		$apiurl           = 'https://api2.postnord.com/rest/businesslocation/v1/servicepoint/findByServicePointId.json';
		$params           = array(
			'apikey'         => $customer_id,
			'countryCode'    => $billing_country, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
			'servicePointId' => $service_point_id, // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
			'locale'         => 'en',
		);
		$remote_url       = add_query_arg( $params, $apiurl );
		$response         = wp_remote_get( $remote_url );
		$response_code    = wp_remote_retrieve_response_code( $response );

		// Check if the right response is received.
		if ( 200 === $response_code ) {
			$response_body = json_decode( wp_remote_retrieve_body( $response ) );
			$pickup_points = $response_body->servicePointInformationResponse->servicePoints; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
			$pickup_point  = woopi_build_pickuppoints( $pickup_points );
			if ( ! empty( $pickup_point[0] ) ) {
				update_post_meta( $order_id, '_pickup_data', $pickup_point[0] );
			}
		}

	}

	/**
	 * Add pickup data in wc order email.
	 *
	 * @param array  $total_rows WC email data rows.
	 * @param object $order WC order object.
	 * @return array
	 */
	public function woopi_order_row_pickup_data_email( $total_rows, $order ) {
		$order_id       = $order->get_id();
		$pickup_address = get_post_meta( $order_id, '_pickup_address', true );
		$temp           = array();

		if ( ! empty( $pickup_address ) ) {
			$temp['_pickup_address'] = array(
				'label' => __( 'Pickup Address:', 'woopi' ),
				'value' => $pickup_address,
			);
			$total_rows              = array_slice( $total_rows, 0, 3, true ) + $temp + array_slice( $total_rows, 3, count( $total_rows ) - 3, true );
		}

		return $total_rows;
	}
}
