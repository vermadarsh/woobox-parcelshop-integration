<?php // phpcs:ignore Generic.Files.LineEndings.InvalidEOLChar -- Can't change the file EOL character.
/**
 * GLS carrier modal content file.
 *
 * @version 1.0.0
 * @package Woobox_Parcelshop_Integration
 * @subpackage Woobox_Parcelshop_Integration/admin/partials/modals/
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="woopi-gls-carrier-modal" class="woopi-modal">
	<div class="woopi-modal-content">
		<div class="woopi-modal-header">
			<span class="woopi-close">&times;</span>
			<h2></h2>
		</div>
		<div class="woopi-modal-body">
			<div class="woopi-gls-carrier-modal-content">
				<p><?php esc_html_e( 'Please wait...', 'woopi' ); ?></p>
			</div>
		</div>
	</div>
</div>
