<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="psf-search-analytics-module-critical">
	<h3><?php _e( 'Critical searches without result', 'psf-search' ); ?></h3>
	<?php if ( ! empty( $vars['critical-searches'] ) ) : ?>
		<p class="psf-search-analytics-subtitle">
			<?php
			// TODO
			//phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
			printf( _n( 'The PSF Search analyzer found <b>1 critical search phrase</b>.', 'The PSF Search analyzer found <b>%d critical search phrases</b>.', $vars['critical-searches-total'], 'psf-search' ), $vars['critical-searches-total'] );
			echo ' ';
			//phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
			printf( _n( 'These phrases have been typed by users over the last 1 day.', 'These phrases have been typed by users over the last %d days.', $vars['days'], 'psf-search' ), $vars['days'] );
			echo ' ';
			_e( "These phrases don`t return any search results. It's time to fix it.", 'psf-search' );
			?>
		</p>
		<div class="psf-search-analytics-module-critical-body">
			<div>
				<table class="widefat fixed psf-search-analytics-table">
					<thead>
					<tr>
						<th>#</th>
						<th><?php _e( 'Phrase', 'psf-search' ); ?></th>
						<th><?php _e( 'Repetitions', 'psf-search' ); ?></th>
						<th><?php _e( "Check if it's been solved", 'psf-search' ); ?></th>
					</tr>
					</thead>
					<tbody>
					<?php
					$i = 1;
					foreach ( $vars['critical-searches'] as $row ) {
						require PSF_SEARCH_DIR . 'partials/admin/stats/critical-searches-row.php';
						$i ++;
					}

					if ( $vars['critical-searches-more'] > 0 ) :
						?>
						<tr class="psf-search-analytics-load-more-row">
							<td colspan="4">
								<div>
						<span class="js-psf-search-critical-searches-load-more">
							<span><?php printf( _n( 'load another %d phrase', 'load another %d phrases', $vars['critical-searches-more'], 'psf-search' ), $vars['critical-searches-more'] ); ?></span>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
								</div>
							</td>

						</tr>
					<?php endif; ?>
					</tbody>
				</table>
				<?php
				if ( $vars['critical-searches-total'] > 0 ) {
					printf( '<a class="js-psf-search-analytics-export-csv" data-context="" href="#">%s</a>', __( 'Export CSV', 'psf-search' ) ) . '<span class="psf-search-ajax-loader"></span>';
				}
				?>
			</div>
			<div class="psf-search-analytics-module-critical-info">
				<h4><?php _e( 'How to fix it?', 'psf-search' ); ?></h4>
				<div>
					<p><?php _e( 'There are several ways to solve these problems. Every phrase may require a different approach. See what methods you could use:', 'psf-search' ); ?></p>
					<ol>
						<li><p><?php _e( "<b>Adding the phrase to the product name</b> - just add the phrase to the product name, description, SKU, custom field, tag or anything else that's in your current search scope", 'psf-search' ); ?></p></li>
						<li><p><?php printf( __( '<b>Synonyms</b> - if the phrase is an alternate version of any other words, add it as a synonym. Learn more about <a href="%s" target="_blank">the synonyms feature.', 'psf-search' ), $vars['links']['synonyms'] ); ?></a></p></li>
						<li>
							<p>
							<?php
							printf(
								__(
									'<b>Varied naming convention</b> - this issue occurs when users may type a phrase in several different way – eg. you have SKU "CB-978-8-7290", but users might type it in a different way: "CB978 8 7290", "CB/978/8/7290", "CB97887290" and so on. To solve such problems, contact our <a href="%s" target="_blank">technical support</a>, because the solution may be different for each case.',
									'psf-search'
								),
								$vars['links']['support']
							);
							?>
									</p></li>
					</ol>
				</div>
			</div>
		</div>


	<?php else : ?>
		<p class="psf-search-analytics-subtitle"><?php printf( __( "Fantastic! The PSF Search analyzer hasn't found any critical search phrases for the last %d days.", 'psf-search' ), $vars['days'] ); ?></p>
	<?php endif; ?>


</div>

