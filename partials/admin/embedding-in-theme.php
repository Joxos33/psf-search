<?php


// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

if ( PSFS()->themeCompatibility->isCurrentThemeSupported() ) :

	if ( ! PSFS()->themeCompatibility->isWhiteLabel() ) :

		$name        = PSFS()->themeCompatibility->getThemeName();
		$parentLabel = '';

		if ( PSFS()->themeCompatibility->isChildTheme() ) {
			$parentLabel = ', ' . sprintf( __( 'child theme of <b>%s</b>', 'psf-search' ), PSFS()->themeCompatibility->getParentThemeName() );
		}

		?>
		<h2><?php printf( __( 'You are using the <b>%1$s</b> theme%2$s. Fantastic!', 'psf-search' ), $name, $parentLabel ); ?></h2>
		<p><?php _e( 'We support this theme so you can easily replace all default search bars.', 'psf-search' ); ?></p>
	<?php endif; ?>
<?php endif; ?>
