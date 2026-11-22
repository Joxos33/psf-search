<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<tr class="js-psf-search-critical-searches-row">
	<td><?php echo $i; ?></td>
	<td><?php echo esc_html( $row['phrase'] ); ?></td>
	<td><?php echo esc_html( $row['qty'] ); ?></td>
	<td>
		<button class="button button-small js-psf-search-stats-critical-check"><?php _e( 'Check', 'psf-search' ); ?></button>
	</td>
</tr>
