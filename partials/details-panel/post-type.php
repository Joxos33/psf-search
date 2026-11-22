<?php
/**
 * The Template for displaying {postType} info in Details Panel
 *
 * This template can be overridden by copying it to yourtheme/psfsearch/details-panel/{postType}.php.
 */

use PsfSearch\Helpers;

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

?>
<div class="psf-search-details-inner psf-search-details-page psf-search-details-cpt psf-search-details-cpt-<?php echo esc_attr( $vars->postType ); ?>">

	<?php do_action( "psf_search/details_panel/{$vars->postType}/container_before", $vars ); ?>

	<div class="psf-search-product-details">

		<?php do_action( "psf_search/details_panel/{$vars->postType}/image_before", $vars ); ?>
		<a class="psf-search-pd-details-post" href="<?php echo esc_url( $vars->link ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $vars->title ) ); ?>">
			<?php if ( ! empty( $vars->imageSrc ) ) : ?>
				<div class="psf-search-details-main-image">
					<img
						src="<?php echo esc_url( $vars->imageSrc ); ?>"
						<?php echo ( ! empty( $vars->imageSrcset ) && ! empty( $vars->imageSizes ) ) ? 'srcset="' . esc_attr( $vars->imageSrcset ) . '"' : ''; ?>
						<?php echo ( ! empty( $vars->imageSrcset ) && ! empty( $vars->imageSizes ) ) ? 'sizes="' . esc_attr( $vars->imageSizes ) . '"' : ''; ?>
						alt="<?php echo esc_attr( wp_strip_all_tags( $vars->title ) ); ?>"
					>
				</div>
			<?php endif; ?>
		</a>
		<?php do_action( "psf_search/details_panel/{$vars->postType}/image_after", $vars ); ?>

		<div class="psf-search-details-space">
			<a class="psf-search-details-post-title" href="<?php echo esc_url( $vars->link ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $vars->title ) ); ?>">
				<?php echo Helpers::secureHtmlOutput( $vars->title, 'name' ); ?>
			</a>

			<?php if ( ! empty( $vars->desc ) ) : ?>
				<div class="psf-search-details-desc">
					<?php echo Helpers::secureHtmlOutput( $vars->desc, 'description' ); ?>
				</div>
			<?php endif; ?>

			<div class="psf-search-details-hr"></div>

			<a class="psf-search-product-details-readmore" href="<?php echo esc_url( $vars->link ); ?>"><?php echo Helpers::secureHtmlOutput( Helpers::getLabel( 'read_more' ), 'read_more' ); ?></a>
		</div>

	</div>

	<?php do_action( "psf_search/details_panel/{$vars->postType}/container_after", $vars ); ?>

</div>
