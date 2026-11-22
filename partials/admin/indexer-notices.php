<?php

// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

if ( ! empty( $notices ) ) : ?>
	<div class="psfs-indexing-notices">
		<?php
		foreach ( $notices as $type => $message ) :
			$dashicon = '';
			switch ( $type ) {
				case 'info':
					$dashicon = '<span class="dashicons dashicons-info"></span>';
					break;
			}
			?>
			<div class="psfs-indexing-notice psfs-indexing-notice--<?php echo $type; ?>">
				<div><?php echo $dashicon; ?></div>
				<div><?php echo $message; ?></div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
endif;
