<?php

namespace PsfSearch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Appearance: every visual property of the search bar, dropdown and mobile overlay
 * is a setting. Settings become CSS custom properties (--psfs-*), which
 * assets/css/style-custom.css reads when the "PSF Custom" style is selected.
 *
 * The admin preview sets the same variables live (assets/js/admin-appearance.js),
 * so the variable map below is mirrored there. Keep the two in sync.
 */
class Appearance {

	const STYLE      = 'custom';
	const SECTION    = 'psf_search_appearance';
	const BODY_CLASS = 'psf-search-custom';

	public function __construct() {
		add_filter( 'psf_search/settings/sections', [ $this, 'addSection' ] );
		add_filter( 'psf_search/settings', [ $this, 'addFields' ] );
		add_filter( 'body_class', [ $this, 'bodyClass' ] );
		add_filter( 'admin_body_class', [ $this, 'adminBodyClass' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueueFront' ], 20 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdmin' ], 20 );
	}

	/**
	 * Is the PSF Custom style active?
	 */
	public static function isActive(): bool {
		return PSF_SEARCH()->settings->getOption( 'search_style' ) === self::STYLE;
	}

	/**
	 * Default values. They reproduce the look PSF had with FiboSearch (green button,
	 * green border), minus the Solaris arrow tab.
	 */
	public static function defaults(): array {
		return [
			// Bar
			'ap_bar_height'          => 44,
			'ap_bar_radius'          => 6,
			'ap_bar_border_width'    => 1,
			'ap_bar_border_color'    => '#31651f',
			'ap_bar_bg'              => '#ffffff',
			'ap_bar_shadow'          => 'none',
			'ap_focus_style'         => 'ring',
			'ap_focus_color'         => '#254d18',
			// Text
			'ap_font_family'         => '',
			'ap_font_size'           => 15,
			'ap_font_weight'         => '400',
			'ap_text_color'          => '#0a0a0a',
			'ap_placeholder_color'   => '#8a8a8a',
			'ap_placeholder_style'   => 'italic',
			// Button
			'ap_btn_layout'          => 'attached',
			'ap_btn_content'         => 'icon',
			'ap_btn_width'           => 52,
			'ap_btn_radius'          => 6,
			'ap_btn_gap'             => 6,
			'ap_btn_icon_size'       => 18,
			'ap_btn_bg'              => '#254d18',
			'ap_btn_color'           => '#f7f7f7',
			'ap_btn_hover_bg'        => '#1b3a11',
			'ap_btn_arrow'           => 'off',
			// Dropdown
			'ap_dd_bg'               => '#fcfcfc',
			'ap_dd_hover_bg'         => '#eef4ea',
			'ap_dd_text'             => '#0a0000',
			'ap_dd_highlight'        => '#254d18',
			'ap_dd_border_color'     => '#d9d9d9',
			'ap_dd_border_width'     => 1,
			'ap_dd_divider_color'    => '#ececec',
			'ap_dd_radius'           => 6,
			'ap_dd_shadow'           => 'soft',
			'ap_dd_gap'              => 0,
			'ap_dd_img_size'         => 48,
			'ap_dd_title_size'       => 14,
			'ap_dd_title_weight'     => '500',
			'ap_dd_price_color'      => '#254d18',
			'ap_dd_price_size'       => 14,
			// Mobile
			'ap_m_bar_height'        => 44,
			'ap_m_font_size'         => 16,
			'ap_m_btn_width'         => 48,
			'ap_m_overlay_bg'        => '#ffffff',
			'ap_m_overlay_bar_bg'    => '#254d18',
			'ap_m_overlay_icon'      => '#ffffff',
		];
	}

	public static function shadows(): array {
		return [
			'none'   => 'none',
			'soft'   => '0 1px 3px rgba(0,0,0,.08)',
			'medium' => '0 4px 12px rgba(0,0,0,.12)',
			'strong' => '0 10px 30px rgba(0,0,0,.18)',
		];
	}

