<?php

// Exit if accessed directly
use PsfSearch\Helpers;
use PsfSearch\Shortcode;

if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

if ( empty( $args['post_type'] ) ) {
	return;
}
?>
	<div class="psfs-posts-results">

		<?php
		if ( empty( $args['results']['results'] ) ) {
			echo apply_filters( 'psfs/shortcode/psfsearch_posts_results/no_results/', $args['no_results'], $args['post_type'], $args );
		} else {
			echo '<h3 class="psfs-posts-results-headline">' . esc_html( $args['headline'] ) . '</h3>';
		}
		$filename = apply_filters( 'psfs/shortcode/psfsearch_posts_results/partial_path/' . $args['layout'], PSFS_DIR . 'partials/search-results-page/' . $args['layout'] . '.php' );
		echo Shortcode::getTemplatePart( $filename, $args );
		?>
	</div>
<?php
