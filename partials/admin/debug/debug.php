<?php

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

$prefix = 'psfsearch-debug_page-';
?>
<div class="wrap psf-search-settings psf-search-debug">

	<h2 class="psf-search-settings__head">
		<div class="psf-search-settings__title">
			<div class="psf-search-settings__title-top">
				<div class="psf-search-settings-logo-wrapper">
					<img class="psf-search-settings-logo" src="<?php echo PSF_SEARCH_URL . 'assets/img/logo-30.png'; ?>"/>
					<span class="psf-search-settings-logo-pro">Pro</span>
				</div>
				<span class="psf-search-settings__title-core"><?php _e( 'Debug page', 'psf-search' ); ?></span>
			</div>
		</div>
	</h2>

	<h2 class="nav-tab-wrapper psfsearch-debug_page-nav-tab-wrapper">
		<?php  ?>
		<a href="#psf_search_deb_search" class="nav-tab" id="psf_search_deb_search-tab">Search</a>
		<a href="#psf_search_deb_indexer" class="nav-tab" id="psf_search_deb_indexer-tab">Indexer</a>
		<a href="#psf_search_deb_product" class="nav-tab" id="psf_search_deb_product-tab">Product</a>
		<a href="#psf_search_deb_term" class="nav-tab" id="psf_search_deb_term-tab">Term</a>
		<a href="#psf_search_deb_source-query" class="nav-tab" id="psf_search_deb_source-query-tab">Source Query</a>
		<a href="#psf_search_deb_visibility" class="nav-tab" id="psf_search_deb_visibility-tab">Products Visibility</a>
		<a href="#psf_search_deb_missing_products" class="nav-tab" id="psf_search_deb_missing_products-tab">Missing Products</a>
		<a href="#psf_search_deb_hooks" class="nav-tab" id="psf_search_deb_hooks-tab">Hooks</a>
	<?php  ?>
		<a href="#psf_search_deb_anatytics" class="nav-tab" id="psf_search_deb_anatytics-tab">Analytics</a>
		<a href="#psf_search_deb_maintenance" class="nav-tab" id="psf_search_deb_maintenance-tab">Maintenance</a>
		<a href="#psf_search_deb_active_integrations" class="nav-tab" id="psf_search_deb_active_integrations-tab">Integrations</a>
	</h2>

	<?php
	$active = 'analytics';
	$active = 'search';
	?>

	<div class="psf-search-settings-body js-psf-search-settings-body" data-psf-search-active="<?php echo esc_attr( $active ); ?>">
		<?php  ?>
		<div id="psf_search_deb_search" class="psfsearch-debug_page-group psf-search-group-active">
			<?php include_once PSF_SEARCH_DIR . 'partials/admin/debug/body-search.php'; ?>
		</div>

		<div id="psf_search_deb_indexer" class="psfsearch-debug_page-group">
			<?php include_once PSF_SEARCH_DIR . 'partials/admin/debug/body-indexer.php'; ?>
		</div>

		<div id="psf_search_deb_product" class="psfsearch-debug_page-group">
			<?php include_once PSF_SEARCH_DIR . 'partials/admin/debug/body-product.php'; ?>
		</div>

		<div id="psf_search_deb_term" class="psfsearch-debug_page-group">
			<?php include_once PSF_SEARCH_DIR . 'partials/admin/debug/body-term.php'; ?>
		</div>

		<div id="psf_search_deb_source-query" class="psfsearch-debug_page-group">
			<?php include_once PSF_SEARCH_DIR . 'partials/admin/debug/body-source-query.php'; ?>
		</div>

		<div id="psf_search_deb_visibility" class="psfsearch-debug_page-group">
			<?php include_once PSF_SEARCH_DIR . 'partials/admin/debug/body-catalog-visibility.php'; ?>
		</div>

		<div id="psf_search_deb_missing_products" class="psfsearch-debug_page-group">
			<?php include_once PSF_SEARCH_DIR . 'partials/admin/debug/body-missing-products.php'; ?>
		</div>

		<div id="psf_search_deb_hooks" class="psfsearch-debug_page-group">
			<?php require_once PSF_SEARCH_DIR . 'partials/admin/debug/hooks.php'; ?>
		</div>
	<?php  ?>

		<div id="psf_search_deb_anatytics" class="psfsearch-debug_page-group">
			<?php require_once PSF_SEARCH_DIR . 'partials/admin/debug/body-analytics.php'; ?>
		</div>
		<div id="psf_search_deb_maintenance" class="psfsearch-debug_page-group">
			<?php require_once PSF_SEARCH_DIR . 'partials/admin/debug/body-maintenance.php'; ?>
		</div>
		<div id="psf_search_deb_active_integrations" class="psfsearch-debug_page-group">
			<?php require_once PSF_SEARCH_DIR . 'partials/admin/debug/active-integrations.php'; ?>
		</div>
	</div>
</div>

<script>
	jQuery(document).ready(function ($) {

		function markActiveGroup($group) {
			var name = $group.attr('id').replace('psf_search_', '');

			$group.addClass('psf-search-group-active');
			$group.closest('.js-psf-search-settings-body').attr('data-psf-search-active', name)
		}

		// A tab href reaches jQuery's $(); only allow "#id" selectors so a forged tab
		// (e.g. injected into an admin notice) cannot smuggle an HTML string into $().
		function psfSearchIsSafeTabHref(href) {
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

					if ($(this).attr('href') === maybe_active && psfSearchIsSafeTabHref(maybe_active)) {
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

			if (!psfSearchIsSafeTabHref($(this).attr('href'))) {
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