	public function addSection( $sections ) {
		$sections[12] = [
			'id'    => self::SECTION,
			'title' => __( 'Appearance', 'psf-search' ),
		];

		return $sections;
	}

	/**
	 * Register the Appearance fields, move the submit-button options next to them and
	 * mark the legacy (Solaris/Pirx) color options so the admin can hide them.
	 */
	public function addFields( $fields ) {
		$d       = self::defaults();
		$px      = ' px';
		$shadows = [
			'none'   => __( 'None', 'psf-search' ),
			'soft'   => __( 'Soft', 'psf-search' ),
			'medium' => __( 'Medium', 'psf-search' ),
			'strong' => __( 'Strong', 'psf-search' ),
		];
		$weights = [
			'300' => __( 'Light (300)', 'psf-search' ),
			'400' => __( 'Normal (400)', 'psf-search' ),
			'500' => __( 'Medium (500)', 'psf-search' ),
			'600' => __( 'Semi-bold (600)', 'psf-search' ),
			'700' => __( 'Bold (700)', 'psf-search' ),
		];

		$head = function ( $name, $label ) {
			return [
				'name'  => $name,
				'label' => $label,
				'type'  => 'head',
				'class' => 'psf-search-sgs-header',
			];
		};
		$num  = function ( $name, $label, $desc = ' px', $min = 0 ) use ( $d ) {
			return [
				'name'              => $name,
				'label'             => $label,
				'type'              => 'number',
				'size'              => 'small',
				'number_min'        => $min,
				'desc'              => $desc,
				'default'           => $d[ $name ],
				'sanitize_callback' => [ __CLASS__, 'sanitizeNumber' ],
			];
		};
		$color = function ( $name, $label ) use ( $d ) {
			return [
				'name'    => $name,
				'label'   => $label,
				'type'    => 'color',
				'default' => $d[ $name ],
			];
		};
		$select = function ( $name, $label, $options, $desc = '' ) use ( $d ) {
			return [
				'name'    => $name,
				'label'   => $label,
				'type'    => 'select',
				'options' => $options,
				'desc'    => $desc,
				'default' => $d[ $name ],
			];
		};

		$section = [
			10  => [
				'name'  => 'ap_intro',
				'label' => '',
				'type'  => 'desc',
				'desc'  => '<p class="js-psf-search-ap-inactive-note psf-search-ap-note">' . __( 'These settings apply when <b>Search bar → Style</b> is set to <b>PSF Custom</b>.', 'psf-search' ) . '</p>',
			],

			100 => $head( 'ap_bar_head', __( 'Search bar', 'psf-search' ) ),
			110 => $num( 'ap_bar_height', __( 'Height', 'psf-search' ), $px, 24 ),
			120 => $num( 'ap_bar_radius', __( 'Corner radius', 'psf-search' ), $px . '. ' . __( 'Use a large value (e.g. 99) for a pill shape', 'psf-search' ) ),
			130 => $num( 'ap_bar_border_width', __( 'Border width', 'psf-search' ) ),
			140 => $color( 'ap_bar_border_color', __( 'Border color', 'psf-search' ) ),
			150 => $color( 'ap_bar_bg', __( 'Background', 'psf-search' ) ),
			160 => $select( 'ap_bar_shadow', __( 'Shadow', 'psf-search' ), $shadows ),
			170 => $select(
				'ap_focus_style',
				__( 'Focus effect', 'psf-search' ),
				[
					'ring'   => __( 'Border color + glow ring', 'psf-search' ),
					'border' => __( 'Border color only', 'psf-search' ),
					'none'   => __( 'None', 'psf-search' ),
				]
			),
			180 => $color( 'ap_focus_color', __( 'Focus color', 'psf-search' ) ),

			200 => $head( 'ap_text_head', __( 'Text', 'psf-search' ) ),
			210 => [
				'name'    => 'ap_font_family',
				'label'   => __( 'Font family', 'psf-search' ),
				'type'    => 'text',
				'desc'    => __( 'Leave blank to use the theme font. Example: Saira, sans-serif', 'psf-search' ),
				'default' => $d['ap_font_family'],
			],
			220 => $num( 'ap_font_size', __( 'Font size', 'psf-search' ), $px, 10 ),
			230 => $select( 'ap_font_weight', __( 'Font weight', 'psf-search' ), $weights ),
			240 => $color( 'ap_text_color', __( 'Text color', 'psf-search' ) ),
			250 => $color( 'ap_placeholder_color', __( 'Placeholder color', 'psf-search' ) ),
			260 => $select(
				'ap_placeholder_style',
				__( 'Placeholder style', 'psf-search' ),
				[
					'italic' => __( 'Italic', 'psf-search' ),
					'normal' => __( 'Normal', 'psf-search' ),
				]
			),

			300 => $head( 'ap_btn_head', __( 'Search button', 'psf-search' ) ),
			// 310/320: show_submit_button + search_submit_text are moved here below.
			330 => $select(
				'ap_btn_layout',
				__( 'Position', 'psf-search' ),
				[
					'attached' => __( 'Attached to the bar (flush right edge)', 'psf-search' ),
					'inset'    => __( 'Inside the bar (with a gap)', 'psf-search' ),
					'separate' => __( 'Separate, next to the bar', 'psf-search' ),
				]
			),
			340 => $select(
				'ap_btn_content',
				__( 'Content', 'psf-search' ),
				[
					'icon' => __( 'Icon', 'psf-search' ),
					'text' => __( 'Text', 'psf-search' ),
					'both' => __( 'Icon + text', 'psf-search' ),
				],
				__( 'Text comes from "Label" above (default: Search)', 'psf-search' )
			),
			350 => $num( 'ap_btn_width', __( 'Width', 'psf-search' ), $px . '. ' . __( 'Minimum width; text buttons grow to fit', 'psf-search' ), 24 ),
			360 => $num( 'ap_btn_radius', __( 'Corner radius', 'psf-search' ), $px . '. ' . __( 'For "Inside" and "Separate" positions', 'psf-search' ) ),
			370 => $num( 'ap_btn_gap', __( 'Gap', 'psf-search' ), $px . '. ' . __( 'Space around the button for "Inside" and "Separate"', 'psf-search' ) ),
			380 => $num( 'ap_btn_icon_size', __( 'Icon size', 'psf-search' ), $px, 8 ),
			390 => $color( 'ap_btn_bg', __( 'Background', 'psf-search' ) ),
			400 => $color( 'ap_btn_hover_bg', __( 'Hover background', 'psf-search' ) ),
			410 => $color( 'ap_btn_color', __( 'Icon / text color', 'psf-search' ) ),
			420 => [
				'name'    => 'ap_btn_arrow',
				'label'   => __( 'Arrow tab', 'psf-search' ),
				'type'    => 'checkbox',
				'desc'    => __( 'Small arrow on the button\'s left edge (the old Solaris look)', 'psf-search' ),
				'default' => $d['ap_btn_arrow'],
			],

			500 => $head( 'ap_dd_head', __( 'Dropdown', 'psf-search' ) ),
			510 => $color( 'ap_dd_bg', __( 'Background', 'psf-search' ) ),
			520 => $color( 'ap_dd_hover_bg', __( 'Hover / selected row', 'psf-search' ) ),
			530 => $color( 'ap_dd_text', __( 'Text color', 'psf-search' ) ),
			540 => $color( 'ap_dd_highlight', __( 'Matched text color', 'psf-search' ) ),
			550 => $num( 'ap_dd_border_width', __( 'Border width', 'psf-search' ) ),
			560 => $color( 'ap_dd_border_color', __( 'Border color', 'psf-search' ) ),
			570 => $color( 'ap_dd_divider_color', __( 'Row divider color', 'psf-search' ) ),
			580 => $num( 'ap_dd_radius', __( 'Corner radius', 'psf-search' ) ),
			590 => $select( 'ap_dd_shadow', __( 'Shadow', 'psf-search' ), $shadows ),
			600 => $num( 'ap_dd_gap', __( 'Gap below the bar', 'psf-search' ), $px . '. ' . __( '0 = dropdown joins the bar', 'psf-search' ) ),
			610 => $num( 'ap_dd_img_size', __( 'Image size', 'psf-search' ), $px, 16 ),
			620 => $num( 'ap_dd_title_size', __( 'Title font size', 'psf-search' ), $px, 10 ),
			630 => $select( 'ap_dd_title_weight', __( 'Title font weight', 'psf-search' ), $weights ),
			640 => $color( 'ap_dd_price_color', __( 'Price color', 'psf-search' ) ),
			650 => $num( 'ap_dd_price_size', __( 'Price font size', 'psf-search' ), $px, 10 ),

			700 => $head( 'ap_m_head', __( 'Mobile', 'psf-search' ) ),
			705 => [
				'name'  => 'ap_m_note',
				'label' => '',
				'type'  => 'desc',
				'desc'  => '<p class="psf-search-ap-note">' . __( 'Used below the mobile breakpoint (Search bar tab). The preview shows desktop.', 'psf-search' ) . '</p>',
			],
			710 => $num( 'ap_m_bar_height', __( 'Bar height', 'psf-search' ), $px, 24 ),
			720 => $num( 'ap_m_font_size', __( 'Font size', 'psf-search' ), $px . '. ' . __( 'Keep at 16 or more, or iPhones zoom in when the bar is tapped', 'psf-search' ), 10 ),
			730 => $num( 'ap_m_btn_width', __( 'Button width', 'psf-search' ), $px, 24 ),
			740 => $color( 'ap_m_overlay_bg', __( 'Full-screen overlay background', 'psf-search' ) ),
			750 => $color( 'ap_m_overlay_bar_bg', __( 'Overlay back-button background', 'psf-search' ) ),
			760 => $color( 'ap_m_overlay_icon', __( 'Overlay back-button icon', 'psf-search' ) ),
		];

		// Move the submit button on/off and label into the Appearance → Search button group.
		foreach ( [ 'show_submit_button' => 310, 'search_submit_text' => 320 ] as $name => $position ) {
			foreach ( $fields['psf_search_form_body'] ?? [] as $key => $field ) {
				if ( isset( $field['name'] ) && $field['name'] === $name ) {
					$field['class'] = trim( str_replace( 'js-psf-search-adv-settings', '', $field['class'] ?? '' ) );
					if ( $name === 'search_submit_text' ) {
						$field['label'] = __( 'Label', 'psf-search' );
					}
					$section[ $position ] = $field;
					unset( $fields['psf_search_form_body'][ $key ] );
				}
			}
		}

		// The old color pickers only drive the Solaris/Pirx styles.
		$legacy = [ 'bg_submit_color', 'text_submit_color', 'search_icon_color', 'search_form', 'bg_input_underlay_color', 'bg_input_color', 'text_input_color', 'border_input_color', 'suggestions_style_head', 'sug_bg_color', 'sug_hover_color', 'sug_text_color', 'sug_highlight_color', 'sug_border_color' ];
		foreach ( $fields as $sectionId => $sectionFields ) {
			foreach ( $sectionFields as $key => $field ) {
				if ( isset( $field['name'] ) && in_array( $field['name'], $legacy, true ) ) {
					$fields[ $sectionId ][ $key ]['class'] = trim( ( $field['class'] ?? '' ) . ' js-psf-search-legacy-style' );
				}
			}
		}

		// Add the PSF Custom style and make it the default.
		foreach ( $fields['psf_search_form_body'] ?? [] as $key => $field ) {
			if ( isset( $field['name'] ) && $field['name'] === 'search_style' ) {
				$fields['psf_search_form_body'][ $key ]['options'] = [ self::STYLE => __( 'PSF Custom (set in the Appearance tab)', 'psf-search' ) ] + $field['options'];
				$fields['psf_search_form_body'][ $key ]['default'] = self::STYLE;
			}
		}

		$fields[ self::SECTION ] = $section;

		return $fields;
	}

