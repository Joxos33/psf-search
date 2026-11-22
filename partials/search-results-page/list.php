<?php

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

?>
<ul class="psf-search-posts-results-list psf-search-posts-results-list--<?php echo sanitize_key( $args['post_type'] ); ?>">
	<?php
	foreach ( $args['results']['results'] as $result ) {
		echo '<li class="psf-search-posts-results-label"><a href="' . esc_url( $result['url'] ) . '">' . esc_html( $result['name'] ) . '</a></li>';
	}
	?>
</ul>
