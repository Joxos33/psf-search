<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( $vars['multilingual']['is-multilingual'] ) : ?>
	<div class="psfs-analytics-langs">
		<h3><?php _e( 'Language', 'psf-search' ); ?></h3>
		<p class="psfs-analytics-subtitle"><?php _e( 'Shows stats only for the selected language.', 'psf-search' ); ?></p>
		<select class="js-psfs-analytics-lang">
			<?php foreach ( $vars['multilingual']['langs'] as $lang => $label ) : ?>
				<option value="<?php echo esc_html( $lang ); ?>" <?php selected( $vars['multilingual']['current-lang'], $lang, true ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
<?php endif; ?>