	public static function sanitizeNumber( $value ) {
		return (string) max( 0, (int) $value );
	}

	public function bodyClass( $classes ) {
		if ( self::isActive() ) {
			$classes[] = self::BODY_CLASS;
		}

		return $classes;
	}

	public function adminBodyClass( $classes ) {
		if ( Helpers::isSettingsPage() && self::isActive() ) {
			$classes .= ' ' . self::BODY_CLASS;
		}

		return $classes;
	}

	public function enqueueFront() {
		wp_enqueue_style( 'psf-search-style-custom', PSF_SEARCH_URL . 'assets/css/style-custom.css', [ 'psf-search-style' ], PSF_SEARCH_VERSION );
	}

	public function enqueueAdmin() {
		if ( ! Helpers::isSettingsPage() ) {
			return;
		}

		wp_enqueue_style( 'psf-search-style-custom', PSF_SEARCH_URL . 'assets/css/style-custom.css', [], PSF_SEARCH_VERSION );
		wp_enqueue_style( 'psf-search-admin-appearance', PSF_SEARCH_URL . 'assets/css/admin-appearance.css', [], PSF_SEARCH_VERSION );
		wp_enqueue_script( 'psf-search-admin-appearance', PSF_SEARCH_URL . 'assets/js/admin-appearance.js', [ 'jquery' ], PSF_SEARCH_VERSION, true );
		wp_localize_script(
			'psf-search-admin-appearance',
			'psfSearchAppearance',
			[
				'defaults'       => self::defaults(),
				'shadows'        => self::shadows(),
				'submitFallback' => __( 'Search', 'psf-search' ),
			]
		);
		wp_add_inline_style( 'psf-search-style-custom', self::getCss() );
	}

