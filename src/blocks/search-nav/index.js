/**
 * External dependencies
 */
import { registerBlockType } from '@wordpress/blocks';
import { Icon } from '@wordpress/icons';

/**
 * Internal dependencies
 */
import Edit from './edit';
import transforms from './transforms';
import psfsearchIcon from '../../icons/psfsearch';

/**
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( 'psfsearch/search-nav', {
	edit: Edit,
	icon: {
		src: <Icon icon={ psfsearchIcon } />,
	},
	save: () => {},
	transforms,
} );
