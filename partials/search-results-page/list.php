<?php

// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

?>
<ul class="psfs-posts-results-list psfs-posts-results-list--<?php echo sanitize_key( $args['post_type'] ); ?>">
	<?php
	foreach ( $args['results']['results'] as $result ) {
		echo '<li class="psfs-posts-results-label"><a href="' . esc_url( $result['url'] ) . '">' . esc_html( $result['name'] ) . '</a></li>';
	}
	?>
</ul>
