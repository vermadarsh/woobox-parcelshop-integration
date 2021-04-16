<?php
/**
 * This file is used for writing all the re-usable custom functions.
 *
 * @since 1.0.0
 * @package Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit, if accessed directly.

/**
 * Function defined to fetch the postnord developer consumer ID.
 *
 * @return string
 * @since    1.0.0
 * @author   Parth Patel <info@woobox.dk>
 */
function woopi_get_consumer_id() {
	$consumer_id = get_option( 'woopi-api-consumer-id', true );

	return $consumer_id;
}

/**
 * Function defined to fetch the google maps api key.
 *
 * @return string
 * @since    1.0.0
 * @author   Parth Patel <info@woobox.dk>
 */
function woopi_get_google_maps_api_key() {
	$gmaps_api_key = get_option( 'woopi-google-maps-api-key', true );

	return $gmaps_api_key;
}

/**
 * Function defined to fetch the shop origin.
 *
 * @return string
 * @since    1.0.0
 * @author   Parth Patel <info@woobox.dk>
 */
function woopi_get_shop_origin() {
	$shop_origin = get_option( 'woopi-shop-origin', true );

	return $shop_origin;
}

/**
 * Function defined to fetch the GLS shipping methods.
 *
 * @return array
 */
function woopi_get_gls_shipping_methods() {
	$shipping_methods = array(
		'gls-avant-13h'      => 'GLS_Avant_13H',
		'gls-point-relais'   => 'GLS_Point_Relais',
		'gls-chez-vous-plus' => 'GLS_Chez_Vous_Plus',
		'gls-chez-vous'      => 'GLS_Chez_Vous',
	);

	return $shipping_methods;
}

/**
 * Function defined to fetch the available shipping methods based on country code.
 *
 * @param string $country_code Holds the country code.
 * @return array
 */
function woopi_get_available_shipping_methods( $country_code ) {

	$dk_shipping_methods = array(
		'dk-dk-privatehome' => 'DK_DK_Privatehome',
		'dk-dk-commercial'  => 'DK_DK_Commercial',
		'dk-dk-pickup'      => 'DK_DK_Pickup',
		'dk-se-pickup'      => 'DK_SE_Pickup',
		'dk-no-pickup'      => 'DK_NO_Pickup',
		'dk-fi-pickup'      => 'DK_FI_Pickup',
		'dk-eu-dpd'         => 'DK_EU_Dpd',
		'dk-int-dpd'        => 'DK_Int_Dpd',
	);

	$se_shipping_methods = array(
		'se-se-mailbox'     => 'SE_SE_Mailbox',
		'se-se-privatehome' => 'SE_SE_Privatehome',
		'se-se-commercial'  => 'SE_SE_Commercial',
		'se-dk-mailbox'     => 'SE_DK_Mailbox',
		'se-no-mailbox'     => 'SE_NO_Mailbox',
		'se-se-pickup'      => 'SE_SE_Pickup',
		'se-dk-pickup'      => 'SE_DK_Pickup',
		'se-no-pickup'      => 'SE_NO_Pickup',
		'se-fi-pickup'      => 'SE_FI_Pickup',
		'se-eu-mailbox'     => 'SE_EU_Mailbox',
		'se-eu-dpd'         => 'SE_EU_Dpd',
		'se-int-mailbox'    => 'SE_Int_Mailbox',
		'se-int-dpd'        => 'SE_Int_Dpd',
	);

	$no_shipping_methods = array(
		'no-no-mailbox'     => 'NO_NO_Mailbox',
		'no-no-privatehome' => 'NO_NO_Privatehome',
		'no-no-pickup'      => 'NO_NO_Pickup',
		'no-dk-pickup'      => 'NO_DK_Pickup',
		'no-se-pickup'      => 'NO_SE_Pickup',
		'no-fi-pickup'      => 'NO_FI_Pickup',
		'no-eu-mailbox'     => 'NO_EU_Mailbox',
		'no-eu-dpd'         => 'NO_EU_Dpd',
		'no-int-mailbox'    => 'NO_Int_Mailbox',
		'no-int-dpd'        => 'NO_Int_Dpd',
	);

	$fi_shipping_methods = array(
		'fi-fi-privatehome' => 'FI_FI_Privatehome',
		'fi-fi-pickup'      => 'FI_FI_Pickup',
		'fi-fi-commercial'  => 'FI_FI_Commercial',
		'fi-dk-pickup'      => 'FI_DK_Pickup',
		'fi-se-pickup'      => 'FI_SE_Pickup',
		'fi-no-pickup'      => 'FI_NO_Pickup',
		'fi-eu-dpd'         => 'FI_EU_Dpd',
		'fi-int-dpd'        => 'FI_Int_Dpd',
	);

	// Merge all the shipping methods into one array.
	$shipping_methods = array_merge(
		$dk_shipping_methods,
		$se_shipping_methods,
		$fi_shipping_methods,
		$no_shipping_methods
	);

	return $shipping_methods;

}

