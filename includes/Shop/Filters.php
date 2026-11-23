<?php

namespace PsfSearch\Shop;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shop filters: category, brand, caliber/gauge, action, availability and price on the
 * shop page, product archives and product search results.
 *
 * Ported from PSF Styling 1.6.3 (PSF_Shop_Query / PSF_Shop_Helpers) with the same URL
 * parameters, so existing links keep working. Text search is not handled here: the
 * PSF Search engine takes the phrase (?s= or ?psf_q=) and the results are intersected
 * with these filters (see SearchPage / Helpers::isSearchQuery).
 */
class Filters {

	const PARAMS = [ 'psf_cat', 'psf_brand', 'psf_caliber', 'psf_action', 'in_stock', 'on_sale', 'layaway', 'min_price', 'max_price', 'psf_price_preset' ];

	const CALIBER_TAXONOMY = 'pa_caliber-gauge';
	const ACTION_TAXONOMY  = 'pa_action';

	public function __construct() {
		add_action( 'woocommerce_product_query', [ $this, 'applyToQuery' ], 20 );
	}

	/**
	 * Shop page, product category/tag/brand/attribute archive.
	 */
	public static function isShopArchive(): bool {
		if ( is_admin() ) {
			return false;
		}

		return ( function_exists( 'is_shop' ) && is_shop() ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() );
	}

