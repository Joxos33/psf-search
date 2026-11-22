<?php
// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

if (
	! empty( $_REQUEST['psf-search-debug-analytics-delete-all-records'] ) &&
	! empty( $_REQUEST['_wpnonce'] ) &&
	wp_verify_nonce( $_REQUEST['_wpnonce'], 'psf_search_debug_analytics' )
) {
	\PsfSearch\Analytics\Database::wipeAllRecords();
	?>
	<div class="psf-search-notice notice notice-success">
		<p>All analytics records have been deleted.</p>
	</div>
	<?php
}

if (
	! empty( $_REQUEST['psf-search-debug-analytics-run-maintenance-task'] ) &&
	! empty( $_REQUEST['_wpnonce'] ) &&
	wp_verify_nonce( $_REQUEST['_wpnonce'], 'psf_search_debug_analytics' )
) {
	do_action( \PsfSearch\Analytics\Maintenance::HOOK );
	?>
	<div class="psf-search-notice notice notice-success">
		<p>The task of maintaining the search analytics has been completed.</p>
	</div>
	<?php
}

?>
	<h3>Analytics</h3>
	<form action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
		<input type="hidden" name="page" value="psf_search_debug">
		<?php wp_nonce_field( 'psf_search_debug_analytics', '_wpnonce', false ); ?>
		<input type="submit" name="psf-search-debug-analytics-delete-all-records" class="button" value="Delete all records">
		<input type="submit" name="psf-search-debug-analytics-run-maintenance-task" class="button" value="Run maintenance task">
	</form>

	<table class="wc_status_table widefat psf-search-table-debug-analytics">
		<tr>
			<td><b>Does the table exist?</b></td>
			<td><?php echo \PsfSearch\Analytics\Database::exist() ? 'yes' : 'no'; ?></td>
		</tr>
		<tr>
			<td><b>Total records</b></td>
			<td><?php echo \PsfSearch\Analytics\Database::getRecordsCount(); ?></td>
		</tr>
		<tr>
			<td><b>Is the maintenance task scheduled?</b></td>
			<td><?php echo wp_next_scheduled( \PsfSearch\Analytics\Maintenance::HOOK ) ? 'yes' : 'no'; ?></td>
		</tr>
		<tr>
			<td><b>Constant <code>PSF_SEARCH_ANALYTICS_ONLY_CRITICAL</code></b></td>
			<?php if ( defined( 'PSF_SEARCH_ANALYTICS_ONLY_CRITICAL' ) ) { ?>
				<td>Is defined. <b>
						<?php
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_dump
						var_dump( PSF_SEARCH_ANALYTICS_ONLY_CRITICAL );
						?>
						</b></td>
			<?php } else { ?>
				<td>not defined</td>
			<?php } ?>
		</tr>
		<tr>
			<td><b>Constant <code>PSF_SEARCH_ANALYTICS_EXPIRATION_IN_DAYS</code></b></td>
			<?php if ( defined( 'PSF_SEARCH_ANALYTICS_EXPIRATION_IN_DAYS' ) ) { ?>
				<td>Is defined. Days: <b><?php echo PSF_SEARCH_ANALYTICS_EXPIRATION_IN_DAYS; ?></b></td>
			<?php } else { ?>
				<td>not defined</td>
			<?php } ?>
		</tr>
	</table>
<?php
