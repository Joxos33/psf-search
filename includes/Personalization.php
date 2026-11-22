<?php

namespace PsfSearch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Personalization {

	public function __construct() {
		add_action( 'wp_head', [ $this, 'printStyle' ] );

		if ( is_admin() ) {
			if ( apply_filters( 'psf_search/personalization/disable_inline_styles_in_admin', false ) ) {
				return;
			}

			add_action(
				'admin_enqueue_scripts',
				function () {
					// Register personalization styles used in block editor.
					wp_register_style( 'psf-search-style-personalization', false, [], true, true );
					wp_add_inline_style( 'psf-search-style-personalization', $this->getStyles() );
				}
			);
		}
	}

	/**
	 * Add personalized CSS
	 *
	 * @return void
	 */
	public function printStyle() {
		if ( apply_filters( 'psf_search/personalization/disable_inline_styles', false ) ) {
			return;
		}
		?>
		<style>
			<?php echo $this->getStyles(); ?>
		</style>
		<?php
	}

	/**
	 * Get personalized CSS (without <style></style>)
	 *
	 * @param bool $minified Whether styles should be minified.
	 *
	 * @return string
	 */
	public function getStyles( $minified = true ) {
		// Search form
		$show_submit             = PSF_SEARCH()->settings->getOption( 'show_submit_button' );
		$bg_input_underlay_color = PSF_SEARCH()->settings->getOption( 'bg_input_underlay_color' ); // Pirx
		$bg_search_input         = PSF_SEARCH()->settings->getOption( 'bg_input_color' );
		$text_input_color        = PSF_SEARCH()->settings->getOption( 'text_input_color' );
		$border_input_color      = PSF_SEARCH()->settings->getOption( 'border_input_color' );
		$bg_submit_color         = PSF_SEARCH()->settings->getOption( 'bg_submit_color' );
		$text_submit_color       = PSF_SEARCH()->settings->getOption( 'text_submit_color' );

		// Suggestions
		$sug_hover_color     = PSF_SEARCH()->settings->getOption( 'sug_hover_color' );
		$sug_highlight_color = PSF_SEARCH()->settings->getOption( 'sug_highlight_color' );
		$sug_text_color      = PSF_SEARCH()->settings->getOption( 'sug_text_color' );
		$sug_bg_color        = PSF_SEARCH()->settings->getOption( 'sug_bg_color' );
		$sug_border_color    = PSF_SEARCH()->settings->getOption( 'sug_border_color' );

		$preloader_url = trim( PSF_SEARCH()->settings->getOption( 'preloader_url' ) );

		$max_form_width = absint( PSF_SEARCH()->settings->getOption( 'max_form_width' ) );

		$search_icon_color = PSF_SEARCH()->settings->getOption( 'search_icon_color' );

		ob_start();
		?>
		.psf-search-ico-magnifier,
		.psf-search-ico-magnifier-handler {
			max-width: 20px;
		}

		.psf-search-search-wrapp {
		<?php
		if ( ! empty( $max_form_width ) ) :
			?>
			max-width: <?php echo $max_form_width; ?>px;
		<?php endif; ?>
		}

		<?php if ( ! empty( $bg_input_underlay_color ) ) : ?>
			.psf-search-style-pirx .psf-search-sf-wrapp {background-color: <?php echo sanitize_text_field( $bg_input_underlay_color ); ?>;}
		<?php endif; ?>

		<?php if ( ! empty( $bg_search_input ) || ! empty( $text_input_color ) || ! empty( $border_input_color ) ) : ?>
		.psf-search-search-wrapp .psf-search-sf-wrapp input[type="search"].psf-search-search-input,
		.psf-search-search-wrapp .psf-search-sf-wrapp input[type="search"].psf-search-search-input:hover,
		.psf-search-search-wrapp .psf-search-sf-wrapp input[type="search"].psf-search-search-input:focus {
			<?php echo ! empty( $bg_search_input ) ? 'background-color:' . sanitize_text_field( $bg_search_input ) . ';' : ''; ?><?php echo ! empty( $text_input_color ) ? 'color:' . sanitize_text_field( $text_input_color ) . ';' : ''; ?><?php echo ! empty( $border_input_color ) ? 'border-color:' . sanitize_text_field( $border_input_color ) . ';' : ''; ?>
		}

			<?php if ( ! empty( $text_input_color ) ) : ?>
		.psf-search-sf-wrapp input[type="search"].psf-search-search-input::placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
			opacity: 0.3;
		}

