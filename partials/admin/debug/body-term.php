<?php
// Exit if accessed directly
if ( ! defined( 'PSF_SEARCH_FILE' ) ) {
	exit;
}

$activeTaxonomies = array_map( 'get_taxonomy', PSF_SEARCH()->tntsearchMySql->taxonomies->getActiveTaxonomies( 'search_direct' ) );
$nonceValid       = ! empty( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'psf_search_debug_term' );
$termID           = $nonceValid && ! empty( $_GET['term_id'] ) ? $_GET['term_id'] : '';
$taxonomy         = $nonceValid && ! empty( $_GET['taxonomy'] ) ? $_GET['taxonomy'] : '';
$lang             = ! empty( $_GET['lang'] ) ? $_GET['lang'] : '';
if ( ! empty( $termID ) && ! empty( $taxonomy ) ) {
	$t = new \PsfSearch\Engines\TNTSearchMySQL\Debug\Term( $termID, $taxonomy, $lang );

	$readableIndexData = $t->getReadableIndexData();
	$wordlist          = $t->getSearchableIndexData();
}
?>

<h3>Term debug</h3>
<form action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
	<input type="hidden" name="page" value="psf_search_debug">
	<?php wp_nonce_field( 'psf_search_debug_term', '_wpnonce', false ); ?>
	<input
		type="text"
		class="regular-text"
		id="psf-search-debug-term"
		name="term_id"
		value="<?php echo esc_html( $termID ); ?>"
		placeholder="Term ID">
	<select id="psf-search-debug-term-taxonomy" placeholder="Taxonomy" name="taxonomy" style="margin-top: -3px">
		<?php
		// phpcs:ignore  WordPress.WP.GlobalVariablesOverride.Prohibited
		foreach ( $activeTaxonomies as $taxonomy ) {
			echo '<option value="' . esc_attr( $taxonomy->name ) . '">' . esc_html( $taxonomy->label ) . '</option>';
		}
		?>
	</select>
	<input
		type="text"
		class="small-text"
		id="psf-search-debug-search-lang"
		name="lang"
		value="<?php echo esc_html( $lang ); ?>"
		placeholder="lang">
	<button class="button" type="submit">Debug</button>
</form>

<?php if ( ! empty( $termID ) && ! empty( $taxonomy ) && ! $t->term->isValid() ) : ?>
	<p>Wrong term ID</p>
<?php endif; ?>

<?php
if ( ! empty( $termID ) && ! empty( $taxonomy ) && $t->term->isValid() ) :
	?>
	<table class="wc_status_table widefat" cellspacing="0">
		<thead>
		<tr>
			<th colspan="2" data-export-label="Searchable Index"><h3>Readable Index (stored in the database)</h3></th>
		</tr>
		</thead>
		<tbody>

		<?php
		if ( ! empty( $readableIndexData ) && is_array( $readableIndexData ) ) {
			foreach ( $readableIndexData as $key => $data ) :
				?>
				<tr>
					<td><b><?php echo $key; ?>: </b></td>
					<td><?php echo esc_html( $data ); ?></td>
				</tr>
				<?php
			endforeach;
		}
		?>
		</tbody>
	</table>

	<table class="wc_status_table widefat" cellspacing="0">
		<thead>
		<tr>
			<th colspan="2" data-export-label="Searchable Index"><h3>Searchable Index (stored in the database)</h3></th>
		</tr>
		</thead>
		<tbody>

		<tr>
			<td><b>Total terms:</b></td>
			<td><?php echo count( $wordlist ); ?></td>
		</tr>

		<tr>
			<td><b>Wordlist: </b></td>
			<td class="psf-search-table-wordlist">
				<p>
					<?php foreach ( $wordlist as $term ) : ?>
						<?php echo $term . '<br />'; ?>
					<?php endforeach; ?>
				</p>
			</td>
		</tr>
		</tbody>
	</table>

<?php endif; ?>
