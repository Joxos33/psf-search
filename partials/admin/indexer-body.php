<?php
/**
 * Exit if accessed directly
 */

if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

$totalNonProducts = ! empty( $info['total_non_products_for_indexing'] ) ? absint( $info['total_non_products_for_indexing'] ) : 0;


if ( in_array( $info['status'], [ 'building', 'done' ] ) ) : ?>
	<div class="progress_bar">
		<div class="pro-bar">
			<small class="progress_bar_title">
				<?php
				$text = __( 'Indexing... This process will continue in the background. You can leave this page without any worries.', 'psf-search' );
				if ( empty( $progressPercent ) ) {
					$text = __( 'Index build progress', 'psf-search' );
				} elseif ( $progressPercent == 100 ) {
					$txt  = \PsfSearch\Engines\TNTSearchMySQL\Indexer\Builder::getReadableTotalIndexed();
					$text = sprintf( __( 'Finalization... Wait a moment. (%s products)', 'psf-search' ), $txt );
				}

				if ( $info['status'] === 'cancellation' ) {
					$text = __( 'Cancellation...', 'psf-search' );
				}

				echo $text;
				?>
				<span class="progress_number"><?php echo round( $progressPercent, 1 ); ?>%</span>
			</small>
			<span class="progress-bar-inner" style="background-color: #e6a51d; width: <?php echo $progressPercent; ?>%;">
		</span>
		</div>
	</div>
<?php endif; ?>

<div class="js-psfs-indexer-details<?php echo $isDetails ? ' show' : ' hide'; ?>">
	<table class="psfs-indexer-table js-psfs-indexer-table js-psfs-indexer-table">
		<tbody>
		<tr>
			<td><?php _e( 'Build ID', 'psf-search' ); ?></td>
			<td><?php echo ! empty( $info['build_id'] ) ? $info['build_id'] : '-'; ?></td>
		</tr>
		<tr>
			<td><?php _e( 'DB', 'psf-search' ); ?></td>
			<td><?php echo ! empty( $info['db'] ) ? $info['db'] : '-'; ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Index build start', 'psf-search' ); ?></td>
			<td><?php echo ! empty( $info['start_ts'] ) ? date( 'Y-m-d H:i:s', $info['start_ts'] ) : '-'; ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Status', 'psf-search' ); ?></td>
			<td><?php echo ! empty( $info['status'] ) ? $info['status'] : '-'; ?></td>
		</tr>
		<tr>
			<td><?php _e( 'Products', 'psf-search' ); ?></td>
			<td><?php echo ! empty( $info['total_products_for_indexing'] ) ? absint( $info['total_products_for_indexing'] ) : '-'; ?></td>
		</tr>
		<?php if ( ! empty( $totalNonProducts ) ) : ?>
			<tr>
				<td><?php _e( 'Non-products', 'psf-search' ); ?></td>
				<td><?php echo $totalNonProducts; ?></td>
			</tr>
		<?php endif; ?>
		<tr>
			<td><?php _e( 'Searchable', 'psf-search' ); ?></td>
			<td>
				<table class="psfs-indexer-table__in">
					<tr>
						<td><?php _e( 'Start', 'psf-search' ); ?></td>
						<td><?php echo ! empty( $info['start_searchable_ts'] ) ? date( 'H:i:s', $info['start_searchable_ts'] ) : '-'; ?></td>
					</tr>
					<tr>
						<td><?php _e( 'End', 'psf-search' ); ?></td>
						<td><?php echo ! empty( $info['end_searchable_ts'] ) ? date( 'H:i:s', $info['end_searchable_ts'] ) : '-'; ?></td>
					</tr>
					<?php if ( ! empty( $info['start_searchable_ts'] ) && ! empty( $info['end_searchable_ts'] ) ) : ?>
						<tr>
							<td><?php _e( 'Time', 'psf-search' ); ?></td>
							<td><?php echo $info['end_searchable_ts'] - $info['start_searchable_ts']; ?> sec</td>
						</tr>
					<?php endif; ?>
				</table>

			</td>
		</tr>
		<tr>
			<td><?php _e( 'Readable', 'psf-search' ); ?></td>
			<td>
				<table class="psfs-indexer-table__in">
					<tbody>
					<tr>
						<td><?php _e( 'Start', 'psf-search' ); ?></td>
						<td><?php echo ! empty( $info['start_readable_ts'] ) ? date( 'H:i:s', $info['start_readable_ts'] ) : '-'; ?></td>
					</tr>
					<tr>
						<td><?php _e( 'End', 'psf-search' ); ?></td>
						<td><?php echo ! empty( $info['end_readable_ts'] ) ? date( 'H:i:s', $info['end_readable_ts'] ) : '-'; ?></td>
					</tr>
					<?php if ( ! empty( $info['start_readable_ts'] ) && ! empty( $info['end_readable_ts'] ) ) : ?>
						<tr>
							<td><?php _e( 'Time', 'psf-search' ); ?></td>
							<td><?php echo $info['end_readable_ts'] - $info['start_readable_ts']; ?> sec</td>
						</tr>
					<?php endif; ?>
					</tbody>
				</table>

			</td>
		</tr>
		<?php if ( $canBuildTaxonomyIndex ) : ?>
			<tr>
				<td><?php _e( 'Taxonomies', 'psf-search' ); ?></td>
				<td>
					<table class="psfs-indexer-table__in">
						<tbody>
						<tr>
							<td><?php _e( 'Start', 'psf-search' ); ?></td>
							<td><?php echo ! empty( $info['start_taxonomies_ts'] ) ? date( 'H:i:s', $info['start_taxonomies_ts'] ) : '-'; ?></td>
						</tr>
						<tr>
							<td><?php _e( 'End', 'psf-search' ); ?></td>
							<td><?php echo ! empty( $info['end_taxonomies_ts'] ) ? date( 'H:i:s', $info['end_taxonomies_ts'] ) : '-'; ?></td>
						</tr>
						<?php if ( ! empty( $info['start_taxonomies_ts'] ) && ! empty( $info['end_taxonomies_ts'] ) ) : ?>
							<tr>
								<td><?php _e( 'Time', 'psf-search' ); ?></td>
								<td><?php echo $info['end_taxonomies_ts'] - $info['start_taxonomies_ts']; ?> sec</td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>

				</td>
			</tr>
		<?php endif; ?>
		</tbody>
	</table>

	<div class="psfs-indexer-logs">
		<h4><?php _e( 'Logs', 'psf-search' ); ?></h4>
		<?php
		foreach ( $logs as $log ) {
			$class = $log['error'] ? 'psfs-indexer-log psfs-indexer-logs__error' : 'psfs-indexer-log';
			echo '<span class="' . $class . '">[' . date( 'Y-m-d H:i:s', $log['time'] ) . '] ' . $log['message'] . '</span>';
		}
		?>
	</div>
</div>
