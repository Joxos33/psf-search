<?php
/**
 * The Template for displaying product variation info in Details Panel
 *
 * This template can be overridden by copying it to yourtheme/psfsearch/details-panel/product-variation.php.
 */

use PsfSearch\Helpers;

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

?>
<div class="psf-search-details-inner psf-search-details-inner-product">

	<?php do_action( 'psf_search/details_panel/product_variation/container_before', $vars ); ?>

	<div class="psf-search-product-details">

		<?php do_action( 'psf_search/details_panel/product_variation/image_before', $vars ); ?>
		<a href="<?php echo esc_url( $vars->link ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $vars->name ) ); ?>">
			<div class="psf-search-details-main-image">
				<img
					src="<?php echo esc_url( $vars->imageSrc ); ?>"
					<?php echo ( ! empty( $vars->imageSrcset ) && ! empty( $vars->imageSizes ) ) ? 'srcset="' . esc_attr( $vars->imageSrcset ) . '"' : ''; ?>
					<?php echo ( ! empty( $vars->imageSrcset ) && ! empty( $vars->imageSizes ) ) ? 'sizes="' . esc_attr( $vars->imageSizes ) . '"' : ''; ?>
					alt="<?php echo esc_attr( wp_strip_all_tags( $vars->name ) ); ?>"
				>
			</div>
		</a>
		<?php do_action( 'psf_search/details_panel/product_variation/image_after', $vars ); ?>

		<div class="psf-search-details-space">
			<a class="psf-search-details-product-title" href="<?php echo esc_url( $vars->link ); ?>" title="<?php echo esc_attr( wp_strip_all_tags( $vars->name ) ); ?>">
				<?php echo Helpers::secureHtmlOutput( $vars->name, 'name' ); ?>
			</a>
			<?php if ( ! empty( $vars->sku ) ) : ?>
				<span class="psf-search-details-product-sku"><?php echo Helpers::secureHtmlOutput( $vars->sku, 'sku' ); ?></span>
			<?php endif; ?>

			<?php if ( $vars->reviewCount > 0 ) : ?>

				<div class="psf-search-pd-rating">
					<?php echo Helpers::secureHtmlOutput( $vars->ratingHtml . ' <span class="psf-search-pd-review">(' . $vars->reviewCount . ')</span>', 'rating' ); ?>
				</div>

			<?php endif; ?>

			<?php do_action( 'psf_search/details_panel/product_variation/price_before', $vars ); ?>
			<div class="psf-search-pd-price">
				<?php echo Helpers::secureHtmlOutput( $vars->priceHtml, 'price' ); ?>
			</div>
			<?php do_action( 'psf_search/details_panel/product_variation/price_after', $vars ); ?>

			<?php if ( ! empty( $vars->attributes ) ) : ?>
				<div class="psf-search-details-hr"></div>
				<div class="psf-search-details-attributes">
					<?php foreach ( $vars->attributes as $attribute ) : ?>
						<div class="psf-search-details-attribute"><b><?php echo Helpers::secureHtmlOutput( $attribute['label'], 'attribute_label' ); ?></b>: <span><?php echo Helpers::secureHtmlOutput( $attribute['value'], 'attribute_value' ); ?></span></div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="psf-search-details-hr"></div>

			<?php
			if ( ! empty( $vars->stockAvailability ) ) {
				echo Helpers::secureHtmlOutput( $vars->stockAvailability, 'stock_status' );
			};
			?>

			<?php do_action( 'psf_search/details_panel/product_variation/add_to_cart_before', $vars ); ?>
			<div class="psf-search-pd-addtc js-psf-search-pd-addtc">
				<form class="psf-search-pd-addtc-form" action="" method="post" enctype="multipart/form-data">
					<?php

					if ( $vars->showQuantity ) {
						woocommerce_quantity_input(
							[
								'input_name' => 'js-psf-search-quantity',
							],
							$vars->wooObject,
							true
						);
					}

					echo WC_Shortcodes::product_add_to_cart(
						[
							'id'         => $vars->ID,
							'show_price' => false,
							'style'      => '',
						]
					);
					?>
				</form>
			</div>
			<?php do_action( 'psf_search/details_panel/product_variation/add_to_cart_after', $vars ); ?>

		</div>

	</div>

	<?php do_action( 'psf_search/details_panel/product_variation/container_after', $vars ); ?>

</div>
