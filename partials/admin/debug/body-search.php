<?php

use PsfSearch\Engines\TNTSearchMySQL\Indexer\Builder;
use PsfSearch\Engines\TNTSearchMySQL\SearchQuery\AjaxQuery;
use PsfSearch\Engines\TNTSearchMySQL\Debug\Debugger;
use PsfSearch\Engines\TNTSearchMySQL\Support\Tokenizer\Tokenizer;

// Exit if accessed directly
if ( ! defined( 'PSFS_FILE' ) ) {
	exit;
}

Debugger::wipeLogs( 'product-search-flow' );
Debugger::wipeLogs( 'search-resutls' );
//phpcs:disable WordPress.Security.NonceVerification.Recommended
$searchPhrase = ! empty( $_GET['s'] ) ? $_GET['s'] : '';
$lang         = ! empty( $_GET['lang'] ) ? $_GET['lang'] : '';

$toTokenize   = ! empty( $_GET['psfs-to-tokenize'] ) ? $_GET['psfs-to-tokenize'] : '';
$tokenizerCtx = ! empty( $_GET['psfs-debug-tokenizer-ctx'] ) ? $_GET['psfs-debug-tokenizer-ctx'] : 'indexer';
//phpcs:enable
?>

<h3>Search flow</h3>
<form action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
	<input type="hidden" name="page" value="psfs_debug">
	<?php wp_nonce_field( 'psfs_debug_search', '_wpnonce', false ); ?>
	<label for="psfs-debug-search"></label>
	<input
		type="text" class="regular-text" id="psfs-debug-search" name="s"
		value="<?php echo esc_html( $searchPhrase ); ?>" placeholder="search phrase">
	<input
		type="text"
		class="small-text"
		id="psfs-debug-search-lang"
		name="lang"
		value="<?php echo esc_html( $lang ); ?>"
		placeholder="lang">
	<button class="button" type="submit">Search</button>
</form>

<hr/>
<h3>Tokenizer</h3>
<form action="<?php echo admin_url( 'admin.php' ); ?>" method="get">
	<input type="hidden" name="page" value="psfs_debug">
	<?php wp_nonce_field( 'psfs_debug_search', '_wpnonce', false ); ?>
	<label for="psfs-debug-tokenizer"></label>
	<input
		type="text"
		class="regular-text"
		id="psfs-debug-tokenizer"
		name="psfs-to-tokenize"
		value="<?php echo esc_html( $toTokenize ); ?>"
		placeholder="To tokenize"">
	<select name="psfs-debug-tokenizer-ctx">
		<option <?php echo $tokenizerCtx === 'search' ? 'selected="selected"' : ''; ?>>search</option>
		<option <?php echo $tokenizerCtx === 'indexer' ? 'selected="selected"' : ''; ?>>indexer</option>
	</select>
	<button class="button" type="submit">Tokenize</button>
</form>

<?php
if (
	! empty( $searchPhrase ) &&
	! empty( $_REQUEST['_wpnonce'] ) &&
	wp_verify_nonce( $_REQUEST['_wpnonce'], 'psfs_debug_search' )
) {

	define( 'PSFS_SEARCH_START', microtime( true ) );

	$lang = empty( $_GET['lang'] ) ? $_GET['lang'] : '';
	if ( ! Builder::searchableCacheExists( $lang ) ) {
		add_filter( 'psfs/tnt/search_cache', '__return_false', PHP_INT_MAX - 5 );
	}

	add_filter(
		'psfs/tnt/search_results/suggestion/product',
		function ( $p, $suggestion ) {
			if ( ! empty( $suggestion->score ) ) {
				$p['score'] = $suggestion->score;
			}

			return $p;
		},
		10,
		2
	);

	$query = new AjaxQuery( true );
	$query->setPhrase( $searchPhrase );

	if ( ! empty( $_GET['lang'] ) ) {
		$query->setLang( $_GET['lang'] );
	}

	$query->searchProducts();
	$query->searchPosts();
	$query->searchTaxonomy();
	Debugger::logSearchResults( $query );


	Debugger::printLogs( 'Search flow', 'product-search-flow' );
	Debugger::printLogs( 'Search resutls', 'search-resutls' );

}
?>

<?php
if (
	! empty( $toTokenize ) &&
	! empty( $_REQUEST['_wpnonce'] ) &&
	wp_verify_nonce( $_REQUEST['_wpnonce'], 'psfs_debug_search' )
) {
	$tokenizer = new Tokenizer();
	$tokenizer->setContext( $tokenizerCtx );

	$stopwords = [];
	if ( $tokenizerCtx === 'search' ) {
		$stopwords = apply_filters( 'psfs/search/stopwords', [], ! empty( $_GET['lang'] ) ? $_GET['lang'] : '' );
	} elseif ( $tokenizerCtx === 'indexer' ) {
		$stopwords = apply_filters( 'psfs/indexer/searchable/stopwords', [], 'product', 0, ! empty( $_GET['lang'] ) ? $_GET['lang'] : '' );
	}

	// phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_var_export
	Debugger::log( '<b>Phrase:</b> <pre>' . var_export( $toTokenize, true ) . '</pre>', 'tokenizer' );
	Debugger::log( '<b>Context:</b> <pre>' . var_export( $tokenizerCtx, true ) . '</pre>', 'tokenizer' );
	Debugger::log( '<b>Split by:</b> <pre>' . var_export( $tokenizer->getSpecialChars(), true ) . '</pre>', 'tokenizer' );
	Debugger::log( '<b>Stopwords:</b> <pre>' . var_export( $stopwords, true ) . '</pre>', 'tokenizer' );
	Debugger::log( '<b>Tokens:</b> <pre>' . var_export( $tokenizer->tokenize( $toTokenize, $stopwords ), true ) . '</pre>', 'tokenizer' );
    // phpcs:enable WordPress.PHP.DevelopmentFunctions.error_log_var_export
	Debugger::printLogs( 'Tokenizer', 'tokenizer' );

}

Debugger::wipeLogs( 'product-search-flow' );
Debugger::wipeLogs( 'search-resutls' );
Debugger::wipeLogs( 'tokenizer' );
