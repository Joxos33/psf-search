<?php

namespace PsfSearch\Engines\TNTSearchMySQL\Indexer;

use PsfSearch\Engines\TNTSearchMySQL\Config;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Readable\Indexer as IndexerR;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Searchable\Indexer as IndexerS;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Taxonomy\Indexer as IndexerTax;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Searchable\Database as DatabaseS;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Readable\Database as DatabaseR;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Taxonomy\Database as DatabaseT;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Taxonomy\Request as RequestT;
use PsfSearch\Engines\TNTSearchMySQL\Libs\Mutex\WpdbMysqlMutex;
use PsfSearch\Engines\TNTSearchMySQL\Support\Cache;
use PsfSearch\Helpers;
use PsfSearch\Multilingual;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Builder {

	const LAST_BUILD_OPTION_KEY              = 'psf_search_indexer_last_build';
	const DETAILS_DISPLAY_KEY                = 'psf_search_indexer_details_display';
	const INDEXING_PREPARE_PROCESS_EXIST_KEY = 'psf_search_indexer_prepare_process_exist';
	const INDEXER_DEBUG_TRANSIENT_KEY        = 'psf_search_indexer_debug';
	const INDEXER_DEBUG_SCOPE_TRANSIENT_KEY  = 'psf_search_indexer_debug_scope';
	// Small sets so each background request saves progress often; these are the values
	// PSF's FiboSearch install was tuned to (Flatsome Child functions.php) on this host.
	const SEARCHABLE_SET_ITEMS_COUNT         = 10;
	const READABLE_SET_ITEMS_COUNT           = 5;
	const TAXONOMY_SET_ITEMS_COUNT           = 10;
	// Items per committed chunk when building in one process (WP-CLI "direct" mode).
	const DIRECT_MODE_CHUNK_SIZE             = 250;

	/**
	 * @var WpdbMysqlMutex
	 */
	private static $addInfoMutex;

	public static $indexerDebugScopes = [
		'all',
		'readable',
		'searchable',
		'taxonomy',
		'bg-process',
	];

	/**
	 * Structure of indexer data
	 *
	 * @return array
	 */
	public static function getIndexInfoStruct() {
		return [
			'build_id'                        => uniqid(),
			'db'                              => 'MySQL',
			'status'                          => '',
			'start_ts'                        => time(),
			'start_searchable_ts'             => 0,
			'start_readable_ts'               => 0,
			'start_taxonomies_ts'             => 0,
			'end_ts'                          => 0,
			'end_searchable_ts'               => 0,
			'end_readable_ts'                 => 0,
			'end_taxonomies_ts'               => 0,
			'last_action_ts'                  => time(),
			'readable_processed'              => 0,
			'searchable_processed'            => 0,
			'terms_processed'                 => 0,
			'total_terms_for_indexing'        => 0,
			'total_products_for_indexing'     => 0,
			'total_non_products_for_indexing' => 0,
			'logs'                            => [],
			'non_critical_errors'             => [],
			'languages'                       => [],
			'plugin_version'                  => '',
			'stemmer'                         => '',
			'variation_support_modes'         => '',
			'active_post_types'               => [],
		];
	}

	/**
	 * @return void
	 */
	private static function createInfoStruct() {
		foreach ( self::getIndexInfoStruct() as $key => $value ) {
			if ( $key === 'logs' ) {
				Storage::update( 'indexer_last_build_logs' . Config::getIndexRoleSuffix(), '[]' );
				continue;
			}
			update_option( self::LAST_BUILD_OPTION_KEY . '_' . $key . Config::getIndexRoleSuffix(), $value, 'no' );
		}
	}

	/**
	 * Add specific info about the last index build
	 *
	 * @param string $key
	 * @param mixed $value
	 * @param string $indexRole Index role
	 *
	 * @return bool
	 */
	public static function addInfo( $key, $value, $indexRole = '' ) {
		if ( $key === 'logs' ) {
			return false;
		}

		if ( self::$addInfoMutex === null ) {
			self::$addInfoMutex = new WpdbMysqlMutex( 'builder_add_info_' . $key, 5 );
		}

		if ( ! self::$addInfoMutex->acquire() ) {
			self::log( "[Builder] Unable to get lock when adding indexer information with key: $key", 'warning', 'file' );

			return false;
		}

		// If empty, get "writable" index.
		if ( empty( $indexRole ) ) {
			$indexRole = Config::getIndexRole();
		}
		$indexRoleSuffix = $indexRole === 'main' ? '' : '_tmp';

		$added = update_option( self::LAST_BUILD_OPTION_KEY . '_' . $key . $indexRoleSuffix, $value );
		update_option( self::LAST_BUILD_OPTION_KEY . '_last_action_ts' . $indexRoleSuffix, time() );
		Cache::set( $key . '_' . $indexRole, $value, 'indexer' );

		self::$addInfoMutex->release();

		return $added;
	}

	public static function getInfo( $key, $indexRole = 'main' ) {
		global $wpdb;

		if ( ! Helpers::isIndexing__premium_only() ) {
			$value = Cache::get( $key . '_' . $indexRole, 'indexer' );
			if ( $value !== false ) {
				return $value;
			}
		}

		$value           = '';
		$indexRoleSuffix = $indexRole === 'main' ? '' : '_tmp';

		if ( $key === 'logs' ) {
			$storageKey = 'indexer_last_build_logs' . $indexRoleSuffix;
			$logsRaw    = Storage::get( $storageKey, true );
			$value      = json_decode( $logsRaw, true );
			if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $value ) ) {
				$value = [];
			}
		} else {
			$infoKey  = self::LAST_BUILD_OPTION_KEY . '_' . $key . $indexRoleSuffix;
			$rawValue = $wpdb->get_var( $wpdb->prepare( "SELECT SQL_NO_CACHE option_value FROM $wpdb->options WHERE option_name = %s", $infoKey ) );

			if ( ! empty( $rawValue ) ) {
				$value = maybe_unserialize( $rawValue );
			}
		}

		Cache::set( $key . '_' . $indexRole, $value, 'indexer' );

		return $value;
	}

	/**
	 * Is indexer debug enabled
	 *
	 * @return bool
	 */
	public static function isDebug() {
		if ( defined( 'PSF_SEARCH_INDEXER_DEBUG' ) ) {
			return (bool) PSF_SEARCH_INDEXER_DEBUG;
		}

		return (bool) get_transient( self::INDEXER_DEBUG_TRANSIENT_KEY );
	}

	/**
	 * Get indexer debug scope
	 *
	 * @return array
	 */
	public static function getDebugScopes() {
		if ( defined( 'PSF_SEARCH_INDEXER_DEBUG_SCOPE' ) ) {
			$scope = explode( ',', PSF_SEARCH_INDEXER_DEBUG_SCOPE );

			return array_map( 'trim', $scope );
		}

		$scope = get_transient( self::INDEXER_DEBUG_SCOPE_TRANSIENT_KEY );

		return is_array( $scope ) ? $scope : [ 'all' ];
	}

	/**
	 * Check if scope of debug is enabled
	 *
	 * @param string $scope
	 *
	 * @return bool
	 */
	public static function isDebugScopeActive( $scope ) {
		if ( $scope === 'all' || in_array( 'all', self::getDebugScopes() ) ) {
			return true;
		}

		return in_array( $scope, self::getDebugScopes() );
	}

	/**
	 * Log indexer message
	 *
	 * @param string $message
	 * @param string $level One of the following:
	 *     'emergency': The indexer has stopped due to a fatal error.
	 *     'warning': PHP warnings.
	 *     'notice': PHP notices.
	 *     'info': Informational messages about indexer.
	 *     'debug': Debug-level messages.
	 * @param string $destination Destination. Choices: 'db', 'file' or 'both'
	 * @param string $scope Scope of log. Choices: look at self::$indexerDebugScopes
	 * @param string $source Source of log
	 *
	 * @return void
	 */
	public static function log( $message, $level = 'info', $destination = 'both', $scope = 'all', $source = '' ) {
		if ( defined( 'PSF_SEARCH_DISABLE_INDEXER_LOGS' ) && PSF_SEARCH_DISABLE_INDEXER_LOGS ) {
			return;
		}

		$context = [];
		if ( $source === 'update_variation' || $source === 'single_product_update' ) {
			$context = [ 'source' => Logger::UPDATER_SOURCE ];
		}

		if ( $destination === 'file' || $destination === 'both' ) {
			Logger::log( $message, $level, $scope, $context );
		}

		if ( $source !== 'update_variation' ) {
			if ( $destination === 'db' || $destination === 'both' ) {
				$indexRoleSuffix = Config::getIndexRoleSuffix();
				$storageKey      = 'indexer_last_build_logs' . $indexRoleSuffix;

				$logsRaw = Storage::get( $storageKey, true );
				if ( $logsRaw === '' ) {
					$logsRaw = '[]';
				}
				$logs = json_decode( $logsRaw, true );
				if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $logs ) ) {
					$logs = [];
				}

				$logs[] = [
					//phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested
					'time'    => current_time( 'timestamp' ),
					'error'   => in_array( $level, [ 'emergency', 'warning', 'notice' ] ),
					'message' => $message,
				];

				Storage::update( $storageKey, wp_json_encode( $logs ) );
			}
		}
	}

	/**
	 * Get all logs
	 *
	 * @return array
	 */
	public static function getLogs( $indexRole = '' ) {
		$logs = self::getInfo( 'logs', $indexRole );

		return empty( $logs ) ? [] : $logs;
	}

	/**
	 * Get all plugin's option and delete cache
	 *
	 * @return void
	 */
	public static function flushOptionsCache() {
		foreach ( Helpers::getAllOptionNames() as $optionKey ) {
			wp_cache_delete( $optionKey, 'options' );
		}
	}

	public static function buildIndex( $async = true ) {

		self::flushOptionsCache();

		$result = self::prepareBuildIndex();

		if ( ! $result ) {
			return;
		}

		if ( Config::isIndexerMode( 'direct' ) ) {
			$async = false;
		}

		if ( $async ) {
			PSF_SEARCH()->tntsearchMySql->asyncRebuildIndex->data( [ 'force' => true ] )->schedule_event()->dispatch();
		} else {
			self::buildIndexProcess();
		}
	}

	public static function prepareBuildIndex() {
		if ( ! defined( 'PSF_SEARCH_PREPARE_BUILD_INDEX' ) ) {
			define( 'PSF_SEARCH_PREPARE_BUILD_INDEX', true );
		}

		if ( Helpers::isLockLocked__premium_only( self::INDEXING_PREPARE_PROCESS_EXIST_KEY, 5 ) ) {
			self::log( 'Indexer already preparing building the index' );

			return false;
		}

		Logger::removeLogs();
		Logger::removeLogs( Logger::UPDATER_SOURCE );

		Helpers::setLock__premium_only( self::INDEXING_PREPARE_PROCESS_EXIST_KEY );

		self::createInfoStruct();

		self::log( sprintf( 'Build ID: %s', self::getInfo( 'build_id', Config::getIndexRole() ) ), 'info', 'file' );
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			self::log( 'Build via: WP-CLI', 'info', 'file' );
		}
		self::log( sprintf( 'Indexer mode: %s', Config::getIndexerMode() ) );
		self::log( sprintf( 'Parallel build: %s', Config::isParallelBuildingEnabled() ? 'yes' : 'no' ) );

		self::cancelBuildIndex( false );

		self::addInfo( 'status', 'preparing' );

		self::wipeActionScheduler();

		delete_option( 'psf_search_variable_products_to_update' );

		self::log( sprintf( 'Plugin version: %s', PSF_SEARCH_VERSION ) );
		self::addInfo( 'plugin_version', PSF_SEARCH_VERSION );

		if ( Multilingual::isMultilingual() ) {
			self::log(
				sprintf(
					'Multilingual: Yes, Provider: %s, Default: %s, Langs: %s',
					Multilingual::getProvider(),
					Multilingual::getDefaultLanguage(),
					implode( ',', Multilingual::getLanguages() )
				)
			);
			self::addInfo( 'languages', Multilingual::getLanguages() );
		}

		if ( self::$addInfoMutex->is_disabled() ) {
			self::log( sprintf( 'Mutex status: %s', 'disabled' ) );
		}

		self::log( 'Indexer prepared for building the index' );
		do_action( 'psf_search/indexer/prepared' );

		return true;
	}

	public static function buildIndexProcess() {
		if ( ! defined( 'PSF_SEARCH_BUILD_INDEX_PROCESS' ) ) {
			define( 'PSF_SEARCH_BUILD_INDEX_PROCESS', true );
		}

		$status = self::getInfo( 'status', Config::getIndexRole() );

		if ( $status === 'building' ) {
			self::log( 'Indexer already running' );

			return;
		} elseif ( $status !== 'preparing' ) {
			self::log( 'Indexer is not prepared for running' );

			return;
		}

		self::addInfo( 'status', 'building' );

		self::log( 'Indexer started building the index' );
		do_action( 'psf_search/indexer/started' );

		$variationSupportModes = Helpers::getVariationSupportModes__premium_only();
		self::addInfo( 'variation_support_modes', $variationSupportModes );
		self::log( sprintf( 'Variation support modes: %s', empty( $variationSupportModes ) ? 'none' : join( ', ', $variationSupportModes ) ), 'info', 'file' );

		// Readable
		try {
			DatabaseR::create( Config::getIndexRoleSuffix() );
		} catch ( \Error $e ) {
			Logger::handleThrowableError( $e, '[Readable index] ' );
		} catch ( \Exception $e ) {
			Logger::handleThrowableError( $e, '[Readable index] ' );
		}
		self::addInfo( 'start_readable_ts', time() );
		self::log( '[Readable index] Building...' );

		// Taxonomies
		if ( self::canBuildTaxonomyIndex() ) {
			try {
				DatabaseT::create( Config::getIndexRoleSuffix() );
			} catch ( \Error $e ) {
				Logger::handleThrowableError( $e, '[Taxonomy index] ' );
			} catch ( \Exception $e ) {
				Logger::handleThrowableError( $e, '[Taxonomy index] ' );
			}
		}

		$toTheQueue = [
			'searchable' => [],
			'readable'   => [],
		];

		// -------------------------------
		// SEARCHABLE AND READABLE INDEX
		// -------------------------------
		try {
			DatabaseS::create( Config::getIndexRoleSuffix() );
		} catch ( \Error $e ) {
			Logger::handleThrowableError( $e, '[Searchable index] ' );
		} catch ( \Exception $e ) {
			Logger::handleThrowableError( $e, '[Searchable index] ' );
		}

		$source      = new SourceQuery( [ 'ids' => true ] );
		$productsIds = $source->getData();

		self::addInfo( 'total_products_for_indexing', count( $productsIds ) );

		self::addInfo( 'start_searchable_ts', time() );
		self::log( '[Searchable index] Building...' );

		// The searchable indexer expects arrays of IDs of the same type.
		$toTheQueue['searchable'][] = $productsIds;
		$toTheQueue['readable']     = $productsIds;

		// --------------------
		// PRODUCT VARIATIONS
		// --------------------
		if ( in_array( 'exact_match', $variationSupportModes ) || in_array( 'as_single_product', $variationSupportModes ) ) {
			/**
			 * Products can be excluded or included from the index, and passing the parent IDs to the VariationsSourceQuery
			 * allows to exclude or include the variations based on the parent product's visibility.
			 */
			$variationSource = new VariationsSourceQuery(
				[
					'ids'       => true,
					'postType'  => 'product_variation',
					'parentIds' => apply_filters( 'psf_search/indexer/variation_parent_ids', $productsIds ),
				]
			);
			$variationsIds   = $variationSource->getData();
			$variationsIds   = apply_filters( 'psf_search/indexer/variation_ids', $variationsIds );

			self::addInfo( 'total_products_for_indexing', count( $productsIds ) + count( $variationsIds ) );

			$toTheQueue['readable'] = array_merge( $toTheQueue['readable'], $variationsIds );

			if ( in_array( 'as_single_product', $variationSupportModes ) ) {
				$toTheQueue['searchable'][] = $variationsIds;
			}
		}

		// -----------------------------------
		// NON-PRODUCTS INDEX - POST TYPES
		// -----------------------------------
		$nonProductsPostTypes = Helpers::getAllowedPostTypes( 'no-products' );
		self::addInfo( 'active_post_types', $nonProductsPostTypes );
		if ( ! empty( $nonProductsPostTypes ) ) {
			$totalNonProducts = 0;
			foreach ( $nonProductsPostTypes as $type ) {
				$npSource = new PostsSourceQuery(
					[
						'ids'      => true,
						'postType' => $type,
					]
				);
				$postIds  = $npSource->getData();

				$totalNonProducts = $totalNonProducts + count( $postIds );

				$toTheQueue['searchable'][] = $postIds;
				$toTheQueue['readable']     = array_merge( $toTheQueue['readable'], $postIds );
			}

			self::addInfo( 'total_non_products_for_indexing', $totalNonProducts );
		}

		// -------------------------------------------
		// PUSH TO QUEUE (for async and sync mode)
		// PROCESS (for direct mode)
		// -------------------------------------------
		if ( Config::isIndexerMode( 'direct' ) ) {
			// Process in chunks, not one set of the whole catalog: each chunk commits its
			// own transaction and records progress (and last_action_ts). With one 50k-item
			// set the build held millions of uncommitted rows, the progress bar never moved,
			// and the stuck-build watchdog cancelled it after 61 minutes.
			foreach ( array_chunk( $toTheQueue['readable'], self::DIRECT_MODE_CHUNK_SIZE ) as $chunk ) {
				PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR->task( $chunk );
			}
			foreach ( $toTheQueue['searchable'] as $ids ) {
				foreach ( array_chunk( $ids, self::DIRECT_MODE_CHUNK_SIZE ) as $chunk ) {
					PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->task( $chunk );
				}
			}
		} else {
			foreach ( $toTheQueue as $indexer => $data ) {
				if ( $indexer === 'searchable' ) {
					foreach ( $data as $subdata ) {
						self::pushToQueue( $subdata, $indexer );
					}
				} else {
					self::pushToQueue( $data, $indexer );
				}
			}
		}

		// ----------------------------------------
		// DISPATCH (for async and sync mode)
		// COMPLETE (for direct mode)
		// ----------------------------------------
		if ( Config::isIndexerMode( 'direct' ) ) {
			// This condition prevents the build completion from starting when an error previously occurred.
			if ( self::getInfo( 'status', Config::getIndexRole() ) === 'building' ) {
				PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR->complete();
				PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->complete();
			}
		} elseif ( Config::isIndexerMode( 'sync' ) ) {
			PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR->save();
			PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->save();

			if ( PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR->get_save_result() && PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->get_save_result() ) {
				PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->maybe_dispatch();
			} else {
				self::log( '[Indexer] [Error code: 006] Failed to save indexer queue to the database.', 'emergency', 'both' );
				self::addInfo( 'status', 'error' );
				self::log( 'Stop building the index. Starting the cancellation process.' );
				self::cancelBuildIndex();
				do_action( 'psf_search/indexer/status/error' );
			}
		} else {
			PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR->save();
			PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->save();

			if (
				PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR->get_save_result()
				&& PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->get_save_result()
			) {
				PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR->maybe_dispatch();
				sleep( 1 );
				PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->maybe_dispatch();
			} else {
				self::log( '[Indexer] [Error code: 006] Failed to save indexer queue to the database.', 'emergency', 'both' );
				self::addInfo( 'status', 'error' );
				self::log( 'Stop building the index. Starting the cancellation process.' );
				self::cancelBuildIndex();
				do_action( 'psf_search/indexer/status/error' );
			}
		}
	}

	/**
	 * Push data to the correct queue
	 *
	 * @TODO SourceQuery should return IDs directly, rather than associative arrays with the key 'ID'.
	 *
	 * @param array $ids
	 * @param string $indexer
	 *
	 * @return void
	 */
	public static function pushToQueue( $ids, $indexer ) {

		$limit  = 50;
		$object = null;
		$set    = [];

		switch ( $indexer ) {
			case 'searchable':
				$object = PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS;
				$limit  = apply_filters( 'psf_search/indexer/searchable_set_items_count', self::SEARCHABLE_SET_ITEMS_COUNT );
				break;
			case 'readable':
				$object = PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR;
				$limit  = apply_filters( 'psf_search/indexer/readable_set_items_count', self::READABLE_SET_ITEMS_COUNT );
				break;
		}

		// Stop early
		if ( empty( $ids ) || ! is_object( $object ) ) {
			return;
		}

		$i = 0;
		foreach ( $ids as $id ) {
			$set[] = $id;

			if ( count( $set ) === $limit || $i + 1 === count( $ids ) ) {
				$object->push_to_queue( $set );
				$set = [];
			}

			$i ++;
		}
	}

	/**
	 * Stops build index and wipes all processes and data
	 *
	 * @param bool $clearInfo clear info (start time of processes)
	 *
	 * @return void
	 */
	public static function cancelBuildIndex( $clearInfo = true ) {
		PSF_SEARCH()->tntsearchMySql->asynchBuildIndexR->cancel_process();
		PSF_SEARCH()->tntsearchMySql->asynchBuildIndexS->cancel_process();
		PSF_SEARCH()->tntsearchMySql->asynchBuildIndexT->cancel_process();

		self::wipeIndexTables();

		Helpers::removeBatchOptions__premium_only();

		if ( $clearInfo ) {
			self::addInfo( 'start_searchable_ts', '' );
			self::addInfo( 'start_readable_ts', '' );
			self::addInfo( 'start_taxonomies_ts', '' );

			self::flushOptionsCache();
		}
	}

	public static function wipeIndexTables( $indexRole = '' ) {
		if ( empty( $indexRole ) ) {
			$indexRole = Config::getIndexRole();
		}
		$indexRoleSuffix = $indexRole === 'main' ? '' : '_tmp';

		Cache::delete( 'table_exists', 'database' );

		$indexerS = new IndexerS();
		$indexerS->wipe( $indexRoleSuffix );

		if ( self::readableIndexExists( $indexRoleSuffix ) ) {
			$indexerR = new IndexerR();
			$indexerR->wipe( $indexRoleSuffix );
		}

		if ( self::taxIndexExists( $indexRoleSuffix ) ) {
			$taxIndexer = new IndexerTax();
			$taxIndexer->wipe( $indexRoleSuffix );
		}
	}

	public static function getReadableProgress() {
		global $wpdb;

		$percent    = 0;
		$totalItems = absint( self::getInfo( 'total_products_for_indexing', Config::getIndexRole() ) );

		if ( ! empty( Helpers::getAllowedPostTypes( 'no-products' ) ) ) {
			$npTotalItems = absint( self::getInfo( 'total_non_products_for_indexing', Config::getIndexRole() ) );
			if ( is_numeric( $totalItems ) && is_numeric( $npTotalItems ) && ! empty( $npTotalItems ) ) {
				$totalItems += $npTotalItems;
			}
		}

		if ( self::readableIndexExists( Config::getIndexRoleSuffix() ) ) {
			$tableName = $wpdb->psf_search_index . Config::getIndexRoleSuffix();
			//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$totalIndexed = $wpdb->get_var( 'SELECT COUNT(DISTINCT post_id) FROM ' . $tableName );
		}

		if (
			! empty( $totalItems )
			&& is_numeric( $totalItems )
			&& ! empty( $totalIndexed )
			&& is_numeric( $totalIndexed )
		) {
			$percent = $totalIndexed * 100 / $totalItems;
		}

		return absint( $percent );
	}

	public static function getSearchableProgress() {

		$percent    = 0;
		$totalItems = absint( self::getInfo( 'total_products_for_indexing', Config::getIndexRole() ) );

		if ( ! empty( Helpers::getAllowedPostTypes( 'no-products' ) ) ) {
			$npTotalItems = absint( self::getInfo( 'total_non_products_for_indexing', Config::getIndexRole() ) );
			if ( is_numeric( $totalItems ) && is_numeric( $npTotalItems ) && ! empty( $npTotalItems ) ) {
				$totalItems += $npTotalItems;
			}
		}

		$processed = absint( self::getInfo( 'searchable_processed', Config::getIndexRole() ) );

		if (
			! empty( $totalItems )
			&& is_numeric( $totalItems )
			&& ! empty( $processed )
			&& is_numeric( $processed )
		) {
			$percent = $processed * 100 / $totalItems;
		}

		return absint( $percent );
	}

	public static function getTaxonomiesProgress() {

		$percent    = 0;
		$totalItems = absint( self::getInfo( 'total_terms_for_indexing', Config::getIndexRole() ) );
		$processed  = absint( self::getInfo( 'terms_processed', Config::getIndexRole() ) );

		if (
			! empty( $totalItems )
			&& is_numeric( $totalItems )
			&& ! empty( $processed )
			&& is_numeric( $processed )
		) {
			$percent = $processed * 100 / $totalItems;
		}

		return absint( $percent );
	}

	public static function getProgressBarValue() {
		if ( self::getInfo( 'status', Config::getIndexRole() ) === 'completed' ) {
			return 100;
		}

		$percentR = self::getReadableProgress();
		$percentS = self::getSearchableProgress();
		$percentT = self::getTaxonomiesProgress();

		if ( self::canBuildTaxonomyIndex() ) {
			$progress = $percentR * 0.4 + $percentS * 0.4 + $percentT * 0.2;
		} else {
			$progress = ( $percentR + $percentS ) / 2;
		}

		$progress = apply_filters( 'psf_search/indexer/process_status/progress', $progress, $percentR, $percentS, $percentT );

		return $progress > 100 ? 99 : $progress;
	}

	public static function renderIndexingStatus( $refreshStatus = true ) {
		global $wpdb;

		// Showing the status may clean up a failed build (DROP TABLE). If another
		// connection still holds those tables (e.g. MySQL rolling back a big batch),
		// give up after a few seconds instead of hanging the settings page.
		$lockWait = $wpdb->get_var( 'SELECT @@SESSION.lock_wait_timeout' );
		$wpdb->query( 'SET SESSION lock_wait_timeout = ' . absint( apply_filters( 'psf_search/indexer/status_lock_wait_timeout', 5 ) ) );

		if ( $refreshStatus ) {
			self::refreshStatus();
		}

		$html  = '<div class="js-psf-search-indexing-wrapper">';
		$html .= self::getIndexHeader();
		$html .= self::getProcessStatus();
		$html .= '</div>';

		if ( is_numeric( $lockWait ) ) {
			$wpdb->query( 'SET SESSION lock_wait_timeout = ' . absint( $lockWait ) );
		}

		return $html;
	}

	public static function refreshStatus() {
		global $wpdb;

		$status = self::getInfo( 'status', Config::getIndexRole() );

		$startTs    = absint( self::getInfo( 'start_ts', Config::getIndexRole() ) );
		$sStartTs   = absint( self::getInfo( 'start_searchable_ts', Config::getIndexRole() ) );
		$rStartTs   = absint( self::getInfo( 'start_readable_ts', Config::getIndexRole() ) );
		$taxStartTs = absint( self::getInfo( 'start_taxonomies_ts', Config::getIndexRole() ) );
		$sEndTs     = absint( self::getInfo( 'end_searchable_ts', Config::getIndexRole() ) );
		$rEndTs     = absint( self::getInfo( 'end_readable_ts', Config::getIndexRole() ) );
		$taxEndTs   = absint( self::getInfo( 'end_taxonomies_ts', Config::getIndexRole() ) );

		switch ( $status ) {
			case 'cancellation':
				self::addInfo( 'status', 'not-exist' );
				self::log( 'Canceling completed' );

				break;
			case 'error':
				self::cancelBuildIndex();

				break;
		}

	}

	public static function getIndexHeader( $indexRole = '' ) {
		if ( empty( $indexRole ) ) {
			$indexRole = Config::getIndexRole();
		}
		$text              = '';
		$statusColor       = '';
		$statusText        = '';
		$status            = self::getInfo( 'status', $indexRole );
		$endTs             = absint( self::getInfo( 'end_ts', $indexRole ) );
		$totalProducts     = absint( self::getInfo( 'total_products_for_indexing', $indexRole ) );
		$totalNonProducts  = absint( self::getInfo( 'total_non_products_for_indexing', $indexRole ) );
		$nonCriticalErrors = self::getInfo( 'non_critical_errors', $indexRole );
		$lastErrorCode     = '';
		$lastErrorMessage  = '';

		switch ( $status ) {
			case 'preparing':
				$text        = __( 'Wait... Preparing indexing in progress', 'psf-search' );
				$statusText  = __(
					'This process will continue in the background. You can leave this page!',
					'psf-search'
				);
				$statusColor = '#e6a51d';
				break;
			case 'building':
				$text        = __( 'Wait... Indexing in progress', 'psf-search' );
				$statusText  = __(
					'This process will continue in the background. You can leave this page!',
					'psf-search'
				);
				$statusColor = '#e6a51d';
				break;
			case 'cancellation':
				$text        = __( 'Wait... The index build process is canceling', 'psf-search' );
				$statusText  = __( 'Canceling...', 'psf-search' );
				$statusColor = '#7293b0';
				break;
			case 'completed':
				$lastDate = ! empty( $endTs ) ? Helpers::localDate( $endTs ) : '-';
				if ( empty( $nonCriticalErrors ) ) {
					$text = __( 'The search index was built successfully.', 'psf-search' );
				} else {
					$text = __( 'The search index was built successfully, but some non-critical errors occurred.', 'psf-search' );
				}
				$statusText  = __( 'Completed. Works.', 'psf-search' );
				$statusColor = '#4caf50';
				break;
			case 'error':
				$text                                     = __( 'The search index could not be built.', 'psf-search' );
				$statusText                               = __( 'Errors', 'psf-search' );
				$statusColor                              = '#d75f5f';
				list( $lastErrorCode, $lastErrorMessage ) = Logger::getLastEmergencyLog();
				break;
			default:
				$text        = __( 'The search index does not exist yet. Build it now.', 'psf-search' );
				$statusText  = __( 'Not exist', 'psf-search' );
				$statusColor = '#aaaaaa';
				break;
		}

		$actionButton    = self::getIndexButton();
		$isDetails       = get_transient( self::DETAILS_DISPLAY_KEY );
		$status          = self::getInfo( 'status', $indexRole );
		$oldIndexEndTime = Helpers::localDate( absint( self::getInfo( 'end_ts', 'main' ) ), 'Y-m-d H:i:s' );
		$notices         = [];

		$infoLimitedEngine  = '<b>' . __( 'Search engine status:', 'psf-search' ) . '</b> ';
		$infoLimitedEngine .= __( "Your search engine does not work optimally because the index hasn't been built yet.", 'psf-search' );
		$infoLimitedEngine .= ' ' . __( 'The best speed and all pro features will be available after the index has been built.', 'psf-search' );

		if ( Config::isParallelBuildingEnabled() && self::getInfo( 'status', 'tmp' ) !== 'completed' ) {
			if ( self::getInfo( 'status', 'main' ) === 'completed' ) {
				$notices['info']  = '<b>' . __( 'Search engine status:', 'psf-search' ) . '</b> ';
				$notices['info'] .= __( 'We have some good news!', 'psf-search' );
				if ( in_array( self::getInfo( 'status', 'tmp' ), [ 'preparing', 'building' ] ) ) {
					$notices['info'] .= ' ' . __( "Even though the current index is being built, it doesn't affect your search engine.", 'psf-search' );
				} else {
					$notices['info'] .= ' ' . __( "Even though the current index wasn't built, it doesn't affect your search engine.", 'psf-search' );
				}
				$notices['info'] .= ' ' . sprintf( __( 'It works well based on the last properly built index from %s', 'psf-search' ), $oldIndexEndTime );
			} else {
				$notices['info'] = $infoLimitedEngine;
			}
		}

		if ( ! Config::isParallelBuildingEnabled() && self::getInfo( 'status', 'main' ) !== 'completed' ) {
			$notices['info'] = $infoLimitedEngine;
		}

		ob_start();
		include PSF_SEARCH_DIR . 'partials/admin/indexer-notices.php';
		include PSF_SEARCH_DIR . 'partials/admin/indexer-header.php';
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public static function getIndexButton() {
		$status = self::getInfo( 'status', Config::getIndexRole() );

		if ( in_array( $status, [ 'building', 'preparing' ] ) ) {
			$html = '<button class="button js-ajax-stop-build-index">' . __(
				'Stop process',
				'psf-search'
			) . '</button>';
		} elseif ( in_array( $status, [ 'completed' ] ) ) {
			$html = '<button class="button js-ajax-build-index">' . __(
				'Rebuild index',
				'psf-search'
			) . '</button>';
		} elseif ( in_array( $status, [ 'error' ] ) ) {
			$html = '<button class="button js-ajax-build-index">' . __(
				'Try to rebuild the index',
				'psf-search'
			) . '</button>';
		} elseif ( in_array( $status, [ 'cancellation' ] ) ) {
			$html = '<button class="button js-ajax-build-index" disabled="disabled">' . __(
				'Stop process',
				'psf-search'
			) . '</button>';
		} else {
			$html = '<button class="button js-ajax-build-index ajax-build-index-primary">' . __(
				'Build index',
				'psf-search'
			) . '</button>';
		}

		return $html;
	}

	/**
	 * Check if readable products table exist
	 *
	 * @return bool
	 */
	public static function readableIndexExists( $indexRoleSuffix = '' ) {
		global $wpdb;

		return Helpers::isTableExists( $wpdb->psf_search_index . $indexRoleSuffix );
	}

	/**
	 * Check if readable taxonmies table exist
	 *
	 * @return bool
	 */
	public static function taxIndexExists( $indexRoleSuffix = '' ) {
		global $wpdb;

		return Helpers::isTableExists( $wpdb->psf_search_tax_index . $indexRoleSuffix );
	}

	/**
	 * Check if variations table exist
	 *
	 * @return bool
	 */
	public static function variationsIndexExists( $indexRoleSuffix = '' ) {
		global $wpdb;

		return Helpers::isTableExists( $wpdb->psf_search_var_index . $indexRoleSuffix );
	}

	/**
	 * Check if searchable tables exist
	 *
	 * @param string $currentLang
	 * @param string $source
	 *
	 * @return bool
	 */
	public static function searchableIndexExists( $currentLang = '', $indexRoleSuffix = '' ) {
		global $wpdb;
		$isShortInit    = defined( 'SHORTINIT' ) && SHORTINIT;
		$wordlistExists = false;
		$doclistExists  = false;

		$currentLang = Multilingual::isLangCode( $currentLang ) ? $currentLang : '';

		$wpdb->hide_errors();

		ob_start();

		if ( ! empty( $currentLang ) || ( ! $isShortInit && Multilingual::isMultilingual() ) ) {
			$wordlistInstances = 0;
			$doclistInstances  = 0;

			if ( ! empty( $currentLang ) ) {
				$langs = [ $currentLang ];
			} else {
				$langs = Multilingual::getLanguages();
			}

			foreach ( $langs as $lang ) {
				$lang = str_replace( '-', '_', $lang );

				$wordlistTable = $wpdb->psf_search_si_wordlist . '_' . $lang . $indexRoleSuffix;
				$doclistTable  = $wpdb->psf_search_si_doclist . '_' . $lang . $indexRoleSuffix;

				if ( Helpers::isTableExists( $wordlistTable ) ) {
					$wordlistInstances ++;
				}

				if ( Helpers::isTableExists( $doclistTable ) ) {
					$doclistInstances ++;
				}
			}

			if ( $wordlistInstances === count( $langs ) ) {
				$wordlistExists = true;
			}

			if ( $doclistInstances === count( $langs ) ) {
				$doclistExists = true;
			}
		} else {
			$wordlistExists = Helpers::isTableExists( $wpdb->psf_search_si_wordlist . $indexRoleSuffix );
			$doclistExists  = Helpers::isTableExists( $wpdb->psf_search_si_doclist . $indexRoleSuffix );
		}

		ob_end_clean();

		return $wordlistExists && $doclistExists;
	}

	/**
	 * Check if cache table exists
	 *
	 * @param string $lang Language
	 * @param string $postType Post type. Leave empty to check 'product' table
	 *
	 * @return bool
	 */
	public static function searchableCacheExists( $lang = '', $postType = '' ) {
		global $wpdb;

		$lang = Multilingual::isLangCode( $lang ) ? $lang : '';
		$lang = str_replace( '-', '_', $lang );

		$cacheTable = $wpdb->psf_search_si_cache;
		if ( ! empty( $postType ) ) {
			$cacheTable .= '_' . $postType;
		}
		if ( ! empty( $lang ) ) {
			$cacheTable .= '_' . $lang;
		}

		return Helpers::isTableExists( $cacheTable );
	}


	public static function getReadableTotalIndexed() {
		global $wpdb;
		$count = 0;

		if ( self::readableIndexExists( Config::getIndexRoleSuffix() ) ) {
			//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$r = $wpdb->get_var( 'SELECT COUNT(DISTINCT post_id) FROM ' . $wpdb->psf_search_index . Config::getIndexRoleSuffix() );
			if ( ! empty( $r ) && is_numeric( $r ) ) {
				$count = absint( $r );
			}
		}

		return $count;
	}

	public static function getProcessStatus() {

		$info = [];
		foreach ( self::getIndexInfoStruct() as $key => $field ) {
			if ( $key === 'logs' ) {
				continue;
			}
			$offset = get_option( 'gmt_offset' );
			$value  = self::getInfo( $key, Config::getIndexRole() );
			if ( strpos( $key, '_ts' ) !== false && ! empty( $value ) && ! empty( $offset ) ) {
				$info[ $key ] = absint( $value ) + ( $offset * 3600 );
			} else {
				$info[ $key ] = $value;
			}
		}

		$progressPercent       = self::getProgressBarValue();
		$logs                  = self::getLogs( Config::getIndexRole() );
		$isDetails             = get_transient( self::DETAILS_DISPLAY_KEY );
		$canBuildTaxonomyIndex = self::canBuildTaxonomyIndex();

		ob_start();
		include PSF_SEARCH_DIR . 'partials/admin/indexer-body.php';
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Check if can index taxonomies
	 *
	 * @return bool
	 */
	public static function canBuildTaxonomyIndex() {
		return ! empty( PSF_SEARCH()->tntsearchMySql->taxonomies->getActiveTaxonomies( 'search_direct' ) );
	}

	/**
	 * Check if index is completed and valid
	 *
	 * @param string $lang
	 *
	 * @return bool
	 */
	public static function isIndexValid( $lang = '', $indexRole = 'main' ) {
		$valid = false;

		$indexRoleSuffix = $indexRole === 'main' ? '' : '_tmp';
		if (
			self::getInfo( 'status', $indexRole ) === 'completed' &&
			self::searchableIndexExists( $lang, $indexRoleSuffix ) &&
			self::readableIndexExists( $indexRoleSuffix )
		) {
			$valid = true;
		}

		return $valid;
	}

	/**
	 * Wipe all data of deprecated SQLite driver
	 *
	 * @return void
	 */
	public static function wipeSQLiteAfterEffects() {

		$uploadDir = wp_upload_dir();
		if ( ! empty( $uploadDir['basedir'] ) ) {

			$directory = $uploadDir['basedir'] . '/psf-search-search';
			$file      = $uploadDir['basedir'] . '/psf-search-search/products.index';

			if ( file_exists( $file ) && is_writable( $file ) ) {
				@unlink( $file );
			}

			if ( file_exists( $directory ) && is_writable( $directory ) ) {
				@rmdir( $directory );
			}
		}

	}

	/**
	 * Complete the search index
	 *
	 * @return void
	 */
	public static function maybeMarkAsCompleted() {
		$status = self::getInfo( 'status', Config::getIndexRole() );
		$sEndTs = absint( self::getInfo( 'end_searchable_ts', Config::getIndexRole() ) );
		$rEndTs = absint( self::getInfo( 'end_readable_ts', Config::getIndexRole() ) );
		$tEndTs = self::canBuildTaxonomyIndex() ? absint( self::getInfo( 'end_taxonomies_ts', Config::getIndexRole() ) ) : 1;

		if ( 'building' === $status && ! empty( $sEndTs ) && ! empty( $rEndTs ) && ! empty( $tEndTs ) ) {
			self::addInfo( 'status', 'completed' );
			self::addInfo( 'end_ts', time() );
			$duplicates = DatabaseR::removeDuplicates( Config::getIndexRoleSuffix() );
			if ( $duplicates > 0 ) {
				self::log( '[Readable index] Removed ' . $duplicates . ' duplicate(s)' );
			}
			self::log( 'Indexing completed' );
			self::copyTmpIndexToMain();
			self::flushOptionsCache();
			do_action( 'psf_search/indexer/status/completed' );
		}
	}

	public static function copyTmpIndexToMain() {
		global $wpdb;

		// Break early if parallel index building is disabled.
		if ( ! Config::isParallelBuildingEnabled() ) {
			self::log( '[Indexer] Skipping copying of "tmp" tables - parallel building is disabled', 'debug', 'file' );

			return;
		}

		if ( ! self::isIndexValid( '', 'tmp' ) ) {
			self::log( '[Indexer] Skipping copying of "tmp" tables - "tmp" index is invalid', 'debug', 'file' );

			return;
		}

		$tables               = Utils::getAllPluginTables();
		$nonRemovableTables   = [
			'psf_search_stats',
		];
		$nonRemovableTables[] = Storage::getTableName();
		foreach ( $tables as $table ) {
			foreach ( $nonRemovableTables as $nonRemovableTable ) {
				if ( strpos( $table, $nonRemovableTable ) !== false ) {
					continue 2;
				}
			}
			// Remove all not "main" index tables (even those that don't have "tmp" version).
			//phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( strpos( $table, '_tmp' ) === false ) {
				$wpdb->query( "DROP TABLE IF EXISTS $table" );
			}
			if ( strpos( $table, '_tmp' ) !== false ) {
				$mainTable = str_replace( '_tmp', '', $table );
				// Make sure the table at index "main" does not exist.
				$wpdb->query( "DROP TABLE IF EXISTS $mainTable" );
				$wpdb->query( "RENAME TABLE $table TO $mainTable" );
			}
			//phpcs:enable
		}

		foreach ( self::getIndexInfoStruct() as $key => $value ) {
			if ( $key === 'logs' ) {
				continue;
			}
			$value = get_option( self::LAST_BUILD_OPTION_KEY . '_' . $key . '_tmp', $value );
			delete_option( self::LAST_BUILD_OPTION_KEY . '_' . $key );

			/*
			 * Make sure the options are copied.
			 * Sometimes it happened that some options were not copied, and we have to be sure that everything went OK.
			 */
			$count = 0;
			do {
				$count ++;
				if ( $count > 1 ) {
					usleep( 500000 );
				}
				if ( $count === 1 ) {
					$addResult  = add_option( self::LAST_BUILD_OPTION_KEY . '_' . $key, $value, '', 'no' );
					$savedValue = get_option( self::LAST_BUILD_OPTION_KEY . '_' . $key );
					$continue   = ! $addResult || $value !== $savedValue;
				} else {
					delete_option( self::LAST_BUILD_OPTION_KEY . '_' . $key );
					$wpdb->query( $wpdb->prepare( "INSERT INTO $wpdb->options (`option_name`, `option_value`, `autoload`) VALUES (%s, %s, 'no')", self::LAST_BUILD_OPTION_KEY . '_' . $key, maybe_serialize( $value ) ) );
					$continue = false;
				}
			} while ( $continue && $count < 3 );
		}

		$logs = Storage::get( 'indexer_last_build_logs_tmp', true );
		if ( empty( $logs ) ) {
			$logs = '[]';
		}
		Storage::update( 'indexer_last_build_logs', $logs );
	}

	/**
	 * Remove all options created by this plugin
	 *
	 * @param bool $networkScope delete tables in whole network
	 *
	 * @return void
	 */
	public static function deleteIndexOptions( $networkScope = false ) {
		global $wpdb;

		$prefix = $wpdb->prefix;

		if ( is_multisite() && $networkScope ) {
			$prefix = $wpdb->base_prefix;
		}

		$lastBuildOptionKey = $wpdb->esc_like( self::LAST_BUILD_OPTION_KEY ) . '%';
		$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $lastBuildOptionKey ) );
		delete_transient( self::DETAILS_DISPLAY_KEY );

		if ( is_multisite() && $networkScope ) {
			foreach ( get_sites() as $site ) {
				if ( is_numeric( $site->blog_id ) ) {

					$blogID = $site->blog_id == 1 ? '' : $site->blog_id . '_';

					$table = $prefix . $blogID . 'options';
					//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wpdb->query( $wpdb->prepare( "DELETE FROM $table WHERE option_name LIKE %s", $lastBuildOptionKey ) );

					$wpdb->delete( $table, [ 'option_name' => '_transient_timeout_' . self::DETAILS_DISPLAY_KEY ] );
					$wpdb->delete( $table, [ 'option_name' => '_transient_' . self::DETAILS_DISPLAY_KEY ] );

				}
			}
		}

	}

	/**
	 * Remove all database tables created by this plugin
	 *
	 * @param bool $networkScope delete tables in whole network
	 *
	 * @return void
	 */
	public static function deleteDatabaseTables( $networkScope = false ) {
		global $wpdb;

		// DB tables
		$tables = Utils::getAllPluginTables( $networkScope );

		if ( ! empty( $tables ) ) {
			foreach ( $tables as $table ) {
				//phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$wpdb->query( "DROP TABLE IF EXISTS $table" );
			}
		}
	}

	/**
	 * Removal of planned actions that will update products in the index
	 */
	public static function wipeActionScheduler() {
		$queue = Utils::getQueue();
		if ( empty( $queue ) ) {
			return;
		}

		try {
			$queue->cancel_all( 'psf_search/tnt/background_product_update' );
			$queue->cancel_all( 'psf_search/tnt/init_variations_update' );
			//phpcs:ignore Squiz.Commenting.EmptyCatchComment.Missing,Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		} catch ( Exception $e ) {

		}
	}

	/**
	 * Dispatch building taxonomies index
	 */
	public static function maybeDispatchTaxonomyAsyncProcess() {
		if ( ! self::canBuildTaxonomyIndex() ) {
			return;
		}

		$status = self::getInfo( 'status', Config::getIndexRole() );
		$rEndTs = absint( self::getInfo( 'end_readable_ts', Config::getIndexRole() ) );

		if (
			( Config::isIndexerMode( 'async' ) && $status === 'building' && ! empty( $rEndTs ) )
			|| ( Config::isIndexerMode( 'sync' ) && $status === 'building' && ! empty( $rEndTs ) )
			|| ( Config::isIndexerMode( 'direct' ) && $status === 'building' )
		) {
			RequestT::handle();
		}
	}

	/**
	 * Check if the indexer working too long without any action
	 *
	 * @return bool
	 */
	public static function isIndexerWorkingTooLong( $forceMaxNoActionTime = 0 ) {
		$status = self::getInfo( 'status', Config::getIndexRole() );

		// Return early if indexer is not working
		if ( ! in_array( $status, [ 'building', 'preparing' ] ) ) {
			return false;
		}

		$lastActionTs = absint( self::getInfo( 'last_action_ts', Config::getIndexRole() ) );

		// Return early if the indexer info hasn't been created yet
		if ( empty( $lastActionTs ) ) {
			return false;
		}

		$diff = time() - $lastActionTs;

		$maxNoActionTime = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ? 61 * MINUTE_IN_SECONDS : 16 * MINUTE_IN_SECONDS;
		/**
		 * Filters maximum no action time of indexer.
		 *
		 * @param int $maxNoActionTime Max time in seconds. 16 min if WP-Cron is enabled or 61 min if not
		 */
		$maxNoActionTime = apply_filters( 'psf_search/indexer/max_no_action_time', $maxNoActionTime );

		if ( $forceMaxNoActionTime > 0 ) {
			$maxNoActionTime = $forceMaxNoActionTime;
		}

		return in_array( $status, [ 'building', 'preparing' ] ) && $diff >= $maxNoActionTime;
	}
}
