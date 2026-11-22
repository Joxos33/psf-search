<?php

namespace PsfSearch\Analytics;

use PsfSearch\Helpers;
use PsfSearch\Multilingual;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Widget {
	/**
	 * @var Analytics
	 */
	private $analytics;

	/**
	 * @var UserInterface
	 */
	private $ui;

	/**
	 * Constructor
	 *
	 * @param Analytics $analytics
	 * @param UserInterface $ui
	 */
	public function __construct( Analytics $analytics, UserInterface $ui ) {
		$this->analytics = $analytics;
		$this->ui        = $ui;
	}

	public function init() {
		if ( $this->analytics->isModuleEnabled() && $this->isCriticalSearchesWidgetEnabled() ) {
			if ( current_user_can( Helpers::shopManagerHasAccess() ? 'manage_woocommerce' : 'manage_options' ) ) {
				add_action( 'wp_dashboard_setup', [ $this, 'addWidget' ] );

				if ( Multilingual::isMultilingual() ) {
					add_action( 'load-index.php', [ $this, 'enqueueTabsScript' ] );
				}
			}
		}
	}

	/**
	 * Check if the Analytics widget is enabled
	 *
	 * @return bool
	 */
	public function isCriticalSearchesWidgetEnabled() {
		return PSF_SEARCH()->settings->getOption( 'analytics_critical_searches_widget_enabled', 'off' ) === 'on';
	}

	public function addWidget() {
		wp_add_dashboard_widget(
			'psfsearch_analytics_critical_searches',
			esc_html__( 'PSF Search - Search Analytics', 'psf-search' ),
			[ $this, 'render' ]
		);
	}

	public function render() {
		$data = new Data();

		$vars = [
			'days'                    => $this->ui->getExpirationInDays(),
			'critical-searches'       => [],
			'critical-searches-total' => 0,
			'settings-analytics-url'  => admin_url( 'admin.php?page=psf_search_settings#analytics' ),
			'multilingual'            => [],
		];

		if ( Multilingual::isMultilingual() ) {
			$vars['multilingual'] = [
				'current-lang' => Multilingual::getCurrentLanguage(),
				'langs'        => [],
			];
			foreach ( Multilingual::getLanguages() as $lang ) {
				$data->setLang( $lang );
				$vars['multilingual']['langs'][ $lang ] = [
					'name'                    => Multilingual::getLanguageField( $lang, 'name' ),
					'critical-searches'       => $data->getCriticalSearches( UserInterface::CRITICAL_SEARCHES_LOAD_LIMIT ),
					'critical-searches-total' => $data->getTotalCriticalSearches(),
				];
			}
		} else {
			$vars['critical-searches']       = $data->getCriticalSearches( UserInterface::CRITICAL_SEARCHES_LOAD_LIMIT );
			$vars['critical-searches-total'] = $data->getTotalCriticalSearches();
		}

		ob_start();
		?>
		<h3><strong><?php _e( 'Critical searches without result', 'psf-search' ); ?></strong></h3>
		<?php if ( ! empty( $vars['multilingual'] ) ) { ?>
			<p>
				<?php _e( 'Language', 'psf-search' ); ?>:
				<span class="psf-search-widget-tab-wrapper">
					<?php
					$next = false;
					foreach ( $vars['multilingual']['langs'] as $lang => $langData ) {
						if ( $next ) {
							echo ' | ';
						}
						printf( '<a class="psf-search-widget-tab" title="%s" href="%s">%s</a>', esc_attr( $langData['name'] ), esc_attr( '#psf-search-widget-tab-content-' . $lang ), esc_html( $langData['name'] ) );
						$next = true;
					}
					?>
				</span>
			</p>
			<?php
			foreach ( $vars['multilingual']['langs'] as $lang => $langName ) {
				?>
				<div
					class="psf-search-widget-tab-content"
					id="psf-search-widget-tab-content-<?php echo esc_attr( $lang ); ?>">
					<?php
					$this->renderTable(
						array_merge( $vars, $vars['multilingual']['langs'][ $lang ] )
					);
					?>
				</div>
				<?php
			}
		} else {
			$this->renderTable( $vars );
		}
		?>
		<p>
			<?php printf( __( 'Go to PSF Search Settings page → %s to see more.', 'psf-search' ), sprintf( '<a title="%2$s" href="%1$s">%2$s</a>', $vars['settings-analytics-url'], esc_attr__( 'Analytics tab', 'psf-search' ) ) ); ?>
		</p>
		<?php
		echo ob_get_clean();
	}

	private function renderTable( $vars ) {
		if ( ! empty( $vars['critical-searches'] ) ) {
			?>
			<p>
				<?php
				// TODO
				//phpcs:ignore WordPress.WP.I18n.MismatchedPlaceholders,WordPress.WP.I18n.MissingSingularPlaceholder
				printf( _n( 'The PSF Search analyzer found <b>1 critical search phrase</b>.', 'The PSF Search analyzer found <b>%d critical search phrases</b>.', $vars['critical-searches-total'], 'psf-search' ), $vars['critical-searches-total'] );
				echo ' ';
				//phpcs:ignore WordPress.WP.I18n.MismatchedPlaceholders,WordPress.WP.I18n.MissingSingularPlaceholder
				printf( _n( 'These phrases have been typed by users over the last 1 day.', 'These phrases have been typed by users over the last %d days.', $vars['days'], 'psf-search' ), $vars['days'] );
				echo ' ';
				_e( "These phrases don`t return any search results. It's time to fix it.", 'psf-search' );
				?>
			</p>
			<table class="widefat fixed">
				<thead>
				<tr>
					<th>#</th>
					<th><?php _e( 'Phrase', 'psf-search' ); ?></th>
					<th><?php _e( 'Repetitions', 'psf-search' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				$i = 1;
				foreach ( $vars['critical-searches'] as $row ) {
					?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo esc_html( $row['phrase'] ); ?></td>
						<td><?php echo esc_html( $row['qty'] ); ?></td>
					</tr>
					<?php
					$i ++;
				}
				?>
				</tbody>
			</table>
			<?php
		} else {
			?>
			<p>
				<?php printf( __( "Fantastic! The PSF Search analyzer hasn't found any critical search phrases for the last %d days.", 'psf-search' ), $vars['days'] ); ?>
			</p>
			<?php
		}
	}

	public function enqueueTabsScript() {
		wp_enqueue_style( 'jquery' );
		ob_start();
		?>
		<script>
			(function ($) {
					$(document).ready(function () {
						const tabs = $('#psfsearch_analytics_critical_searches .psf-search-widget-tab');
						tabs.on('click', function (event) {
							event.preventDefault();

							// Never pass the attribute value to $(), it would be parsed as HTML.
							const href = $(this).attr('href');
							if (typeof href !== 'string' || !/^#psf-search-widget-tab-content-[A-Za-z0-9_-]+$/.test(href)) {
								return;
							}

							tabs.filter('.psf-search-widget-tab-active').removeClass('psf-search-widget-tab-active');
							$(this).addClass('psf-search-widget-tab-active');
							$('#psfsearch_analytics_critical_searches .psf-search-widget-tab-content').hide();
							$(document.getElementById(href.slice(1))).show();
						});

						if (tabs.length > 0) {
							$(tabs[0]).trigger('click');
						}
					});
				}(jQuery)
			);
		</script>
		<?php
		$script = str_replace( [ '<script>', '</script>' ], [ '', '' ], ob_get_clean() );
		wp_add_inline_script( 'jquery', $script );
	}
}
