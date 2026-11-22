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
			if ( apply_filters( 'psfs/personalization/disable_inline_styles_in_admin', false ) ) {
				return;
			}

			add_action(
				'admin_enqueue_scripts',
				function () {
					// Register personalization styles used in block editor.
					wp_register_style( 'psfs-style-personalization', false, [], true, true );
					wp_add_inline_style( 'psfs-style-personalization', $this->getStyles() );
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
		if ( apply_filters( 'psfs/personalization/disable_inline_styles', false ) ) {
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
		$show_submit             = PSFS()->settings->getOption( 'show_submit_button' );
		$bg_input_underlay_color = PSFS()->settings->getOption( 'bg_input_underlay_color' ); // Pirx
		$bg_search_input         = PSFS()->settings->getOption( 'bg_input_color' );
		$text_input_color        = PSFS()->settings->getOption( 'text_input_color' );
		$border_input_color      = PSFS()->settings->getOption( 'border_input_color' );
		$bg_submit_color         = PSFS()->settings->getOption( 'bg_submit_color' );
		$text_submit_color       = PSFS()->settings->getOption( 'text_submit_color' );

		// Suggestions
		$sug_hover_color     = PSFS()->settings->getOption( 'sug_hover_color' );
		$sug_highlight_color = PSFS()->settings->getOption( 'sug_highlight_color' );
		$sug_text_color      = PSFS()->settings->getOption( 'sug_text_color' );
		$sug_bg_color        = PSFS()->settings->getOption( 'sug_bg_color' );
		$sug_border_color    = PSFS()->settings->getOption( 'sug_border_color' );

		$preloader_url = trim( PSFS()->settings->getOption( 'preloader_url' ) );

		$max_form_width = absint( PSFS()->settings->getOption( 'max_form_width' ) );

		$search_icon_color = PSFS()->settings->getOption( 'search_icon_color' );

		ob_start();
		?>
		.psfs-ico-magnifier,
		.psfs-ico-magnifier-handler {
			max-width: 20px;
		}

		.psfs-search-wrapp {
		<?php
		if ( ! empty( $max_form_width ) ) :
			?>
			max-width: <?php echo $max_form_width; ?>px;
		<?php endif; ?>
		}

		<?php if ( ! empty( $bg_input_underlay_color ) ) : ?>
			.psfs-style-pirx .psfs-sf-wrapp {background-color: <?php echo sanitize_text_field( $bg_input_underlay_color ); ?>;}
		<?php endif; ?>

		<?php if ( ! empty( $bg_search_input ) || ! empty( $text_input_color ) || ! empty( $border_input_color ) ) : ?>
		.psfs-search-wrapp .psfs-sf-wrapp input[type="search"].psfs-search-input,
		.psfs-search-wrapp .psfs-sf-wrapp input[type="search"].psfs-search-input:hover,
		.psfs-search-wrapp .psfs-sf-wrapp input[type="search"].psfs-search-input:focus {
			<?php echo ! empty( $bg_search_input ) ? 'background-color:' . sanitize_text_field( $bg_search_input ) . ';' : ''; ?><?php echo ! empty( $text_input_color ) ? 'color:' . sanitize_text_field( $text_input_color ) . ';' : ''; ?><?php echo ! empty( $border_input_color ) ? 'border-color:' . sanitize_text_field( $border_input_color ) . ';' : ''; ?>
		}

			<?php if ( ! empty( $text_input_color ) ) : ?>
		.psfs-sf-wrapp input[type="search"].psfs-search-input::placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
			opacity: 0.3;
		}

		.psfs-sf-wrapp input[type="search"].psfs-search-input::-webkit-input-placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
			opacity: 0.3;
		}

		.psfs-sf-wrapp input[type="search"].psfs-search-input:-moz-placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
			opacity: 0.3;
		}

		.psfs-sf-wrapp input[type="search"].psfs-search-input::-moz-placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
			opacity: 0.3;
		}

		.psfs-sf-wrapp input[type="search"].psfs-search-input:-ms-input-placeholder {
			color: <?php echo sanitize_text_field( $text_input_color ); ?>;
		}

		.psfs-no-submit.psfs-search-wrapp .psfs-ico-magnifier path,
		.psfs-search-wrapp .psfs-close path {
			fill: <?php echo sanitize_text_field( $text_input_color ); ?>;
		}

		.psfs-loader-circular-path {
			stroke: <?php echo sanitize_text_field( $text_input_color ); ?>;
		}

		.psfs-preloader {
			opacity: 0.6;
		}

		<?php endif; ?>
		<?php endif; ?>

		<?php
		// Submit button
		if ( $show_submit === 'on' && ( ! empty( $bg_submit_color ) || ! empty( $text_submit_color ) ) ) :
			?>
		.psfs-search-wrapp .psfs-sf-wrapp .psfs-search-submit::before {
			<?php echo ! empty( $bg_submit_color ) ? 'border-color: transparent ' . sanitize_text_field( $bg_submit_color ) . ';' : ''; ?>
		}

		.psfs-search-wrapp .psfs-sf-wrapp .psfs-search-submit:hover::before,
		.psfs-search-wrapp .psfs-sf-wrapp .psfs-search-submit:focus::before {
			<?php echo ! empty( $bg_submit_color ) ? 'border-right-color: ' . sanitize_text_field( $bg_submit_color ) . ';' : ''; ?>
		}

		.psfs-search-wrapp .psfs-sf-wrapp .psfs-search-submit,
		.psfs-om-bar .psfs-om-return {
			<?php echo ! empty( $bg_submit_color ) ? 'background-color: ' . sanitize_text_field( $bg_submit_color ) . ';' : ''; ?><?php echo ! empty( $text_submit_color ) ? 'color: ' . sanitize_text_field( $text_submit_color ) . ';' : ''; ?>
		}

		.psfs-search-wrapp .psfs-ico-magnifier,
		.psfs-search-wrapp .psfs-sf-wrapp .psfs-search-submit svg path,
		.psfs-om-bar .psfs-om-return svg path {
			<?php echo ! empty( $text_submit_color ) ? 'fill: ' . sanitize_text_field( $text_submit_color ) . ';' : ''; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_bg_color ) ) : ?>
		.psfs-suggestions-wrapp,
		.psfs-details-wrapp {
			<?php echo ! empty( $sug_bg_color ) ? 'background-color: ' . sanitize_text_field( $sug_bg_color ) . ';' : ''; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_hover_color ) ) : ?>
		.psfs-suggestion-selected {
			<?php echo ! empty( $sug_hover_color ) ? 'background-color: ' . sanitize_text_field( $sug_hover_color ) . ';' : ''; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_text_color ) ) : ?>
		.psfs-suggestions-wrapp *,
		.psfs-details-wrapp *,
		.psfs-sd,
		.psfs-suggestion * {
			<?php echo ! empty( $sug_text_color ) ? 'color: ' . sanitize_text_field( $sug_text_color ) . ';' : ''; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_highlight_color ) ) : ?>
		.psfs-st strong,
		.psfs-sd strong {
			<?php echo 'color: ' . sanitize_text_field( $sug_highlight_color ) . ';'; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $sug_border_color ) ) : ?>
		.psfs-suggestions-wrapp,
		.psfs-details-wrapp,
		.psfs-suggestion,
		.psfs-datails-title,
		.psfs-details-more-products {
			<?php echo 'border-color: ' . sanitize_text_field( $sug_border_color ) . '!important;'; ?>
		}

		<?php endif; ?>

		<?php if ( ! empty( $preloader_url ) ) : ?>
		.psfs-inner-preloader {
			background-image: url('<?php echo esc_url( $preloader_url ); ?>');
		}

		.psfs-inner-preloader * {
			display: none;
		}

		<?php endif; ?>

		<?php
		if ( ! empty( $search_icon_color ) ) {
			echo '.psfs-search-icon { color: ' . sanitize_text_field( $search_icon_color ) . ';}';
			echo '.psfs-search-icon path { fill: ' . sanitize_text_field( $search_icon_color ) . ';}';
		}

		if ( $minified ) {
			return Helpers::minifyCSS( ob_get_clean() );
		} else {
			return ob_get_clean();
		}
	}
}
