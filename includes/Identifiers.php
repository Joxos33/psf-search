<?php

namespace PsfSearch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Index the product identifier fields PSF Styling's old search looked in
 * (UPC, MPN, stock ID, vendor SKU, part number…), so those still find products.
 *
 * Each field costs one subquery per product during indexing, so only keys that
 * actually exist on this site are added (checked once a day).
 */
class Identifiers {

	const TRANSIENT = 'psf_search_identifier_keys_v1';

	const CANDIDATES = [
		'stockid',
		'upc',
		'gtin',
		'ean',
		'mpn',
		'manufacturer_part_number',
		'manufacturer-part-number',
		'model',
		'vendor_sku',
		'item_number',
		'itemnumber',
		'product_code',
		'part_number',
	];

	public function __construct() {
		add_filter( 'psf_search/indexer/source_query/search_in_custom_fields', [ $this, 'addFields' ] );
	}

	/**
	 * @param string[] $fields
	 *
	 * @return string[]
	 */
	public function addFields( $fields ) {
		$fields = is_array( $fields ) ? $fields : [];

		return array_values( array_unique( array_merge( $fields, self::existingKeys() ) ) );
	}

	/**
	 * Candidate keys (with and without a leading underscore) present in postmeta.
	 *
	 * @return string[]
	 */
	public static function existingKeys(): array {
		$cached = get_transient( self::TRANSIENT );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;

		$keys = [];
		foreach ( apply_filters( 'psf_search/identifiers/candidates', self::CANDIDATES ) as $key ) {
			$keys[] = $key;
			$keys[] = '_' . $key;
		}

		$placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$found = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT meta_key FROM $wpdb->postmeta WHERE meta_key IN ($placeholders)", $keys ) );
		$found = is_array( $found ) ? array_values( $found ) : [];

		set_transient( self::TRANSIENT, $found, DAY_IN_SECONDS );

		return $found;
	}
}
