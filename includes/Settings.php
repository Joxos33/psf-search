<?php

namespace PsfSearch;

use PsfSearch\Admin\SettingsAPI;
use PsfSearch\Engines\TNTSearchMySQL\Config;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Builder;
use PsfSearch\Engines\TNTSearchMySQL\Indexer\Scheduler;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings API data
 */
class Settings {
	/**
	 * Unique settings slug
	 *
	 * @var string
	 */
	private $settingSlug = PSF_SEARCH_SETTINGS_KEY;

	/**
	 * All options values in one array
	 *
	 * @var array
	 */
	public $opt;

	/**
	 * Settings API object
	 *
	 * @var object
	 */
	public $settingsApi;

	/**
	 * Check if user can see advanced settings
	 *
	 * @var object
	 */
	public $canSeeAdvSettings = null;

	/**
	 * Settings defaults
	 *
	 * @var array
	 */
	public $defaults = [];

	/**
	 * Settings cache
	 *
	 * @var array
	 */
	private $settingsCache = [];

	public function __construct() {
		global $psfSearchSettings;

		// Set global variable with settings
		$settings = get_option( $this->settingSlug );
		if ( ! isset( $settings ) || empty( $settings ) ) {
			$psfSearchSettings = [];
		} else {
			$psfSearchSettings = $settings;
		}

		$this->settingsApi = new SettingsAPI( $this->settingSlug );

		add_action( 'admin_init', [ $this, 'settingsInit' ] );

		add_filter( 'psf_search/settings/option_value', [ $this, 'restoreDefaultValueForFreePlan' ], 10, 3 );

		add_action( 'wp_ajax_psf_search_adv_settings', [ $this, 'toggleAdvancedSettings' ] );

		add_action(
			'wp_ajax_psf_search_settings_list_custom_fields',
			[
				$this,
				'listSearchableCustomFields__premium_only',
			]
		);
		add_action(
			'wp_ajax_psf_search_settings_search_terms',
			[
				$this,
				'searchTermsForFilter__premium_only',
			]
		);

		if ( Helpers::shopManagerHasAccess() ) {
			add_filter(
				'option_page_capability_psf_search_troubleshooting',
				function ( $cap ) {
					return 'manage_woocommerce';
				}
			);
		}

		$this->dependentOptions();
	}

	/**
	 * Set sections and fields
	 *
	 * @return void
	 */
	public function settingsInit() {

		// Set the settings
		$this->settingsApi->set_sections( $this->settings_sections() );
		$this->settingsApi->set_fields( $this->settingsFields() );

		// Initialize settings
		$this->settingsApi->settings_init();
	}

	/*
	 * Set settings sections
	 *
	 * @return array settings sections
	 */

	public function settings_sections() {

		$sections = [
			5  => [
				'id'    => 'psf_search_basic',
				'title' => __( 'Starting', 'psf-search' ),
			],
			10 => [
				'id'    => 'psf_search_form_body',
				'title' => __( 'Search bar', 'psf-search' ),
			],
			15 => [
				'id'    => 'psf_search_autocomplete',
				'title' => __( 'Autocomplete', 'psf-search' ),
			],
			25 => [
				'id'    => 'psf_search_search',
				'title' => __( 'Search config', 'psf-search' ),
			],
		];

		$suffix = '';
		// TODO tutaj trzeba wskazac budowany indeks
		if ( Builder::getInfo( 'status', Config::getIndexRole() ) === 'error' || Builder::isIndexerWorkingTooLong() ) {
			$suffix .= '<span class="js-psf-search-indexer-tab-error psf-search-tab-mark active">!</span>';
		} else {
			$suffix .= '<span class="js-psf-search-indexer-tab-error psf-search-tab-mark">!</span>';
		}

		if ( in_array( Builder::getInfo( 'status', Config::getIndexRole() ), [ 'preparing', 'building', 'cancellation' ] ) ) {
			$suffix .= '<span class="js-psf-search-indexer-tab-progress psf-search-tab-progress active"></span>';
		} else {
			$suffix .= '<span class="js-psf-search-indexer-tab-progress psf-search-tab-progress"></span>';
		}
		$sections[30] = [
			'id'    => 'psf_search_performance',
			'title' => __( 'Indexer', 'psf-search' ) . $suffix,
		];

		$sections = apply_filters( 'psf_search_settings_sections', $sections ); // deprecated since v1.2.0
		$sections = apply_filters( 'psf_search/settings/sections', $sections );

		ksort( $sections );

		return $sections;
	}

