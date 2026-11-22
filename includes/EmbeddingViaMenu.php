<?php

namespace PsfSearch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EmbeddingViaMenu {
	const SEARCH_PLACEHOLDER = 'psf_search_search_box';

	public function init() {
		if ( is_admin() ) {
			add_action( 'admin_head-nav-menus.php', [ $this, 'addNavMenuMetaBoxes' ] );
			add_action( 'wp_nav_menu_item_custom_fields', [ $this, 'addNavMenuItemCustomFields' ], 10, 2 );
			add_action( 'wp_update_nav_menu_item', [ $this, 'updateNavMenuItem' ], 10, 3 );

			add_action( 'admin_head', [ $this, 'navMenuStyle' ] );
			add_action( 'admin_footer', [ $this, 'navMenuScripts' ] );
		} else {
			add_filter( 'walker_nav_menu_start_el', [ $this, 'processMenuItem' ], 50, 2 );
			add_filter( 'megamenu_walker_nav_menu_start_el', [ $this, 'processMenuItem' ], 50, 2 );
		}

		add_action(
			'wp_nav_menu_item_custom_fields_customize_template',
			[
				$this,
				'addNavMenuItemCustomFieldsTemplate',
			]
		);
	}

	/**
	 * Check if nav-menus screen is active
	 *
	 * @return bool
	 */
	private function isNavMenuScreen() {
		$isNav  = false;
		$screen = get_current_screen();

		if ( ! empty( $screen->id ) && ( $screen->id === 'nav-menus' ) ) {
			$isNav = true;
		}

		return $isNav;
	}

	/**
	 * Add custom nav meta box.
	 *
	 * Adapted from http://www.johnmorrisonline.com/how-to-add-a-fully-functional-custom-meta-box-to-wordpress-navigation-menus/.
	 *
	 * @return void
	 */
	public function addNavMenuMetaBoxes() {
		add_meta_box(
			'psf_search_endpoints_nav_link',
			__( 'PSF Search bar', 'psf-search' ),
			[ $this, 'navMenuLinks' ],
			'nav-menus',
			'side',
			'low'
		);
	}

	/**
	 * Add custom fields to own menu item
	 */
	public function addNavMenuItemCustomFields( $item_id, $item ) {
		if ( $item->post_title !== self::SEARCH_PLACEHOLDER ) {
			return;
		}
		$layout = get_post_meta( $item_id, '_menu_item_psf_search_layout', true );
		if ( empty( $layout ) ) {
			$layout = 'default';
		}
		$searchIconColor = get_post_meta( $item_id, '_menu_item_psf_search_search_icon_color', true );
		?>
		<p class="description description-wide psf-search-description">
			<label for="edit-menu-item-psf-search-layout-<?php echo $item_id; ?>">
				<?php _e( 'Layout', 'psf-search' ); ?>
				<select id="edit-menu-item-psf-search-layout-<?php echo $item_id; ?>" name="menu-item-psf-search-layout[<?php echo $item_id; ?>]" class="psf-search-layout-select">
					<?php
					foreach ( $this->getLayoutOptions() as $value => $name ) {
						$selected = selected( $value, $layout, false );
						printf( '<option %s value="%s">%s</option>', $selected, $value, $name );
					}
					?>
				</select>
			</label>
		</p>
		<p class="description description-wide psf-search-description">
			<?php _e( 'Search icon color', 'psf-search' ); ?><br/>
			<input
				type="text"
				class="widefat wp-color-picker-field dwgt-wcas-color-picker"
				name="menu-item-psf-search-search-icon-color[<?php echo $item_id; ?>]"
				value="<?php echo esc_attr( $searchIconColor ); ?>"/>
		</p>
		<?php
	}

