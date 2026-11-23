<?php

namespace PsfSearch\Shop;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * One unified Brand facet across every brand-like taxonomy (importers store
 * manufacturers in different places). Ported from PSF Styling 1.6.3.
 */
class Brands {

	const CACHE_KEY = 'psf_search_brand_terms_v1';

	/**
	 * @return string[]
	 */
	public static function taxonomies(): array {
		$candidates = apply_filters( 'psf_search/shop/brand_taxonomies', [ 'product_brand', 'pwb-brand', 'yith_product_brand', 'brand', 'pa_brand', 'pa_manufacturer' ] );

		$out = [];
		foreach ( array_unique( array_filter( array_map( 'sanitize_key', (array) $candidates ) ) ) as $taxonomy ) {
			if ( taxonomy_exists( $taxonomy ) ) {
				$out[] = $taxonomy;
			}
		}

		return $out;
	}

	/**
	 * A-Z list of brands. Same-named terms from different taxonomies are merged under
	 * one canonical slug that remembers its source terms.
	 *
	 * @return object[] { name, slug, count, sources: array<taxonomy, slug[]> }
	 */
	public static function terms(): array {
		$cached = get_transient( self::CACHE_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$merged = [];
		foreach ( self::taxonomies() as $taxonomy ) {
			$terms = get_terms(
				[
					'taxonomy'   => $taxonomy,
					'hide_empty' => true,
					'number'     => 0,
				]
			);
			if ( is_wp_error( $terms ) ) {
				continue;
			}

			foreach ( $terms as $term ) {
				$canonical = sanitize_title( $term->name );
				$canonical = $canonical !== '' ? $canonical : sanitize_title( $term->slug );
				if ( $canonical === '' ) {
					continue;
				}

				if ( ! isset( $merged[ $canonical ] ) ) {
					$merged[ $canonical ] = (object) [
						'name'    => $term->name,
						'slug'    => $canonical,
						'count'   => 0,
						'sources' => [],
					];
				}

				$merged[ $canonical ]->count                   += (int) $term->count;
				$merged[ $canonical ]->sources[ $taxonomy ][] = $term->slug;
			}
		}

		$terms = array_values( $merged );
		usort(
			$terms,
			function ( $a, $b ) {
				return strcasecmp( $a->name, $b->name );
			}
		);

		set_transient( self::CACHE_KEY, $terms, HOUR_IN_SECONDS );

		return $terms;
	}

	/**
	 * Most-stocked brands, for the short list shown before "Browse all brands".
	 *
	 * @return object[]
	 */
	public static function top( int $limit ): array {
		$terms = self::terms();
		usort(
			$terms,
			function ( $a, $b ) {
				return $b->count <=> $a->count;
			}
		);

		return array_slice( $terms, 0, $limit );
	}

	/**
	 * Source terms for a unified brand slug (or a legacy source-term slug from older URLs).
	 *
	 * @return array<string, string[]>
	 */
	public static function termMatches( string $brand ): array {
		$needle = sanitize_title( $brand );
		if ( $needle === '' ) {
			return [];
		}

		foreach ( self::terms() as $term ) {
			if ( $term->slug === $needle ) {
				return (array) $term->sources;
			}
		}

		$matches = [];
		foreach ( self::taxonomies() as $taxonomy ) {
			$term = get_term_by( 'slug', $needle, $taxonomy );
			if ( $term && ! is_wp_error( $term ) ) {
				$matches[ $taxonomy ] = [ $term->slug ];
			}
		}

		return $matches;
	}

	/**
	 * Display item for a brand slug (unified or legacy).
	 */
	public static function find( string $brand ) {
		$needle = sanitize_title( $brand );
		foreach ( self::terms() as $term ) {
			if ( $term->slug === $needle ) {
				return $term;
			}
			foreach ( (array) $term->sources as $slugs ) {
				if ( in_array( $needle, array_map( 'sanitize_title', (array) $slugs ), true ) ) {
					return $term;
				}
			}
		}

		return null;
	}
}