		.psf-search-sf-wrapp input[type="search"].psf-search-search-input::-webkit-input-placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
			opacity: 0.3;
		}

		.psf-search-sf-wrapp input[type="search"].psf-search-search-input:-moz-placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
			opacity: 0.3;
		}

		.psf-search-sf-wrapp input[type="search"].psf-search-search-input::-moz-placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
			opacity: 0.3;
		}

		.psf-search-sf-wrapp input[type="search"].psf-search-search-input:-ms-input-placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
		}

		.psf-search-no-submit.psf-search-search-wrapp .psf-search-ico-magnifier path,
		.psf-search-search-wrapp .psf-search-close path {
			fill: <?php echo sanitize_text_field( $text_input_color ); ?>;
		}

		.psf-search-loader-circular-path {
			stroke: <?php echo sanitize_text_field( $text_input_color ); ?>;
		}

		.psf-search-preloader {
			opacity: 0.6;
		}

		<?php endif; ?>
		<?php endif; ?>

		<?php
		// Submit button
		if ( $show_submit === 'on' && ( ! empty( $bg_submit_color ) || ! empty( $text_submit_color ) ) ) :
			?>
		.psf-search-search-wrapp .psf-search-sf-wrapp .psf-search-search-submit::before {
			<?php echo ! empty( $bg_submit_color ) ? 'border-color: transparent ' . sanitize_text_field( $bg_submit_color ) . ';' : ''; ?>
		}

		.psf-search-search-wrapp .psf-search-sf-wrapp .psf-search-search-submit:hover::before,
		.psf-search-search-wrapp .psf-search-sf-wrapp .psf-search-search-submit:focus::before {
			<?php echo ! empty( $bg_submit_color ) ? 'border-right-color: ' . sanitize_text_field( $bg_submit_color ) . ';' : ''; ?>
		}

		.psf-search-search-wrapp .psf-search-sf-wrapp .psf-search-search-submit,
		.psf-search-om-bar .psf-search-om-return {
			<?php echo ! empty( $bg_submit_color ) ? 'background-color: ' . sanitize_text_field( $bg_submit_color ) . ';' : ''; ?><?php echo ! empty( $text_submit_color ) ? 'color: ' . sanitize_text_field( $text_submit_color ) . ';' : ''; ?>
		}

		.psf-search-search-wrapp .psf-search-ico-magnifier,
		.psf-search-search-wrapp .psf-search-sf-wrapp .psf-search-search-submit svg path,
		.psf-search-om-bar .psf-search-om-return svg path {
			<?php echo ! empty( $text_submit_color ) ? 'fill: ' . sanitize_text_field( $text_submit_color ) . ';' : ''; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_bg_color ) ) : ?>
		.psf-search-suggestions-wrapp,
		.psf-search-details-wrapp {
			<?php echo ! empty( $sug_bg_color ) ? 'background-color: ' . sanitize_text_field( $sug_bg_color ) . ';' : ''; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_hover_color ) ) : ?>
		.psf-search-suggestion-selected {
			<?php echo ! empty( $sug_hover_color ) ? 'background-color: ' . sanitize_text_field( $sug_hover_color ) . ';' : ''; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_text_color ) ) : ?>
		.psf-search-suggestions-wrapp *,
		.psf-search-details-wrapp *,
		.psf-search-sd,
		.psf-search-suggestion * {
			<?php echo ! empty( $sug_text_color ) ? 'color: ' . sanitize_text_field( $sug_text_color ) . ';' : ''; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_highlight_color ) ) : ?>
		.psf-search-st strong,
		.psf-search-sd strong {
			<?php echo 'color: ' . sanitize_text_field( $sug_highlight_color ) . ';'; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_border_color ) ) : ?>
		.psf-search-suggestions-wrapp,
		.psf-search-details-wrapp,
		.psf-search-suggestion,
		.psf-search-datails-title,
		.psf-search-details-more-products {
			<?php echo 'border-color: ' . sanitize_text_field( $sug_border_color ) . '!important;'; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $preloader_url ) ) : ?>
		.psf-search-inner-preloader {
			background-image: url('<?php echo esc_url( $preloader_url ); ?>');
		}

		.psf-search-inner-preloader * {
			display: none;
		}

		<?php endif; ?>

		<?php
		if ( ! empty( $search_icon_color ) ) {
			echo '.psf-search-search-icon { color: ' . sanitize_text_field( $search_icon_color ) . ';}';
			echo '.psf-search-search-icon path { fill: ' . sanitize_text_field( $search_icon_color ) . ';}';
		}

		if ( $minified ) {
			return Helpers::minifyCSS( ob_get_clean() );
		} else {
			return ob_get_clean();
		}
	}
}
