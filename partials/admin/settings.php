<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$advSettings = PSF_SEARCH()->settings->canSeeAdvSettings();

$classes = [];
$classes[] = 'psf-search-settings-pro';
if ( PSF_SEARCH()->themeCompatibility->isCurrentThemeSupported() ) {
	$classes[] = 'psf-search-settings-theme-supported';
}
?>
<div class="wrap psf-search-settings <?php echo implode( ' ', $classes ); ?>">


	<h2 class="psf-search-settings__head">
		<div class="psf-search-settings__title">
			<div class="psf-search-settings__title-top">
				<div class="psf-search-settings-logo-wrapper">
					<img class="psf-search-settings-logo" src="<?php echo PSF_SEARCH_URL . 'assets/img/logo-30.png'; ?>"/>
					<span class="psf-search-settings-logo-pro">Pro</span>
				</div>
				<span class="psf-search-settings__title-core"><?php _e( 'Settings', 'psf-search' ); ?></span>
			</div>
		</div>
		<span class="psf-search-settings__advanced js-psf-search-settings__advanced">
			<span
				class="js-psf-search-adv-settings-toggle woocommerce-input-toggle woocommerce-input-toggle--<?php echo $advSettings ? 'enabled' : 'disabled'; ?>">
				<?php
					_e(
						'Show advanced settings',
						'psf-search'
					);
					?>
					</span>
			<span class="psf-search-adv-settings-label"><?php _e( 'Show advanced settings', 'psf-search' ); ?></span>
		</span>
	</h2>

	<?php $settings->show_navigation(); ?>

	<div class="psf-search-settings-body js-psf-search-settings-body">
		<?php
		require PSF_SEARCH_DIR . 'partials/admin/search-preview.php';
		$settings->show_forms();
		?>
	</div>

</div>
