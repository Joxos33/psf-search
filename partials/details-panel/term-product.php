<?php
/**
 * The Template for displaying single product on the list of products from a given category, etc. in the Details Panel
 *
 * This template can be overridden by copying it to yourtheme/psfsearch/details-panel/term-product.php.
 */

use PsfSearch\Helpers;

// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

do_action( 'psfs/details_panel/term_products/product/container_before', $vars ); ?>

	<a class="psfs-tax-product-details" href="<?php echo esc_url( $vars->link ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $vars->name ) ); ?>">

		<?php do_action( 'psfs/details_panel/term_products/product/image_before', $vars ); ?>
		<div class="psfs-tpd-image">
			<img
				src="<?php echo esc_url( $vars->imageSrc ); ?>"
				<?php echo ( ! empty( $vars->imageSrcset ) && ! empty( $vars->imageSizes ) ) ? 'srcset="' . esc_attr( $vars->imageSrcset ) . '"' : ''; ?>
				<?php echo ( ! empty( $vars->imageSrcset ) && ! empty( $vars->imageSizes ) ) ? 'sizes="' . esc_attr( $vars->imageSizes ) . '"' : ''; ?>
				alt="<?php echo esc_attr( wp_strip_all_tags( $vars->name ) ); ?>"
			>
		</div>
		<?php do_action( 'psfs/details_panel/term_products/product/image_after', $vars ); ?>

		<div class="psfs-tpd-rest">

			<span class="psfs-tpd-rest-title"><?php echo Helpers::secureHtmlOutput( $vars->name, 'name' ); ?></span>

			<?php if ( $vars->reviewCount > 0 ) : ?>
				<div class="psfs-pd-rating">
					<?php echo Helpers::secureHtmlOutput( $vars->ratingHtml . ' <span class="psfs-pd-review">(' . $vars->reviewCount . ')</span>', 'rating' ); ?>
				</div>
			<?php endif; ?>

			<?php do_action( 'psfs/details_panel/term_products/product/price_before', $vars ); ?>
			<div class="psfs-tpd-price">
				<?php echo Helpers::secureHtmlOutput( $vars->priceHtml, 'price' ); ?>
			</div>
			<?php do_action( 'psfs/details_panel/term_products/product/price_before', $vars ); ?>

		</div>
	</a>

<?php
do_action( 'psfs/details_panel/term_products/product/container_after', $vars );
