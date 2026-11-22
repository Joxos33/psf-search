<?php
/**
 * Exit if accessed directly.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'WC_Widget' ) ) {

	add_action(
		'widgets_init',
		function () {
			register_widget( 'PSFS_Search_Widget' );
		}
	);

	class PSFS_Search_Widget extends WC_Widget {

		/**
		 * Constructor
		 */
		public function __construct() {

			$this->widget_cssclass    = 'woocommerce psfs-widget';
			$this->widget_description = __( 'AJAX (live) search form for WooCommerce', 'psf-search' );
			$this->widget_id          = 'psfs_ajax_search';
			$this->widget_name        = __( 'PSF Search bar', 'psf-search' );
			$this->settings           = [
				'title'          => [
					'type'  => 'text',
					'std'   => '',
					'label' => __( 'Title', 'psf-search' ),
				],
				'layout'         => [
					'type'    => 'select',
					'std'     => 'default',
					'options' => [
						'default'           => __( 'Default', 'psf-search' ),
						'classic'           => __( 'Search bar', 'psf-search' ),
						'icon'              => __( 'Search icon', 'psf-search' ),
						'icon-flexible'     => __( 'Icon on mobile, search bar on desktop', 'psf-search' ),
						'icon-flexible-inv' => __( 'Icon on desktop, search bar on mobile', 'psf-search' ),
					],
					'label'   => __( 'Layout', 'psf-search' ),
				],
				'mobile_overlay' => [
					'type'  => 'checkbox',
					'std'   => '',
					'label' => __( 'Overlay on mobile', 'psf-search' ),
				],
			];

			parent::__construct();
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			ob_start();

			$this->widget_start( $args, $instance );
			$params = '';

			// Layout

			if ( ! empty( $instance['layout'] ) && in_array( $instance['layout'], [ 'classic', 'icon', 'icon-flexible', 'icon-flexible-inv' ] ) ) {
				$params .= ' layout="' . $instance['layout'] . '"';
			}

			// Overlay on mobile
			if ( ! empty( $instance['mobile_overlay'] ) && $instance['mobile_overlay'] === 'on' ) {
				$params .= ' mobile_overlay="1"';
			}

			echo do_shortcode( '[psfsearch' . $params . ']' );

			$this->widget_end( $args );

			$html = ob_get_clean();
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo apply_filters( 'psfs/widget/html', $html, $args, $instance );
		}
	}
}