	/**
	 * Handle updates of custom fileds for own menu item
	 *
	 * @see wp_update_nav_menu_item()
	 */
	public function updateNavMenuItem( $menu_id, $menu_item_db_id, $args ) {
		if ( ! isset( $args['menu-item-title'] ) || $args['menu-item-title'] !== self::SEARCH_PLACEHOLDER ) {
			return;
		}
		// TODO
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$layout = isset( $_POST['menu-item-psf-search-layout'][ $menu_item_db_id ] ) ? sanitize_key( $_POST['menu-item-psf-search-layout'][ $menu_item_db_id ] ) : '';
		update_post_meta( $menu_item_db_id, '_menu_item_psf_search_layout', $layout );
		//phpcs:ignore WordPress.Security.NonceVerification.Missing
		$searchIconColor = isset( $_POST['menu-item-psf-search-search-icon-color'][ $menu_item_db_id ] ) ? sanitize_hex_color( $_POST['menu-item-psf-search-search-icon-color'][ $menu_item_db_id ] ) : '';
		update_post_meta( $menu_item_db_id, '_menu_item_psf_search_search_icon_color', $searchIconColor );
	}

	/**
	 * Modifies the menu item display on frontend.
	 *
	 * @param string $itemOutput
	 *
	 * @return string
	 */
	public function processMenuItem( $itemOutput, $item ) {

		if (
			! empty( $itemOutput )
			&& is_string( $itemOutput )
			&& strpos( $itemOutput, self::SEARCH_PLACEHOLDER ) !== false
		) {
			$args   = '';
			$style  = '';
			$layout = get_post_meta( $item->ID, '_menu_item_psf_search_layout', true );
			if ( in_array( $layout, [ 'classic', 'icon', 'icon-flexible', 'icon-flexible-inv' ] ) ) {
				$args .= 'layout="' . $layout . '" ';
			}
			$searchIconColor = get_post_meta( $item->ID, '_menu_item_psf_search_search_icon_color', true );
			if ( in_array( $layout, [ 'icon', 'icon-flexible', 'icon-flexible-inv' ] ) && ! empty( $searchIconColor ) ) {
				$args .= 'class="psf-search-menu-item-' . $item->ID . ' " ';
				$style = sprintf( '<style>.psf-search-menu-item-%d .psf-search-ico-magnifier-handler path {fill: %s;}</style>', $item->ID, esc_attr( $searchIconColor ) );
			}
			$itemOutput = do_shortcode( sprintf( '[psfsearch %s]', $args ) ) . $style;
		}

		return $itemOutput;
	}

	/**
	 * Output menu links.
	 *
	 * @return void
	 */
	public function navMenuLinks() {
		?>
		<div id="posttype-psf-search-endpoints" class="posttypediv">
			<p><?php _e( 'Add PSF Search as a menu item.', 'psf-search' ); ?></p>
			<div id="tabs-panel-psf-search-endpoints" class="tabs-panel tabs-panel-active">
				<ul id="psf-search-endpoints-checklist" class="categorychecklist form-no-clear">
					<li>
						<label class="menu-item-title">
							<input
								type="checkbox" class="menu-item-checkbox" name="menu-item[-1][menu-item-object-id]"
								value="-1"/> <?php echo __( 'PSF Search bar', 'psf-search' ); ?>
						</label>
						<input type="hidden" class="menu-item-type" name="menu-item[-1][menu-item-type]" value="custom"/>
						<input type="hidden" class="menu-item-title" name="menu-item[-1][menu-item-title]" value="<?php echo self::SEARCH_PLACEHOLDER; ?>"/>
						<input type="hidden" class="menu-item-classes" name="menu-item[-1][menu-item-classes]"/>
					</li>
				</ul>
			</div>
			<p class="button-controls">
				<span class="add-to-menu">
					<button type="submit" class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to menu', 'woocommerce' ); ?>"
							name="add-post-type-menu-item" id="submit-posttype-psf-search-endpoints"><?php esc_html_e( 'Add to menu', 'woocommerce' ); ?></button>
					<span class="spinner"></span>
				</span>
			</p>
		</div>
		<?php
	}

	public function getDescription() {
		$html  = '<div class="psf-search-admin-menu-item-desc js-psf-search-admin-menu-item-desc">';
		$html .= '<span>' . __( 'PSF Search bar will be displayed here.', 'psf-search' ) . '</span>';
		$html .= '</div>';

		return $html;
	}

