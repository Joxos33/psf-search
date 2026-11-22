<?php

namespace PsfSearch\Admin;

use PsfSearch\Engines\TNTSearchMySQL\Config;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Builder;
use PsfSearch\Helpers;
use PsfSearch\Settings;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AdminMenu {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'addMenu' ], 20 );
	}

	/**
	 * Add meun items
	 *
	 * @return void
	 */
	public function addMenu() {

		$menuSuffix = '';
		if ( Builder::getInfo( 'status', Config::getIndexRole() ) === 'error' || Builder::isIndexerWorkingTooLong() ) {
			$menuSuffix = '<span class="psf-search-menu-warning-icon">!</span>';
			add_action(
				'admin_print_styles',
				function () {
					?>
				<style>
					.psf-search-menu-warning-icon {
						display: inline-block;
						vertical-align: top;
						box-sizing: border-box;
						margin: 1px 0 -1px 3px;
						padding: 0 5px;
						min-width: 18px;
						height: 18px;
						border-radius: 9px;
						background-color: #d63638;
						color: #fff;
						font-size: 11px;
						line-height: 1.6;
						text-align: center;
						z-index: 26;
					}
				</style>
					<?php
				}
			);
		}

		if ( false ) {
			add_action(
				'admin_print_styles',
				function () {
					?>
				<style>
					#adminmenu > .toplevel_page_psf_search_settings {
						display: none;
					}
				</style>
					<?php
				}
			);
		}

		add_submenu_page(
			'woocommerce',
			__( 'PSF Search', 'psf-search' ),
			__( 'PSF Search', 'psf-search' ) . $menuSuffix,
			Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options',
			'psf_search_settings',
			[ $this, 'settingsPage' ]
		);

		if ( ! false && Helpers::isDebugPage() ) {
			add_submenu_page(
				'woocommerce',
				'PSF Search Debug',
				sprintf( '<span class="fs-submenu-item fs-sub">%s</span>', 'Debug' ),
				'manage_options',
				'psf_search_debug',
				[ $this, 'debugPage' ]
			);
		}
	}

	/**
	 * Settings page
	 *
	 * @return void
	 */
	public function settingsPage() {
		Settings::output();
	}

	/**
	 * Debug page
	 *
	 * @return void
	 */
	public function debugPage() {
		include_once PSF_SEARCH_DIR . 'partials/admin/debug/debug.php';
	}
}
