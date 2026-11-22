<?php

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

if ( ! empty( $notices ) ) : ?>
	<div class="psf-search-indexing-notices">
		<?php
		foreach ( $notices as $type => $message ) :
			$dashicon = '';
			switch ( $type ) {
				case 'info':
					$dashicon = '<span class="dashicons dashicons-info"></span>';
					break;
			}
			?>
			<div class="psf-search-indexing-notice psf-search-indexing-notice--<?php echo $type; ?>">
				<div><?php echo $dashicon; ?></div>
				<div><?php echo $message; ?></div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
endif;
