<?php

use PsfSearch\Helpers;
use PsfSearch\Multilingual;

// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

$layout = Helpers::getLayoutSettings();

$hasSubmit    = isset( $args['submit_btn'] ) ? $args['submit_btn'] : PSFS()->settings->getOption( 'show_submit_button' );
$submitText   = isset( $args['submit_text'] ) ? $args['submit_text'] : Helpers::getLabel( 'submit' );
$uniqueID     = ++ PSFS()->searchInstances;
$layoutType   = ! empty( $args['layout'] ) ? $args['layout'] : $layout->layout;
$iconType     = ! empty( $args['icon'] ) ? $args['icon'] : $layout->icon;
$isAMP        = Helpers::isAMPEndpoint();
$iconColor    = ! empty( $args['icon_color'] ) ? $args['icon_color'] : '';
$customParams = apply_filters( 'psfs/search_bar/custom_params', [], PSFS()->searchInstances );

if ( ! empty( $args['mobile_overlay'] ) && ! empty( $args['mobile_overlay_breakpoint'] ) ) {
	$customParams['mobile_overlay_breakpoint'] = absint( $args['mobile_overlay_breakpoint'] );
}

if ( ! empty( $args['layout_breakpoint'] ) && ! empty( $args['layout_breakpoint'] ) ) {
	$customParams['layout_breakpoint'] = absint( $args['layout_breakpoint'] );
}

?>
<div <?php echo $isAMP ? "id='psfs-search-wrapp{$uniqueID}'" : ''; ?> class="psfs-search-wrapp <?php echo Helpers::searchWrappClasses( $args ); ?>">
	<?php if ( in_array( $layoutType, [ 'icon', 'icon-flexible', 'icon-flexible-inv' ] ) ) : ?>
		<?php echo Helpers::getIcon( 'preloader', 'psfs-icon-preloader' ); ?>
		<a href="#" <?php echo $isAMP ? "on='tap:psfs-search-wrapp{$uniqueID}.toggleClass(class=\"psfs-layout-icon-open\")'" : ''; ?> class="psfs-search-icon js-psfs-search-icon-handler" aria-label="<?php _e( 'Open search bar', 'psf-search' ); ?>"><?php echo Helpers::getMagnifierIco( 'psfs-ico-magnifier-handler', $iconType, $iconColor ); ?></a>
		<div class="psfs-search-icon-arrow"></div>
	<?php endif; ?>
	<form class="psfs-search-form" role="search" action="<?php echo Helpers::searchFormAction(); ?>" method="get">
		<div class="psfs-sf-wrapp">
			<?php echo $hasSubmit !== 'on' ? Helpers::getMagnifierIco( 'psfs-ico-magnifier', $iconType ) : ''; ?>
			<label class="screen-reader-text"
				for="psfs-search-input-<?php echo $uniqueID; ?>">
				<?php
				_e(
					'Products search',
					'psf-search'
				);
				?>
			</label>

			<input
				id="psfs-search-input-<?php echo $uniqueID; ?>"
				type="search"
				class="psfs-search-input"
				name="<?php echo Helpers::getSearchInputName(); ?>"
				value="<?php echo apply_filters( 'psfs/search_bar/value', get_search_query(), PSFS()->searchInstances ); ?>"
				placeholder="<?php echo esc_attr( Helpers::getLabel( 'search_placeholder' ) ); ?>"
				autocomplete="off"
				<?php echo ! empty( $customParams ) ? ' data-custom-params="' . htmlspecialchars( json_encode( (object) $customParams ) ) . '"' : ''; ?>
			/>
			<div class="psfs-preloader"></div>

			<div class="psfs-voice-search"></div>

			<?php if ( $hasSubmit === 'on' ) : ?>
				<button type="submit"
						aria-label="<?php echo empty( $submitText ) ? __( 'Search', 'psf-search' ) : esc_attr( $submitText ); ?>"
						class="psfs-search-submit"><?php echo empty( $submitText ) ? Helpers::getMagnifierIco( 'psfs-ico-magnifier', $iconType ) : esc_html( $submitText ); ?></button>
			<?php endif; ?>

			<input type="hidden" name="post_type" value="product"/>
			<input type="hidden" name="psfs" value="1"/>

			<?php if ( Multilingual::isWPML() ) : ?>
				<input type="hidden" name="lang" value="<?php echo esc_attr( Multilingual::getCurrentLanguage() ); ?>"/>
			<?php endif ?>

			<?php do_action( 'psfs/form' ); ?>
		</div>
	</form>
</div>