<?php if ( ! defined( 'PSF_SEARCH_ANALYTICS_ONLY_CRITICAL' ) || ! PSF_SEARCH_ANALYTICS_ONLY_CRITICAL ) : ?>
	<div class="psf-search-analytics-module-tiles">
		<h3><?php printf( __( 'Searches stats (last %d days)', 'psf-search' ), $vars['days'] ); ?></h3>

		<div class="psf-search-analytics-tiles">
			<div class="psf-search-analytics-tile">
				<div class="psf-search-analytics-tile__values">
					<span><?php _e( 'Total searches', 'psf-search' ); ?></span>
					<span><?php _e( 'autocomplete', 'psf-search' ); ?></span>
					<span><?php echo esc_html( $vars['autocomplete']['total-results'] ); ?></span>
				</div>
				<div class="psf-search-analytics-tile__icon">
					<?php echo \PsfSearch\Helpers::getIcon( 'magnifier-pirx' ); ?>
				</div>
			</div>

			<div class="psf-search-analytics-tile">
				<div class="psf-search-analytics-tile__values">
					<span><?php _e( 'Total searches', 'psf-search' ); ?></span>
					<span><?php _e( 'search results page', 'psf-search' ); ?></span>
					<span><?php echo esc_html( $vars['search-page']['total-results'] ); ?></span>
				</div>
				<div class="psf-search-analytics-tile__icon">
					<?php echo \PsfSearch\Helpers::getIcon( 'magnifier-pirx' ); ?>
				</div>
			</div>

			<div class="psf-search-analytics-tile">
				<div class="psf-search-analytics-tile__values">
					<span><?php _e( 'Searches', 'psf-search' ); ?></span>
					<span><?php _e( 'returning results', 'psf-search' ); ?></span>
					<span><?php echo esc_html( $vars['returning-results-percent'] ); ?>%</span>
				</div>
				<div class="psf-search-analytics-tile__icon">
					<?php
					if ( ! empty( $vars['returning-results-percent'] ) ) {
						if ( $vars['returning-results-percent-satisfying'] ) {
							echo \PsfSearch\Helpers::getIcon( 'face-smile', 'psf-search-stats-icon-smile' );
						} else {
							echo \PsfSearch\Helpers::getIcon( 'face-sad', 'psf-search-stats-icon-sad' );
						}
					}
					?>
				</div>
			</div>
		</div>
	</div>

	<br/>

	<div class="psf-search-analytics-module-tables">
		<div class="psf-search-analytics-module-table">
			<h3><?php _e( 'Top searches - autocomplete', 'psf-search' ); ?></h3>
			<p class="psf-search-analytics-subtitle"><?php _e( 'The list of phrases with results is displayed to users as a drop down list with auto suggestions.', 'psf-search' ); ?></p>

			<table class="widefat fixed psf-search-analytics-table">
				<thead>
				<tr>
					<th>#</th>
					<th><?php _e( 'Phrase', 'psf-search' ); ?></th>
					<th><?php _e( 'Repetitions', 'psf-search' ); ?></th>
				</tr>

				</thead>
				<tbody>
				<?php if ( ! empty( $vars['autocomplete']['with-results'] ) ) : ?>

					<?php
					$i = 1;
					foreach ( $vars['autocomplete']['with-results'] as $row ) {
						require PSF_SEARCH_DIR . 'partials/admin/stats/ac-searches-row.php';
						$i ++;
					}

					if ( $vars['autocomplete']['total-with-results-uniq'] > count( $vars['autocomplete']['with-results'] ) ) :
						?>
						<tr class="psf-search-analytics-load-more-row">
							<td colspan="3">
								<div>
						<span class="js-psf-search-autocomplete-with-results-load-more">
							<span><?php _e( 'show top 100 phrases', 'psf-search' ); ?></span>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
								</div>
							</td>
						</tr>
					<?php endif; ?>

				<?php else : ?>
					<tr>
						<td colspan="3">
							<?php _e( '0 searches with results', 'psf-search' ); ?>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
			<?php
			if ( ! empty( $vars['autocomplete']['with-results'] ) ) {
				printf( '<a class="js-psf-search-analytics-export-csv" data-context="autocomplete" href="#">%s</a>', __( 'Export CSV', 'psf-search' ) ) . '<span class="psf-search-ajax-loader"></span>';
			}
			?>

		</div>
		<div class="psf-search-analytics-module-table">

			<h3><?php _e( 'Top searches - WooCommerce search results page', 'psf-search' ); ?></h3>
			<p class="psf-search-analytics-subtitle"><?php _e( 'Here is the list of phrases that were typed by users who followed the pattern: type the phrase into the bar → clicked “See all results” or just hit enter/return (for Mac users)', 'psf-search' ); ?></p>


			<table class="widefat fixed psf-search-analytics-table">
				<thead>
				<tr>
					<th>#</th>
					<th><?php _e( 'Phrase', 'psf-search' ); ?></th>
					<th><?php _e( 'Repetitions', 'psf-search' ); ?></th>
				</tr>

				</thead>
				<tbody>
				<?php if ( ! empty( $vars['search-page']['with-results'] ) ) : ?>

					<?php
					$i = 1;
					foreach ( $vars['search-page']['with-results'] as $row ) {
						require PSF_SEARCH_DIR . 'partials/admin/stats/sp-searches-row.php';
						$i ++;
					}

					if ( $vars['search-page']['total-with-results-uniq'] > count( $vars['search-page']['with-results'] ) ) :
						?>
						<tr class="psf-search-analytics-load-more-row">
							<td colspan="3">
								<div>
						<span class="js-psf-search-search-page-with-results-load-more">
							<span><?php _e( 'show top 100 phrases', 'psf-search' ); ?></span>
							<span class="dashicons dashicons-arrow-down-alt2"></span>
						</span>
								</div>
							</td>
						</tr>
					<?php endif; ?>

				<?php else : ?>
					<tr>
						<td colspan="3">
							<?php _e( '0 searches with results', 'psf-search' ); ?>
						</td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
			<?php
			if ( ! empty( $vars['search-page']['with-results'] ) ) {
				printf( '<a class="js-psf-search-analytics-export-csv" data-context="search-results-page" href="#">%s</a>', __( 'Export CSV', 'psf-search' ) ) . '<span class="psf-search-ajax-loader"></span>';
			}
			?>
		</div>
	</div>

