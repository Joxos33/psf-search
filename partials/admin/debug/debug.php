<?php

// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

$prefix = 'psfsearch-debug_page-';
?>
<div class="wrap psfs-settings psfs-debug">

	<h2 class="psfs-settings__head">
		<div class="psfs-settings__title">
			<div class="psfs-settings__title-top">
				<div class="psfs-settings-logo-wrapper">
					<img class="psfs-settings-logo" src="<?php echo PSFS_URL . 'assets/img/logo-30.png'; ?>"/>
					<span class="psfs-settings-logo-pro">Pro</span>
				</div>
				<span class="psfs-settings__title-core"><?php _e( 'Debug page', 'psf-search' ); ?></span>
			</div>
		</div>
	</h2>

	<h2 class="nav-tab-wrapper psfsearch-debug_page-nav-tab-wrapper">
		<?php  ?>
		<a href="#psfs_deb_search" class="nav-tab" id="psfs_deb_search-tab">Search</a>
		<a href="#psfs_deb_indexer" class="nav-tab" id="psfs_deb_indexer-tab">Indexer</a>
		<a href="#psfs_deb_product" class="nav-tab" id="psfs_deb_product-tab">Product</a>
		<a href="#psfs_deb_term" class="nav-tab" id="psfs_deb_term-tab">Term</a>
		<a href="#psfs_deb_source-query" class="nav-tab" id="psfs_deb_source-query-tab">Source Query</a>
		<a href="#psfs_deb_visibility" class="nav-tab" id="psfs_deb_visibility-tab">Products Visibility</a>
		<a href="#psfs_deb_missing_products" class="nav-tab" id="psfs_deb_missing_products-tab">Missing Products</a>
		<a href="#psfs_deb_hooks" class="nav-tab" id="psfs_deb_hooks-tab">Hooks</a>
	<?php  ?>
		<a href="#psfs_deb_anatytics" class="nav-tab" id="psfs_deb_anatytics-tab">Analytics</a>
		<a href="#psfs_deb_maintenance" class="nav-tab" id="psfs_deb_maintenance-tab">Maintenance</a>
		<a href="#psfs_deb_active_integrations" class="nav-tab" id="psfs_deb_active_integrations-tab">Integrations</a>
	</h2>

	<?php
	$active = 'analytics';
	$active = 'search';
	?>

	<div class="psfs-settings-body js-psfs-settings-body" data-psfs-active="<?php echo esc_attr( $active ); ?>">
		<?php  ?>
		<div id="psfs_deb_search" class="psfsearch-debug_page-group psfs-group-active">
			<?php include_once PSFS_DIR . 'partials/admin/debug/body-search.php'; ?>
		</div>

		<div id="psfs_deb_indexer" class="psfsearch-debug_page-group">
			<?php include_once PSFS_DIR . 'partials/admin/debug/body-indexer.php'; ?>
		</div>

		<div id="psfs_deb_product" class="psfsearch-debug_page-group">
			<?php include_once PSFS_DIR . 'partials/admin/debug/body-product.php'; ?>
		</div>

		<div id="psfs_deb_term" class="psfsearch-debug_page-group">
			<?php include_once PSFS_DIR . 'partials/admin/debug/body-term.php'; ?>
		</div>

		<div id="psfs_deb_source-query" class="psfsearch-debug_page-group">
			<?php include_once PSFS_DIR . 'partials/admin/debug/body-source-query.php'; ?>
		</div>

		<div id="psfs_deb_visibility" class="psfsearch-debug_page-group">
			<?php include_once PSFS_DIR . 'partials/admin/debug/body-catalog-visibility.php'; ?>
		</div>

		<div id="psfs_deb_missing_products" class="psfsearch-debug_page-group">
			<?php include_once PSFS_DIR . 'partials/admin/debug/body-missing-products.php'; ?>
		</div>

		<div id="psfs_deb_hooks" class="psfsearch-debug_page-group">
			<?php require_once PSFS_DIR . 'partials/admin/debug/hooks.php'; ?>
		</div>
	<?php  ?>

		<div id="psfs_deb_anatytics" class="psfsearch-debug_page-group">
			<?php require_once PSFS_DIR . 'partials/admin/debug/body-analytics.php'; ?>
		</div>
		<div id="psfs_deb_maintenance" class="psfsearch-debug_page-group">
			<?php require_once PSFS_DIR . 'partials/admin/debug/body-maintenance.php'; ?>
		</div>
		<div id="psfs_deb_active_integrations" class="psfsearch-debug_page-group">
			<?php require_once PSFS_DIR . 'partials/admin/debug/active-integrations.php'; ?>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function ($) {

		function markActiveGroup($group) {
			var name = $group.attr('id').replace('psfs_', '');

			$group.addClass('psfs-group-active');
			$group.closest('.js-psfs-settings-body').attr('data-psfs-active', name)
		}

		// A tab href reaches jQuery's $(); only allow "#id" selectors so a forged tab
		// (e.g. injected into an admin notice) cannot smuggle an HTML string into $().
		function psfsIsSafeTabHref(href) {
			return typeof href === 'string' && /^#[A-Za-z0-9_-]+$/.test(href);
		}

		// Switches option sections
		$('.<?php echo $prefix; ?>group').hide();
		var activetab = '';

		if (typeof (localStorage) != 'undefined') {
			maybe_active = localStorage.getItem('<?php echo $prefix; ?>settings-active-tab');

			if (maybe_active) {
				// Check if tabs exists
				$('.<?php echo $prefix; ?>nav-tab-wrapper a:not(.js-nav-tab-minor)').each(function () {

					if ($(this).attr('href') === maybe_active && psfsIsSafeTabHref(maybe_active)) {
						activetab = maybe_active;
					}
				});
			}
		}

		if (activetab != '' && $(activetab).length) {
			$(activetab).fadeIn();
			markActiveGroup($(activetab));
		} else {
			$('.<?php echo $prefix; ?>group:first').fadeIn();
			markActiveGroup($('.<?php echo $prefix; ?>group:first'));
		}

		$('.<?php echo $prefix; ?>group .collapsed').each(function () {
			$(this).find('input:checked').parent().parent().parent().nextAll().each(
				function () {
					if ($(this).hasClass('last')) {
						$(this).removeClass('hidden');
						return false;
					}
					$(this).filter('.hidden').removeClass('hidden');
				});
		});

		if (activetab != '' && $(activetab + '-tab').length) {
			$(activetab + '-tab').addClass('nav-tab-active');
		} else {
			$('.<?php echo $prefix; ?>nav-tab-wrapper a:first').addClass('nav-tab-active');
		}

		$('.<?php echo $prefix; ?>nav-tab-wrapper a:not(.js-nav-tab-minor)').on('click', function (evt) {

			if (!psfsIsSafeTabHref($(this).attr('href'))) {
				evt.preventDefault();
				return;
			}

			if (typeof (localStorage) != 'undefined') {
				localStorage.setItem('<?php echo $prefix; ?>settings-active-tab', $(this).attr('href'));
			}

			$('.<?php echo $prefix; ?>nav-tab-wrapper a').removeClass('nav-tab-active');

			$(this).addClass('nav-tab-active').trigger('blur');
			var clicked_group = $(this).attr('href');

			$('.<?php echo $prefix; ?>group').hide();
			$(clicked_group).fadeIn();
			markActiveGroup($(clicked_group));
			evt.preventDefault();
		});
	});
</script>
