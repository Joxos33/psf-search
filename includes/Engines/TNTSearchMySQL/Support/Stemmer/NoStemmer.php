<?php

namespace PsfSearch\Engines\TNTSearchMySQL\Support\Stemmer;

class NoStemmer implements StemmerInterface {
	public static function stem( $word ) {
		return $word;
	}
}
