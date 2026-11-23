<?php

namespace PsfSearch\Shop;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The shop filter panel: markup, assets, and the cached term lists it loads on demand.
 *
 * Rendered where PSF Styling's catalog shell asks for the sidebar
 * (do_action( 'psf_shop_sidebar' )), keeping PSF Styling's #psf-shop-filter-panel /
 * .psf-shop-sidebar wrapper so its layout and mobile drawer CSS still apply.
 * Everything inside is PSF Search's (.psf-search-filters), styled from Appearance.
 */
class Panel {

	const TOP_BRANDS    = 6;
	const TOP_TERMS     = 8;
	const PRICE_PRESETS = [
		'0-250'    => 'Under $250',
		'250-500'  => '$250–$500',
		'500-1000' => '$500–$1,000',
		'1000-0'   => '$1,000+',
	];

	public function __construct() {
		add_action( 'psf_shop_sidebar', [ $this, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ], 30 );
		add_action( 'wc_ajax_psf_search_filter_terms', [ $this, 'termsEndpoint' ] );
	}

	public function enqueue() {
		if ( ! Filters::isShopContext() ) {
			return;
		}

		wp_enqueue_style( 'psf-search-filters', PSF_SEARCH_URL . 'assets/css/filters.css', [], PSF_SEARCH_VERSION );
		wp_enqueue_script( 'psf-search-filters', PSF_SEARCH_URL . 'assets/js/filters.js', [], PSF_SEARCH_VERSION, true );
		wp_localize_script(
			'psf-search-filters',
			'psfSearchFilters',
			[
				'termsUrl' => \WC_AJAX::get_endpoint( 'psf_search_filter_terms' ),
				'i18n'     => [
					'noMatches' => __( 'No matches', 'psf-search' ),
					'loading'   => __( 'Loading…', 'psf-search' ),
					'error'     => __( 'Could not load results. Please try again.', 'psf-search' ),
				],
			]
		);
	}

	/**
	 * JSON list of every term for one facet: [{ s: slug, n: name }], cached for an hour.
	 */
	public function termsEndpoint() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$facet = isset( $_GET['facet'] ) ? sanitize_key( wp_unslash( $_GET['facet'] ) ) : '';
		$list  = self::facetTerms( $facet );

		if ( $list === null ) {
			wp_send_json_error( null, 400 );
		}