	/**
	 * Create settings fields
	 *
	 * @return array settings fields
	 */
	public function settingsFields() {

		$styleLink          = 'https://fibosearch.com/pirx-a-bean-shaped-search-bar-design-youll-love/';
		$darkenedBgLink     = 'https://fibosearch.com/darkened-background/';
		$fuzzySearchLink    = 'https://fibosearch.com/documentation/features/fuzzy-search/';
		$synonymsLink       = 'https://fibosearch.com/documentation/features/synonyms/';
		$excludeIncludeLink = 'https://fibosearch.com/documentation/features/exclude-include/';
		$detailsPanelLink   = 'https://fibosearch.com/feature/details-panel/';
		$schedulerLink      = 'https://fibosearch.com/documentation/features/index-scheduling/';
		$customFieldsLink   = 'https://fibosearch.com/documentation/features/search-in-custom-fields/';
		$mobileOverlayLink  = 'https://fibosearch.com/documentation/features/overlay-on-mobile/';
		$searchLayoutLink   = 'https://fibosearch.com/documentation/features/search-bar-layout/';
		$searchHistory      = 'https://fibosearch.com/user-search-history/';
		$noResultsExtLink   = 'https://fibosearch.com/documentation/tips-tricks/extended-no-results-message/';
		$searchBySkuLink    = 'https://fibosearch.com/documentation/plugins-integrations/search-in-single-variations/#search-by-exact-sku-match';
		$searchByGuidLink   = 'https://fibosearch.com/documentation/plugins-integrations/search-in-single-variations/#search-by-exact-guid-match';

		$readMore = __( '<a target="_blank" href="%s">Read more</a> about this feature.', 'psf-search' );

		$settingsFields = [
			'psf_search_basic'        => apply_filters(
				'psf_search/settings/section=basic',
				[
					90  => [
						'name'  => 'embedding_search_form_head',
						'label' => __( 'How to add search bar in your theme?', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],
					100 => [
						'name'  => 'how_to_use',
						'label' => __( 'How to add?', 'psf-search' ),
						'type'  => 'desc',
						'class' => 'psf-search-only-desc',
						'desc'  => Helpers::howToUseHtml(),
					],
				]
			),
			'psf_search_form_body'    => apply_filters(
				'psf_search/settings/section=form',
				[
					100  => [
						'name'  => 'form_head',
						'label' => __( 'Basic', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],
					200  => [
						'name'              => 'min_chars',
						'label'             => __( 'Minimum characters', 'psf-search' ),
						'type'              => 'number',
						'size'              => 'small',
						'number_min'        => 1,
						'class'             => 'js-psf-search-adv-settings',
						'sanitize_callback' => [ '\PsfSearch\Admin\SettingsAPI', 'sanitize_natural_numbers' ],
						'desc'              => __( 'Min characters to show autocomplete', 'psf-search' ),
						'default'           => 3,
					],
					300  => [
						'name'    => 'max_form_width',
						'label'   => __( 'Max form width', 'psf-search' ),
						'type'    => 'number',
						'size'    => 'small',
						'desc'    => ' px. ' . __( 'To set 100% width leave blank', 'psf-search' ),
						'class'   => 'js-psf-search-adv-settings',
						'default' => 600,
					],
					400  => [
						'name'    => 'show_submit_button',
						'label'   => __( 'Show submit button', 'psf-search' ),
						'type'    => 'checkbox',
						'class'   => 'psf-search-options-cb-toggle js-psf-search-cbtgroup-submit-btn',
						'size'    => 'small',
						'default' => 'off',
					],
					500  => [
						'name'    => 'search_submit_text',
						'label'   => __( 'Label', 'psf-search' ) . ' ' . Helpers::createQuestionMark( 'search-submit-text', __( 'To display the magnifier icon leave this field empty.', 'psf-search' ) ),
						'type'    => 'text',
						'class'   => 'js-psf-search-cbtgroup-submit-btn psf-search-settings-suboption',
						'default' => __( 'Search', 'psf-search' ),
					],
					510  => [
						'name'    => 'bg_submit_color',
						'label'   => __( 'Background color', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings js-psf-search-cbtgroup-submit-btn psf-search-settings-suboption',
						'default' => '',
					],
					520  => [
						'name'    => 'text_submit_color',
						'label'   => '<span>' . __( 'Text color', 'psf-search' ) . ' </span><span>' . __( 'Search icon color', 'psf-search' ) . '</span>',
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings js-psf-search-cbtgroup-submit-btn psf-search-settings-suboption',
						'default' => '',
					],
					600  => [
						'name'              => 'search_placeholder',
						'label'             => __( 'Search input placeholder', 'psf-search' ),
						'type'              => 'text',
						'sanitize_callback' => [ '\PsfSearch\Admin\SettingsAPI', 'strip_all_tags' ],
						'default'           => __( 'Search for products...', 'psf-search' ),
					],
					630  => [
						'name'  => 'layout_head',
						'label' => __( 'Appearance', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header js-psf-search-adv-settings',
					],
					645  => [
						'name'    => 'search_style',
						'label'   => __( 'Style', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'search_style',
							sprintf(
								__(
									'PSF Search provides two different styles of search bars: Solaris and Pirx. Solaris has a rectangular shape while Pirx is a bean-shaped bar with curvy and smooth edges. <a target="_blank" href="%s">See the differences</a> between Solaris and Pirx style.',
									'psf-search'
								),
								$styleLink
							)
						),
						'type'    => 'select',
						'options' => [
							'pirx'         => _x( 'Pirx', 'Pirx is proper name.', 'psf-search' ),
							'pirx-compact' => _x( 'Pirx (compact version)', 'Pirx (compact) is proper name.', 'psf-search' ),
							'solaris'      => _x( 'Solaris', 'Solaris is proper name.', 'psf-search' ),
						],
						'default' => 'pirx',
						'class'   => 'js-psf-search-adv-settings',
					],
					660  => [
						'name'    => 'search_layout',
						'label'   => __( 'Layout', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'search_layout',
							__( 'PSF Search might be displayed as a search bar, icon, or mixed of them.', 'psf-search' )
							. ' <a target="_blank" href="' . $searchLayoutLink . '">' . __( 'See what each of these layouts looks like', 'psf-search' ) . '</a>'
						),
						'type'    => 'select',
						'options' => [
							'classic'           => __( 'Search bar', 'psf-search' ),
							'icon'              => __( 'Search icon', 'psf-search' ),
							'icon-flexible'     => __( 'Icon on mobile, search bar on desktop', 'psf-search' ),
							'icon-flexible-inv' => __( 'Icon on desktop, search bar on mobile', 'psf-search' ),
						],
						'default' => 'classic',
						'class'   => 'js-psf-search-adv-settings',
					],
					670  => [
						'name'    => 'mobile_breakpoint',
						'label'   => __( 'Breakpoint', 'psf-search' ),
						'desc'    => __( 'px', 'psf-search' ),
						'type'    => 'number',
						'class'   => 'js-psf-search-adv-settings psf-search-settings-suboption',
						'size'    => 'small',
						'default' => 992,
					],
					675  => [
						'name'    => 'search_icon_color',
						'label'   => __( 'Search icon color', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings psf-search-settings-suboption',
						'default' => '',
					],
					680  => [
						'name'    => 'enable_mobile_overlay',
						'label'   => __( 'Overlay on mobile', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'enable_mobile_overlay',
							__(
								'Covers anything that is displayed on the screen and simplifies the view to search bar + results.',
								'psf-search'
							) . ' ' . sprintf(
								$readMore,
								$mobileOverlayLink
							)
						),
						'desc'    => __( 'The search will open as an overlay on mobile', 'psf-search' ),
						'type'    => 'checkbox',
						'default' => 'on',
						'class'   => 'js-psf-search-adv-settings',
					],
					685  => [
						'name'    => 'mobile_overlay_breakpoint',
						'label'   => __( 'Breakpoint', 'psf-search' ),
						'desc'    => __( 'px', 'psf-search' ),
						'type'    => 'number',
						'class'   => 'js-psf-search-adv-settings psf-search-settings-suboption',
						'size'    => 'small',
						'default' => 992,
					],
					695  => [
						'name'    => 'darken_background',
						'label'   => __( 'Darkened background', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'darken-_background',
							__(
								'Darkening the page background while autocomplete is active gives it stronger emphasis, minimizing elements (e.g., ads, carousels, and other page content) that could distract users from considering autocomplete suggestions.',
								'psf-search'
							) . ' ' . sprintf(
								$readMore,
								$darkenedBgLink
							)
						),
						'type'    => 'checkbox',
						'class'   => 'js-psf-search-adv-settings',
						'size'    => 'small',
						'default' => 'off',
					],
					700  => [
						'name'  => 'search_form',
						'label' => __( 'Other colors', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header js-psf-search-adv-settings',
					],
					790  => [
						'name'    => 'bg_input_underlay_color',
						'label'   => __( 'Search bar underlay', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
					800  => [
						'name'    => 'bg_input_color',
						'label'   => __( 'Search input background', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
					900  => [
						'name'    => 'text_input_color',
						'label'   => __( 'Search input text', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
					1000 => [
						'name'    => 'border_input_color',
						'label'   => __( 'Search input border', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
					1500 => [
						'name'  => 'preloader_head',
						'label' => __( 'Preloader', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header js-psf-search-adv-settings',
					],
					1700 => [
						'name'    => 'show_preloader',
						'label'   => __( 'Show preloader', 'psf-search' ),
						'class'   => 'psf-search-options-cb-toggle js-psf-search-cbtgroup-preloader js-psf-search-adv-settings',
						'type'    => 'checkbox',
						'default' => 'on',
					],
					1800 => [
						'name'    => 'preloader_url',
						'label'   => __( 'Upload preloader image', 'psf-search' ),
						'class'   => 'js-psf-search-cbtgroup-preloader js-psf-search-adv-settings',
						'type'    => 'file',
						'default' => '',
					],
				]
			),
			'psf_search_autocomplete' => apply_filters(
				'psf_search/settings/section=autocomplete',
				[
					20   => [
						'name'  => 'autocomplete_head',
						'label' => __( 'Basic', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],
					50   => [
						'name'    => 'suggestions_limit',
						'label'   => __( 'Limit', 'psf-search' ),
						'type'    => 'number',
						'size'    => 'small',
						'desc'    => __( 'maximum number of suggestions', 'psf-search' ),
						'default' => 7,
					],
					80   => [
						'name'              => 'search_no_results_text',
						'label'             => _x( 'No results label', 'admin', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'no-results-label',
							sprintf( __( 'The following HTML tags are allowed:<br /> %s.', 'psf-search' ), '<code>h1-h6,p,ul,ol,li,b,em,br,div,span,a</code>' )
							. ' <br /> ' . sprintf( __( 'See an example of a more complex "No results" message in <a href="%s" target="_blank">our documentation</a>.', 'psf-search' ), $noResultsExtLink )
						),
						'type'              => 'textarea',
						'textarea_rows'     => 2,
						'class'             => 'psf-search-settings-textarea--half',
						'sanitize_callback' => [ '\PsfSearch\Admin\SettingsAPI', 'sanitize_no_results_field' ],
						'default'           => __( 'No results', 'psf-search' ),
					],
					100  => [
						'name'  => 'product_suggestion_head',
						'label' => __( 'Products', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],
					300  => [
						'name'    => 'show_product_image',
						'label'   => __( 'Show product image', 'psf-search' ),
						'type'    => 'checkbox',
						'default' => 'on',
					],
					400  => [
						'name'    => 'show_product_price',
						'label'   => __( 'Show price', 'psf-search' ),
						'type'    => 'checkbox',
						'default' => 'off',
					],
					500  => [
						'name'    => 'show_product_desc',
						'label'   => __( 'Show product description', 'psf-search' ),
						'type'    => 'checkbox',
						'default' => 'off',
						'class'   => 'js-psf-search-adv-settings',
					],
					600  => [
						'name'    => 'show_product_sku',
						'label'   => __( 'Show SKU', 'psf-search' ),
						'type'    => 'checkbox',
						'default' => 'off',
					],
					// phpcs:ignore Squiz.PHP.CommentedOutCode.Found
					// 700 => array(
					// 'name'       => 'show_sale_badge',
					// 'label'      => __( 'Show sale badge', 'psf-search' ),
					// 'type'       => 'checkbox',
					// 'default'    => 'off',
					// ),
					// 800 => array(
					// 'name'       => 'show_featured_badge',
					// 'label'      => __( 'Show featured badge', 'psf-search' ),
					// 'type'       => 'checkbox',
					// 'default'    => 'off',
					// ),
					900  => [
						'name'              => 'search_see_all_results_text',
						'label'             => __( 'More results label', 'psf-search' ),
						'type'              => 'text',
						'sanitize_callback' => [ '\PsfSearch\Admin\SettingsAPI', 'strip_all_tags' ],
						'default'           => __( 'See all products...', 'psf-search' ),
					],
					1000 => [
						'name'  => 'non_products_in_autocomplete_head',
						'label' => __( 'Non-products in autocomplete', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],
					1100 => [
						'name'       => 'show_product_tax_product_cat',
						'label'      => __( 'Show categories', 'psf-search' ),
						'type'       => 'checkbox',
						'class'      => 'js-psf-search-settings-margin js-psf-search-options-toggle-sibling',
						'default'    => 'on',
						'input_data' => 'data-option-trigger="show_matching_categories"',
					],
					1150 => [
						'name'       => 'show_product_tax_product_cat_images',
						'label'      => __( 'show images', 'psf-search' ),
						'type'       => 'checkbox',
						'class'      => 'js-psf-search-adv-settings psf-search-premium-only',
						'default'    => 'off',
						'desc'       => __( 'show images', 'psf-search' ),
						'move_dest'  => 'show_product_tax_product_cat',
						'input_data' => 'data-option-trigger="show_categories_images"',
					],
					1300 => [
						'name'       => 'show_product_tax_product_tag',
						'label'      => __( 'Show tags', 'psf-search' ),
						'type'       => 'checkbox',
						'class'      => 'js-psf-search-settings-margin js-psf-search-adv-settings',
						'default'    => 'off',
						'input_data' => 'data-option-trigger="show_matching_tags"',
					],
					1600 => [
						'name'    => 'show_matching_posts',
						'label'   => __( 'Show posts', 'psf-search' ),
						'type'    => 'checkbox',
						'class'   => 'js-psf-search-adv-settings psf-search-premium-only',
						'default' => 'off',
					],
					1800 => [
						'name'    => 'show_matching_pages',
						'label'   => __( 'Show pages', 'psf-search' ),
						'type'    => 'checkbox',
						'class'   => 'js-psf-search-adv-settings psf-search-premium-only',
						'default' => 'off',
					],
					2000 => [
						'name'  => 'details_box_head',
						'label' => __( 'Extra views', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],
					2100 => [
						'name'    => 'show_details_box',
						'label'   => __( 'Show Details Panel', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'details-box',
							__( 'The Details Panel is an additional container for extended information. The details change dynamically when the cursor hovers over one of the suggestions.', 'psf-search' ) . ' ' . sprintf( $readMore, $detailsPanelLink )
						),
						'type'    => 'checkbox',
						'size'    => 'small',
						'default' => 'off',
					],
					2200 => [
						'name'    => 'show_user_history',
						'label'   => __( 'User search history', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'user-search-history',
							__( "The current search history is presented when the user clicked/taped on the search bar, but hasn't yet typed the query. The history includes the last searched products and phrases.", 'psf-search' ) . ' ' . sprintf( $readMore, $searchHistory )
						),
						'type'    => 'checkbox',
						'size'    => 'small',
						'class'   => 'js-psf-search-adv-settings',
						'default' => 'off',
					],
					2500 => [
						'name'  => 'suggestions_style_head',
						'label' => __( 'Suggestions colors', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header js-psf-search-adv-settings',
					],
					2600 => [
						'name'    => 'sug_bg_color',
						'label'   => __( 'Suggestion background', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
					2700 => [
						'name'    => 'sug_hover_color',
						'label'   => __( 'Suggestion selected', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
					2800 => [
						'name'    => 'sug_text_color',
						'label'   => __( 'Text color', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
					2900 => [
						'name'    => 'sug_highlight_color',
						'label'   => __( 'Highlight color', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
					3000 => [
						'name'    => 'sug_border_color',
						'label'   => __( 'Border color', 'psf-search' ),
						'type'    => 'color',
						'class'   => 'js-psf-search-adv-settings',
						'default' => '',
					],
				]
			),
			'psf_search_search'       => apply_filters(
				'psf_search/settings/section=search',
				[
					10  => [
						'name'  => 'search_search_head',
						'label' => __( 'Products search scope', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],
					50  => [
						'name'    => 'search_in_product_content',
						'label'   => __( 'Search in description', 'psf-search' ),
						'type'    => 'checkbox',
						'default' => 'off',
					],
					100 => [
						'name'    => 'search_in_product_excerpt',
						'label'   => __( 'Search in short description', 'psf-search' ),
						'type'    => 'checkbox',
						'default' => 'off',
					],
					150 => [
						'name'    => 'search_in_product_sku',
						'label'   => __( 'Search in SKU', 'psf-search' ),
						'type'    => 'checkbox',
						'desc'    => __(
							'searching also in variable products SKU',
							'psf-search'
						),
						'default' => 'off',
					],
					160 => [
						'name'    => 'search_in_product_global_unique_id',
						'label'   => __( 'Search in GUID', 'psf-search' ) . ' ' . Helpers::createQuestionMark( 'search_in_product_global_unique_id', __( 'Make your products searchable by their Global Unique Identifier (GTIN, UPC, EAN or ISBN).', 'psf-search' ) ),
						'type'    => 'checkbox',
						'desc'    => __(
							'searching also in variable products GUID',
							'psf-search'
						),
						'default' => 'off',
						'class'   => 'js-psf-search-adv-settings psf-search-hidden psf-search-premium-only',
					],
					200 => [
						'name'    => 'search_in_product_attributes',
						'label'   => __( 'Search in attributes', 'psf-search' ),
						'class'   => 'psf-search-premium-only',
						'type'    => 'checkbox',
						'default' => 'off',
					],
					250 => [
						'name'    => 'search_in_product_tax_product_cat',
						'label'   => __( 'Search in categories', 'psf-search' ),
						'class'   => 'js-psf-search-adv-settings psf-search-premium-only',
						'type'    => 'checkbox',
						'default' => 'off',
					],
					275 => [
						'name'    => 'search_in_product_tax_product_tag',
						'label'   => __( 'Search in tags', 'psf-search' ),
						'class'   => 'js-psf-search-adv-settings psf-search-premium-only',
						'type'    => 'checkbox',
						'default' => 'off',
					],
					300 => [
						'name'    => 'search_in_custom_fields',
						'label'   => __( 'Search in custom fields', 'psf-search' ) . ' ' . Helpers::createQuestionMark( 'search_in_custom_fields', __( 'Make your custom fields searchable from our search bar.', 'psf-search' ) . ' ' . sprintf( $readMore, $customFieldsLink ) ),
						'class'   => 'psf-search-premium-only',
						'type'    => 'text',
						'default' => '',
					],
					350 => [
						'name'    => 'exclude_out_of_stock',
						'label'   => __( 'Exclude “out of stock” products', 'psf-search' ),
						'type'    => 'checkbox',
						'default' => 'off',
					],
					400 => [
						'name'  => 'search_scope_fuzziness_head',
						'label' => __( 'Fuzziness', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],
					500 => [
						'name'  => 'search_synonyms_head',
						'label' => __( 'Synonyms', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header js-psf-search-adv-settings',
					],
					520 => [
						'name'  => 'search_synonyms',
						'label' => __( 'Synonyms', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'synonyms',
							sprintf(
								__( 'The synonyms feature allows your users to find more relevant results. If your products have alternative names and users often misspell them, consider adding synonyms. <a target="_blank" href="%s">Read more</a> about this feature.', 'psf-search' ),
								$synonymsLink
							)
						),
						'type'  => 'textarea',
						'desc'  => __(
							'Synonyms should be separated by a comma. Each new synonyms group is entered in a new line. You can use a phrase instead of a single word. <br /> <br />Sample list:<br /> <br /><span class="psf-search-synonyms-sample">sofa, couch, davenport, divan, settee<br />big, grand, great, large, outsize</span>',
							'psf-search'
						),
						'class'             => 'psf-search-settings-synonyms js-psf-search-adv-settings psf-search-premium-only',
						'sanitize_callback' => 'sanitize_textarea_field',
					],
					600 => [
						'name'  => 'filter_products_head',
						'label' => __( 'Exclude/include products', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header js-psf-search-adv-settings',
					],
					625 => [
						'name'    => 'filter_products_mode',
						'label'   => __( 'Filtering mode', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'filter_products_mode',
							__( 'Exclude the product group from the search results or allow search only among the indicated product group.', 'psf-search' ) . ' ' . sprintf( $readMore, $excludeIncludeLink ),
							'',
							'right'
						),
						'class'   => 'js-psf-search-adv-settings psf-search-premium-only',
						'type'    => 'select',
						'options' => [
							'exclude' => __( 'Exclude', 'psf-search' ),
							'include' => __( 'Include', 'psf-search' ),
						],
						'default' => 'exclude',
					],
					650 => [
						'name'    => 'filter_products_rules',
						'label'   => __( 'Filters', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'filter_products_head',
							__( 'Filters that specify the product group and taxonomy that will be affected by the above mode', 'psf-search' ),
							'',
							'right'
						),
						'type'    => 'filters_rules_plug',
						'class'   => 'js-psf-search-adv-settings psf-search-premium-only',
						'default' => '[]',
					],
				]
			),
			'psf_search_performance'  => apply_filters(
				'psf_search/settings/section=performance',
				[
					10  => [
						'name'  => 'search_engine_head',
						'label' => __( 'Speed up search!', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header',
					],

					100 => [
						'name'  => 'indexer_schedule_head',
						'label' => __( 'Scheduling indexing', 'psf-search' ),
						'type'  => 'head',
						'class' => 'psf-search-sgs-header js-psf-search-adv-settings',
					],
					110 => [
						'name'    => 'indexer_schedule',
						'label'   => __( 'Enable Scheduler', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
							'indexer-schedule',
							__(
								"In most cases, you don't need to use the scheduler because the search index updates when you edit products. If you use import tools or custom code to refresh prices or bulk add/edit products, the indexing scheduler will be helpful.",
								'psf-search'
							) . ' ' . sprintf(
								$readMore,
								$schedulerLink
							)
						),
						'type'    => 'checkbox',
						'size'    => 'small',
						'class'   => 'psf-search-options-cb-toggle js-psf-search-cbtgroup-indexer-schedule js-psf-search-adv-settings psf-search-premium-only',
						'default' => 'off',
					],
					120 => [
						'name'    => 'indexer_schedule_interval',
						'label'   => __( 'Interval', 'psf-search' ),
						'type'    => 'select',
						'class'   => 'js-psf-search-cbtgroup-indexer-schedule js-psf-search-adv-settings psf-search-premium-only',
						'options' => [
							'daily'  => __( 'daily', 'psf-search' ),
							'weekly' => __( 'weekly', 'psf-search' ),
						],
						'default' => 'weekly',
					],
					130 => [
						'name'    => 'indexer_schedule_start_time',
						'label'   => __( 'Schedule time', 'psf-search' ),
						'type'    => 'select',
						'class'   => 'js-psf-search-cbtgroup-indexer-schedule js-psf-search-adv-settings psf-search-premium-only',
						'options' => Helpers::getHours(),
						'default' => 3,
					],
				]
			),
		];

		$fuzzinesText1 = '<strong>' . __( 'Increases sales conversions.', 'psf-search' ) . '</strong>';
		$fuzzinesText2 = sprintf(
			__(
				'Returns suggestions based on likely relevance, even though a search keyword may not exactly match. E.g if you type “ipho<b>m</b>e” you get the same results as for “iphone”. <a target="_blank" href="%s">Read more</a> about the fuzzy search feature.',
				'psf-search'
			),
			$fuzzySearchLink
		);



		// Custom fields option
		$settingsFields['psf_search_search'][300] = [
			'name'                 => 'search_in_custom_fields',
			'label'                => __( 'Search in custom fields', 'psf-search' ) . ' ' . Helpers::createQuestionMark( 'search_in_custom_fields', __( 'Make your custom fields searchable from our search bar.', 'psf-search' ) . ' ' . sprintf( $readMore, $customFieldsLink ) ),
			'type'                 => 'selectize',
			'options'              => [],
			'sanitize_callback'    => [
				'\PsfSearch\Admin\SettingsAPI',
				'sanitize_selectize_custom_fields__premium_only',
			],
			'desc'                 => __( 'select the custom fields you want to add to the search scope', 'psf-search' ),
			'disabled_before_init' => true,
		];

		// Fuzzy search option
		$settingsFields['psf_search_search'][450] = [
			'name'    => 'fuzziness_enabled',
			'label'   => __( 'Fuzzy matching', 'psf-search' ) . ' ' . Helpers::createQuestionMark( 'fuzzy_search', $fuzzinesText2 ),
			'class'   => 'psf-search-premium-only',
			'type'    => 'select',
			'options' => [
				'off'    => __( '-- Disabled', 'psf-search' ),
				'soft'   => __( 'Soft', 'psf-search' ),
				'normal' => __( 'Normal', 'psf-search' ),
				'hard'   => __( 'Hard', 'psf-search' ),
			],
			'default' => 'normal',
		];

		// Exclude/include products feature
		$settingsFields['psf_search_search'][600] = [
			'name'  => 'filter_products_head',
			'label' => __( 'Exclude/include products', 'psf-search' ),
			'type'  => 'head',
			'class' => 'psf-search-sgs-header js-psf-search-adv-settings',
		];
		$settingsFields['psf_search_search'][625] = [
			'name'    => 'filter_products_mode',
			'label'   => __( 'Filtering mode', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
				'filter_products_mode',
				__( 'Exclude the product group from the search results or allow search only among the indicated product group.', 'psf-search' ) . ' ' . sprintf( $readMore, $excludeIncludeLink ),
				'',
				'right'
			),
			'class'   => 'js-psf-search-adv-settings',
			'type'    => 'select',
			'options' => [
				'exclude' => __( 'Exclude', 'psf-search' ),
				'include' => __( 'Include', 'psf-search' ),
			],
			'default' => 'exclude',
		];
		$settingsFields['psf_search_search'][650] = [
			'name'              => 'filter_products_rules',
			'label'             => __( 'Filters', 'psf-search' ) . ' ' . Helpers::createQuestionMark(
				'filter_products_rules',
				__( 'Filters that specify the product group and taxonomy that will be affected by the above mode', 'psf-search' ),
				'',
				'right'
			),
			'type'              => 'filters_rules__premium_only',
			'sanitize_callback' => [ '\PsfSearch\Admin\SettingsAPI', 'sanitize_filters_rules__premium_only' ],
			'class'             => 'js-psf-search-adv-settings',
			'default'           => '[]',
		];

		// Search in SKU - tooltip only for Pro version.
		$settingsFields['psf_search_search'][150]['label'] .= ' ' .
			Helpers::createQuestionMark(
				'search_in_product_sku',
				sprintf(
					'%s %s',
					__( 'In the autocomplete results, a variation may appear when there\'s an exact SKU match.', 'psf-search' ),
					sprintf( $readMore, $searchBySkuLink )
				)
			);

		// Search in GUID.
		$settingsFields['psf_search_search'][160]['label'] =
			__( 'Search in GUID', 'psf-search' ) . ' ' .
			Helpers::createQuestionMark(
				'search_in_product_global_unique_id',
				__(
					'Make your products searchable by their Global Unique Identifier (GTIN, UPC, EAN or ISBN).',
					'psf-search'
				) . '<br />' .
				__(
					'In the autocomplete results, a variation may appear when there\'s an exact GUID match.',
					'psf-search'
				) . ' ' .
				sprintf( $readMore, $searchByGuidLink )
			);

		// Indexer feature
		$settingsFields['psf_search_performance'][11] = [
			'name'  => 'search_engine_build',
			'label' => __( 'Index status', 'psf-search' ),
			'type'  => 'desc',
			'desc'  => Helpers::isSettingsPage() ? Builder::renderIndexingStatus() : '',
			'class' => 'psf-search-premium-only psf-search-opt-tntsearch',
		];

		// Show next task description
		$settingsFields['psf_search_performance'][110]['desc'] = Helpers::isSettingsPage() ? Scheduler::nextTaskDescription() : '';

		// Remove promo box with list of Pro features
		unset( $settingsFields['psf_search_performance'][0] );

		// Show "Search in GUID" option if there are products with these values.
		if ( Helpers::isSettingsPage() && Helpers::productsUseGlobalUniqueId() ) {
			$settingsFields['psf_search_search'][160]['class'] = str_replace( 'psf-search-hidden', '', $settingsFields['psf_search_search'][160]['class'] );
		}


		$settingsFields = apply_filters( 'psf_search/settings', $settingsFields );

		// Set defaults
		foreach ( $settingsFields as $sections ) {
			foreach ( $sections as $option ) {
				if ( ! empty( $option['name'] ) ) {
					$this->defaults[ $option['name'] ] = isset( $option['default'] ) ? $option['default'] : '';
				}
			}
		}

		foreach ( $settingsFields as $key => $sections ) {
			ksort( $settingsFields[ $key ] );
		}

		return $settingsFields;
	}

	/*
	 * Option value
	 *
	 * @param string $option_key
	 * @param string $default default value if option not exist
	 *
	 * @return string
	 */

	public function getOption( $option_key, $default = '' ) {

		$value = '';

		if ( is_string( $option_key ) && ! empty( $option_key ) ) {

			if ( ! empty( $this->settingsCache ) ) {
				$settings = $this->settingsCache;
			} else {
				$settings = get_option( $this->settingSlug );
			}

			if ( ! empty( $settings ) && is_array( $settings ) ) {

				$this->settingsCache = $settings;

				if ( array_key_exists( $option_key, $settings ) ) {
					$value = $settings[ $option_key ];
				} else {

					// Catch default
					if ( empty( $default ) ) {
						foreach ( $this->defaults as $key => $defaultValue ) {
							if ( $key === $option_key ) {
								$value = $defaultValue;
							}
						}
					}
				}
			}
		}

		if ( $value === '' && ! empty( $default ) ) {
			$value = $default;
		}

		$value = apply_filters( 'psf_search/settings/load_value', $value, $option_key );
		$value = apply_filters( 'psf_search/settings/load_value/key=' . $option_key, $value );

		return $value;
	}

	/**
	 * Update option
	 *
	 * @param string $optionKey
	 * @param string $value
	 *
	 * @return bool
	 */
	public function updateOpt( $optionKey, $value = '' ) {

		$updated = false;

		if ( is_string( $optionKey ) && ! empty( $optionKey ) ) {

			$settings = get_option( $this->settingSlug );

			$value = apply_filters( 'psf_search/settings/update_value', $value, $optionKey );
			$value = apply_filters( 'psf_search/settings/update_value/key=' . $optionKey, $value );

			$canUpdate = false;
			if ( array_key_exists( $optionKey, $this->defaults ) ) {
				$settings[ $optionKey ] = $value;
				$canUpdate              = true;
			}

			if ( $canUpdate ) {
				$updated = update_option( $this->settingSlug, $settings );
				if ( $updated ) {
					$this->settingsCache = [];
					do_action( 'psf_search/settings/option_updated', $optionKey, $value );
				}
			}
		}

		return $updated;
	}

	/**
	 * Handles output of the settings
	 */
	public static function output() {

		$settings = PSF_SEARCH()->settings->settingsApi;

		include_once PSF_SEARCH_DIR . 'partials/admin/settings.php';
	}

	/**
	 * Restore default option value
	 *
	 * @param mixed $value
	 * @param mixed $default
	 * @param array $option
	 *
	 * @return mixed
	 */
	public function restoreDefaultValueForFreePlan( $value, $default, $option ) {

		return $value;
	}

	/**
	 * Check if user can see advanced settings
	 *
	 * @return bool
	 */
	public function canSeeAdvSettings() {

		$canSee = false;

		if ( is_bool( $this->canSeeAdvSettings ) ) {
			$canSee = $this->canSeeAdvSettings;
		} else {

			$settings = get_option( 'psf_search_settings_show_advanced' );

			if ( ! empty( $settings ) ) {

				if ( $settings === 'on' ) {
					$canSee = true;
				} elseif ( $settings === 'off' ) {
					$canSee = false;
				}

				$this->canSeeAdvSettings = $canSee;
			}
		}

		return $canSee;
	}

	/**
	 * Toggle visibility of advanced settings
	 * Ajax endpoint
	 *
	 * @return void
	 */
	public function toggleAdvancedSettings() {
		if ( ! current_user_can( Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		check_ajax_referer( 'psf_search_advanced_options_switch' );

		$show = ! empty( $_GET['adv_settings_value'] ) && $_GET['adv_settings_value'] === 'show' ? 'on' : 'off';

		update_option( 'psf_search_settings_show_advanced', $show );

		wp_send_json_success();
	}

	/**
	 * List searchable custom fields
	 * Ajax endpoint
	 *
	 * @return void
	 */
	public function listSearchableCustomFields__premium_only() {
		if ( ! current_user_can( Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		check_ajax_referer( 'psf_search_get_custom_fields' );

		wp_send_json_success( Helpers::getSearchableCustomFields() );
	}

	/**
	 * List terms for filters
	 * Ajax endpoint
	 *
	 * @return void
	 */
	public function searchTermsForFilter__premium_only() {
		if ( ! current_user_can( Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options' ) ) {
			wp_die( - 1, 403 );
		}

		check_ajax_referer( 'psf_search_get_filter_terms' );

		$query = ! empty( $_POST['query'] ) ? wc_clean( wp_unslash( $_POST['query'] ) ) : '';
		$type  = ! empty( $_POST['type'] ) ? wc_clean( wp_unslash( $_POST['type'] ) ) : '';

		// Attribute taxonomy has form: "pa:X"
		if ( strpos( $type, 'pa:' ) !== false ) {
			$type = wc_attribute_taxonomy_name_by_id( (int) str_replace( 'pa:', '', $type ) );
		}

		if ( empty( $type ) ) {
			wp_send_json_success( [] );
		}

		$args = [
			'hide_empty' => false,
			'number'     => 100,
			'taxonomy'   => $type,
		];
		if ( ! empty( $query ) ) {
			$args['search'] = $query;
		}

		if ( Multilingual::isMultilingual() ) {
			$terms = Multilingual::getTermsInLang( $args );
		} else {
			$terms = get_terms( $args );
		}

		if ( empty( $terms ) ) {
			wp_send_json_success( [] );
		}

		$list = array_map(
			function ( $term ) {
				return [
					'key'   => $term->term_id,
					'label' => $term->name,
				];
			},
			$terms
		);

		wp_send_json_success( array_values( $list ) );
	}

	/**
	 * Check if a option is premium
	 *
	 * @param array $option
	 *
	 * @return bool
	 */
	public static function isOptionPremium( $option ) {
		$is_premium = false;
		if ( ! empty( $option['class'] ) && strpos( $option['class'], 'psf-search-premium-only' ) !== false ) {
			$is_premium = true;
		}

		return $is_premium;
	}


	/**
	 * Force values of some settings if they depend on other settings
	 *
	 * @return void
	 */
	private function dependentOptions() {
		add_filter(
			'psf_search/settings/section=form',
			function ( $settings ) {
				$text                   = __( "You have selected the <b>Appearance -> Style -> Pirx</b> option. Pirx style forces a submit button to be enabled. You can find this option a few rows below. That's why this option is blocked.", 'psf-search' );
				$settings[400]['label'] = Helpers::createOverrideTooltip( 'ovtt-pirx-submit-button', '<p>' . $text . '</p>' ) . $settings[400]['label'];

				return $settings;
			}
		);

		// Pirx or Pirx compact style - force options for submit button.
		// Mark that the value of the option "mobile overlay" is forced.
		if ( in_array( $this->getOption( 'search_style' ), [ 'pirx', 'pirx-compact' ] ) ) {

			// Submit button
			add_filter(
				'psf_search/settings/load_value/key=show_submit_button',
				function () {
					return 'on';
				}
			);

			add_filter(
				'psf_search/settings/section=form',
				function ( $settings ) {
					$settings[400]['disabled'] = true;

					return $settings;
				}
			);

			// Value of submit button
			add_filter(
				'psf_search/settings/load_value/key=search_submit_text',
				function () {
					return '';
				}
			);

			add_filter(
				'psf_search/settings/section=form',
				function ( $settings ) {
					$settings[500]['disabled'] = true;
					$settings[500]['class']    = $settings[500]['class'] . ' psf-search-hidden';

					return $settings;
				}
			);

			// Submit background color
			add_filter(
				'psf_search/settings/load_value/key=bg_submit_color',
				function () {
					return '';
				}
			);

			add_filter(
				'psf_search/settings/section=form',
				function ( $settings ) {
					$settings[510]['disabled'] = true;
					$settings[510]['class']    = $settings[510]['class'] . ' psf-search-hidden';

					return $settings;
				}
			);
		}
	}

	/**
	 * Clear settings cache
	 */
	public function clearCache() {
		$this->settingsCache = [];
	}
}
