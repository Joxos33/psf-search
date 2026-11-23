/**
 * PSF Search: shop filter panel.
 *
 * Filters apply as soon as they change: the new page is fetched in the background and
 * only the panel and the results are swapped in (history kept with pushState), so the
 * page never fully reloads. Anything unexpected falls back to a normal page load.
 * On mobile the panel is an off-canvas drawer; results update behind it and
 * "Show results" just closes it.
 */
(function () {
	'use strict';

	var cfg = window.psfSearchFilters || {};
	var i18n = cfg.i18n || {};
	var PANEL = '#psf-shop-filter-panel';
	var RESULTS = '.psf-shop-results';
	var MAX_OPTIONS = 250;
	var DRAWER_MQ = '(max-width: 1024px)'; // same switch point as PSF Styling's toolbar

	var termCache = {};
	var controller = null;
	var applyTimer = null;

	function panel() {
		return document.querySelector(PANEL);
	}

	function isDrawer() {
		return window.matchMedia(DRAWER_MQ).matches;
	}

	/* ---------- URL building ---------- */

	function formUrl(form, base) {
		base = base || panel().getAttribute('data-base-url') || form.getAttribute('action') || window.location.pathname;
		var url = new URL(base, window.location.href);
		var data = new FormData(form);

		data.forEach(function (value, key) {
			value = String(value).trim();
			if (value !== '' || key === 's') {
				url.searchParams.set(key, value);
			}
		});

		// An empty keyword on an archive just means "no keyword".
		if (url.searchParams.get('s') === '' && !url.searchParams.has('post_type')) {
			url.searchParams.delete('s');
		}

		return url.toString();
	}

	/* ---------- Fetch and swap ---------- */

	function openFacets() {
		var open = [];
		document.querySelectorAll(PANEL + ' .js-psf-search-facet.is-open').forEach(function (el) {
			open.push(el.getAttribute('data-facet'));
		});
		return open;
	}

	function load(url, push) {
		var current = panel();
		var results = document.querySelector(RESULTS);

		if (!current || !results || !window.fetch || !window.DOMParser) {
			window.location.assign(url);
			return;
		}

		if (controller) {
			controller.abort();
		}
		controller = window.AbortController ? new AbortController() : null;

		var keepOpen = openFacets();
		var wasOpen = current.classList.contains('is-open');
		var scrollTop = current.querySelector('.psf-search-filters__form') ? current.querySelector('.psf-search-filters__form').scrollTop : 0;

		document.documentElement.classList.add('psf-search-filters-loading');

		fetch(url, {
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'psf-search-filters' },
			signal: controller ? controller.signal : undefined
		})
			.then(function (response) {
				if (!response.ok || response.redirected && new URL(response.url).pathname !== new URL(url).pathname) {
					throw new Error('fallback');
				}
				return response.text();
			})
			.then(function (html) {
				var doc = new DOMParser().parseFromString(html, 'text/html');
				var newPanel = doc.querySelector(PANEL);
				var newResults = doc.querySelector(RESULTS);

				if (!newPanel || !newResults) {
					throw new Error('fallback');
				}

				keepOpen.forEach(function (facet) {
					var el = newPanel.querySelector('.js-psf-search-facet[data-facet="' + facet + '"]');
					if (el) {
						el.classList.add('is-open');
						var toggle = el.querySelector('.js-psf-search-facet-toggle');
						if (toggle) {
							toggle.setAttribute('aria-expanded', 'true');
						}
					}
				});
				if (wasOpen) {
					newPanel.classList.add('is-open');
				}

				var resultsEl = document.querySelector(RESULTS);
				resultsEl.parentNode.replaceChild(document.importNode(newResults, true), resultsEl);
				var panelEl = panel();
				panelEl.parentNode.replaceChild(document.importNode(newPanel, true), panelEl);

				var form = panel().querySelector('.psf-search-filters__form');
				if (form) {
					form.scrollTop = scrollTop;
				}

				if (doc.title) {
					document.title = doc.title;
				}
				if (push) {
					window.history.pushState({ psfSearchFilters: true }, '', url);
				}

				afterSwap();
			})
			.catch(function (err) {
				if (err && err.name === 'AbortError') {
					return;
				}
				window.location.assign(url);
			})
			.then(function () {
				document.documentElement.classList.remove('psf-search-filters-loading');
			});
	}

	function afterSwap() {
		var results = document.querySelector(RESULTS);
		if (!results) {
			return;
		}

		// Flatsome re-binds lazy images, quick view, tooltips etc. on new content.
		if (window.Flatsome && typeof window.Flatsome.attach === 'function') {
			try {
				window.Flatsome.attach(results);
			} catch (e) {}
		}

		// Lazy images that nothing picked up: load them now.
		results.querySelectorAll('img[data-src]').forEach(function (img) {
			if (!img.getAttribute('src') || img.getAttribute('src').indexOf('data:') === 0) {
				img.setAttribute('src', img.getAttribute('data-src'));
				if (img.getAttribute('data-srcset')) {
					img.setAttribute('srcset', img.getAttribute('data-srcset'));
				}
			}
		});

		if (window.jQuery) {
			window.jQuery(document.body).trigger('psf-search:catalog-updated');
		}
		document.dispatchEvent(new CustomEvent('psf-search:catalog-updated', { detail: { results: results } }));
	}

	function apply(delay) {
		var p = panel();
		var form = p && p.querySelector('.js-psf-search-filters-form');
		if (!form) {
			return;
		}

		window.clearTimeout(applyTimer);
		applyTimer = window.setTimeout(function () {
			load(formUrl(form), true);
		}, delay || 0);
	}

	/* ---------- Facets: search, browse all ---------- */

	function getTerms(facet) {
		if (termCache[facet]) {
			return Promise.resolve(termCache[facet]);
		}

		var url = new URL(cfg.termsUrl, window.location.href);
		url.searchParams.set('facet', facet);

		return fetch(url.toString(), { credentials: 'same-origin' })
			.then(function (r) {
				return r.json();
			})
			.then(function (json) {
				termCache[facet] = json && json.success && Array.isArray(json.data) ? json.data : [];
				return termCache[facet];
			});
	}

	function normalize(text) {
		return String(text).toLowerCase().replace(/[^a-z0-9]+/g, '');
	}

	function renderOptions(facetEl, list, filter) {
		var ul = facetEl.querySelector('.js-psf-search-facet-options');
		var param = facetEl.getAttribute('data-param');
		var current = facetEl.getAttribute('data-current') || '';
		var needle = normalize(filter || '');
		var shown = 0;

		ul.innerHTML = '';

		for (var i = 0; i < list.length && shown < MAX_OPTIONS; i++) {
			var term = list[i];
			if (needle && normalize(term.n).indexOf(needle) === -1 && normalize(term.s).indexOf(needle) === -1) {
				continue;
			}

			var li = document.createElement('li');
			var label = document.createElement('label');
			var input = document.createElement('input');
			var span = document.createElement('span');

			label.className = 'psf-search-filters__check';
			input.type = 'radio';
			input.name = param;
			input.value = term.s;
			input.className = 'js-psf-search-filters-auto';
			input.checked = term.s === current;
			span.textContent = term.n;

			label.appendChild(input);
			label.appendChild(span);
			li.appendChild(label);
			ul.appendChild(li);
			shown++;
		}

		if (!shown) {
			var empty = document.createElement('li');
			empty.className = 'psf-search-filters__empty';
			empty.textContent = i18n.noMatches || 'No matches';
			ul.appendChild(empty);
		}
	}

	function showFull(facetEl, filter) {
		var facet = facetEl.getAttribute('data-facet');
		var ul = facetEl.querySelector('.js-psf-search-facet-options');
		var more = facetEl.querySelector('.js-psf-search-facet-more');

		if (!facetEl._psfTop) {
			facetEl._psfTop = ul.innerHTML;
		}

		return getTerms(facet).then(function (list) {
			ul.classList.add('is-full');
			if (more) {
				more.hidden = true;
			}
			renderOptions(facetEl, list, filter);
		});
	}

	/* ---------- Drawer ---------- */

	function scrim() {
		var el = document.querySelector('.psf-search-filters-scrim');
		if (!el) {
			el = document.createElement('div');
			el.className = 'psf-search-filters-scrim';
			el.addEventListener('click', closeDrawer);
			document.body.appendChild(el);
		}
		return el;
	}

	function openDrawer() {
		var p = panel();
		if (!p) {
			return;
		}
		scrim();
		p.classList.add('is-open');
		document.documentElement.classList.add('psf-search-filters-open');
		var close = p.querySelector('.psf-search-filters__close');
		if (close) {
			close.focus({ preventScroll: true });
		}
	}

	function closeDrawer() {
		var p = panel();
		if (p) {
			p.classList.remove('is-open');
		}
		document.documentElement.classList.remove('psf-search-filters-open');
	}

	/* ---------- Events (all delegated, so they survive swaps) ---------- */

	document.addEventListener('click', function (e) {
		var t = e.target;

		// The "Filters" button in PSF Styling's toolbar: drawer on mobile, show/hide the sidebar on desktop.
		var trigger = t.closest('.js-psf-search-filters-open, [data-psf-search-filters-open], [data-psf-shop-filter]');
		if (trigger) {
			e.preventDefault();
			if (isDrawer()) {
				openDrawer();
			} else {
				var catalog = document.querySelector('[data-psf-shop-catalog]');
				if (catalog) {
					var collapsed = catalog.classList.toggle('is-sidebar-collapsed');
					trigger.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
				}
			}
			return;
		}

		if (t.closest('[data-psf-shop-filter-close]') && !t.closest(PANEL)) {
			closeDrawer();
			return;
		}

		if (!t.closest(PANEL)) {
			// Pagination, active-filter chips and the grid/list switch stay in place too.
			var pageLink = t.closest([
				RESULTS + ' a.page-numbers',
				RESULTS + ' .woocommerce-pagination a',
				RESULTS + ' a.psf-shop-filter-chip',
				RESULTS + ' a.psf-shop-filter-clear',
				RESULTS + ' .psf-shop-view-switch a'
			].join(','));
			if (pageLink && panel() && !e.metaKey && !e.ctrlKey && !e.shiftKey) {
				e.preventDefault();
				load(pageLink.href, true);
				if (!pageLink.matches('.page-numbers, .woocommerce-pagination a')) {
					return;
				}
				var top = document.querySelector('[data-psf-shop-catalog]') || document.querySelector(RESULTS);
				if (top) {
					window.scrollTo({ top: top.getBoundingClientRect().top + window.pageYOffset - 100, behavior: 'smooth' });
				}
			}
			return;
		}

		if (t.closest('.js-psf-search-filters-close')) {
			e.preventDefault();
			closeDrawer();
			return;
		}

		var link = t.closest('.js-psf-search-filters-link');
		if (link && !e.metaKey && !e.ctrlKey && !e.shiftKey) {
			e.preventDefault();
			load(link.href, true);
			return;
		}

		var toggle = t.closest('.js-psf-search-facet-toggle');
		if (toggle) {
			var facetEl = toggle.closest('.js-psf-search-facet');
			var open = !facetEl.classList.contains('is-open');
			facetEl.classList.toggle('is-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			return;
		}

		var more = t.closest('.js-psf-search-facet-more');
		if (more) {
			more.textContent = i18n.loading || 'Loading…';
			showFull(more.closest('.js-psf-search-facet'), '');
			return;
		}

		// Clicking the selected brand/caliber/action again clears it (no change event fires).
		if (t.matches('.js-psf-search-facet input[type="radio"]')) {
			var facet = t.closest('.js-psf-search-facet');
			if (facet.getAttribute('data-current') === t.value) {
				t.checked = false;
				facet.setAttribute('data-current', '');
				apply(0);
			} else {
				facet.setAttribute('data-current', t.value);
			}
		}
	});

	document.addEventListener('change', function (e) {
		var t = e.target;
		if (!t.closest(PANEL)) {
			return;
		}

		// Category on a category page: go to that category's page, keeping the other filters.
		if (t.matches('.js-psf-search-filters-nav')) {
			if (t.value) {
				window.location.assign(formUrl(t.form, t.value));
			}
			return;
		}

		if (t.matches('.js-psf-search-filters-auto')) {
			apply(120);
			return;
		}

		// Price boxes apply when you leave them or press Enter.
		if (t.matches('input[name="min_price"], input[name="max_price"]')) {
			apply(0);
		}
	});

	// Sort dropdown: WooCommerce binds it to the form element, which a swap replaces,
	// so handle it here first (capture phase) for every swap, initial page included.
	document.addEventListener('change', function (e) {
		var t = e.target;
		if (!t.matches || !t.matches(RESULTS + ' .woocommerce-ordering select') || !panel()) {
			return;
		}
		e.stopPropagation();
		var url = new URL(window.location.href);
		new FormData(t.form).forEach(function (value, key) {
			url.searchParams.set(key, String(value));
		});
		url.searchParams.delete('paged');
		url.searchParams.delete('product-page');
		url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');
		load(url.toString(), true);
	}, true);

	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form.matches || !form.matches(PANEL + ' .js-psf-search-filters-form')) {
			return;
		}
		e.preventDefault();
		var active = document.activeElement;
		if (active && active.blur && active.type !== 'search') {
			active.blur();
		}
		apply(0);
	});

	var findTimer = null;
	document.addEventListener('input', function (e) {
		var t = e.target;
		if (!t.matches('.js-psf-search-facet-find')) {
			return;
		}

		var facetEl = t.closest('.js-psf-search-facet');
		window.clearTimeout(findTimer);
		findTimer = window.setTimeout(function () {
			if (t.value.trim() === '' && facetEl._psfTop && !facetEl.querySelector('.js-psf-search-facet-options.is-full')) {
				facetEl.querySelector('.js-psf-search-facet-options').innerHTML = facetEl._psfTop;
				return;
			}
			showFull(facetEl, t.value.trim());
		}, 120);
	});

	// Enter in a facet's search box picks the first match instead of submitting.
	document.addEventListener('keydown', function (e) {
		var t = e.target;
		if (e.key === 'Escape' && document.documentElement.classList.contains('psf-search-filters-open')) {
			closeDrawer();
			return;
		}
		if (e.key !== 'Enter' || !t.matches || !t.matches('.js-psf-search-facet-find')) {
			return;
		}
		e.preventDefault();
		var first = t.closest('.js-psf-search-facet').querySelector('.js-psf-search-facet-options input[type="radio"]');
		if (first && !first.checked) {
			first.checked = true;
			t.closest('.js-psf-search-facet').setAttribute('data-current', first.value);
			apply(0);
		}
	});

	window.addEventListener('popstate', function () {
		if (panel()) {
			load(window.location.href, false);
		}
	});

	// Leave the drawer state behind when the screen grows past the breakpoint.
	window.matchMedia(DRAWER_MQ).addEventListener('change', function (mq) {
		if (!mq.matches) {
			closeDrawer();
		}
	});

	// Keep the sticky desktop panel below Flatsome's sticky header.
	var ticking = false;
	function stickyOffset() {
		ticking = false;
		if (isDrawer()) {
			return;
		}
		var header = document.querySelector('.header-wrapper.stuck');
		var top = (header ? Math.max(0, header.getBoundingClientRect().bottom) : 0) + 16;
		document.documentElement.style.setProperty('--psf-search-filters-top', top + 'px');
	}
	window.addEventListener('scroll', function () {
		if (!ticking) {
			ticking = true;
			window.requestAnimationFrame(stickyOffset);
		}
	}, { passive: true });

	if (panel()) {
		window.history.replaceState({ psfSearchFilters: true }, '', window.location.href);
	}
})();
