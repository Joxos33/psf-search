<?php

namespace PsfSearch\Engines\TNTSearchMySQL\Indexer\Taxonomy;

use PsfSearch\Engines\TNTSearchMySQL\Config;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Builder;
use PsfSearch\Helpers;
use PsfSearch\Multilingual;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Request {

	public static function handle() {

		Builder::addInfo( 'start_taxonomies_ts', time() );
		Builder::log( '[Taxonomy index] Building...' );

		$activeTaxonomies = PSFS()->tntsearchMySql->taxonomies->getActiveTaxonomies( 'search_direct' );
		foreach ( $activeTaxonomies as $taxonomy ) {
			self::buildForTaxonomy( $taxonomy );
		}

		if ( Config::isIndexerMode( 'direct' ) ) {
			PSFS()->tntsearchMySql->asynchBuildIndexT->complete();
		} else {
			PSFS()->tntsearchMySql->asynchBuildIndexT->save()->maybe_dispatch();
		}
	}

	private static function buildForTaxonomy( $taxonomy ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			return false;
		}

		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		];
		$args = apply_filters( 'psfs/search/' . $taxonomy . '/args', $args );

		$langsWithoutDefault = [];
		if ( Multilingual::isMultilingual() ) {
			$terms               = Multilingual::getTermsInAllLangs( $taxonomy );
			$langsWithoutDefault = array_values( array_diff( Multilingual::getLanguages(), [ Multilingual::getDefaultLanguage() ] ) );
		} else {
			$terms = get_terms( apply_filters( 'psfs/search/' . $taxonomy . '/args', $args ) );
		}

		if ( empty( $terms ) ) {
			return true;
		}

		// Exclude terms matched by user choice from "Exclude/include products" option
		$filterMode = PSFS()->settings->getOption( 'filter_products_mode', 'exclude' );
		$rules      = Helpers::getFilterProductsRules__premium_only();
		if ( $filterMode === 'exclude' && ! empty( $rules ) && isset( $rules[ $taxonomy ] ) ) {
			$excluded = array_map( 'intval', $rules[ $taxonomy ] );

			// Get excluded term ids from other languages
			if ( ! empty( $langsWithoutDefault ) ) {
				foreach ( $langsWithoutDefault as $lang ) {
					foreach ( $excluded as $termID ) {
						$termTranslated = Multilingual::getTerm( $termID, $taxonomy, $lang );
						if ( ! empty( $termTranslated ) ) {
							$excluded[] = $termTranslated->term_id;
						}
					}
				}
			}

			$terms = array_filter(
				$terms,
				function ( $term ) use ( $excluded ) {
					return ! in_array( $term->term_id, $excluded );
				}
			);
		}

		$terms = array_map(
			function ( $term ) {
				return [
					'term_id'  => $term->term_id,
					'taxonomy' => $term->taxonomy,
				];
			},
			$terms
		);

		$termsSet      = [];
		$termsSetCount = apply_filters( 'psfs/indexer/taxonomy-set-items-count', Builder::TAXONOMY_SET_ITEMS_COUNT );
		$termsSetCount = apply_filters( 'psfs/indexer/taxonomy_set_items_count', $termsSetCount );

		if ( Config::isIndexerMode( 'direct' ) ) {
			PSFS()->tntsearchMySql->asynchBuildIndexT->task( $terms );
		} else {
			$i = 0;
			foreach ( $terms as $term ) {
				$termsSet[] = $term;

				if ( count( $termsSet ) === $termsSetCount || $i + 1 === count( $terms ) ) {
					PSFS()->tntsearchMySql->asynchBuildIndexT->push_to_queue( $termsSet );
					$termsSet = [];
				}

				$i ++;
			}
		}

		$totalTerms = absint( Builder::getInfo( 'total_terms_for_indexing', Config::getIndexRole() ) );
		Builder::addInfo( 'total_terms_for_indexing', $totalTerms + count( $terms ) );

		return true;
	}

}
