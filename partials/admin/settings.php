<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$advSettings = PSFS()->settings->canSeeAdvSettings();

$classes = [];
$classes[] = 'psfs-settings-pro';
if ( PSFS()->themeCompatibility->isCurrentThemeSupported() ) {
	$classes[] = 'psfs-settings-theme-supported';
}
?>
<div class="wrap psfs-settings <?php echo implode( ' ', $classes ); ?>">


	<h2 class="psfs-settings__head">
		<div class="psfs-settings__title">
			<div class="psfs-settings__title-top">
				<div class="psfs-settings-logo-wrapper">
					<img class="psfs-settings-logo" src="<?php echo PSFS_URL . 'assets/img/logo-30.png'; ?>"/>
					<span class="psfs-settings-logo-pro">Pro</span>
				</div>
				<span class="psfs-settings__title-core"><?php _e( 'Settings', 'psf-search' ); ?></span>
			</div>
		</div>
		<span class="psfs-settings__advanced js-psfs-settings__advanced">
			<span
				class="js-psfs-adv-settings-toggle woocommerce-input-toggle woocommerce-input-toggle--<?php echo $advSettings ? 'enabled' : 'disabled'; ?>">
				<?php
					_e(
						'Show advanced settings',
						'psf-search'
					);
					?>
					</span>
			<span class="psfs-adv-settings-label"><?php _e( 'Show advanced settings', 'psf-search' ); ?></span>
		</span>
	</h2>

	<?php $settings->show_navigation(); ?>

	<div class="psfs-settings-body js-psfs-settings-body">
		<?php
		require PSFS_DIR . 'partials/admin/search-preview.php';
		$settings->show_forms();
		?>
	</div>

</div>
