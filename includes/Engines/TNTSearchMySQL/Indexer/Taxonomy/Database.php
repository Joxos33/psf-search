<?php

namespace PsfSearch\Engines\TNTSearchMySQL\Indexer\Taxonomy;

use PsfSearch\Engines\TNTSearchMySQL\Config;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Utils;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\WPDBException;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\WPDB;
use PsfSearch\Engines\TNTSearchMySQL\Support\Cache;
use PsfSearch\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database {
	/**
	 * Add table names to the $wpdb object
	 *
	 * @return void
	 */
	public static function registerTables() {
		global $wpdb;

		$wpdb->psf_search_tax_index = $wpdb->prefix . Config::READABLE_TAX_INDEX;
		if ( Helpers::isTableExists( $wpdb->psf_search_tax_index ) ) {
			$wpdb->tables[] = Config::READABLE_TAX_INDEX;
		}
	}

	/**
	 * Install DB table
	 *
	 * @return void
	 * @throws WPDBException
	 */
	private static function install( $indexRoleSuffix = '' ) {
		global $wpdb;

		$collate = Utils::getCollate( 'taxonomy/main' );

		/**
		 * We use 'id' column because 'term_id' because 'term_id' is not always unique.
		 * This happens, for example, with the TranslatePress plugin, when records of different
		 * languages have the same 'term_id'.
		 */
		$tableName = $wpdb->psf_search_tax_index . $indexRoleSuffix;
		$table     = "CREATE TABLE $tableName (
			id              BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			term_id         BIGINT(20) UNSIGNED NOT NULL,
			term_name       TEXT NOT NULL,
			term_link       TEXT NOT NULL,
			image           TEXT NOT NULL,
			breadcrumbs     TEXT NOT NULL,
			total_products  INT NOT NULL,
			meta            LONGTEXT NOT NULL,
			taxonomy        VARCHAR(50) NOT NULL,
			lang            VARCHAR(20) NOT NULL,
			PRIMARY KEY    (id)
		) ENGINE=InnoDB ROW_FORMAT=DYNAMIC $collate;";

		WPDB::get_instance()->query( $table );

		WPDB::get_instance()->query( "CREATE INDEX main_term_id ON $tableName(term_id);" );
		WPDB::get_instance()->query( "CREATE INDEX main_taxonomy ON $tableName(taxonomy);" );
		WPDB::get_instance()->query( "CREATE INDEX main_lang ON $tableName(lang);" );
	}

	/**
	 * Create database structure from the scratch
	 *
	 * @return void
	 * @throws WPDBException
	 */
	public static function create( $indexRoleSuffix = '' ) {
		self::install( $indexRoleSuffix );
	}

	/**
	 * Remove DB table
	 *
	 * @return void
	 */
	public static function remove( $indexRoleSuffix = '' ) {
		global $wpdb;

		$wpdb->hide_errors();

		$tableName = $wpdb->psf_search_tax_index . $indexRoleSuffix;

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DROP TABLE IF EXISTS $tableName" );

		Cache::delete( 'table_exists', 'database' );
	}
}
