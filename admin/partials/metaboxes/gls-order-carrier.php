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
$gls_logo = WOOPI_PLUGIN_URL . 'admin/images/gls-logo.jpg';
?>
<p class="gls-logo"><img src="<?php echo esc_url( $gls_logo ); ?>" alt="GLS" class="img-responsive center-block"></p>
<p class="description"><?php esc_html_e( 'You can print a GLS shipping label even if a GLS carrier is not associated to this order. Additional information will be required before the shipping label is generated.', 'woopi' ); ?></p>
<button type="button" data-orderid="<?php echo esc_attr( $post->ID ); ?>" class="button button-secondary woopi-open-print-label-modal"><?php esc_html_e( 'Print Label', 'woopi' ); ?></button>