/**
 * Generate the pickup HTML on the checkout page.
 *
 * @param string $apikey Holds the google maps API key.
 * @return string
 */
function woopi_get_pickup_html( $apikey = '' ) {
	ob_start();
	?>
	<div class="woopi-pickup-location-container">
		<?php if ( ! empty( $apikey ) ) { ?>
			<div id="woopi-gmap" style="width:100%;height:200px;"></div>
		<?php } ?>
		<div id="woopi-pickup-locations"></div>
	</div>
	<?php if ( ! empty( $apikey ) ) { ?>
		<script type="application/javascript">
			var markers = '', marker_cluster = [];

			function woopi_gmap() {
				setTimeout(function () {
					var mapProp = {
						center: new google.maps.LatLng(55.491887, 9.482139),
						zoom: 10,
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						mapTypeControl: false
					};
					var map = new google.maps.Map(document.getElementById('woopi-gmap'), mapProp);
					var infowindow = new google.maps.InfoWindow(), marker, lat, lng;
					console.log('markers', markers);
					for (var i in markers) {
						lat = markers[i].lat;
						lng = markers[i].lng;
						name = markers[i].name;

						var icon = {
							url: '<?php echo esc_url( WOOPI_PLUGIN_URL . 'public/images/map-icon.png' ); ?>', // Image URL.
							scaledSize: new google.maps.Size(50, 50), // scaled size
							origin: new google.maps.Point(0, 0), // origin
							anchor: new google.maps.Point(0, 0) // anchor
						};

						marker = new google.maps.Marker({
							position: new google.maps.LatLng(lat, lng),
							name: name,
							map: map,
							icon: icon
						});
						marker_cluster.push(marker);
						google.maps.event.addListener(marker, 'click', function (e) {
							infowindow.setContent(this.name);
							infowindow.open(map, this);
						}.bind(marker));
					}
					var markerCluster = new MarkerClusterer(map, marker_cluster,
						{imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'});
				}, 4000);
			}
		</script>
		<script src="<?php echo esc_url( WOOPI_PLUGIN_URL . 'public/js/markerclusterer.js' ); ?>"></script><?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Cannot enqueue script in wp_enqueue_scripts for inline map code. ?>
		<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_html( $apikey ); ?>&callback=woopi_gmap"></script><?php //phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Cannot enqueue script in wp_enqueue_scripts for inline map code. ?>
		<?php
	}

	return ob_get_clean();

}

/**
 * Function to fetch default postcode based on country.
 *
 * @param string $country_code Holds the country code.
 * @return string
 */
function woopi_get_default_postcodes( $country_code ) {

	if ( 'NO' === $country_code ) {
		return '0180';
	} elseif ( 'SE' === $country_code ) {
		return '11152';
	} elseif ( 'FI' === $country_code ) {
		return '00002';
	}

	return '2630';
}

/**
 * Function defined to fetch the pickup points.
 *
 * @param int    $customer_id Holds the customer ID.
 * @param string $postcode Holds the postcode.
 * @param string $country_code Holds the country code.
 * @return string
 */
function woopi_get_pickupoints( $customer_id, $postcode, $country_code ) {

	$apiurl        = 'https://api2.postnord.com/rest/businesslocation/v1/servicepoint/findNearestByAddress.json';
	$params        = array(
		'apikey'                => $customer_id,
		'countryCode'           => $country_code,
		'postalCode'            => $postcode,
		'numberOfServicePoints' => 10,
		'locale'                => 'en',
	);
	$remote_url    = add_query_arg( $params, $apiurl );
	$response      = wp_remote_get( $remote_url );
	$response_code = wp_remote_retrieve_response_code( $response );
	if ( 200 === $response_code ) {
		$response_body = json_decode( wp_remote_retrieve_body( $response ) );
		$pickup_points = $response_body->servicePointInformationResponse->servicePoints; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
		$pickup_points = woopi_build_pickuppoints( $pickup_points );

		return $pickup_points;
	}

}

/**
 * Function defined to fetch the pickup points html.
 *
 * @param array $pickup_points Holds the array of pickup points.
 * @return string
 */
function woopi_get_pickup_points_html( $pickup_points ) {
	ob_start();
	?>
	<input type="hidden" name="woopi-hidden-pickup-selected-address" value="">
	<?php
	if ( ! empty( $pickup_points ) ) {
		foreach ( $pickup_points as $pickup_point ) {
			?>
			<div class="woopi-pickup-location" id="pickup-<?php echo esc_html( $pickup_point->servicePointId ); ?>"><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
				<div class="first">
					<input type="radio" value="<?php echo esc_html( $pickup_point->servicePointId ); ?>" name="woopi-pickup-point" id="woopi-pickup-point-<?php echo esc_html( $pickup_point->servicePointId ); ?>" data-pointid="<?php echo esc_html( $pickup_point->servicePointId ); ?>"><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
					<label for="woopi-pickup-point-<?php echo esc_html( $pickup_point->servicePointId ); ?>" data-pointid="<?php echo esc_html( $pickup_point->servicePointId ); ?>"><strong><?php echo esc_html( $pickup_point->name ); ?></strong></label><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
				</div>
				<div class="second">
					<p class="woopi-visiting-address"><?php echo wp_kses_post( $pickup_point->visitingAddress ); ?></p><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
					<p class="woopi-opening-hours">
					<ul>
						<li><?php echo esc_html( $pickup_point->openingHours['weekdays'] ); ?></li><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
						<li><?php echo esc_html( $pickup_point->openingHours['weekend'] ); ?></li><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
					</ul>
					</p>
				</div>
			</div>
			<?php
		}
	}

	return ob_get_clean();
}

/**
 * Function to return the coordinates array.
 *
 * @param array $pickup_points Holds the array of pickup points.
 * @return array
 */
function woopi_get_pickup_points_coordinates( $pickup_points ) {
	$coordinates = array();

	if ( ! empty( $pickup_points ) ) {
		foreach ( $pickup_points as $pickup_point ) {
			$coordinate = $pickup_point->coordinate;
			if ( isset( $coordinate->northing ) && ! empty( $coordinate->northing ) && isset( $coordinate->easting ) && ! empty( $coordinate->easting ) ) {
				$coordinates[] = array(
					'name' => $pickup_point->name,
					'lat'  => $coordinate->northing,
					'lng'  => $coordinate->easting,
				);
			}
		}
	}

	return $coordinates;
}

/**
 * Function to build an array of the available pickup points.
 *
 * @param array $points Holds the array of pickup points.
 * @return array
 */
function woopi_build_pickuppoints( $points ) {
	$processed_points = array();

	if ( ! empty( $points ) ) {
		foreach ( $points as $point ) {
			if ( ! empty( $point->visitingAddress->streetName ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
				$processed_point                  = new \stdClass();
				$processed_point->servicePointId  = isset( $point->servicePointId ) ? trim( $point->servicePointId ) : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
				$processed_point->name            = isset( $point->name ) ? trim( $point->name ) : '';
				$processed_point->visitingAddress = woopi_get_delivery_address( $point ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
				$processed_point->openingHours    = woopi_get_working_time( $point ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
				$processed_point->coordinate      = woopi_get_coordinates( $point );
				$processed_point->forwarderName   = 'PostNord'; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.

				$processed_points[] = $processed_point;
			}
		}
	}

	return $processed_points;
}

/**
 * Function to return the delivery address of the service point.
 *
 * @param object $point Holds the pickup point data object.
 * @return string
 */
function woopi_get_delivery_address( $point ) {
	$country_code  = isset( $point->visitingAddress->countryCode ) ? trim( $point->visitingAddress->countryCode ) : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$city          = isset( $point->visitingAddress->city ) ? trim( $point->visitingAddress->city ) : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$street_name   = isset( $point->visitingAddress->streetName ) ? trim( $point->visitingAddress->streetName ) : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$street_number = isset( $point->visitingAddress->streetNumber ) ? trim( $point->visitingAddress->streetNumber ) : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$postcode      = isset( $point->visitingAddress->postalCode ) ? trim( $point->visitingAddress->postalCode ) : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$country       = WC()->countries->countries[ $country_code ];

	// Prepare the delivery address now.
	$address  = "{$street_number}, {$street_name}<br />";
	$address .= "{$city}, {$country} - {$postcode}<br />";

	return $address;
}

/**
 * Function to return the working hours of the service point.
 *
 * @param object $point Holds the pickup point data object.
 * @return array
 */
function woopi_get_working_time( $point ) {
	$weekdays_opening = isset( $point->openingHours[0]->from1 ) ? $point->openingHours[0]->from1 : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$weekdays_closing = isset( $point->openingHours[0]->to1 ) ? $point->openingHours[0]->to1 : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$weekends_opening = isset( $point->openingHours[5]->from1 ) ? $point->openingHours[5]->from1 : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$weekends_closing = isset( $point->openingHours[5]->to1 ) ? $point->openingHours[5]->to1 : ''; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
	$opening_hours    = array(
		/* translators: 1: %s: opening timing */
		'weekdays' => sprintf( __( 'Monday-Friday: %1$s', 'woopi' ), "{$weekdays_opening}-{$weekdays_closing}" ),
		/* translators: 1: %s: opening timing */
		'weekend'  => sprintf( __( 'Saturday: %1$s', 'woopi' ), "{$weekends_opening}-{$weekends_closing}" ),
	);

	return $opening_hours;
}

/**
 * Function to return the coordinates of the service point.
 *
 * @param object $point Holds the pickup point data object.
 * @return object
 */
function woopi_get_coordinates( $point ) {

	$coordinates       = new \stdClass();
	$_coordinates_data = $point->coordinate;
	if ( empty( $_coordinates_data ) ) {
		$_coordinates_data = $point->coordinates;

		if ( ! empty( $_coordinates_data[0] ) ) {
			$_coordinates_data = $_coordinates_data[0];
		}
	}

	$coordinates->northing = $_coordinates_data->northing;
	$coordinates->easting  = $_coordinates_data->easting;

	return $coordinates;
}

/**
 * Function that returns custom rates html for shipping methods.
 *
 * @param array $rates Holds the rates array.
 * @param int   $id Holds the loop index.
 * @return string
 */
function woopi_shipping_method_custom_rates_html( $rates, $id ) {
	ob_start();
	?>
	<h2><?php esc_html_e( 'Rates', 'woopi' ); ?></h2>
	<table class="wc-shipping-classes woopi-shipping-model widefat" id="<?php echo esc_attr( "{$id}_rates" ); ?>">
		<thead>
		<tr>
			<th>
				<input type="checkbox" class="woopi-select-all-rates"/>
			</th>
			<th>
				<?php esc_html_e( 'Min. Weight', 'woopi' ); ?>
				<?php echo wp_kses_post( wc_help_tip( __( 'Product minimum weight for this rule.', 'woopi' ) ) ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Max. Weight', 'woopi' ); ?>
				<?php echo wp_kses_post( wc_help_tip( __( 'Product maximum weight for this rule.', 'woopi' ) ) ); ?>
			</th>
			<th><?php esc_html_e( 'Total from', 'woopi' ); ?>
				<?php echo wp_kses_post( wc_help_tip( __( 'The lower value of cart items total for this rule.', 'woopi' ) ) ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Total to', 'woopi' ); ?>&nbsp;
				<?php echo wp_kses_post( wc_help_tip( __( 'The upper value of cart items total for this rule.', 'woopi' ) ) ); ?>
			</th>
			<th>
				<?php esc_html_e( 'Shipping price', 'woopi' ); ?>
				<?php echo wp_kses_post( wc_help_tip( __( 'The shipping rate to be charged.', 'woopi' ) ) ); ?>
			</th>
		</tr>
		</thead>
		<tbody id="rates">
		<?php
		$i = -1;
		if ( ! empty( $rates ) && is_array( $rates ) ) {
			foreach ( $rates as $rate ) {
				$i++;
				?>
				<tr class="woopi-rate">
					<th class="check-column woopi-check-column"><input type="checkbox" name="select"/></th>
					<td><input type="number" placeholder="0.00" step="any" min="0" size="4"
								value="<?php echo esc_attr( $rate['weight_min'] ); ?>"
								name="woocommerce_<?php echo esc_attr( $id . '_rates][' . $i . '][weight_min]' ); ?>"/>
					</td>
					<td><input type="number" placeholder="0.00" step="any" min="0" size="4"
								value="<?php echo esc_attr( $rate['weight_max'] ); ?>"
								name="woocommerce_<?php echo esc_attr( $id . '_rates][' . $i . '][weight_max]' ); ?>"/>
					</td>
					<td><input type="number" placeholder="0.00" step="any" min="0" size="4"
								value="<?php echo esc_attr( $rate['total_from'] ); ?>"
								name="woocommerce_<?php echo esc_attr( $id . '_rates][' . $i . '][total_from]' ); ?>"/>
					</td>
					<td><input type="number" placeholder="0.00" step="any" min="0" size="4"
								value="<?php echo esc_attr( $rate['total_to'] ); ?>"
								name="woocommerce_<?php echo esc_attr( $id . '_rates][' . $i . '][total_to]' ); ?>"/>
					</td>
					<td><input type="number" placeholder="0.00" step="any" min="0"
							value="<?php echo esc_attr( $rate['cost'] ); ?>"
							name="woocommerce_<?php echo esc_attr( $id . '_rates][' . $i . '][cost]' ); ?>"/>
					</td>
				</tr>
				<?php
			}
		}
		?>
		</tbody>
		<tfoot>
		<tr>
			<th colspan="6">
				<a href="javascript:void(0);"
					class="button plus insert woopi-add-rate"
					data-id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Insert row', 'woopi' ); ?></a>
				<a href="javascript:void(0);"
					class="button minus woopi-remove-rate"
					data-id="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Remove selected row(s)', 'woopi' ); ?></a>
			</th>
		</tr>
		</tfoot>
	</table>
	<?php

	return ob_get_clean();

}

/**
 * Function defined to generate the extra cost.
 *
 * @param string $country Holds the country code.
 * @param string $postcode Holds the postcode.
 * @param array  $settings Holds the settings array.
 * @return float
 */
function woopi_get_extra_shipping_cost_gls_shipping_method( $country, $postcode, $settings ) {
	$extra_cost = 0.00;
	$postcode   = str_replace( ' ', '', $postcode );

	if ( ! empty( $country ) && ! empty( $postcode ) ) {
		if ( 'FR' === $country && preg_match( '/^20\d{3}$/', $postcode ) && isset( $settings['corsica_price'] ) ) {
			// Corsica.
			$extra_cost = $settings['corsica_price'];
		} elseif ( 'FR' === $country
			&& in_array(
				$postcode,
				array( '22870', '29242', '29253', '29259', '29990', '56360', '56590', '56780', '56840', '85350' ),
				true
			)
			&& isset( $settings['french_islands_price'] )
		) {
			// FR islands (excl. DOM-TOM and Corsica).
			$extra_cost = $settings['french_islands_price'];
		} elseif ( 'GB' === $country && preg_match( '/^(GY|HS|IM|JE|KW15|KW16|KW17|ZE){1}.*$/', $postcode ) && isset( $settings['british_islands_price'] ) ) {
			// GB islands.
			$extra_cost = $settings['british_islands_price'];
		} elseif ( isset( $settings['other_country_islands_price'] ) && ( ( 'ES' === $country && preg_match( '/^(7|07).*$/', $postcode ) )
				|| ( 'GR' === $country && in_array(
					$postcode,
					array(
						'18010', '18020', '18040', '18050', '18900', '28100', '29100', '31100', '37002', '37003', '49100', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
						'64004', '68002', '70014', '70300', '70400', '71300', '72100', '72200', '72300', '73100', '74100', '81100', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
						'81107', '81400', '82100', '83100', '83300', '84001', '84002', '84003', '84005', '84006', '84100', '84200', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
						'84300', '84400', '84500', '84600', '84700', '84801', '85100', '85200', '85300', '85400', '85700', '85900', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					),
					true
				) )
				|| ( 'IT' === $country && preg_match( '/^(07|08|09|9).*$/', $postcode ) ) )
		) {
			// Islands from other countries.
			$extra_cost = $settings['other_country_islands_price'];
		} elseif ( isset( $settings['spanish_portuguese_islands_price'] ) &&
			( ( 'ES' === $country && preg_match( '/^(35|38|51|52).*$/', $postcode ) )
				|| ( 'PT' === $country && preg_match( '/^9.*$/', $postcode ) ) )
		) {
			// ES and PT islands (excl. Balearic).
			$extra_cost = $settings['spanish_portuguese_islands_price'];
		} elseif ( isset( $settings['mountain_area_price'] ) &&
			'FR' === $country &&
			in_array(
				$postcode,
				array(
					'04160', '04170', '04240', '04260', '04310', '04330', '04360', '04370', '04400', '04510', '04530', '04600', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'04850', '05100', '05120', '05150', '05160', '05170', '05200', '05220', '05240', '05250', '05260', '05290', '05310', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'05320', '05330', '05340', '05350', '05460', '05470', '05500', '05560', '05600', '05700', '05800', '06380', '06390', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'06420', '06430', '06440', '06450', '06460', '06470', '06540', '06620', '06660', '06710', '06750', '06830', '06850', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'06910', '09110', '09140', '09220', '09230', '09300', '09390', '09460', '15140', '15300', '25160', '25190', '25240', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'25370', '25380', '25430', '25470', '31110', '38112', '38114', '38142', '38190', '38250', '38350', '38410', '38520', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'38580', '38650', '38660', '38680', '38700', '38710', '38730', '38740', '38750', '38770', '38830', '38860', '38880', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'38930', '38970', '63113', '63240', '63610', '63680', '63850', '64440', '64490', '64560', '64570', '65110', '65120', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'65170', '65240', '65260', '65400', '65510', '65710', '66120', '66210', '66230', '66260', '66320', '66340', '66360', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'66480', '66720', '66730', '66760', '66800', '66820', '73120', '73130', '73140', '73150', '73170', '73210', '73220', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'73260', '73270', '73300', '73320', '73340', '73350', '73360', '73440', '73450', '73470', '73480', '73500', '73520', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'73530', '73550', '73570', '73590', '73620', '73630', '73640', '73660', '73670', '73700', '73710', '73720', '73730', '73790', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'73870', '74110', '74120', '74170', '74190', '74220', '74230', '74250', '74260', '74310', '74340', '74360', '74390', '74400', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
					'74420', '74430', '74440', '74450', '74470', '74480', '74490', '74550', '74560', '74660', '74740', '74920', '83560', '83630', // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine -- Items cannot be in new line.
				),
				true
			)
		) {
			// FR mountain area.
			$extra_cost = $settings['mountain_area_price'];
		}
	}

	return $extra_cost;

}

/**
 * Include the file if only it exists.
 *
 * @param string $file Holds the file path.
 */
function woopi_include_file_if_exists( $file ) {

	if ( file_exists( $file ) ) {
		include_once $file;
	}
}
