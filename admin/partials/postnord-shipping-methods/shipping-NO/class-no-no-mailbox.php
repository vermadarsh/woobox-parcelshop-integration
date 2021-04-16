<?php
/**
 * NO NO Mailbox Shipping Method.
 *
 * @version 1.0.0
 * @package Woobox Parcelshop Integration/Admin/Partials/Shipping Methods/Shipping NO
 */

defined( 'ABSPATH' ) || exit;

/**
 * NO_NO_Mailbox class.
 */
class NO_NO_Mailbox extends WC_Shipping_Method {

	/**
	 * The array of custom rates.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    array $woopi_custom_rates The array that holds the list of custom rates.
	 */
	protected $woopi_custom_rates;

	/**
	 * Constructor.
	 *
	 * @param int $instance_id Holds the instance ID.
	 */
	public function __construct( $instance_id = 0 ) {

		// Parent constructor.
		parent::__construct();
		$this->id                 = 'woopi-pn-no-no-mailbox';
		$this->instance_id        = absint( $instance_id );
		$this->method_title       = __( 'NO NO Mailbox', 'woopi' );
		$this->method_description = __( 'Shipping method dealing - Norway Mailbox.', 'woopi' );
		$this->supports           = array(
			'shipping-zones',
			'instance-settings',
			'instance-settings-modal',
		);

		$this->init();

		add_action( "woocommerce_update_options_shipping_{$this->id}", array( $this, 'process_admin_options' ) );

	}

	/**
	 * Initialize custom shiping method.
	 */
	public function init() {
		$this->instance_form_fields = $this->init_form_fields();
		$this->title                = $this->get_option( 'title' );
		$this->tax_status           = $this->get_option( 'tax_status' );
		$this->cost                 = $this->get_option( 'cost' );
		$this->woopi_custom_rates   = get_option( "{$this->id}_rates" );
		$this->type                 = $this->get_option( 'type', 'class' );
		$this->init_settings();
	}

	/**
	 * Calculate custom shipping method.
	 *
	 * @param array $package Holds the cart items package data.
	 *
	 * @return void
	 */
	public function calculate_shipping( $package = array() ) {

		if ( ! empty( $package['contents'] ) ) {

			$total_weight = 0.00;
			$cart_total   = 0.00;
			foreach ( $package['contents'] as $cart_item ) {
				$product_id    = $cart_item['product_id'];
				$variation_id  = $cart_item['variation_id'];
				$quantity      = $cart_item['quantity'];
				$product_id    = 0 === $variation_id ? $product_id : $variation_id;
				$_product      = wc_get_product( $product_id );
				$weight        = (float) $_product->get_weight();
				$total_weight += ( $weight * $quantity );
				$cart_total   += $cart_item['line_total'];
			}

			$cost = $this->cost;

			if ( ! empty( $this->woopi_custom_rates ) ) {
				$rate_to_consider = $this->woopi_custom_rates[0];
				$weight_minimum   = (float) $rate_to_consider['weight_min'];
				$weight_maximum   = (float) $rate_to_consider['weight_max'];
				$total_from       = (float) $rate_to_consider['total_from'];
				$total_to         = (float) $rate_to_consider['total_to'];

				if (
					( $total_weight >= $weight_minimum && $total_weight <= $weight_maximum ) &&
					( $cart_total >= $total_from && $cart_total <= $total_to )
				) {
					$cost = (float) $rate_to_consider['cost'];
				}
			}

			$rate = array(
				'id'    => $this->id,
				'label' => $this->title,
				'cost'  => $cost,
			);
			$this->add_rate( $rate );

			/**
			 * Do something after the rate is added.
			 *
			 * @param object $this Holds this shipping method object.
			 * @param array  $rate Holds the shipping rate data array.
			 */
			do_action( "woocommerce_{$this->id}_shipping_add_rate", $this, $rate );
		}
	}

	/**
	 * Init form fields.
	 *
	 * @return array
	 */
	public function init_form_fields() {
		$settings = array(
			'title'      => array(
				'title'       => __( 'Method title', 'woopi' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woopi' ),
				'default'     => __( 'NO NO Mailbox', 'woopi' ),
				'desc_tip'    => true,
			),
			'tax_status' => array(
				'title'   => __( 'Tax status', 'woopi' ),
				'type'    => 'select',
				'class'   => 'wc-enhanced-select',
				'default' => 'taxable',
				'options' => array(
					'taxable' => __( 'Taxable', 'woopi' ),
					'none'    => _x( 'None', 'Tax status', 'woopi' ),
				),
			),
			'cost'       => array(
				'title'       => __( 'Cost', 'woopi' ),
				'type'        => 'number',
				'placeholder' => 0.00,
				'default'     => 0,
				'desc_tip'    => true,
			),
		);

		return $settings;
	}

	/**
	 * Function added to create the custom rates HTML.
	 *
	 * @return false|string
	 *
	 * @since    1.0.0
	 * @author   Parth Patel <info@woobox.dk>
	 */
	public function woopi_add_custom_rates_html() {

		return woopi_shipping_method_custom_rates_html( $this->woopi_custom_rates, $this->id );

	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the erroneous field out.
	 *
	 * @return bool was anything saved?
	 * @since 1.0.0
	 */
	public function process_admin_options() {
		parent::process_admin_options();

		if ( $this->instance_id ) {
			$this->init_instance_settings();
			$post_data = $this->get_post_data();

			if ( ! empty( $post_data[ "woocommerce_{$this->id}_rates" ] ) ) {
				update_option(
					"{$this->id}_rates",
					$post_data[ "woocommerce_{$this->id}_rates" ],
					false
				);
			}

			return update_option(
				$this->get_instance_option_key(),
				apply_filters(
					"woocommerce_shipping_{$this->id}_instance_settings_values",
					$this->instance_settings,
					$this
				)
			);
		}
	}
}
