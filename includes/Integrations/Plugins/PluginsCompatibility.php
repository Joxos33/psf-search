<?php

namespace PsfSearch\Integrations\Plugins;

use PsfSearch\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PluginsCompatibility {

	public function __construct() {
		// Break early if the "nopsfsearch" mode is active.
		if ( Helpers::isNoPsfSearchModeActive() ) {
			return;
		}

		$this->loadCompatibilities();
	}

	public function getIntegrationClasses(): array {
		$integrationClasses = [];
		$directories        = glob( PSFS_DIR . 'includes/Integrations/Plugins/*', GLOB_ONLYDIR );

		$directories = apply_filters( 'psfs/plugins_compatibility/directories', $directories );

		if ( ! empty( $directories ) && is_array( $directories ) ) {
			foreach ( $directories as $dir ) {
				$name     = str_replace( PSFS_DIR . 'includes/Integrations/Plugins/', '', $dir );
				$filename = $name . '.php';

				$file  = $dir . '/' . $filename;
				$class = '\\PsfSearch\\Integrations\\Plugins\\' . $name . '\\' . $name;

				if ( file_exists( $file ) && class_exists( $class ) ) {
					$integrationClasses[] = $class;
				}
			}
		}

		return $integrationClasses;
	}

	/**
	 * Load class with compatibilities logic for current theme
	 *
	 * @return void
	 */
	private function loadCompatibilities() {
		$integrations = $this->getIntegrationClasses();

		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
		/* @var AbstractPluginIntegration $integration */
		foreach ( $integrations as $integration ) {
			$integration::registerTroubleshootingHooks();
			if ( $integration::isActive() || $integration::shouldInitLate() ) {
				$tmp = new $integration();
				$tmp->init();
			}
		}
	}
}
