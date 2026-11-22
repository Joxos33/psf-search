<?php
// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

add_filter(
	'body_class',
	function ( $classes ) {
		$classes[] = 'psfs-theme-flatsome';

		return $classes;
	}
);

add_action(
	'wp_loaded',
	function () {
		remove_shortcode( 'search' );
		add_shortcode( 'search', [ 'PsfSearch\\Shortcode', 'addBody' ] );
	}
);

add_filter(
	'get_search_form',
	function( $form, $args ) {
		// Used on 404 page.
		return do_shortcode( '[psfsearch]' );
	},
	10,
	2
);

global $psfs_flatsome_search_counter;

$psfs_flatsome_search_counter = 0;

add_action(
	'wp_head',
	function () { ?>
	<style>
		.psfs-flatsome-up {
			margin-top: -40vh;
		}

		#search-lightbox .psfs-sf-wrapp input[type=search].psfs-search-input {
			height: 60px;
			font-size: 20px;
		}

		#search-lightbox .psfs-search-wrapp {
			-webkit-transition: all 100ms ease-in-out;
			-moz-transition: all 100ms ease-in-out;
			-ms-transition: all 100ms ease-in-out;
			-o-transition: all 100ms ease-in-out;
			transition: all 100ms ease-in-out;
		}

		#search-lightbox .psfs-sf-wrapp .psfs-search-submit:before {
			top: 21px;
		}

		.psfs-overlay-mobile-on .mfp-wrap .mfp-content {
			width: 100vw;
		}

		.psfs-overlay-mobile-on .mfp-wrap,
		.psfs-overlay-mobile-on .mfp-close,
		.psfs-overlay-mobile-on .nav-sidebar {
			display: none;
		}

		.psfs-overlay-mobile-on .main-menu-overlay {
			display: none;
		}

		.psfs-open .header-search-dropdown .nav-dropdown {
			opacity: 1;
			max-height: inherit;
			left: -15px !important;
		}

		.psfs-open:not(.psfs-theme-flatsome-dd-sc) .nav-right .header-search-dropdown .nav-dropdown {
			left: auto;
			/*right: -15px;*/
		}

		.psfs-theme-flatsome .nav-dropdown .psfs-search-wrapp {
			min-width: 450px;
		}

		.header-search-form {
			min-width: 250px;
		}
	</style>
		<?php
	}
);

// Count search items in headers.
add_action(
	'flatsome_header_elements',
	function ( $value ) {
		global $psfs_flatsome_search_counter;

		if ( $value === 'search' ) {
			$psfs_flatsome_search_counter ++;
		}
	}
);

add_action(
	'wp_footer',
	function () {
		global $psfs_flatsome_search_counter;

		// Overwriting search icon.
		if ( get_theme_mod( 'header_search_style', 'dropdown' ) === 'dropdown' && $psfs_flatsome_search_counter > 0 ) {
			for ( $i = 0; $i < $psfs_flatsome_search_counter; $i ++ ) {
				echo '<div id="psfs-theme-search-' . $i . '" style="display: block;" class="psfs-theme-search"><li>' . do_shortcode( '[psfsearch layout="icon"]' ) . '</li></div>';
			}
			?>
		<style>
			.header-main .psfs-search-wrapp.psfs-layout-icon .psfs-search-icon {
				width: 16px;
			}

			.header-main .psfs-search-wrapp.psfs-layout-icon .psfs-ico-magnifier-handler {
				fill: hsla(0, 0%, 40%, .85);
				max-width: 16px;
			}

			.header-main .psfs-search-wrapp.psfs-layout-icon .psfs-ico-magnifier-handler:hover {
				fill: hsla(0, 0%, 7%, .85);
			}

			.header-main.nav-dark .psfs-search-wrapp.psfs-layout-icon .psfs-ico-magnifier-handler {
				fill: hsla(0, 0%, 100%, .8);
			}

			.header-main.nav-dark .psfs-search-wrapp.psfs-layout-icon .psfs-ico-magnifier-handler:hover {
				fill: #ffffff;
			}
		</style>
		<script>
			psfsThemeSearch = document.querySelectorAll('.header-search');
			if (psfsThemeSearch.length > 0) {
				psfsThemeSearch.forEach((psfsThemeSearchItem, index) => {
					if (document.querySelector('#psfs-theme-search-' + index + ' > li') !== null) {
						psfsThemeSearchItem.replaceWith(document.querySelector('#psfs-theme-search-' + index + ' > li'));
					}
				});
			}
			document.querySelectorAll('.psfs-theme-search').forEach(function (elem) {
				elem.remove();
			});
		</script>
			<?php
		}

		$minChars = PSFS()->settings->getOption( 'min_chars' );
		if ( empty( $minChars ) || ! is_numeric( $minChars ) ) {
			$minChars = 3;
		}
		?>
	<script>
		(function ($) {
			$(document).ready(function () {
				$(document).on('keyup', '#search-lightbox .psfs-search-wrapp .psfs-search-input', function () {
					if (this.value.length >= <?php echo $minChars; ?>) {
						if (!$(this).closest('.psfs-search-wrapp').hasClass('psfs-flatsome-up')) {
							setTimeout(function () {
								$(window).trigger('resize.autocomplete');
							}, 105);
						}
						$(this).closest('.psfs-search-wrapp').addClass('psfs-flatsome-up');
					}
				});

				var refreshDropdownPosition;
				var style = '';
				var positioning = false;
				$(document).on('mouseenter', '.header-search-dropdown a', function (e) {
					if (positioning) {
						return;
					}

					setTimeout(function () {
						var pos = $(e.target).closest('.header-search').find('.nav-dropdown').attr('style');

						if (typeof pos == 'string' && pos.length > 0) {
							style = pos;
						}

						refreshDropdownPosition = setInterval(function () {
							if ($('body').hasClass('psfs-open') && style.length > 0) {
								$('.nav-dropdown').attr('style', style);
							}

							if (!$('body').hasClass('psfs-open') && !$('.header-search').hasClass('current-dropdown')) {
								clearInterval(refreshDropdownPosition);
								$('.nav-dropdown').removeAttr('style');
								style = '';
								positioning = false;
							}

						}, 10)
					}, 400);

					positioning = true;
				});

				$(document).on('click', '.header-search-lightbox > a, .header-search-lightbox > .header-button > a', function () {
					var formWrapper = $('#search-lightbox').find('.psfs-search-wrapp');
					setTimeout(function () {
						if (formWrapper.find('.psfs-close')[0]) {
							formWrapper.find('.psfs-close')[0].click();
						}

						formWrapper.removeClass('psfs-flatsome-up');
						formWrapper.find('.psfs-search-input').trigger('focus');

						// Recalc button positions.
						const plugin = formWrapper.find('.psfs-search-input').data('autocomplete');
						plugin.positionPreloaderAndMic();
					}, 300);
				});

				// Mobile
				$(document).on('click', '.mobile-nav .header-search .icon-search', function () {
					var $handler = $('.js-psfs-enable-mobile-form');
					if ($handler.length) {
						$handler[0].click();

						// Close unused modal.
						setTimeout(function () {
							var $modalClose = $('.mfp-wrap .mfp-close');
							if ($modalClose.length) {
								$modalClose[0].click();
							}
						}, 300);
					}
				});
			});
		})(jQuery);
	</script>
		<?php
	},
	1000
);

