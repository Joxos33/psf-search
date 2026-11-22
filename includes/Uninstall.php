<?php

namespace PsfSearch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Uninstall {

	public static function afterUninstall() {
		self::wipeOptions();
		self::wipeTransients();

		/**
		 * Multisite.
		 */
		if ( is_multisite() ) {
			$currentSiteId = get_current_blog_id();

			foreach ( get_sites() as $site ) {
				if ( is_numeric( $site->blog_id ) && $site->blog_id > 1 ) {
					switch_to_blog( $site->blog_id );

					self::wipeOptions();
					self::wipeTransients();
				}
			}

			// Switch back to the original site.
			switch_to_blog( ( $currentSiteId ) );
		}
	}

	/**
	 * Wipe options.
	 */
	private static function wipeOptions() {
		$options = [
			'psfs_schedule_single',
			'psfs_settings_show_advanced',
			'psfs_images_regenerated',
			'psfs_activation_date',
			'psfs_dismiss_review_notice',
			'psfs_stats_db_version',
			'widget_psfs_ajax_search',
		];

		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}

	/**
	 * Wipe transients.
	 */
	private static function wipeTransients() {
		$transients = [
			'psfs_troubleshooting_async_results',
		];

		foreach ( $transients as $transient ) {
			delete_transient( $transient );
		}
	}

	public static function afterUninstall__premium_only() {
		/**
		 * Single site.
		 */
		self::wipeTables__premium_only();
		self::wipeOptions__premium_only();
		self::wipeTransients__premium_only();
		self::wipeActionSchedulerLogsAndActions__premium_only();
		self::wipeLogs__premium_only();

		/**
		 * Multisite.
		 */
		if ( is_multisite() ) {
			$currentSiteId = get_current_blog_id();

			foreach ( get_sites() as $site ) {
				if ( is_numeric( $site->blog_id ) && $site->blog_id > 1 ) {
					switch_to_blog( $site->blog_id );

					self::wipeTables__premium_only();
					self::wipeOptions__premium_only();
					self::wipeTransients__premium_only();
					self::wipeActionSchedulerLogsAndActions__premium_only();
					self::wipeLogs__premium_only();
				}
			}

			// Switch back to the original site.
			switch_to_blog( ( $currentSiteId ) );
		}
	}

	/**
	 * Wipe database tables.
	 */
	private static function wipeTables__premium_only() {
		global $wpdb;

		$pluginTables = $wpdb->get_col( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . 'psfs_' ) . '%' ) );
		foreach ( $pluginTables as $table ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
		}
	}

	/**
	 * Wipe options.
	 */
	private static function wipeOptions__premium_only() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}options WHERE option_name LIKE 'psfs_indexer_last_build%'" ) );

		$options = [
			'psfs_schedule_single',
			'psfs_schedule_last_data',
			'psfs_inv_index_db_version',
			'psfs_index_db_version',
			'psfs_tax_index_db_version',
			'psfs_var_index_db_version',
			'psfs_settings_show_advanced',
			'psfs_db_json_support',
			'psfs_images_regenerated',
			'psfs_debug_search_logs',
			'psfs_indexer_prepare_process_exist',
			'psfs_activation_date',
			'psfs_dismiss_review_notice',
			'psfs_indexer_last_failure_data',
			'psfs_auto_send_indexer_failure_reports',
			'psfs_dismiss_indexer_failure_notices',
			'psfs_db_locking_support',
			'psfs_stats_db_version',
			'widget_psfs_ajax_search',
			'psfs_storage_db_version',
		];

		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}

	/**
	 * Wipe transients.
	 */
	private static function wipeTransients__premium_only() {
		$transients = [
			'psfs_indexer_details_display',
			'psfs_searchable_custom_fields',
			'psfs_troubleshooting_async_results',
			'psfs_indexer_debug',
			'psfs_indexer_debug_scope',
		];

		foreach ( $transients as $transient ) {
			delete_transient( $transient );
		}
	}

	/**
	 * Wipe Action Scheduler actions and its logs.
	 */
	private static function wipeActionSchedulerLogsAndActions__premium_only() {
		global $wpdb;

		$logIds = $wpdb->get_col( "SELECT action_id FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = 'psfs/tnt/background_product_update'" );

		if ( ! empty( $logIds ) ) {
			$placeholders = array_fill( 0, count( $logIds ), '%d' );
			$format       = implode( ', ', $placeholders );
			// TODO
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
			$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}actionscheduler_logs WHERE action_id IN ($format)", $logIds ) );
		}
		$wpdb->query( "DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = 'psfs/tnt/background_product_update'" );
	}

	/**
	 * Wipe logs.
	 */
	private static function wipeLogs__premium_only() {
		if ( class_exists( 'WC_Log_Handler_File' ) ) {
			$wcLogHandler = new \WC_Log_Handler_File();
			foreach ( [ 'psfsearch-updater', 'psfsearch-fail-indexer', 'psfsearch-indexer' ] as $source ) {
				$files = $wcLogHandler->get_log_files();
				foreach ( $files as $handle => $filename ) {
					if ( strpos( $handle, $source ) !== false ) {
						$wcLogHandler->remove( $handle );
					}
				}
			}
		}
	}
}
