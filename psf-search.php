<?php
/**
 * Plugin Name: PSF Search
 * Description: Live product search for Puget Sound Firearms. Private fork of PSF Search 1.34.3 (GPLv2), stripped and tailored for PSF's Flatsome/WooCommerce store.
 * Version: 0.1.0
 * Author: Puget Sound Firearms
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 * Requires PHP: 8.1
 * WC requires at least: 9.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PSF_Search' ) ) {

	final class PSF_Search {


		private static $instance;
		private $tnow;
		public $engine = 'native';
		/**
		 * @var \PsfSearch\Settings
		 */
		public $settings;
		/**
		 * @var \PsfSearch\Setup
		 */
		public $setup;
		public $multilingual;
		/**
		 * @var \PsfSearch\Integrations\Themes\ThemesCompatibility
		 */
		public $themeCompatibility;
		/**
		 * @var \PsfSearch\Integrations\Brands
		 */
		public $brands;
		/**
		 * @var \PsfSearch\Engines\WordPressNative\Search
		 */
		public $nativeSearch;
		/**
		 * @var \PsfSearch\Personalization
		 */
		public $personalization;
		/**
		 * @var \PsfSearch\Engines\TNTSearchMySQL\TNTSearch
		 */
		public $tntsearchMySql;
		/**
		 * @var \PsfSearch\Search
		 */
		protected $search;

		public $tntsearchMySqlValid = false;
		public $searchInstances     = 0;

		public static function getInstance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof PSF_Search ) ) {

				self::$instance = new PSF_Search();

				self::$instance->constants();
				self::$instance->loadTextdomain();

				if ( ! self::$instance->checkRequirements() ) {
					return;
				}

				self::$instance->systemHooks();

				self::$instance->autoload();

				self::$instance->setup = new \PsfSearch\Setup();
				self::$instance->setup->init();

				self::$instance->settings = new \PsfSearch\Settings();

				self::$instance->hooks();

				new \PsfSearch\Integrations\Plugins\PluginsCompatibility();

				self::$instance->multilingual = new \PsfSearch\Multilingual();

				self::$instance->tntsearchMySqlValid = \PsfSearch\Engines\TNTSearchMySQL\Indexer\Builder::isIndexValid();

				self::$instance->engine = 'native';

				if ( self::$instance->tntsearchMySqlValid ) {
					self::$instance->engine = 'tntsearchMySql';
				}

				self::$instance->tntsearchMySql = new \PsfSearch\Engines\TNTSearchMySQL\TNTSearch();

				// WP-CLI integration.
				if ( defined( 'WP_CLI' ) && WP_CLI ) {
					WP_CLI::add_command( 'psfsearch index', '\PsfSearch\Integrations\WpCliPsfSearchIndex' );
				}

				self::$instance->nativeSearch = new \PsfSearch\Engines\WordPressNative\Search();

				// @TODO Temporary always use native WordPress DetailsBox engine.
				// Replace with details.php and shortinit in future releases
				new \PsfSearch\Engines\WordPressNative\DetailsBox();

				self::$instance->personalization = new \PsfSearch\Personalization();
				new \PsfSearch\Scripts();

				$embeddingViaMenu = new \PsfSearch\EmbeddingViaMenu();
				$embeddingViaMenu->init();

				self::$instance->themeCompatibility = new \PsfSearch\Integrations\Themes\ThemesCompatibility();

				self::$instance->brands = new \PsfSearch\Integrations\Brands();
				self::$instance->brands->init();

				\PsfSearch\Shortcode::register();

				if ( is_admin() ) {
					\PsfSearch\Admin\Install::maybeInstall();
					new \PsfSearch\Admin\AdminMenu();
				}

				if ( is_admin() || wp_doing_cron() ) {
					$analytics = new \PsfSearch\Analytics\Analytics();
					$analytics->init();
				}

				global $wp_version;

				if ( version_compare( $wp_version, '5.9' ) >= 0 ) {
					$blocks = new \PsfSearch\Blocks();
					$blocks->init();
				}
			}
			self::$instance->tnow = time();

			return self::$instance;
		}

		/**
		 * Constructor Function
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Uninstall, Activate, Deactivate hooks
		 *
		 * @return void
		 */
		private function systemHooks() {

			register_deactivation_hook(
				__FILE__,
				function () {

					\PsfSearch\Helpers::removeBatchOptions__premium_only();
					$maintenance = new \PsfSearch\Analytics\Maintenance();
					$maintenance->unschedule();
				}
			);

		}

		/**
		 * Check requirements
		 *
		 * @return bool
		 */
		private function checkRequirements() {
			if ( version_compare( PHP_VERSION, '7.0' ) < 0 ) {
				add_action( 'admin_notices', [ $this, 'adminNoticeReqPhp70' ] );

				return false;
			}

			if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_AJAX' ) ) {
				add_action( 'admin_notices', [ $this, 'adminNoticeNoWoocommerce' ] );

				return false;
			}

			if ( ! file_exists( PSFS_DIR . 'autoload.php' ) ) {
				add_action( 'admin_notices', [ $this, 'adminNoticeNoVendor' ] );

				return false;
			}

			return true;
		}

		/**
		 * Notice: Minimum required PHP version is 7.0
		 *
		 * @return void
		 */
		public function adminNoticeReqPhp70() {

			if ( defined( 'DISABLE_NAG_NOTICES' ) && DISABLE_NAG_NOTICES ) {
				return;
			}

			?>
			<div class="notice notice-error psfs-notice">
				<p>
					<?php
					printf(
						__(
							'%1$s: You need PHP version at least 7.0 to run this plugin. You are currently using PHP version %2$s.',
							'psf-search'
						),
						'<b>' . PSFS_NAME . '</b>',
						PHP_VERSION
					);
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Notice: requires WooCommerce
		 *
		 * @return void
		 */
		public function adminNoticeNoWoocommerce() {
			?>
			<div class="notice notice-error psfs-notice">
				<p>
					<?php
					printf(
						__( '%1$s is enabled but not effective. It requires %2$s in order to work.', 'psf-search' ),
						'<b>' . PSFS_FULL_NAME . '</b>',
						'<a href="https://wordpress.org/plugins/woocommerce/"  target="_blank">WooCommerce</a>'
					);
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Notice: requires /vendor
		 *
		 * @return void
		 */
		public function adminNoticeNoVendor() {
			?>
			<div class="notice notice-error psfs-notice">
				<p>
					<?php
					printf(
						__( '%s is enabled but not effective. It is missing core files. Please reinstall the plugin.', 'psf-search' ),
						'<b>' . PSFS_FULL_NAME . '</b>'
					);
					?>
				</p>
			</div>
			<?php
		}

		/**
		 * Setup plugin constants
		 *
		 * @return void
		 */
		private function constants() {

			$v = get_file_data( __FILE__, [ 'Version' => 'Version' ], 'plugin' );

			$this->define( 'PSFS_NAME', 'PSF Search' );
			$this->define( 'PSFS_FULL_NAME', 'PSF Search' );

			$this->define( 'PSFS_VERSION', $v['Version'] );
			$this->define( 'PSFS_FILE', __FILE__ );
			$this->define( 'PSFS_DIR', plugin_dir_path( __FILE__ ) );
			$this->define( 'PSFS_URL', plugin_dir_url( __FILE__ ) );

			$this->define( 'PSFS_SETTINGS_KEY', 'psfs_settings' );

			$this->define( 'PSFS_SEARCH_ACTION', 'psfs_ajax_search' );
			$this->define( 'PSFS_RESULT_DETAILS_ACTION', 'psfs_result_details' );
			$this->define( 'PSFS_GET_PRICES_ACTION', 'psfs_get_prices' );
			$this->define( 'PSFS_SEARCH_PRO_ACTION', 'psfs_ajax_search_pro' );

			$this->define( 'PSFS_WC_AJAX_ENDPOINT', true );

		}

		/**
		 * Define constant if not already set
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 *
		 * @return void
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * PSR-4 autoload
		 *
		 * @return void
		 */
		public function autoload() {
			require_once PSFS_DIR . 'autoload.php';
			require_once PSFS_DIR . 'widget.php';
		}

		/**
		 * Actions and filters
		 *
		 * @return void
		 */
		private function hooks() {
			\PsfSearch\Engines\TNTSearchMySQL\Indexer\Readable\Database::registerTables();
			\PsfSearch\Engines\TNTSearchMySQL\Indexer\Taxonomy\Database::registerTables();
			\PsfSearch\Engines\TNTSearchMySQL\Indexer\Searchable\Database::registerTables();
			\PsfSearch\Engines\TNTSearchMySQL\Indexer\Storage::registerTables();

			add_action( 'admin_init', [ $this, 'adminScripts' ], 8 );
		}


		/**
		 * Enqueue admin sripts
		 *
		 * @return void
		 */
		public function adminScripts() {
			$min = SCRIPT_DEBUG ? '' : '.min';

			// Register CSS
			wp_register_style(
				'psfs-admin-style',
				PSFS_URL . 'assets/css/admin-style' . $min . '.css',
				[],
				PSFS_VERSION
			);
			// Register front styles for block editor
			wp_register_style( 'psfs-style', apply_filters( 'psfs/scripts/css_style_url', PSFS_URL . 'assets/css/style' . $min . '.css' ), [], PSFS_VERSION );

			// Register JS
			//phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
			wp_register_script(
				'psfs-admin-js',
				PSFS_URL . 'assets/js/admin' . $min . '.js',
				[
					'jquery',
					'wp-url',
				],
				PSFS_VERSION
			);

			if ( \PsfSearch\Helpers::isSettingsPage() ) {
				$localize = [
					'adminurl'    => admin_url( 'admin-ajax.php' ), // This adminurl is not relative.
					'labels'      => \PsfSearch\Helpers::getLabels(),
					'adminLabels' => [
						'preview' => __( 'No interaction! This is only a preview.', 'psf-search' ),
					],
					'nonces'      => [
						'build_index'             => wp_create_nonce( 'psfs_build_index' ),
						'stop_build_index'        => wp_create_nonce( 'psfs_stop_build_index' ),
						'build_index_heartbeat'   => wp_create_nonce( 'psfs_build_index_heartbeat' ),
						'advanced_options_switch' => wp_create_nonce( 'psfs_advanced_options_switch' ),
						'index_details_toggle'    => wp_create_nonce( 'psfs_index_details_toggle' ),
					],
					'images'      => [
						'admin_preloader_url' => PSFS_URL . 'assets/img/preloader.gif',
					],
					'postTypes'   => [ 'post', 'page' ],
				];
				$localize['is_premium'] = true;
				$localize['postTypes']  = $this->tntsearchMySql->postTypes->getPostTypesSlugs();
				//phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
				$localize['adminLabels']['custom_fields_loading_error'] = __( 'Error loading custom fields.', 'psf-search' ) . ' ' . '<a target="_blank" href="https://fibosearch.com/documentation/troubleshooting/unable-to-select-custom-fields-in-psfsearch-settings/">' . __( 'Learn more', 'psf-search' ) . '.</a>';
				$localize = apply_filters( 'psfs/scripts/admin/localize', $localize );
				wp_localize_script( 'psfs-admin-js', 'psfs', $localize );

				// Enqueue CSS
				wp_enqueue_style( 'psfs-admin-style' );

				if ( ! false ) {
					add_thickbox();
					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'psfs-admin-js' );
					wp_enqueue_script( 'wp-color-picker' );
					// TODO
					//phpcs:disable WordPress.WP.EnqueuedResourceParameters.NotInFooter
					wp_enqueue_script( 'psfs-admin-popper-js', PSFS_URL . 'assets/js/popper.min.js', [ 'jquery' ], PSFS_VERSION );
					wp_enqueue_script( 'psfs-admin-tooltip-js', PSFS_URL . 'assets/js/tooltip.min.js', [ 'jquery' ], PSFS_VERSION );
					wp_enqueue_style( 'psfs-style', apply_filters( 'psfs/scripts/css_style_url', PSFS_URL . 'assets/css/style' . $min . '.css' ), [], PSFS_VERSION );

					wp_enqueue_style( 'psfs-admin-selectize', PSFS_URL . 'assets/css/selectize' . $min . '.css', [], PSFS_VERSION );
					wp_enqueue_script( 'psfs-admin-selectize-js', PSFS_URL . 'assets/js/selectize.min.js', [ 'jquery' ], PSFS_VERSION );
					wp_enqueue_script( 'psfs-admin-vue', PSFS_URL . 'assets/js/vue' . $min . '.js', [], PSFS_VERSION );
					wp_enqueue_script( 'psfs-admin-vue-selectize', PSFS_URL . 'assets/js/vue2-selectize.js', [ 'psfs-admin-selectize-js' ], PSFS_VERSION );
					//phpcs:enable
				}
			}

			if ( \PsfSearch\Helpers::isCheckoutPage() ) {
				wp_enqueue_style( 'psfs-admin-style' );
			}

			if ( \PsfSearch\Helpers::isDebugPage() ) {
				wp_enqueue_style( 'psfs-admin-style' );
			}
		}

		/**
		 * Register text domain
		 *
		 * @return void
		 */
		private function loadTextdomain() {
			$lang_dir = dirname( plugin_basename( PSFS_FILE ) ) . '/languages/';
			load_plugin_textdomain( 'psf-search', false, $lang_dir );
		}

		/**
		 * @see \PsfSearch\Search
		 */
		public function searchPosts( $phrase, $args = [] ) {
			if ( is_null( $this->search ) ) {
				$this->search = new \PsfSearch\Search();
			}

			return $this->search->searchPosts( $phrase, $args );
		}

		public function getInfo( $type = '' ) {
			switch ( $type ) {
				case 'search_endpoint':
					return \PsfSearch\Helpers::getEndpointUrl( 'search' );
				case 'current_language':
					return \PsfSearch\Multilingual::isMultilingual() ? \PsfSearch\Multilingual::getCurrentLanguage() : '';
				default:
					return '';
			}
		}
	}

	// Init the plugin
	function PSFS() {
		return PSF_Search::getInstance();
	}

	add_action( 'plugins_loaded', 'PSFS', 15 );
}
