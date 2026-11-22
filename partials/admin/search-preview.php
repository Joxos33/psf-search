<?php

use PsfSearch\Helpers;

// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

$submitText = Helpers::getLabel( 'submit' );
$hasSubmit  = PSFS()->settings->getOption( 'show_submit_button' );

$labelSeeAll = Helpers::getLabel( 'show_more' );

?>
<div class="psfs-preview js-psfs-preview">

	<div class="js-psfs-preview-inner psfs-preview-inner">
		<div class="js-psfs-preview-head psfs-preview-head">
			<span class="psfs-preview-header"><?php _e( 'Preview', 'psf-search' ); ?></span>
			<span
				class="psfs-preview-subheader psfs-preview-subheader__sb"><?php _e( 'Search bar', 'psf-search' ); ?></span>
			<span
				class="psfs-preview-subheader psfs-preview-subheader__ac"><?php _e( 'Autocomplete', 'psf-search' ); ?></span>
		</div>

		<div class="js-psfs-preview-source psfs-preview-source">

			<span class="js-psfs-preview-device-info psfs-preview-device-info psfs-hidden" data-device="desktop">
				<span><?php _e( 'On mobile', 'psf-search' ); ?></span>
				<span><?php _e( 'On desktop', 'psf-search' ); ?></span>
			</span>
			<div
				class="js-psfs-search-wrapp js-psfs-preview-bar-example psfs-search-wrapp <?php echo Helpers::searchWrappClasses(); ?>"
				data-psfs-context="75c2">
				<form class="psfs-search-form" role="search" action="" method="get">
					<div class="psfs-sf-wrapp">
						<?php echo Helpers::getMagnifierIco( 'psfs-ico-magnifier js-psfs-ico-magnifier-solaris' ); ?>
						<?php echo Helpers::getMagnifierIco( 'psfs-ico-magnifier js-psfs-ico-magnifier-pirx', 'magnifier-pirx' ); ?>

						<label
							class="screen-reader-text"><?php _e( 'Products search', 'psf-search' ); ?></label>

						<input
							type="search"
							class="js-psfs-search-input psfs-search-input"
							name="<?php echo esc_attr( Helpers::getSearchInputName() ); ?>"
							value="<?php echo esc_attr( get_search_query() ); ?>"
							autocomplete="off"
							placeholder="<?php echo esc_attr( Helpers::getLabel( 'search_placeholder' ) ); ?>"
						/>
						<div class="psfs-preloader"></div>

						<button type="submit" class="js-psfs-search-submit psfs-search-submit">
							<?php
							echo '<span class="js-psfs-search-submit-l">' . esc_html( $submitText ) . '</span>';
							echo '<span class="js-psfs-search-submit-m">';
							echo Helpers::getMagnifierIco( 'psfs-ico-magnifier js-psfs-ico-magnifier-solaris' );
							echo Helpers::getMagnifierIco( 'psfs-ico-magnifier js-psfs-ico-magnifier-pirx', 'magnifier-pirx' );
							echo '</span>';
							?>
						</button>

						<input type="hidden" name="post_type" value="product">
						<input type="hidden" name="psfs" value="1">

						<input type="hidden" name="lang" value="en">

					</div>
				</form>
			</div>

			<span class="js-psfs-preview-device-info psfs-preview-device-info psfs-hidden" data-device="mobile">
				<span><?php _e( 'On mobile', 'psf-search' ); ?></span>
				<span><?php _e( 'On desktop', 'psf-search' ); ?></span>
			</span>
			<div
				class="js-psfs-search-wrapp psfs-layout-icon-open js-psfs-preview-icon-example psfs-search-wrapp psfs-hidden <?php echo Helpers::searchWrappClasses(); ?>"
				data-psfs-context="75c3">
				<a
					href="#" class="psfs-search-icon js-psfs-search-icon-handler"><?php echo Helpers::getMagnifierIco( 'psfs-ico-magnifier-handler' ); ?>
				</a>
				<div class="psfs-search-icon-arrow"></div>
				<form class="psfs-search-form" role="search" action="" method="get">
					<div class="psfs-sf-wrapp">
						<?php echo Helpers::getMagnifierIco( 'psfs-ico-magnifier js-psfs-ico-magnifier-solaris' ); ?>
						<?php echo Helpers::getMagnifierIco( 'psfs-ico-magnifier js-psfs-ico-magnifier-pirx', 'magnifier-pirx' ); ?>

						<label
							class="screen-reader-text"><?php _e( 'Products search', 'psf-search' ); ?></label>
						<input
							type="search"
							class="js-psfs-search-input psfs-search-input"
							name="<?php echo esc_attr( Helpers::getSearchInputName() ); ?>"
							value="<?php echo esc_attr( get_search_query() ); ?>"
							autocomplete="off"
							placeholder="<?php echo esc_attr( Helpers::getLabel( 'search_placeholder' ) ); ?>"
						/>
						<div class="psfs-preloader"></div>

						<button type="submit" class="js-psfs-search-submit psfs-search-submit">
							<?php
							echo '<span class="js-psfs-search-submit-l">' . esc_html( $submitText ) . '</span>';
							echo '<span class="js-psfs-search-submit-m">';
							echo Helpers::getMagnifierIco( 'psfs-ico-magnifier js-psfs-ico-magnifier-solaris' );
							echo Helpers::getMagnifierIco( 'psfs-ico-magnifier js-psfs-ico-magnifier-pirx', 'magnifier-pirx' );
							echo '</span>';
							?>
						</button>

						<input type="hidden" name="post_type" value="product">
						<input type="hidden" name="psfs" value="1">

						<input type="hidden" name="lang" value="en">

					</div>
				</form>
			</div>

			<div class="psfs-autocomplete">

				<div
					class="psfs-suggestions-wrapp js-psfs-suggestions-wrapp woocommerce psfs-has-price psfs-has-desc psfs-has-sku psfs-has-headings"
					unselectable="on">

					<div class="psfs-suggestion psfs-suggestion-headline-brand psfs-suggestion-headline">
					<span class="psfs-st">
						<?php echo Helpers::getLabel( 'tax_' . PSFS()->brands->getBrandTaxonomy() . '_plu' ); ?>
					</span>
					</div>

					<div class="psfs-suggestion psfs-suggestion-brand js-psfs-suggestion-brand">
					<span class="psfs-si">
						<img src="<?php echo PSFS_URL; ?>assets/img/product-preview.png">
					</span>
						<span class="psfs-st">
						<?php _e( 'Sample brand <strong>name</strong>', 'psf-search' ); ?>
					</span>
					</div>

					<div class="psfs-suggestion psfs-suggestion-headline-cat psfs-suggestion-headline">
					<span class="psfs-st">
						<?php echo Helpers::getLabel( 'tax_product_cat_plu' ); ?>
					</span>
					</div>

					<div class="psfs-suggestion psfs-suggestion-cat js-psfs-suggestion-cat">
					<span class="psfs-si">
						<img src="<?php echo PSFS_URL; ?>assets/img/product-preview.png">
					</span>
						<span class="psfs-st">
						<?php _e( 'Sample category <strong>name</strong>', 'psf-search' ); ?>
					</span>
					</div>

					<div class="psfs-suggestion psfs-suggestion-headline-tag psfs-suggestion-headline">
					<span class="psfs-st">
						<?php echo Helpers::getLabel( 'tax_product_tag_plu' ); ?>
					</span>
					</div>

					<div class="psfs-suggestion psfs-suggestion-tag">
					<span class="psfs-st">
						<?php _e( 'Sample tag <strong>name</strong>', 'psf-search' ); ?>
					</span>
					</div>

					<?php
					$postTypes = [ 'post', 'page' ];
					$postTypes = PSFS()->tntsearchMySql->postTypes->getPostTypesSlugs();
					foreach ( $postTypes as $postType ) {
						$label = '';
						if ( $postType === 'post' || $postType === 'page' ) {
							$label = $postType;
						} else {
							$details = PSFS()->tntsearchMySql->postTypes->getPostTypeDetails( $postType );
							$label   = empty( $details['labels']['singular_name'] ) ? $postType : strtolower( $details['labels']['singular_name'] );
						}
						?>
						<div
							class="psfs-suggestion psfs-suggestion-headline-cpt psfs-suggestion-headline-<?php echo esc_attr( $postType ); ?> psfs-suggestion-headline">
						<span class="psfs-st">
						<?php echo Helpers::getLabel( 'post_type_' . $postType . '_plu' ); ?>
						</span>
						</div>

						<div
							class="psfs-suggestion psfs-suggestion-cpt psfs-suggestion-<?php echo esc_attr( $postType ); ?> js-psfs-suggestion-<?php echo esc_attr( $postType ); ?>">
						<span class="psfs-si">
						<img src="<?php echo PSFS_URL; ?>assets/img/product-preview.png">
						</span>
							<span class="psfs-st">
						<?php echo sprintf( __( 'Sample %s <strong>name</strong>', 'psf-search' ), $label ); ?>
						</span>
						</div>
						<?php
					}
					?>

					<div class="psfs-suggestion js-psfs-suggestion-headline psfs-suggestion-headline">
					<span class="psfs-st">
						<?php echo Helpers::getLabel( 'product_plu' ); ?>
					</span>
					</div>

					<div class="psfs-suggestion psfs-suggestion-product">
					<span class="js-psfs-si psfs-si">
						<img src="<?php echo PSFS_URL; ?>assets/img/product-preview.png">
					</span>
						<div class="js-psfs-content-wrapp psfs-content-wrapp">
						<span class="psfs-st">
							<span
								class="psfs-st-title"><?php _e( 'Sample product <strong>name</strong>', 'psf-search' ); ?></span>
							<span
								class="psfs-sku js-psfs-sku">(<?php echo Helpers::getLabel( 'sku_label' ); ?> 0001)</span>
							<span
								class="psfs-sd js-psfs-sd"><?php _e( 'Lorem <strong>ipsum</strong> dolor sit amet, consectetur adipiscing elit. Quisque gravida lacus nec diam porttitor pharetra. Nulla facilisi. Proin pharetra imperdiet neque, non varius.', 'psf-search' ); ?></span>
						</span>
							<span class="psfs-sp js-psfs-sp">
							<?php echo wc_price( 99 ); ?>
						</span>
						</div>
					</div>


					<div
						class="psfs-suggestion js-psfs-suggestion-more psfs-suggestion-more psfs-suggestion-no-border-bottom"
						data-index="7">
						<span class="psfs-st-more"><span
								class="js-psfs-st-more-label"><?php echo esc_html( $labelSeeAll ); ?></span> (73)</span>
					</div>

					<div class="psfs-suggestion-nores js-psfs-suggestion-nores psfs-hide">
					</div>

				</div>

				<div class="psfs-details-wrapp js-psfs-details-wrapp woocommerce">

					<div class="psfs-details-inner">

						<div class="psfs-product-details">

							<a href="#">
								<div class="psfs-details-main-image">
									<img src="<?php echo PSFS_URL; ?>assets/img/product-preview.png"/>
								</div>
							</a>

							<div class="psfs-details-space">

								<a class="psfs-details-product-title" href="#">
									<?php _e( 'Sample product name', 'psf-search' ); ?>
								</a>
								<span class="psfs-details-product-sku">0001</span>

								<div class="psfs-pd-price">
									<?php echo wc_price( 99 ); ?>
								</div>

								<div class="psfs-details-hr"></div>

								<div class="psfs-pd-desc">
									<?php
									_e(
										'Lorem <strong>ipsum</strong> dolor sit amet, consectetur adipiscing elit. Quisque gravida lacus nec diam porttitor pharetra. Nulla facilisi. Proin pharetra imperdiet neque, non varius.',
										'psf-search'
									);
									?>
								</div>

								<div class="psfs-details-hr"></div>

								<div class="psfs-pd-addtc js-psfs-pd-addtc">
									<form class="psfs-pd-addtc-form" action="" method="post" enctype="multipart/form-data">
										<div class="quantity buttons_added">
											<input type="button" value="-" class="minus button is-form">
											<input
												type="number" class="input-text qty text" step="1" min="0" max="9999"
												name="js-psfs-quantity" value="1" title="Qty" size="4"
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

	<div class="psfs-preview-elements psfs-hidden">
		<div class="js-psfs-preview-elements-close"><?php echo Helpers::getIcon( 'close' ); ?></div>
	</div>
</div>
