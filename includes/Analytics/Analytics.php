<?php

namespace PsfSearch\Analytics;

use PsfSearch\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Analytics {
	public function init() {

		$allowByConstant = defined( 'PSF_SEARCH_ANALYTICS_ENABLE' ) && PSF_SEARCH_ANALYTICS_ENABLE;

		// Publish this module conditionally in v1.18.0 and for everyone in v1.19.0
		if (
			! $this->isModuleEnabled()
			&& version_compare( PSF_SEARCH_VERSION, '1.18.99' ) < 0
			&& ! $allowByConstant
		) {
			return;
		}

		if ( is_admin() ) {
			// Load user interface
			$ui = new UserInterface( $this );
			$ui->init();

			$widget = new Widget( $this, $ui );
			$widget->init();
		}

		// Database
		Database::registerTables();
		$this->maybeInstallDatabase();

		// Maintenance.
		$maintenance = new Maintenance();
		if ( $this->isModuleEnabled() ) {
			$maintenance->init();
		} else {
			$maintenance->unschedule();
		}
	}

	/**
	 * Check if the Analytics module is enabled
	 *
	 * @return bool
	 */
	public function isModuleEnabled() {
		return PSF_SEARCH()->settings->getOption( 'analytics_enabled', 'off' ) === 'on';
	}

	/**
	 * Create the database table if necessary
	 *
	 * @return void
	 */
	public function maybeInstallDatabase() {
		// Try to create tables after enabling Search Analytics module
		add_action(
			'update_option_' . PSF_SEARCH_SETTINGS_KEY,
			function ( $oldValue, $newValue ) {

				$key = 'analytics_enabled';

				$nowEnabled  = isset( $newValue[ $key ] ) && $newValue[ $key ] === 'on';
				$wasDisabled = ! isset( $oldValue[ $key ] ) || ( isset( $oldValue[ $key ] ) && $oldValue[ $key ] !== 'on' );

				if ( $nowEnabled && $wasDisabled ) {
					Database::maybeInstall();
				}

			},
			10,
			2
		);

		// Try to create tables when Search Analytics module is created, but from some reasons the table wasn't created
		if (
			Helpers::isSettingsPage()
			&& $this->isModuleEnabled()
			&& ! Database::exist()
		) {
			Database::maybeInstall();
		}
	}
}
