<?php

namespace PsfSearch;


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Scripts {

	public function __construct() {
		// Break early if the "nopsfsearch" mode is active.
		if ( Helpers::isNoPsfSearchModeActive() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'loadScripts' ] );
	}

	/**
	 * Loads scripts and styles
	 * Uses a WP hook wp_enqueue_scripts
	 *
	 * @return void
	 */

	public function loadScripts() {
		$min = SCRIPT_DEBUG ? '' : '.min';

		// Register
		wp_register_style( 'psfs-style', apply_filters( 'psfs/scripts/css_style_url', PSFS_URL . 'assets/css/style' . $min . '.css' ), [], PSFS_VERSION );
		wp_register_script( 'jquery-psfs', apply_filters( 'psfs/scripts/js_url', PSFS_URL . 'assets/js/search' . $min . '.js' ), [ 'jquery' ], PSFS_VERSION, true );

		// Enqueue
		wp_enqueue_style( 'psfs-style' );

		$localize = Helpers::getScriptsSettings();

		wp_localize_script( 'jquery-psfs', 'psfs', $localize );
	}
}