	/**
	 * Current value of an Appearance setting, falling back to its default.
	 */
	public static function get( string $key ) {
		$defaults = self::defaults();
		$value    = PSF_SEARCH()->settings->getOption( $key );

		if ( $value === '' || $value === null ) {
			return $defaults[ $key ] ?? '';
		}

		return $value;
	}

	/**
	 * CSS custom properties for the current settings.
	 *
	 * @return array{desktop: array<string,string>, mobile: array<string,string>}
	 */
	public static function variables(): array {
		$px     = function ( $key ) {
			return max( 0, (int) self::get( $key ) ) . 'px';
		};
		$color  = function ( $key ) {
			$value = sanitize_hex_color( (string) self::get( $key ) );

			return $value ? $value : self::defaults()[ $key ];
		};
		$choice = function ( $key, array $allowed ) {
			$value = (string) self::get( $key );

			return in_array( $value, $allowed, true ) ? $value : self::defaults()[ $key ];
		};

		$shadows    = self::shadows();
		$focusStyle = $choice( 'ap_focus_style', [ 'ring', 'border', 'none' ] );
		$focusColor = $color( 'ap_focus_color' );
		$borderCol  = $color( 'ap_bar_border_color' );
		$barShadow  = $shadows[ $choice( 'ap_bar_shadow', array_keys( $shadows ) ) ];
		$ddGap      = max( 0, (int) self::get( 'ap_dd_gap' ) );
		$barRadius  = $px( 'ap_bar_radius' );
		$ddRadius   = $px( 'ap_dd_radius' );
		$font       = trim( wp_strip_all_tags( (string) self::get( 'ap_font_family' ) ) );
		$font       = preg_replace( '/[^a-zA-Z0-9 ,\'"\-]/', '', $font );

		// A box-shadow list containing "none" is invalid CSS, so join only the real parts.
		$focusParts  = array_filter(
			[
				$focusStyle === 'ring' ? '0 0 0 3px ' . self::rgba( $focusColor, 0.2 ) : '',
				$barShadow === 'none' ? '' : $barShadow,
			]
		);
		$focusShadow = $focusParts ? implode( ',', $focusParts ) : 'none';

		$desktop = [
			'--psfs-bar-h'         => $px( 'ap_bar_height' ),
			'--psfs-bar-radius'    => $barRadius,
			'--psfs-bar-bw'        => $px( 'ap_bar_border_width' ),
			'--psfs-bar-bc'        => $borderCol,
			'--psfs-bar-bg'        => $color( 'ap_bar_bg' ),
			'--psfs-bar-shadow'    => $barShadow,
			'--psfs-focus-bc'      => $focusStyle === 'none' ? $borderCol : $focusColor,
			'--psfs-focus-shadow'  => $focusShadow,
			'--psfs-font'          => $font === '' ? 'inherit' : $font,
			'--psfs-fs'            => $px( 'ap_font_size' ),
			'--psfs-fw'            => $choice( 'ap_font_weight', [ '300', '400', '500', '600', '700' ] ),
			'--psfs-text'          => $color( 'ap_text_color' ),
			'--psfs-ph'            => $color( 'ap_placeholder_color' ),
			'--psfs-ph-style'      => $choice( 'ap_placeholder_style', [ 'italic', 'normal' ] ),
			'--psfs-btn-w'         => $px( 'ap_btn_width' ),
			'--psfs-btn-radius'    => $px( 'ap_btn_radius' ),
			'--psfs-btn-gap'       => $px( 'ap_btn_gap' ),
			'--psfs-btn-ico'       => $px( 'ap_btn_icon_size' ),
			'--psfs-btn-bg'        => $color( 'ap_btn_bg' ),
			'--psfs-btn-hover'     => $color( 'ap_btn_hover_bg' ),
			'--psfs-btn-c'         => $color( 'ap_btn_color' ),
			'--psfs-dd-bg'         => $color( 'ap_dd_bg' ),
			'--psfs-dd-hover'      => $color( 'ap_dd_hover_bg' ),
			'--psfs-dd-text'       => $color( 'ap_dd_text' ),
			'--psfs-dd-hl'         => $color( 'ap_dd_highlight' ),
			'--psfs-dd-bw'         => $px( 'ap_dd_border_width' ),
			'--psfs-dd-bc'         => $color( 'ap_dd_border_color' ),
			'--psfs-dd-div'        => $color( 'ap_dd_divider_color' ),
			'--psfs-dd-radius'     => $ddRadius,
			'--psfs-dd-shadow'     => $shadows[ $choice( 'ap_dd_shadow', array_keys( $shadows ) ) ],
			'--psfs-dd-gap'        => $ddGap . 'px',
			// Bar and dropdown merge into one shape when there is no gap.
			'--psfs-open-radius'   => $ddGap === 0 ? '0px' : $barRadius,
			'--psfs-dd-top-radius' => $ddGap === 0 ? '0px' : $ddRadius,
			'--psfs-dd-top-bw'     => $ddGap === 0 ? '0px' : $px( 'ap_dd_border_width' ),
			'--psfs-dd-img'        => $px( 'ap_dd_img_size' ),
			'--psfs-dd-title-fs'   => $px( 'ap_dd_title_size' ),
			'--psfs-dd-title-fw'   => $choice( 'ap_dd_title_weight', [ '300', '400', '500', '600', '700' ] ),
			'--psfs-dd-price-c'    => $color( 'ap_dd_price_color' ),
			'--psfs-dd-price-fs'   => $px( 'ap_dd_price_size' ),
			'--psfs-om-bg'         => $color( 'ap_m_overlay_bg' ),
			'--psfs-om-bar-bg'     => $color( 'ap_m_overlay_bar_bg' ),
			'--psfs-om-ico'        => $color( 'ap_m_overlay_icon' ),
		];

		$mobile = [
			'--psfs-bar-h' => $px( 'ap_m_bar_height' ),
			'--psfs-fs'    => $px( 'ap_m_font_size' ),
			'--psfs-btn-w' => $px( 'ap_m_btn_width' ),
		];

		return [
			'desktop' => $desktop,
			'mobile'  => $mobile,
		];
	}

