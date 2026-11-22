<?php

namespace PsfSearch\Engines\TNTSearchMySQL\Support\Stemmer;

interface StemmerInterface {
	/**
	 * @param string $word
	 *
	 * @return string
	 */
	public static function stem( $word );
}
