<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar -- Can't change the file EOL character.
/**
 * GLS carrier metabox content file.
 *
 * @version 1.0.0
 * @package Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/admin/partials/metabox/
 */

defined( 'ABSPATH' ) || exit;

global $post;

// Nonce field to validate form request came from current site.
wp_nonce_field( basename( __FILE__ ), 'woopi_fields' );

$pickup_data = get_post_meta( $post->ID, '_pickup_data', true );
?>
<table class="form-table">
	<tbody class="woopi-pickup-data">

	<!-- SERVICE POINT ID -->
	<?php
	if ( ! empty( $pickup_data->servicePointId ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
		?>
		<tr>
			<th scope="row">
				<label for="woopi-service-point-id"><?php esc_html_e( 'Service Point ID', 'woopi' ); ?></label>
			</th>
			<td><?php echo esc_html( "#{$pickup_data->servicePointId}" ); ?></td><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
		</tr>
	<?php } ?>

	<!-- SERVICE POINT NAME -->
	<?php
	if ( ! empty( $pickup_data->name ) ) {
		?>
		<tr>
			<th scope="row">
				<label for="woopi-service-point-name"><?php esc_html_e( 'Service Point Name', 'woopi' ); ?></label>
			</th>
			<td><?php echo esc_html( $pickup_data->name ); ?></td>
		</tr>
	<?php } ?>

	<!-- SERVICE POINT VISITING ADDRESS -->
	<?php
	if ( ! empty( $pickup_data->visitingAddress ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
		?>
		<tr>
			<th scope="row">
				<label for="woopi-service-point-visiting-address"><?php esc_html_e( 'Visiting Address', 'woopi' ); ?></label>
			</th>
			<td><?php echo wp_kses_post( $pickup_data->visitingAddress ); ?></td><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
		</tr>
	<?php } ?>

	<!-- SERVICE POINT OPENING HOURS -->
	<?php
	if ( ! empty( $pickup_data->openingHours['weekdays'] ) && ! empty( $pickup_data->openingHours['weekend'] ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
		?>
		<tr>
			<th scope="row">
				<label for="woopi-service-point-opening-hours"><?php esc_html_e( 'Opening Hours', 'woopi' ); ?></label>
			</th>
			<td>
				<p><?php echo esc_html( $pickup_data->openingHours['weekdays'] ); ?></p><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
				<p><?php echo esc_html( $pickup_data->openingHours['weekend'] ); ?></p><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
			</td>
		</tr>
	<?php } ?>

	<!-- SERVICE POINT FORWARDER -->
	<?php
	if ( ! empty( $pickup_data->forwarderName ) ) { // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties.
		?>
		<tr>
			<th scope="row">
				<label for="woopi-service-point-forwarder"><?php esc_html_e( 'Forwarder', 'woopi' ); ?></label>
			</th>
			<td><?php echo esc_html( $pickup_data->forwarderName ); ?></td><?php // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- Cannot change the object properties. ?>
		</tr>
	<?php } ?>
	</tbody>
</table>