	/**
	 * Inline CSS: the variables on :root, with mobile overrides below the breakpoint.
	 */
	public static function getCss(): string {
		$vars       = self::variables();
		$breakpoint = absint( PSF_SEARCH()->settings->getOption( 'mobile_breakpoint' ) );
		$breakpoint = $breakpoint > 0 ? $breakpoint : 992;

		$decl = function ( array $map ) {
			$out = '';
			foreach ( $map as $name => $value ) {
				$out .= $name . ':' . $value . ';';
			}

			return $out;
		};

		return ':root{' . $decl( $vars['desktop'] ) . '}'
			. '@media (max-width:' . $breakpoint . 'px){:root{' . $decl( $vars['mobile'] ) . '}}';
	}

	/**
	 * Wrapper classes for the custom style (button position/content/arrow).
	 */
	public static function wrapperClasses(): array {
		$layout  = (string) self::get( 'ap_btn_layout' );
		$content = (string) self::get( 'ap_btn_content' );

		$classes   = [];
		$classes[] = 'psf-search-btn-' . ( in_array( $layout, [ 'attached', 'inset', 'separate' ], true ) ? $layout : 'attached' );
		$classes[] = 'psf-search-btn-content-' . ( in_array( $content, [ 'icon', 'text', 'both' ], true ) ? $content : 'icon' );
		if ( self::get( 'ap_btn_arrow' ) === 'on' ) {
			$classes[] = 'psf-search-btn-arrow';
		}

		return $classes;
	}

