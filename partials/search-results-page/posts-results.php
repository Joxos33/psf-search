<?php

// Exit if accessed directly
use PsfSearch\Helpers;
use PsfSearch\Shortcode;

if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

if ( empty( $args['post_type'] ) ) {
	return;
}
?>
	<div class="psf-search-posts-results">

		<?php
		if ( empty( $args['results']['results'] ) ) {
			echo apply_filters( 'psf_search/shortcode/psfsearch_posts_results/no_results/', $args['no_results'], $args['post_type'], $args );
		} else {
			echo '<h3 class="psf-search-posts-results-headline">' . esc_html( $args['headline'] ) . '</h3>';
		}
		$filename = apply_filters( 'psf_search/shortcode/psfsearch_posts_results/partial_path/' . $args['layout'], PSF_SEARCH_DIR . 'partials/search-results-page/' . $args['layout'] . '.php' );
		echo Shortcode::getTemplatePart( $filename, $args );
		?>
	</div>
<?php
