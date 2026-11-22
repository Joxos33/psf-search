<?php


// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

$utmLink             = 'https://fibosearch.com/pricing/?utm_source=wp-admin&utm_medium=referral&utm_campaign=upgrade-link&utm_content=how-to-use';
$blockEditorUrl      = 'https://fibosearch.com/documentation/get-started/how-to-add-psfsearch-to-your-website/#add-psfsearch-with-the-dedicated-psfsearch-block';
$blockEditorLink     = '<a href="' . $blockEditorUrl . '">' . __( 'learn how to use blocks', 'psf-search' ) . '</a>';
$displayMethodsCount = defined( 'ELEMENTOR_PRO_VERSION' ) ? 'six' : 'five';

?>
	<h4><?php printf( __( 'There are %s easy ways to display the search bar in your theme', 'psf-search' ), $displayMethodsCount ); ?>:</h4>
	<ol>
		<?php if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) : ?>
				<li><?php echo __( 'As an Elementor widget named “PSF Search”', 'psf-search' ); ?></li>
		<?php endif; ?>
		<li><?php printf( __( 'As a menu item - go to the %s and add the menu item “PSF Search bar”. Done!', 'psf-search' ), '<a href="' . admin_url( 'nav-menus.php' ) . '" target="_blank">' . __( 'Menu Screen', 'psf-search' ) . '</a>' ); ?>
		<li><?php echo __( 'As a block', 'psf-search' ) . ' - ' . $blockEditorLink; ?></li>
		<li><?php printf( __( 'Using a shortcode - %s', 'psf-search' ), '<code>[psfsearch]</code>' ); ?></li>
		<li><?php printf( __( 'As a widget - go to the %s and choose “PSF Search bar”', 'psf-search' ), '<a href="' . admin_url( 'widgets.php' ) . '" target="_blank">' . __( 'Widgets Screen', 'psf-search' ) . '</a>' ); ?>
		<li><?php printf( __( 'Using PHP - %s', 'psf-search' ), '<code>&lt;?php echo do_shortcode(\'[psfsearch]\'); ?&gt;</code>' ); ?></li>
	</ol>
<?php  ?>