	public function getLayoutOptions() {
		return [
			'default'           => __( 'Default', 'psf-search' ),
			'classic'           => __( 'Search bar', 'psf-search' ),
			'icon'              => __( 'Search icon', 'psf-search' ),
			'icon-flexible'     => __( 'Icon on mobile, search bar on desktop', 'psf-search' ),
			'icon-flexible-inv' => __( 'Icon on desktop, search bar on mobile', 'psf-search' ),
		];
	}

	public function navMenuStyle() {

		if ( ! $this->isNavMenuScreen() ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		?>
		<style>
			.psf-search-admin-menu-item-desc {
				display: flex;
				flex-direction: row;
				justify-content: left;
				align-items: center;
			}

			.psf-search-admin-menu-item-desc img {
				display: block;
				margin-right: 15px;
				border-radius: 4px;
			}

			.psf-search-description select {
				width: 100%;
			}
		</style>

		<?php

	}

	public function navMenuScripts() {

		if ( ! $this->isNavMenuScreen() ) {
			return;
		}

		?>
		<script>
			(function ($) {

				function replaceLabels($menuItem) {

					var $menuItems = $('#menu-to-edit .menu-item-title');

					if ($menuItems.length > 0) {

						$menuItems.each(function () {
							if ($(this).text() === '<?php echo self::SEARCH_PLACEHOLDER; ?>') {

								var $menuItem = $(this).closest('.menu-item');

								$menuItem.find('.menu-item-title').text('PSF Search bar');
								$menuItem.find('.item-type').text('<?php _e( 'Search bar', 'psf-search' ); ?>');
								$menuItem.find('.menu-item-settings .edit-menu-item-title').closest('label').hide();
								$menuItem.find('.field-url').hide();


								if ($menuItem.find('.js-psf-search-admin-menu-item-desc').length == 0) {
									$menuItem.find('.menu-item-settings').prepend('<?php echo $this->getDescription(); ?>');
								}
							}
						});
					}
				}

				function colorPickers() {
					var $colorPickers = $('.dwgt-wcas-color-picker');
					if ($colorPickers.length > 0) {
						$colorPickers.wpColorPicker();
					}
				}

				function toggleColorPicker(el) {
					var layout = el.val();
					if (layout === 'default' || layout === 'classic') {
						el.closest('.psf-search-description').next().slideUp();
					} else {
						el.closest('.psf-search-description').next().slideDown();
					}
				}

				function syncColorPickersWithSelects() {
					var layoutSelects = $('.psf-search-layout-select');
					if (layoutSelects.length > 0) {
						layoutSelects.each(function (i, el) {
							toggleColorPicker($(el));
						});
					}
				}

				$(document).ready(function () {

					replaceLabels();
					colorPickers()
					syncColorPickersWithSelects();

				});

				$(document).on('change', '.psf-search-layout-select', function (e) {
					toggleColorPicker($(this))
				});

				$(document).ajaxComplete(function (event, request, settings) {

					if (
						typeof settings != 'undefined'
						&& typeof settings.data == 'string'
						&& settings.data.indexOf('action=add-menu-item') !== -1
						&& settings.data.indexOf('psf_search_search_box') !== -1
					) {
						replaceLabels();
						colorPickers();
						syncColorPickersWithSelects();

						setTimeout(function () {
							replaceLabels();
							colorPickers();
							syncColorPickersWithSelects();
						}, 500)

					}

				});

			}(jQuery));
		</script>

		<?php

	}

	public function addNavMenuItemCustomFieldsTemplate() {
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped,WordPress.Security.EscapeOutput.UnsafePrintingFunction
		?>
		<# if ( data.title === 'psf_search_search_box' ) { #>
		<fieldset class="nav_menu_role_display_mode">
			<legend class="customize-control-title"><?php _e( 'PSF Search bar', 'psf-search' ); ?></legend>
			<?php printf( __( 'Go to <a href="%s">Appearance -> Menus</a> to configure the PSF Search bar.', 'psf-search' ), esc_url( admin_url( 'nav-menus.php' ) ) ); ?>
		</fieldset>
		<# } #>
		<?php
		// phpcs:enable
	}
}
