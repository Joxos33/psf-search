<?php

use PsfSearch\Helpers;

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

$submitText = Helpers::getLabel( 'submit' );
$hasSubmit  = PSF_SEARCH()->settings->getOption( 'show_submit_button' );

$labelSeeAll = Helpers::getLabel( 'show_more' );

?>
<div class="psf-search-preview js-psf-search-preview">

	<div class="js-psf-search-preview-inner psf-search-preview-inner">
		<div class="js-psf-search-preview-head psf-search-preview-head">
			<span class="psf-search-preview-header"><?php _e( 'Preview', 'psf-search' ); ?></span>
			<span
				class="psf-search-preview-subheader psf-search-preview-subheader__sb"><?php _e( 'Search bar', 'psf-search' ); ?></span>
			<span
				class="psf-search-preview-subheader psf-search-preview-subheader__ac"><?php _e( 'Autocomplete', 'psf-search' ); ?></span>
		</div>

		<div class="js-psf-search-preview-source psf-search-preview-source">

			<span class="js-psf-search-preview-device-info psf-search-preview-device-info psf-search-hidden" data-device="desktop">
				<span><?php _e( 'On mobile', 'psf-search' ); ?></span>
				<span><?php _e( 'On desktop', 'psf-search' ); ?></span>
			</span>
			<div
				class="js-psf-search-search-wrapp js-psf-search-preview-bar-example psf-search-search-wrapp <?php echo Helpers::searchWrappClasses(); ?>"
				data-psf-search-context="75c2">
				<form class="psf-search-search-form" role="search" action="" method="get">
					<div class="psf-search-sf-wrapp">
						<?php echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier js-psf-search-ico-magnifier-solaris' ); ?>
						<?php echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier js-psf-search-ico-magnifier-pirx', 'magnifier-pirx' ); ?>

						<label
							class="screen-reader-text"><?php _e( 'Products search', 'psf-search' ); ?></label>

						<input
							type="search"
							class="js-psf-search-search-input psf-search-search-input"
							name="<?php echo esc_attr( Helpers::getSearchInputName() ); ?>"
							value="<?php echo esc_attr( get_search_query() ); ?>"
							autocomplete="off"
							placeholder="<?php echo esc_attr( Helpers::getLabel( 'search_placeholder' ) ); ?>"
						/>
						<div class="psf-search-preloader"></div>

						<button type="submit" class="js-psf-search-search-submit psf-search-search-submit">
							<?php
							echo '<span class="js-psf-search-search-submit-l">' . esc_html( $submitText ) . '</span>';
							echo '<span class="js-psf-search-search-submit-m">';
							echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier js-psf-search-ico-magnifier-solaris' );
							echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier js-psf-search-ico-magnifier-pirx', 'magnifier-pirx' );
							echo '</span>';
							?>
						</button>

						<input type="hidden" name="post_type" value="product">
						<input type="hidden" name="psf_search" value="1">

						<input type="hidden" name="lang" value="en">

					</div>
				</form>
			</div>

			<span class="js-psf-search-preview-device-info psf-search-preview-device-info psf-search-hidden" data-device="mobile">
				<span><?php _e( 'On mobile', 'psf-search' ); ?></span>
				<span><?php _e( 'On desktop', 'psf-search' ); ?></span>
			</span>
			<div
				class="js-psf-search-search-wrapp psf-search-layout-icon-open js-psf-search-preview-icon-example psf-search-search-wrapp psf-search-hidden <?php echo Helpers::searchWrappClasses(); ?>"
				data-psf-search-context="75c3">
				<a
					href="#" class="psf-search-search-icon js-psf-search-search-icon-handler"><?php echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier-handler' ); ?>
				</a>
				<div class="psf-search-search-icon-arrow"></div>
				<form class="psf-search-search-form" role="search" action="" method="get">
					<div class="psf-search-sf-wrapp">
						<?php echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier js-psf-search-ico-magnifier-solaris' ); ?>
						<?php echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier js-psf-search-ico-magnifier-pirx', 'magnifier-pirx' ); ?>

						<label
							class="screen-reader-text"><?php _e( 'Products search', 'psf-search' ); ?></label>
						<input
							type="search"
							class="js-psf-search-search-input psf-search-search-input"
							name="<?php echo esc_attr( Helpers::getSearchInputName() ); ?>"
							value="<?php echo esc_attr( get_search_query() ); ?>"
							autocomplete="off"
							placeholder="<?php echo esc_attr( Helpers::getLabel( 'search_placeholder' ) ); ?>"
						/>
						<div class="psf-search-preloader"></div>

						<button type="submit" class="js-psf-search-search-submit psf-search-search-submit">
							<?php
							echo '<span class="js-psf-search-search-submit-l">' . esc_html( $submitText ) . '</span>';
							echo '<span class="js-psf-search-search-submit-m">';
							echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier js-psf-search-ico-magnifier-solaris' );
							echo Helpers::getMagnifierIco( 'psf-search-ico-magnifier js-psf-search-ico-magnifier-pirx', 'magnifier-pirx' );
							echo '</span>';
							?>
						</button>

						<input type="hidden" name="post_type" value="product">
						<input type="hidden" name="psf_search" value="1">

						<input type="hidden" name="lang" value="en">

					</div>
				</form>
			</div>

			<div class="psf-search-autocomplete">

				<div
					class="psf-search-suggestions-wrapp js-psf-search-suggestions-wrapp woocommerce psf-search-has-price psf-search-has-desc psf-search-has-sku psf-search-has-headings"
					unselectable="on">

					<div class="psf-search-suggestion psf-search-suggestion-headline-brand psf-search-suggestion-headline">
					<span class="psf-search-st">
						<?php echo Helpers::getLabel( 'tax_' . PSF_SEARCH()->brands->getBrandTaxonomy() . '_plu' ); ?>
					</span>
					</div>

					<div class="psf-search-suggestion psf-search-suggestion-brand js-psf-search-suggestion-brand">
					<span class="psf-search-si">
						<img src="<?php echo PSF_SEARCH_URL; ?>assets/img/product-preview.png">
					</span>
						<span class="psf-search-st">
						<?php _e( 'Sample brand <strong>name</strong>', 'psf-search' ); ?>
					</span>
					</div>

					<div class="psf-search-suggestion psf-search-suggestion-headline-cat psf-search-suggestion-headline">
					<span class="psf-search-st">
						<?php echo Helpers::getLabel( 'tax_product_cat_plu' ); ?>
					</span>
					</div>

					<div class="psf-search-suggestion psf-search-suggestion-cat js-psf-search-suggestion-cat">
					<span class="psf-search-si">
						<img src="<?php echo PSF_SEARCH_URL; ?>assets/img/product-preview.png">
					</span>
						<span class="psf-search-st">
						<?php _e( 'Sample category <strong>name</strong>', 'psf-search' ); ?>
					</span>
					</div>

					<div class="psf-search-suggestion psf-search-suggestion-headline-tag psf-search-suggestion-headline">
					<span class="psf-search-st">
						<?php echo Helpers::getLabel( 'tax_product_tag_plu' ); ?>
					</span>
					</div>

					<div class="psf-search-suggestion psf-search-suggestion-tag">
					<span class="psf-search-st">
						<?php _e( 'Sample tag <strong>name</strong>', 'psf-search' ); ?>
					</span>
					</div>

					<?php
					$postTypes = [ 'post', 'page' ];
					$postTypes = PSF_SEARCH()->tntsearchMySql->postTypes->getPostTypesSlugs();
					foreach ( $postTypes as $postType ) {
						$label = '';
						if ( $postType === 'post' || $postType === 'page' ) {
							$label = $postType;
						} else {
							$details = PSF_SEARCH()->tntsearchMySql->postTypes->getPostTypeDetails( $postType );
							$label   = empty( $details['labels']['singular_name'] ) ? $postType : strtolower( $details['labels']['singular_name'] );
						}
						?>
						<div
							class="psf-search-suggestion psf-search-suggestion-headline-cpt psf-search-suggestion-headline-<?php echo esc_attr( $postType ); ?> psf-search-suggestion-headline">
						<span class="psf-search-st">
						<?php echo Helpers::getLabel( 'post_type_' . $postType . '_plu' ); ?>
						</span>
						</div>

						<div
							class="psf-search-suggestion psf-search-suggestion-cpt psf-search-suggestion-<?php echo esc_attr( $postType ); ?> js-psf-search-suggestion-<?php echo esc_attr( $postType ); ?>">
						<span class="psf-search-si">
						<img src="<?php echo PSF_SEARCH_URL; ?>assets/img/product-preview.png">
						</span>
							<span class="psf-search-st">
						<?php echo sprintf( __( 'Sample %s <strong>name</strong>', 'psf-search' ), $label ); ?>
						</span>
						</div>
						<?php
					}
					?>

					<div class="psf-search-suggestion js-psf-search-suggestion-headline psf-search-suggestion-headline">
					<span class="psf-search-st">
						<?php echo Helpers::getLabel( 'product_plu' ); ?>
					</span>
					</div>

					<div class="psf-search-suggestion psf-search-suggestion-product">
					<span class="js-psf-search-si psf-search-si">
						<img src="<?php echo PSF_SEARCH_URL; ?>assets/img/product-preview.png">
					</span>
						<div class="js-psf-search-content-wrapp psf-search-content-wrapp">
						<span class="psf-search-st">
							<span
								class="psf-search-st-title"><?php _e( 'Sample product <strong>name</strong>', 'psf-search' ); ?></span>
							<span
								class="psf-search-sku js-psf-search-sku">(<?php echo Helpers::getLabel( 'sku_label' ); ?> 0001)</span>
							<span
								class="psf-search-sd js-psf-search-sd"><?php _e( 'Lorem <strong>ipsum</strong> dolor sit amet, consectetur adipiscing elit. Quisque gravida lacus nec diam porttitor pharetra. Nulla facilisi. Proin pharetra imperdiet neque, non varius.', 'psf-search' ); ?></span>
						</span>
							<span class="psf-search-sp js-psf-search-sp">
							<?php echo wc_price( 99 ); ?>
						</span>
						</div>
					</div>


					<div
						class="psf-search-suggestion js-psf-search-suggestion-more psf-search-suggestion-more psf-search-suggestion-no-border-bottom"
						data-index="7">
						<span class="psf-search-st-more"><span
								class="js-psf-search-st-more-label"><?php echo esc_html( $labelSeeAll ); ?></span> (73)</span>
					</div>

					<div class="psf-search-suggestion-nores js-psf-search-suggestion-nores psf-search-hide">
					</div>

				</div>

				<div class="psf-search-details-wrapp js-psf-search-details-wrapp woocommerce">

					<div class="psf-search-details-inner">

						<div class="psf-search-product-details">

							<a href="#">
								<div class="psf-search-details-main-image">
									<img src="<?php echo PSF_SEARCH_URL; ?>assets/img/product-preview.png"/>
								</div>
							</a>

							<div class="psf-search-details-space">

								<a class="psf-search-details-product-title" href="#">
									<?php _e( 'Sample product name', 'psf-search' ); ?>
								</a>
								<span class="psf-search-details-product-sku">0001</span>

								<div class="psf-search-pd-price">
									<?php echo wc_price( 99 ); ?>
								</div>

								<div class="psf-search-details-hr"></div>

								<div class="psf-search-pd-desc">
									<?php
									_e(
										'Lorem <strong>ipsum</strong> dolor sit amet, consectetur adipiscing elit. Quisque gravida lacus nec diam porttitor pharetra. Nulla facilisi. Proin pharetra imperdiet neque, non varius.',
										'psf-search'
									);
									?>
								</div>

								<div class="psf-search-details-hr"></div>

								<div class="psf-search-pd-addtc js-psf-search-pd-addtc">
									<form class="psf-search-pd-addtc-form" action="" method="post" enctype="multipart/form-data">
										<div class="quantity buttons_added">
											<input type="button" value="-" class="minus button is-form">
											<input
												type="number" class="input-text qty text" step="1" min="0" max="9999"
												name="js-psf-search-quantity" value="1" title="Qty" size="4"
												inputmode="numeric">
											<input type="button" value="+" class="plus button is-form">
										</div>
										<p class="product woocommerce add_to_cart_inline " style="">
											<a
												href="#" data-quantity="1"
												class="button product_type_simple add_to_cart_button ajax_add_to_cart"
												rel="nofollow"><?php _e( 'Add to cart', 'woocommerce' ); ?></a>
										</p>
									</form>
								</div>

							</div>


						</div>

					</div>

				</div>

			</div>
		</div>

	</div>

	<div class="psf-search-preview-elements psf-search-hidden">
		<div class="js-psf-search-preview-elements-close"><?php echo Helpers::getIcon( 'close' ); ?></div>
	</div>
</div>
