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
		wp_register_style( 'psf-search-style', apply_filters( 'psf_search/scripts/css_style_url', PSF_SEARCH_URL . 'assets/css/style' . $min . '.css' ), [], PSF_SEARCH_VERSION );
		wp_register_script( 'jquery-psf_search', apply_filters( 'psf_search/scripts/js_url', PSF_SEARCH_URL . 'assets/js/search' . $min . '.js' ), [ 'jquery' ], PSF_SEARCH_VERSION, true );

		// Enqueue
		wp_enqueue_style( 'psf-search-style' );

		$localize = Helpers::getScriptsSettings();

		wp_localize_script( 'jquery-psf_search', 'psf_search', $localize );
	}
}
