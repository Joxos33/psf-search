<?php

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}
?>
<div class="psf-search-posts-results-grid psf-search-posts-results-grid--<?php echo sanitize_key( $args['post_type'] ); ?>">
	<?php
	foreach ( $args['results']['results'] as $result ) {
		echo '<div class="psf-search-posts-results-grid-item">';
		echo '<a href="' . esc_url( $result['url'] ) . '" >';
		if ( ! empty( $args['show_image'] ) && ! empty( $result['image'] ) ) {
			echo '<div class="psf-search-posts-results-image">';
			echo '<img src="' . esc_url( $result['image'] ) . '" alt="' . esc_attr( $result['name'] ) . '" />';
			echo '</div>';
		}
		echo '<span class="psf-search-posts-results-label">' . esc_html( $result['name'] ) . '</span>';
		echo '</a>';
		echo '</div>';
	}
	?>
</div>