		header( 'Cache-Control: public, max-age=3600' );
		wp_send_json_success( $list );
	}

	/**
	 * @return array|null
	 */
	public static function facetTerms( string $facet ) {
		$cacheKey = 'psf_search_facet_terms_' . $facet;
		$cached   = get_transient( $cacheKey );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		switch ( $facet ) {
			case 'brand':
				$terms = array_map(
					function ( $t ) {
						return [
							's' => $t->slug,
							'n' => $t->name,
						];
					},
					Brands::terms()
				);
				break;
			case 'caliber':
			case 'action':
				$taxonomy = $facet === 'caliber' ? Filters::CALIBER_TAXONOMY : Filters::ACTION_TAXONOMY;
				$terms    = self::termList( $taxonomy, 0 );
				break;
			default:
				return null;
		}

		set_transient( $cacheKey, $terms, HOUR_IN_SECONDS );

		return $terms;
	}

	/**
	 * @return array<int, array{s:string,n:string}>
	 */
	private static function termList( string $taxonomy, int $limit, string $orderby = 'name' ): array {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return [];
		}

		$terms = get_terms(
			[
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'number'     => $limit,
				'orderby'    => $orderby,
				'order'      => $orderby === 'count' ? 'DESC' : 'ASC',
			]
		);
		if ( is_wp_error( $terms ) ) {
			return [];
		}

		$out = [];
		foreach ( $terms as $term ) {
			$out[] = [
				's' => $term->slug,
				'n' => html_entity_decode( $term->name, ENT_QUOTES ),
			];
		}

		return $out;
	}

	/**
	 * Category options: top level and one level of children.
	 */
	private static function categoryOptions(): array {
		$cached = get_transient( 'psf_search_facet_categories' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$top = get_terms(
			[
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => 0,
			]
		);

		$out = [];
		if ( ! is_wp_error( $top ) ) {
			foreach ( $top as $term ) {
				if ( $term->slug === 'uncategorized' ) {
					continue;
				}
				$out[] = [
					's'     => $term->slug,
					'n'     => html_entity_decode( $term->name, ENT_QUOTES ),
					'depth' => 0,
				];

				$children = get_terms(
					[
						'taxonomy'   => 'product_cat',
						'hide_empty' => true,
						'parent'     => $term->term_id,
					]
				);
				if ( ! is_wp_error( $children ) ) {
					foreach ( $children as $child ) {
						$out[] = [
							's'     => $child->slug,
							'n'     => html_entity_decode( $child->name, ENT_QUOTES ),
							'depth' => 1,
						];
					}
				}
			}
		}

		set_transient( 'psf_search_facet_categories', $out, HOUR_IN_SECONDS );

		return $out;
	}

	public function render() {
		$f        = Filters::state();
		$resetUrl = Filters::url( array_fill_keys( array_merge( Filters::PARAMS, [ 's', 'psf_q', 'psf_search', 'post_type' ] ), null ) );

		$selectedBrand = $f['brand'] !== '' ? Brands::find( $f['brand'] ) : null;
		$topBrands     = array_map(
			function ( $t ) {
				return [
					's' => $t->slug,
					'n' => $t->name,
				];
			},
			Brands::top( self::TOP_BRANDS )
		);

		$calibers = self::termList( Filters::CALIBER_TAXONOMY, self::TOP_TERMS, 'count' );
		$actions  = self::termList( Filters::ACTION_TAXONOMY, self::TOP_TERMS, 'count' );

		$isArchive   = Filters::isShopArchive() && ! Filters::isProductSearch();
		$searchLabel = __( 'Search products', 'psf-search' );
		$term        = get_queried_object();
		if ( $isArchive && $term instanceof \WP_Term ) {
			/* translators: %s: category or brand name */
			$searchLabel = sprintf( __( 'Search in %s', 'psf-search' ), $term->name );
		}
		?>
		<aside id="psf-shop-filter-panel" class="psf-shop-sidebar psf-search-filters js-psf-search-filters" aria-label="<?php esc_attr_e( 'Product filters', 'psf-search' ); ?>" data-base-url="<?php echo esc_url( Filters::baseUrl() ); ?>">
			<div class="psf-search-filters__head">
				<h2 class="psf-search-filters__title"><?php esc_html_e( 'Filters', 'psf-search' ); ?></h2>
				<a class="psf-search-filters__clear js-psf-search-filters-link" href="<?php echo esc_url( $resetUrl ); ?>"><?php esc_html_e( 'Clear all', 'psf-search' ); ?></a>
				<button type="button" class="psf-search-filters__close js-psf-search-filters-close" aria-label="<?php esc_attr_e( 'Close filters', 'psf-search' ); ?>">&times;</button>
			</div>

			<form class="psf-search-filters__form js-psf-search-filters-form" action="<?php echo esc_url( Filters::baseUrl() ); ?>" method="get">
				<?php $this->renderPreservedParams(); ?>

				<div class="psf-search-filters__group psf-search-filters__group--search">
					<label class="psf-search-filters__label" for="psf-search-filters-q"><?php echo esc_html( $searchLabel ); ?></label>
					<div class="psf-search-filters__search">
						<input id="psf-search-filters-q" type="search" name="<?php echo $isArchive ? 'psf_q' : 's'; ?>" value="<?php echo esc_attr( $f['q'] ); ?>" placeholder="<?php esc_attr_e( 'Model, brand, UPC…', 'psf-search' ); ?>" autocomplete="off">
						<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'psf-search' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg></button>
					</div>
				</div>

				<div class="psf-search-filters__group">
					<label class="psf-search-filters__label" for="psf-search-filters-cat"><?php esc_html_e( 'Category', 'psf-search' ); ?></label>
					<select id="psf-search-filters-cat" name="psf_cat" class="psf-search-filters__select js-psf-search-filters-auto">
						<option value=""><?php esc_html_e( 'All categories', 'psf-search' ); ?></option>
						<?php foreach ( self::categoryOptions() as $option ) : ?>
							<option value="<?php echo esc_attr( $option['s'] ); ?>" <?php selected( $f['cat'], $option['s'] ); ?>><?php echo esc_html( ( $option['depth'] ? '— ' : '' ) . $option['n'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>

				<fieldset class="psf-search-filters__group">
					<legend class="psf-search-filters__label"><?php esc_html_e( 'Availability', 'psf-search' ); ?></legend>
					<?php
					$this->checkbox( 'in_stock', __( 'In stock only', 'psf-search' ), $f['instock'] );
					$this->checkbox( 'on_sale', __( 'On sale', 'psf-search' ), $f['on_sale'] );
					$this->checkbox( 'layaway', __( 'Layaway eligible', 'psf-search' ), $f['layaway'] );
					?>
				</fieldset>

				<?php
				$this->facet( 'brand', 'psf_brand', __( 'Brand', 'psf-search' ), $topBrands, $f['brand'], $selectedBrand ? $selectedBrand->name : '', __( 'Search all brands…', 'psf-search' ), __( 'Browse all brands', 'psf-search' ), true );
				$this->facet( 'caliber', 'psf_caliber', __( 'Caliber / Gauge', 'psf-search' ), $calibers, $f['caliber'], self::termName( Filters::CALIBER_TAXONOMY, $f['caliber'] ), __( 'Search calibers…', 'psf-search' ), __( 'Browse all calibers', 'psf-search' ), $f['caliber'] !== '' );
				$this->facet( 'action', 'psf_action', __( 'Action', 'psf-search' ), $actions, $f['action'], self::termName( Filters::ACTION_TAXONOMY, $f['action'] ), __( 'Search actions…', 'psf-search' ), __( 'Browse all actions', 'psf-search' ), $f['action'] !== '' );
				?>

				<fieldset class="psf-search-filters__group">
					<legend class="psf-search-filters__label"><?php esc_html_e( 'Price', 'psf-search' ); ?></legend>
					<div class="psf-search-filters__price">
						<label><span>$</span><input type="number" min="0" step="1" inputmode="numeric" name="min_price" value="<?php echo $f['min'] > 0 ? esc_attr( (string) (int) $f['min'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Min', 'psf-search' ); ?>" aria-label="<?php esc_attr_e( 'Minimum price', 'psf-search' ); ?>"></label>
						<span class="psf-search-filters__to"><?php esc_html_e( 'to', 'psf-search' ); ?></span>
						<label><span>$</span><input type="number" min="0" step="1" inputmode="numeric" name="max_price" value="<?php echo $f['max'] > 0 ? esc_attr( (string) (int) $f['max'] ) : ''; ?>" placeholder="<?php esc_attr_e( 'Max', 'psf-search' ); ?>" aria-label="<?php esc_attr_e( 'Maximum price', 'psf-search' ); ?>"></label>
					</div>
					<div class="psf-search-filters__presets">
						<?php
						foreach ( self::PRICE_PRESETS as $range => $label ) :
							[ $min, $max ] = array_map( 'intval', explode( '-', $range ) );
							$active        = (int) $f['min'] === $min && (int) $f['max'] === $max && ( $min || $max );
							$url           = Filters::url(
								[
									'min_price'        => $min ? $min : null,
									'max_price'        => $max ? $max : null,
									'psf_price_preset' => null,
								]
							);
							?>
							<a class="psf-search-filters__preset js-psf-search-filters-link<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
						<?php endforeach; ?>
					</div>
				</fieldset>

				<div class="psf-search-filters__footer">
					<a class="psf-search-filters__reset js-psf-search-filters-link" href="<?php echo esc_url( $resetUrl ); ?>"><?php esc_html_e( 'Reset', 'psf-search' ); ?></a>
					<button type="button" class="psf-search-filters__show js-psf-search-filters-close"><?php esc_html_e( 'Show results', 'psf-search' ); ?></button>
				</div>

				<noscript><button type="submit" class="psf-search-filters__show"><?php esc_html_e( 'Apply filters', 'psf-search' ); ?></button></noscript>
			</form>
		</aside>
		<?php
	}

	/**
	 * Keep parameters the panel doesn't own (sort, view, search flags) when the form submits.
	 */
	private function renderPreservedParams() {
		$keep = [ 'orderby', 'view', 'post_type', 'psf_search' ];
		foreach ( $keep as $key ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) && $_GET[ $key ] !== '' ) {
				printf( '<input type="hidden" name="%s" value="%s">', esc_attr( $key ), esc_attr( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			}
		}
	}

	private function checkbox( string $name, string $label, bool $checked ) {
		?>
		<label class="psf-search-filters__check">
			<input type="checkbox" name="<?php echo esc_attr( $name ); ?>" value="1" class="js-psf-search-filters-auto" <?php checked( $checked ); ?>>
			<span><?php echo esc_html( $label ); ?></span>
		</label>
		<?php
	}

	/**
	 * Single-choice facet: a short list, a search box and "Browse all" (full list loaded on demand).
	 *
	 * @param array<int, array{s:string,n:string}> $top
	 */
	private function facet( string $facet, string $param, string $label, array $top, string $selected, string $selectedName, string $searchPlaceholder, string $browseLabel, bool $open ) {
		// Make sure the selected option is visible even if it isn't in the short list.
		if ( $selected !== '' && ! in_array( $selected, wp_list_pluck( $top, 's' ), true ) ) {
			array_unshift(
				$top,
				[
					's' => $selected,
					'n' => $selectedName !== '' ? $selectedName : $selected,
				]
			);
		}
		$id = 'psf-search-facet-' . $facet;
		?>
		<fieldset class="psf-search-filters__group psf-search-filters__facet js-psf-search-facet<?php echo $open ? ' is-open' : ''; ?>" data-facet="<?php echo esc_attr( $facet ); ?>" data-param="<?php echo esc_attr( $param ); ?>" data-current="<?php echo esc_attr( $selected ); ?>">
			<legend class="psf-search-filters__label">
				<button type="button" class="psf-search-filters__toggle js-psf-search-facet-toggle" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $id ); ?>">
					<span><?php echo esc_html( $label ); ?></span>
					<?php if ( $selectedName !== '' ) : ?>
						<em class="psf-search-filters__current"><?php echo esc_html( $selectedName ); ?></em>
					<?php endif; ?>
					<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6"></path></svg>
				</button>
			</legend>
			<div id="<?php echo esc_attr( $id ); ?>" class="psf-search-filters__facet-body">
				<div class="psf-search-filters__find">
					<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg>
					<input type="search" class="js-psf-search-facet-find" placeholder="<?php echo esc_attr( $searchPlaceholder ); ?>" aria-label="<?php echo esc_attr( $searchPlaceholder ); ?>" autocomplete="off">
				</div>
				<ul class="psf-search-filters__options js-psf-search-facet-options">
					<?php foreach ( $top as $option ) : ?>
						<li><?php $this->option( $param, $option['s'], $option['n'], $selected === $option['s'] ); ?></li>
					<?php endforeach; ?>
				</ul>
				<button type="button" class="psf-search-filters__more js-psf-search-facet-more"><?php echo esc_html( '+ ' . $browseLabel ); ?></button>
			</div>
		</fieldset>
		<?php
	}

	private function option( string $param, string $slug, string $name, bool $checked ) {
		?>
		<label class="psf-search-filters__check">
			<input type="radio" name="<?php echo esc_attr( $param ); ?>" value="<?php echo esc_attr( $slug ); ?>" class="js-psf-search-filters-auto" <?php checked( $checked ); ?>>
			<span><?php echo esc_html( $name ); ?></span>
		</label>
		<?php
	}

	private static function termName( string $taxonomy, string $slug ): string {
		if ( $slug === '' ) {
			return '';
		}

		$term = get_term_by( 'slug', $slug, $taxonomy );

		return $term && ! is_wp_error( $term ) ? html_entity_decode( $term->name, ENT_QUOTES ) : $slug;
	}
}
