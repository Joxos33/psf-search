<?php

namespace DgoraWcas\Engines\TNTSearchMySQL\Debug;

use DgoraWcas\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Debugger {
	const DEBUG_SEARCH_LOGS_KEY = 'dgwt_wcas_debug_search_logs';

	/**
	 * Save log for the search
	 *
	 * @param string $message
	 * @param string $context
	 *
	 * @return bool
	 */
	public static function log( $message, $context = 'common' ) {
		$added = false;

		$logs = self::getRawLogs();

		if ( ! array_key_exists( $context, $logs ) ) {
			$logs[ $context ] = [];
		}

		if ( is_array( $logs[ $context ] ) ) {
			$logs[ $context ][] = $message;

			$added = update_option( self::DEBUG_SEARCH_LOGS_KEY, $logs );
		}

		return $added;
	}

	/**
	 * Get raw logs
	 *
	 * @return array
	 */
	public static function getRawLogs() {
		global $wpdb;

		$data = [];

		$opt = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SQL_NO_CACHE option_value FROM $wpdb->options WHERE option_name = %s",
				self::DEBUG_SEARCH_LOGS_KEY
			)
		);

		if ( ! empty( $opt ) ) {
			// TODO
			//phpcs:disable WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			$opt = @unserialize( $opt );
			if ( is_array( $opt ) ) {
				$data = $opt;
			}
		}

		return $data;
	}

	/**
	 * Get all logs
	 *
	 * @param string $context
	 *
	 * @return array
	 */
	public static function getLogs( $context = 'common' ) {

		$logs   = [];
		$values = self::getRawLogs();

		if (
			! empty( $values )
			&& is_array( $values )
			&& array_key_exists( $context, $values )
			&& is_array( $values[ $context ] )
		) {
			$logs = $values[ $context ];
		}

		return $logs;
	}

	/**
	 * Print logs
	 *
	 * @param string $title
	 * @param string $context
	 *
	 * @return void
	 */
	public static function printLogs( $title = '', $context = 'common' ) {
		$title = ! empty( $title ) ? $title : $context;

		$html  = '<div class="dgwt-wcas-indexer-logs">';
		$html .= '<h4>' . $title . '</h4>';

		$logs = self::getLogs( $context );

		if ( ! empty( $logs ) ) {
			foreach ( $logs as $log ) {
				$html .= '<span class="dgwt-wcas-indexer-log">';
				$html .= Helpers::stripScripts( $log );
				$html .= '</span>';
			}
		}

		$html .= '</div>';

		echo $html;
	}

	/**
	 * Wipe logs
	 *
	 * @return void
	 */
	public static function wipeLogs( $context = 'common' ) {
		$logs = self::getRawLogs();

		if (
			! empty( $logs )
			&& is_array( $logs )
			&& array_key_exists( $context, $logs )
		) {
			unset( $logs[ $context ] );
			update_option( self::DEBUG_SEARCH_LOGS_KEY, $logs );
		}
	}

	/**
	 * Log search results
	 *
	 * @param $queryObj
	 *
	 * @return void
	 */
	public static function logSearchResults( $queryObj ) {
		if ( ! $queryObj->hasResults() ) {
			self::log( 'no results', 'search-resutls' );

			return;
		}

		ob_start();
		$queryObj->sendResults( false );
		$results = ob_get_clean();
		ob_start();
		$response = json_decode( $results );

		$rows = [];
		$html = '';

		if ( ! empty( $response->suggestions ) ) {
			foreach ( $response->suggestions as $suggestion ) {
				if (
					! empty( $suggestion->type )
					&& in_array(
						$suggestion->type,
						[
							'taxonomy',
							'product',
							'product_variation',
							'post',
							'page',
						]
					)
				) {
					// These fields include product/term names and SKUs, which authors can set to arbitrary
					// values. The result is printed as HTML on the debug page, so escape every field.
					$name = esc_html( $suggestion->value ) . ' | ' . esc_html( $suggestion->type );

					if ( ! empty( $suggestion->post_id ) ) {
						$name .= ' | ' . esc_html( $suggestion->post_id );
					}

					if ( ! empty( $suggestion->taxonomy ) ) {
						$name .= ' | ' . esc_html( $suggestion->term_id ) . ' | ' . esc_html( $suggestion->taxonomy );
					}

					if ( ! empty( $suggestion->sku ) ) {
						$name .= ' | SKU ' . esc_html( $suggestion->sku );
					}

					if ( ! empty( $suggestion->score ) ) {
						$name .= ' | Score ' . esc_html( $suggestion->score );
					}

					$rows[] = $name;
				}
			}
		}

		if ( ! empty( $rows ) ) {
			$html = '<ol>';
			foreach ( $rows as $row ) {
				$html .= '<li>' . $row . '</li>';
			}
			$html .= '</ol>';
		}

		$html .= '<p>Total: ' . $response->total . ' | TNT Time: ' . $response->tntTime . ' | Time: ' . $response->time . '</p>';

		self::log( $html, 'search-resutls' );
	}
}
