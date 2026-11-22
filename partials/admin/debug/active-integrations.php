<?php

use PsfSearch\Integrations\Plugins\AbstractPluginIntegration;

// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

$plugins        = new \PsfSearch\Integrations\Plugins\PluginsCompatibility();
$pluginsClasses = $plugins->getIntegrationClasses();
?>

<div class="psf-search-debug psf-search-debug-active-integrations">
	<h3>Active integrations with the plugins</h3>

	<table class="widefat striped">
		<thead>
		<tr>
			<th><?php esc_html_e( 'Plugin name', 'psf-search' ); ?></th>
			<th><?php esc_html_e( 'Version', 'psf-search' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		/** @var AbstractPluginIntegration $class */
		foreach ( $pluginsClasses as $class ) :
			if ( ! $class::isActive() ) {
				continue;
			}
			$label   = $class::label();
			$version = $class::pluginVersion();
			?>
			<tr>
				<td class="plugin-name"><?php echo esc_html( $label ); ?></td>
				<td><?php echo esc_html( $version !== '' ? 'v' . $version : '—' ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
</div>