add_action(
	'wp_footer',
	function () {
		?>
	<script>
		(function ($) {
			// Fix Quantity buttons
			$(document).on('psfsDetailsPanelLoaded', function () {
				var $quantityFields = $('.psfs-details-wrapp .quantity');

				if ($quantityFields.length) {
					$quantityFields.addQty();
				}
			});
		})(jQuery);
	</script>
		<?php
	},
	1001
);

add_filter(
	'psfs/troubleshooting/tests',
	function ( $tests ) {
		$tests['direct'][] = [
			'label' => 'Flatsome incompatible settings',
			'test'  => function () {
				$result = [
					'label'       => '',
					'status'      => 'good',
					'description' => '',
					'actions'     => '',
					'test'        => 'FlatsomeIncompatibleSettings',
				];

				if (
					get_theme_mod( 'header_search_style' ) === 'lightbox' &&
					get_theme_mod( 'header_cart_style', 'dropdown' ) === 'off-canvas' &&
					PSFS()->settings->getOption( 'show_details_box' ) === 'on'
				) {
					$customizeUrl = admin_url( 'customize.php' );

					$result['status']       = 'critical';
					$result['label']        = __( 'There is a conflict between Flatsome theme settings and our plugin', 'psf-search' );
					$result['description']  = '<p style="max-width: 740px">' . __( "There is a rare combination of <b>PSF Search</b> and <b>Flatsome</b> settings that might cause issues when adding a product to the cart from the autocomplete search results. Unfortunately, you have this combination. You can't use <b>Off-Canvas Sidebar</b> as <b>Cart Style (Flatsome)</b>, <b>Search Icon Type</b> as <b>Search Icon Type (Flatsome)</b>, and <b>Details Panel (PSF Search)</b> at the same time. The solution is to resign from one of these options.", 'psf-search' ) . '</p>';
					$result['description'] .= '<p><b>' . __( 'Solutions (you only need to use one of them)', 'psf-search' ) . '</b></p>';
					$result['description'] .= '<ol><li>' . __( '(PSF Search settings) Go to <code>Autocomplete</code> tab and disable <code>Show Details Panel</code> option.', 'psf-search' ) . '</li>';
					$result['description'] .= '<li>' . sprintf( __( '(Flatsome settings) Go to <code>Appearance -> <a href="%s" target="_blank">Customize</a> -> Header -> Search</code> and change <code>Search Icon Type</code> option to <code>Dropdown</code>.', 'psf-search' ), $customizeUrl ) . '</li>';
					$result['description'] .= '<li>' . sprintf( __( '(Flatsome settings) Go to <code>Appearance -> <a href="%s" target="_blank">Customize</a> -> Header -> Cart</code> and change <code>Cart Style</code> option to <code>Dropdown</code> or <code>Link only</code>.', 'psf-search' ), $customizeUrl ) . '</li></ol>';
				}

				return $result;
			},
		];

		return $tests;
	}
);
