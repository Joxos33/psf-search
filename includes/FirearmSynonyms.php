<?php

namespace PsfSearch;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Starter synonyms for a firearms catalog: caliber spellings, gauge shorthand,
 * Glock model numbers and brand abbreviations.
 *
 * They go into the normal, editable Synonyms setting (Search config → advanced),
 * and only when that setting is empty. At index time every product that mentions
 * one phrase of a group is also findable by the others ("9x19" finds "9mm").
 *
 * Bare numbers (223, 308, 556) are left out on purpose: they would pull in any
 * product with that number in a part number or description. "5.56" already
 * indexes as "556", so typing it joined still works.
 */
class FirearmSynonyms {

	public static function defaults(): string {
		$groups = [
			// Handgun calibers
			'9mm, 9x19, 9x19mm, 9mm luger, 9mm parabellum, 9mm para, 9 para',
			'.380 acp, .380 auto, 380 acp, 380 auto',
			'.40 s&w, 40 s&w, .40 sw, 40sw, .40 cal, 40 cal',
			'.45 acp, 45 acp, 45acp, .45 auto, 45 auto',
			'10mm, 10mm auto, 10 mm auto',
			'.357 magnum, .357 mag, 357 mag, 357 magnum',
			'.38 special, .38 spl, 38 special, 38 spl',
			'.44 magnum, .44 mag, 44 mag, 44 magnum',
			'.45 long colt, .45 colt, 45 colt, .45 lc, 45 lc',
			'5.7x28, 5.7 x 28, 5.7 x 28 mm, 5.7x28mm, 57x28',
			// Rimfire
			'.22 lr, 22 lr, 22lr, .22 long rifle, 22 long rifle',
			'.22 wmr, 22 wmr, 22wmr, .22 magnum, .22 mag, 22 mag, .22 winchester magnum',
			'.17 hmr, 17 hmr, 17hmr',
			// Rifle calibers
			'.223 rem, .223 remington, 223 rem, 5.56 nato, 5.56x45, 5.56x45mm, 5.56mm',
			'.308 win, .308 winchester, 308 win, 7.62 nato, 7.62x51, 7.62x51mm',
			'7.62x39, 7.62 x 39, 7.62x39mm, 7.62 x 39mm, 762x39',
			'.300 blackout, .300 aac blackout, 300 blackout, 300 aac, 300 blk, 300blk, .300 blk',
			'.30-06, 30-06, .30-06 springfield, 30-06 springfield',
			'6.5 creedmoor, 6.5mm creedmoor, 6.5 creed',
			'6mm creedmoor, 6 creedmoor',
			'6.5 prc, 6.5mm prc',
			'6.5 grendel, 6.5mm grendel',
			'.300 win mag, .300 winchester magnum, 300 win mag, 300 wm',
			'7mm rem mag, 7mm remington magnum, 7 mm remington magnum, 7mm mag',
			'.243 win, .243 winchester, 243 win',
			'.270 win, .270 winchester, 270 win',
			'.350 legend, 350 legend',
			'.450 bushmaster, 450 bushmaster, 450 bm',
			'.45-70, 45-70, .45-70 government, 45-70 govt',
			'.22-250, 22-250, .22-250 remington',
			// Shotgun gauges
			'12 gauge, 12 ga, 12ga, 12-gauge',
			'20 gauge, 20 ga, 20ga, 20-gauge',
			'16 gauge, 16 ga, 16ga',
			'28 gauge, 28 ga, 28ga, .28 gauge',
			'10 gauge, 10 ga, 10ga',
			'.410 bore, .410, 410 bore, .410 ga, .410ga, 410ga',
			// Brands and platforms
			'smith & wesson, smith and wesson, smith wesson, s&w',
			'sig sauer, sig-sauer, sigsauer',
			'heckler & koch, heckler and koch, h&k',
			'ar-15, ar15, ar 15',
			'ak-47, ak47, ak 47',
		];

		// Glock models: "g19" finds "Glock 19".
		foreach ( [ '17', '19', '19x', '20', '21', '22', '23', '26', '27', '29', '30', '32', '34', '36', '40', '42', '43', '43x', '44', '45', '47', '48', '49' ] as $model ) {
			$groups[] = 'glock ' . $model . ', g' . $model;
		}

		return implode( "\n", $groups );
	}

	/**
	 * Fill an empty Synonyms setting with the defaults (run on update).
	 */
	public static function seedIfEmpty() {
		$settings = get_option( PSF_SEARCH_SETTINGS_KEY );
		if ( ! is_array( $settings ) ) {
			return;
		}

		if ( isset( $settings['search_synonyms'] ) && trim( (string) $settings['search_synonyms'] ) !== '' ) {
			return;
		}

		$settings['search_synonyms'] = self::defaults();
		update_option( PSF_SEARCH_SETTINGS_KEY, $settings );

		global $psfSearchSettings;
		if ( is_array( $psfSearchSettings ) ) {
			$psfSearchSettings['search_synonyms'] = $settings['search_synonyms'];
		}
		PSF_SEARCH()->settings->clearCache();
	}
}