	/**
	 * Product search results (not a blog search).
	 */
	public static function isProductSearch(): bool {
		if ( is_admin() || ! is_search() ) {
			return false;
		}

		$postType = get_query_var( 'post_type' );

		return $postType === 'product' || ( is_array( $postType ) && in_array( 'product', $postType, true ) ) || isset( $_GET['psf_search'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	public static function isShopContext(): bool {
		return self::isShopArchive() || self::isProductSearch();
	}

	/**
	 * Current filter state from the URL.
	 *
	 * @return array{q:string,cat:string,brand:string,caliber:string,action:string,instock:bool,on_sale:bool,layaway:bool,min:float,max:float}
	 */
	public static function state(): array {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$get = function ( $key ) {
			return isset( $_GET[ $key ] ) && is_string( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) : '';
		};

		$min = (float) $get( 'min_price' );
		$max = (float) $get( 'max_price' );

		if ( ! $min && ! $max && $get( 'psf_price_preset' ) !== '' ) {
			$parts = explode( '-', $get( 'psf_price_preset' ) );
			$min   = isset( $parts[0] ) ? (float) $parts[0] : 0;
			$max   = isset( $parts[1] ) ? (float) $parts[1] : 0;
		}

		$q = $get( 's' );
		if ( $q === '' ) {
			$q = $get( 'psf_q' );
		}

		return [
			'q'       => $q,
			'cat'     => sanitize_title( $get( 'psf_cat' ) ),
			'brand'   => sanitize_title( $get( 'psf_brand' ) ),
			'caliber' => sanitize_title( $get( 'psf_caliber' ) ),
			'action'  => sanitize_title( $get( 'psf_action' ) ),
			'instock' => ! empty( $_GET['in_stock'] ),
			'on_sale' => ! empty( $_GET['on_sale'] ),
			'layaway' => ! empty( $_GET['layaway'] ),
			'min'     => max( 0, $min ),
			'max'     => max( 0, $max ),
		];
		// phpcs:enable
	}

	/**
	 * Apply the filters to the main product query.
	 *
	 * @param \WP_Query $q
	 */
	public function applyToQuery( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! self::isShopContext() ) {
			return;
		}

		$f    = self::state();
		$meta = array_filter( (array) $q->get( 'meta_query' ) );
		$tax  = array_filter( (array) $q->get( 'tax_query' ) );

		self::addTaxFilter( $tax, 'product_cat', $f['cat'], true );
		self::addBrandFilter( $tax, $f['brand'] );
		self::addTaxFilter( $tax, self::CALIBER_TAXONOMY, $f['caliber'] );
		self::addTaxFilter( $tax, self::ACTION_TAXONOMY, $f['action'] );

		if ( $f['instock'] ) {
			$meta[] = [
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '=',
			];
		}

		if ( $f['on_sale'] ) {
			$saleIds  = array_filter( array_map( 'absint', (array) wc_get_product_ids_on_sale() ) );
			// Keep an existing restriction, including PSF Styling's deep-page guard ([0]).
			$existing = array_map( 'absint', (array) $q->get( 'post__in' ) );
			if ( $existing ) {
				$saleIds = array_values( array_intersect( $existing, $saleIds ) );
			}
			$q->set( 'post__in', $saleIds ? $saleIds : [ 0 ] );
		}

		if ( $f['layaway'] ) {
			self::addLayawayFilter( $tax, $meta );
		}

		if ( $f['min'] > 0 || $f['max'] > 0 ) {
			$meta[] = [
				'key'     => '_price',
				'value'   => [ $f['min'] > 0 ? $f['min'] : 0, $f['max'] > 0 ? $f['max'] : 99999999 ],
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL(10,2)',
			];
		}

		if ( $meta ) {
			$q->set( 'meta_query', $meta );
		}
		if ( $tax ) {
			$q->set( 'tax_query', $tax );
		}
	}

	private static function addTaxFilter( array &$tax, string $taxonomy, string $slug, bool $includeChildren = false ) {
		if ( $slug === '' || ! taxonomy_exists( $taxonomy ) ) {
			return;
		}

		$tax[] = [
			'taxonomy'         => $taxonomy,
			'field'            => 'slug',
			'terms'            => $slug,
			'include_children' => $includeChildren,
		];
	}

	/**
	 * One logical Brand selection across every brand-like taxonomy (OR-ed together).
	 */
	private static function addBrandFilter( array &$tax, string $brand ) {
		if ( $brand === '' ) {
			return;
		}

		$clauses = [];
		foreach ( Brands::termMatches( $brand ) as $taxonomy => $slugs ) {
			if ( taxonomy_exists( $taxonomy ) && $slugs ) {
				$clauses[] = [
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => array_values( array_unique( array_map( 'sanitize_title', (array) $slugs ) ) ),
				];
			}
		}

		if ( count( $clauses ) === 1 ) {
			$tax[] = $clauses[0];
		} elseif ( count( $clauses ) > 1 ) {
			$tax[] = array_merge( [ 'relation' => 'OR' ], $clauses );
		}
	}

	/**
	 * Layaway eligibility, using PSF Styling's layaway settings when present.
	 * "Auto" (PSF's setting) means: priced and in stock.
	 */
	private static function addLayawayFilter( array &$tax, array &$meta ) {
		$setting = function ( $key, $default ) {
			return class_exists( 'PSF_Shop_Settings' ) ? \PSF_Shop_Settings::get( $key, $default ) : $default;
		};

		$source = $setting( 'layaway_source', 'auto' );
		$slug   = $setting( 'layaway_tag', 'layaway-eligible' );

		if ( $source === 'meta' ) {
			$meta[] = [
				'key'     => $setting( 'layaway_meta_key', '_psf_layaway_eligible' ),
				'value'   => [ 'yes', '1', 'true', 'on' ],
				'compare' => 'IN',
			];
		} elseif ( $source === 'category' ) {
			self::addTaxFilter( $tax, 'product_cat', (string) $slug, true );
		} elseif ( $source === 'tag' ) {
			self::addTaxFilter( $tax, 'product_tag', (string) $slug );
		} else {
			$meta[] = [
				'key'     => '_price',
				'value'   => '',
				'compare' => '!=',
			];
			$meta[] = [
				'key'     => '_stock_status',
				'value'   => 'instock',
				'compare' => '=',
			];
		}
	}

	/**
	 * Base URL of the current listing (shop page, archive, or search).
	 */
	public static function baseUrl(): string {
		if ( self::isProductSearch() ) {
			return home_url( '/' );
		}

		if ( function_exists( 'is_shop' ) && is_shop() ) {
			$id = wc_get_page_id( 'shop' );
			if ( $id > 0 ) {
				return get_permalink( $id );
			}
		}

		$term = get_queried_object();
		if ( $term instanceof \WP_Term ) {
			$link = get_term_link( $term );
			if ( ! is_wp_error( $link ) ) {
				return $link;
			}
		}

		return home_url( '/shop/' );
	}

	/**
	 * URL of the current listing with some parameters changed (null/'' removes them).
	 */
	public static function url( array $args = [] ): string {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current = array_map( 'wp_unslash', (array) $_GET );
		unset( $current['paged'], $current['product-page'] );

		$merged = array_merge( $current, $args );
		foreach ( $merged as $key => $value ) {
			if ( $value === null || $value === '' ) {
				unset( $merged[ $key ] );
			}
		}

		return $merged ? add_query_arg( array_map( 'rawurlencode_deep', $merged ), self::baseUrl() ) : self::baseUrl();
	}
}