<?php endif; ?>


<div class="psf-search-analytics-module-reset">
	<h3><?php _e( 'Maintenance', 'psf-search' ); ?></h3>
	<?php if ( defined( 'PSF_SEARCH_ANALYTICS_ONLY_CRITICAL' ) && PSF_SEARCH_ANALYTICS_ONLY_CRITICAL ) : ?>
		<p class="psf-search-analytics-subtitle">
			<span class="psf-search-analytics-red-color"><b><?php _e( 'Warning:', 'psf-search' ); ?></b> </span>
			<?php
			/* Translators: %s PHP constant name. */
			printf( __( "You have defined %s constant and it's set to <code>true</code>. It means only critical searches will be stored in the database. Other modules than Critical Searches are not visible in this mode.", 'psf-search' ), '<code>PSF_SEARCH_ANALYTICS_ONLY_CRITICAL</code>' )
			?>
		</p>
	<?php endif; ?>
	<p class="psf-search-analytics-subtitle">
		<?php
		$reset = sprintf( '<a class="js-psf-search-analytics-reset" href="#">%s</a>', __( 'reset your stats', 'psf-search' ) ) . '<span class="psf-search-ajax-loader"></span>';
		$size  = $vars['autocomplete']['total-results'] > 0 ? $vars['table-info']['data'] + $vars['table-info']['index'] : 0;
		?>
		<?php
		printf(
			_x( 'The stats older than %1$d days are removed from your database on a daily basis. Now you have %2$d records in the DB that weigh %3$.2fMB. You can %4$s now and start collecting them all over again.', 'The last placeholder is a button with text "reset your stats"', 'psf-search' ),
			$vars['days'],
			esc_html( $vars['autocomplete']['total-results'] ),
			$size,
			$reset
		);
		?>
	</p>
</div>
