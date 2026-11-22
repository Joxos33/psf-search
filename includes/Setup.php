<?php

namespace PsfSearch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Setup {
	/**
	 * @var string
	 */
	private $thumbnailSize;

	public function init() {
		add_action( 'init', [ $this, 'setThumbnailSize' ] );

		add_filter( 'woocommerce_regenerate_images_intermediate_image_sizes', [ $this, 'getImageSizes' ] );
	}

	/**
	 * Get default image size
	 *
	 * @return string
	 */
	public function getThumbnailSize() {
		return $this->thumbnailSize;
	}

	/**
	 * Register custom image size
	 *
	 * @return void
	 */
	public function setThumbnailSize() {
		$this->thumbnailSize = apply_filters( 'psfs/setup/thumbnail_size', 'psfs-product-suggestion' );

		if ( $this->thumbnailSize === 'psfs-product-suggestion' ) {
			add_image_size( 'psfs-product-suggestion', 64, 0, false );
		}
	}

	/**
	 * Images sizes to regenerate
	 *
	 * @param array $sizes
	 *
	 * @return array
	 */
	public function getImageSizes( $sizes ) {
		$sizes[] = $this->getThumbnailSize();

		return array_unique( $sizes );
	}
}
