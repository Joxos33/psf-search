<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

if (
	! empty( $_REQUEST['psf-search-debug-setting-reset-to-default'] ) &&
	! empty( $_REQUEST['_wpnonce'] ) &&
	wp_verify_nonce( $_REQUEST['_wpnonce'], 'psf_search_debug_reset_settings' )
) {
	global $psfSearchSettings;

	$psfSearchSettings = [];
	delete_option( PSF_SEARCH_SETTINGS_KEY );
	PsfSearch\Admin\Install::createOptions();
	?>
	<div class="psf-search-notice notice notice-success">
		<p>The settings have been reset to the default values.</p>
	</div>
	<?php
}

?>
	<h3>Settings</h3>
	<form
		action="<?php echo admin_url( 'admin.php' ); ?>" method="get"
		onsubmit="return confirm('Are you sure you want to reset the settings?');">
		<input
			type="hidden" name="page" value="psf_search_debug">
		<?php wp_nonce_field( 'psf_search_debug_reset_settings', '_wpnonce', false ); ?>
		<input
			type="submit" name="psf-search-debug-setting-reset-to-default" class="button"
			value="Reset the plugin settings to default values">
	</form>
<?php
