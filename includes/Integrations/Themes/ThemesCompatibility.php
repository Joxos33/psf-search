<?php

namespace DgoraWcas\Integrations\Themes;

use DgoraWcas\Helpers;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ThemesCompatibility {
	private $themeSlug       = '';
	private $themeName       = '';
	private $themeVersion    = '';
	private $parentThemeName = '';
	private $theme           = null;
	private $supportActive   = false;

	public function __construct() {
		// Break early if the "nofibosearch" mode is active.
		if ( Helpers::isNoFiboSearchModeActive() ) {
			return;
		}

		$this->setCurrentTheme();
		$this->loadCompatibilities();
	}

	private function setCurrentTheme() {
		$theme = wp_get_theme();

		if ( is_object( $theme ) && is_a( $theme, 'WP_Theme' ) ) {
			$template        = $theme->get_template();
			$stylesheet      = $theme->get_stylesheet();
			$isChildTheme    = $template !== $stylesheet;
			$this->themeSlug = sanitize_title( $theme->Name );

			$this->theme           = $theme;
			$this->themeName       = $theme->name;
			$this->themeVersion    = $theme->offsetGet( 'Version' );
			$this->parentThemeName = ! empty( $theme->parent_theme ) ? $theme->parent_theme : '';

			if ( $isChildTheme ) {
				$this->themeSlug    = strtolower( $template );
				$this->themeVersion = $theme->parent()->get( 'Version' );
			}
		}

		$this->themeSlug = apply_filters( 'dgwt/wcas/integrations/themes/current_theme_slug', $this->themeSlug );
		$this->themeName = apply_filters( 'dgwt/wcas/integrations/themes/current_theme_name', $this->themeName );
		if ( $this->isChildTheme() ) {
			$this->parentThemeName = apply_filters( 'dgwt/wcas/integrations/themes/current_parent_theme_name', $this->parentThemeName );
		}
	}

	/**
	 *  All supported themes
	 *
	 * @return array
	 */
	public function supportedThemes() {
		return [
			'flatsome' => [
				'slug' => 'flatsome',
				'name' => 'Flatsome',
				'args' => [
					'forceMobileOverlayBreakpoint' => 850,
					'forceLayoutBreakpoint'        => 850,
				],
			],
		];
	}

	/**
	 * Load class with compatibilities logic for current theme
	 *
	 * @return void
	 */
	private function loadCompatibilities() {
		foreach ( $this->supportedThemes() as $theme ) {
			if ( $theme['slug'] === $this->themeSlug ) {
				if ( isset( $theme['args']['minimumVersion'] ) && version_compare( $this->themeVersion, $theme['args']['minimumVersion'], '<' ) ) {
					$currentVersion = $this->themeVersion;
					// Break if theme version is lower than required.
					add_filter(
						'dgwt/wcas/troubleshooting/unsupported_theme_version',
						function () use ( $theme, $currentVersion ) {
							return [
								'name'           => $theme['name'],
								'currentVersion' => $currentVersion,
								'minimumVersion' => $theme['args']['minimumVersion'],
							];
						}
					);
					break;
				}

				$this->supportActive = true;

				$class = '\\DgoraWcas\\Integrations\\Themes\\';

				if ( isset( $theme['className'] ) ) {
					$class .= $theme['className'] . '\\' . $theme['className'];
				} else {
					$class .= $theme['name'] . '\\' . $theme['name'];
				}

				$args = isset( $theme['args'] ) && is_array( $theme['args'] ) ? $theme['args'] : [];

				if ( $this->isWhiteLabel() ) {
					$args['whiteLabel'] = true;
				}

				if ( class_exists( $class ) ) {
					new $class( $this->themeSlug, $this->themeName, $args );
				} else {
					new GenericTheme( $this->themeSlug, $this->themeName, $args );
				}

				break;
			}
		}
	}

	/**
	 * Check if current theme is supported
	 *
	 * @return bool
	 */
	public function isCurrentThemeSupported() {
		return $this->supportActive;
	}

	/**
	 * Get current theme info
	 *
	 * @return null|object
	 */
	public function getTheme() {
		return $this->theme;
	}

	/**
	 * Get the name of the current theme
	 *
	 * @return string
	 */
	public function getThemeName() {
		return ! empty( $this->themeName ) && is_string( $this->themeName ) ? $this->themeName : '';
	}

	/**
	 * Check if the current them is child theme
	 *
	 * @return bool
	 */
	public function isChildTheme() {
		return ! empty( $this->parentThemeName );
	}

	/**
	 * Check if the integration is under white label
	 *
	 * @return bool
	 */
	public function isWhiteLabel() {
		return apply_filters( 'dgwt/wcas/integrations/themes/white_label', false );
	}

	/**
	 * Get the name of the current parent theme
	 *
	 * @return string
	 */
	public function getParentThemeName() {
		return ! empty( $this->parentThemeName ) ? $this->parentThemeName : '';
	}

	/**
	 * Get current theme image src
	 *
	 * @return string
	 */
	public function getThemeImageSrc() {
		$src = '';

		if ( ! empty( $this->theme ) ) {

			foreach ( [ 'png', 'jpg' ] as $ext ) {
				if ( empty( $src ) && file_exists( $this->theme->get_template_directory() . '/screenshot.' . $ext ) ) {
					$src = $this->theme->get_template_directory_uri() . '/screenshot.' . $ext;
					break;
				}
			}
		}

		return ! empty( $src ) ? esc_url( $src ) : '';
	}

}


