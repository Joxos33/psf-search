# PSF Search

Live product search for [Puget Sound Firearms](https://pugetsoundfirearms.com) (WooCommerce + Flatsome).

Private fork of **FiboSearch Pro 1.34.3** (GPLv2 or later). The unmodified upstream code is the
first commit on `main`, so every PSF change is a reviewable diff against it.

## Install

The plugin folder must be named `psf-search` (the fast search endpoint checks for
`psf-search/psf-search.php`). Upload the folder to `wp-content/plugins/`, activate, then build the
index from **WooCommerce → PSF Search → Indexer** or with WP-CLI:

```
wp psfsearch index build
```

## Coexisting with FiboSearch

Everything is renamed so the two plugins never share data: PHP namespace `PsfSearch`,
constants `PSFS_*`, options/tables/AJAX actions `psfs_*`, hooks `psfs/...`,
CSS classes `psfs-*`, shortcode `[psfsearch]`, blocks `psfsearch/*`.

Both plugins take over Flatsome's header search, so only one should be active at a time.

## Optional custom code

Filters that must run inside the fast (SHORTINIT) search endpoint go in
`psf-search-filters.php` in the active theme folder or `wp-content/`.

## Roadmap

| Milestone | Scope |
|---|---|
| M1 | Strip unused code, rename everything to PSF Search |
| M2 | Products-only: remove posts/pages, taxonomy suggestions, native fallback engine |
| M3 | Firearm search: SKU prefix handling (`ZND\|SAIA11`), UPC/MPN/SKU exact hits, caliber + model normalization, editable synonyms |
| M4 | Stock: index stock status, out-of-stock badge, batched reindex queue for bulk syncs |
| M5 | Admin dashboard: header mode, mobile settings, live preview, every visual setting editable |
| M6 | Analytics: top searches + zero-results report; production cutover |
