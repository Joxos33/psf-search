<?php


// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

if ( PSF_SEARCH()->themeCompatibility->isCurrentThemeSupported() ) :

	if ( ! PSF_SEARCH()->themeCompatibility->isWhiteLabel() ) :

		$name        = PSF_SEARCH()->themeCompatibility->getThemeName();
		$parentLabel = '';

		if ( PSF_SEARCH()->themeCompatibility->isChildTheme() ) {
			$parentLabel = ', ' . sprintf( __( 'child theme of <b>%s</b>', 'psf-search' ), PSF_SEARCH()->themeCompatibility->getParentThemeName() );
		}

		?>
		<h2><?php printf( __( 'You are using the <b>%1$s</b> theme%2$s. Fantastic!', 'psf-search' ), $name, $parentLabel ); ?></h2>
		<p><?php _e( 'We support this theme so you can easily replace all default search bars.', 'psf-search' ); ?></p>
	<?php endif; ?>
<?php endif; ?>