	public static function rgba( string $hex, float $alpha ): string {
		$hex = ltrim( $hex, '#' );
		if ( strlen( $hex ) === 3 ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		if ( strlen( $hex ) !== 6 || ! ctype_xdigit( $hex ) ) {
			return 'rgba(0,0,0,' . $alpha . ')';
		}

		return sprintf( 'rgba(%d,%d,%d,%s)', hexdec( substr( $hex, 0, 2 ) ), hexdec( substr( $hex, 2, 2 ) ), hexdec( substr( $hex, 4, 2 ) ), $alpha );
	}

	/**
	 * One-time migration when updating from 0.1.x: switch to PSF Custom and seed the
	 * Appearance settings from the legacy color pickers, so the look carries over.
	 */
	public static function migrateFromLegacy() {
		$settings = get_option( PSF_SEARCH_SETTINGS_KEY );
		if ( ! is_array( $settings ) || isset( $settings['ap_bar_height'] ) ) {
			return;
		}

		$map = [
			'border_input_color' => [ 'ap_bar_border_color', 'ap_focus_color' ],
			'bg_input_color'     => [ 'ap_bar_bg' ],
			'text_input_color'   => [ 'ap_text_color' ],
			'bg_submit_color'    => [ 'ap_btn_bg', 'ap_dd_price_color', 'ap_m_overlay_bar_bg' ],
			'text_submit_color'  => [ 'ap_btn_color', 'ap_m_overlay_icon' ],
			'sug_bg_color'       => [ 'ap_dd_bg' ],
			'sug_text_color'     => [ 'ap_dd_text' ],
		];

		$seeded = self::defaults();
		foreach ( $map as $legacy => $targets ) {
			$value = isset( $settings[ $legacy ] ) ? sanitize_hex_color( (string) $settings[ $legacy ] ) : '';
			if ( $value ) {
				foreach ( $targets as $target ) {
					$seeded[ $target ] = $value;
				}
			}
		}

		$settings                 = array_merge( $seeded, $settings );
		$settings['search_style'] = self::STYLE;

		update_option( PSF_SEARCH_SETTINGS_KEY, $settings );

		// Install::createOptions() merges defaults into this global right after us.
		global $psfSearchSettings;
		$psfSearchSettings = $settings;
		PSF_SEARCH()->settings->clearCache();
	}
}
