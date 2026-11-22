<?php

use PsfSearch\Helpers;
use PsfSearch\Multilingual;

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

$layout = Helpers::getLayoutSettings();

$hasSubmit    = isset( $args['submit_btn'] ) ? $args['submit_btn'] : PSF_SEARCH()->settings->getOption( 'show_submit_button' );
$submitText   = isset( $args['submit_text'] ) ? $args['submit_text'] : Helpers::getLabel( 'submit' );
$uniqueID     = ++ PSF_SEARCH()->searchInstances;
$layoutType   = ! empty( $args['layout'] ) ? $args['layout'] : $layout->layout;
$iconType     = ! empty( $args['icon'] ) ? $args['icon'] : $layout->icon;
$isAMP        = Helpers::isAMPEndpoint();
$isCustomStyle = ( $args['style'] ?? PSF_SEARCH()->settings->getOption( 'search_style' ) ) === \PsfSearch\Appearance::STYLE;
$iconColor    = ! empty( $args['icon_color'] ) ? $args['icon_color'] : '';
$customParams = apply_filters( 'psf_search/search_bar/custom_params', [], PSF_SEARCH()->searchInstances );

if ( ! empty( $args['mobile_overlay'] ) && ! empty( $args['mobile_overlay_breakpoint'] ) ) {
	$customParams['mobile_overlay_breakpoint'] = absint( $args['mobile_overlay_breakpoint'] );
}

if ( ! empty( $args['layout_breakpoint'] ) && ! empty( $args['layout_breakpoint'] ) ) {
	$customParams['layout_breakpoint'] = absint( $args['layout_breakpoint'] );
}

?>
<div <?php echo $isAMP ? "id='psf-search-search-wrapp{$uniqueID}'" : ''; ?> class="psf-search-search-wrapp <?php echo Helpers::searchWrappClasses( $args ); ?>">
	<?php if ( in_array( $layoutType, [ 'icon', 'icon-flexible', 'icon-flexible-inv' ] ) ) : ?>
		<?php echo Helpers::getIcon( 'preloader', 'psf-search-icon-preloader' ); ?>
		<a href="#" <?php echo $isAMP ? "on='tap:psf-search-search-wrapp{$uniqueID}.toggleClass(class=\"psf-search-layout-icon-open\")'" : ''; ?> class="psf-search-search-icon js-psf-search-search-icon-handler" aria-label="<?php _e( 'Open search bar', 'psf-search' ); ?>"><?php echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier-handler', $iconType, $iconColor ); ?></a>
		<div class="psf-search-search-icon-arrow"></div>
	<?php endif; ?>
	<form class="psf-search-search-form" role="search" action="<?php echo Helpers::searchFormAction(); ?>" method="get">
		<div class="psf-search-sf-wrapp">
			<?php echo $hasSubmit !== 'on' ? Helpers::getMagnifierIco( 'psf-search-ico-magnifier', $iconType ) : ''; ?>
			<label class="screen-reader-text"
				for="psf-search-search-input-<?php echo $uniqueID; ?>">
				<?php
				_e(
					'Products search',
					'psf-search'
				);
				?>
			</label>

			<input
				id="psf-search-search-input-<?php echo $uniqueID; ?>"
				type="search"
				class="psf-search-search-input"
				name="<?php echo Helpers::getSearchInputName(); ?>"
				value="<?php echo apply_filters( 'psf_search/search_bar/value', get_search_query(), PSF_SEARCH()->searchInstances ); ?>"
				placeholder="<?php echo esc_attr( Helpers::getLabel( 'search_placeholder' ) ); ?>"
				autocomplete="off"
				<?php echo ! empty( $customParams ) ? ' data-custom-params="' . htmlspecialchars( json_encode( (object) $customParams ) ) . '"' : ''; ?>
			/>
			<div class="psf-search-preloader"></div>

			<div class="psf-search-voice-search"></div>

			<?php if ( $hasSubmit === 'on' && $isCustomStyle ) : ?>
				<button type="submit"
						aria-label="<?php echo esc_attr( empty( $submitText ) ? __( 'Search', 'psf-search' ) : $submitText ); ?>"
						class="psf-search-search-submit"><span class="psf-search-btn-ico"><?php echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier', $iconType ); ?></span><span class="psf-search-btn-txt"><?php echo esc_html( empty( $submitText ) ? __( 'Search', 'psf-search' ) : $submitText ); ?></span></button>
			<?php elseif ( $hasSubmit === 'on' ) : ?>
				<button type="submit"
						aria-label="<?php echo empty( $submitText ) ? __( 'Search', 'psf-search' ) : esc_attr( $submitText ); ?>"
						class="psf-search-search-submit"><?php echo empty( $submitText ) ? Helpers::getMagnifierIco( 'psf-search-ico-magnifier', $iconType ) : esc_html( $submitText ); ?></button>
			<?php endif; ?>

			<input type="hidden" name="post_type" value="product"/>
			<input type="hidden" name="psf_search" value="1"/>

			<?php if ( Multilingual::isWPML() ) : ?>
				<input type="hidden" name="lang" value="<?php echo esc_attr( Multilingual::getCurrentLanguage() ); ?>"/>
			<?php endif ?>

			<?php do_action( 'psf_search/form' ); ?>
		</div>
	</form>
</div>
