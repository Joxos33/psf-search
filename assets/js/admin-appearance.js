/**
 * PSF Search: live preview for the Appearance tab.
 *
 * Mirrors Appearance::variables() in includes/Appearance.php: reads the Appearance
 * fields, builds the same --psfs-* CSS variables and sets them on the preview, so
 * every change shows up immediately. Keep the two in sync.
 */
(function ($) {
	'use strict';

	var cfg = window.psfSearchAppearance || {defaults: {}, shadows: {}},
		D = cfg.defaults,
		SHADOWS = cfg.shadows,
		overrides = {}; // values from the color picker, which fires before the input updates

	function field(key) {
		return $('[id="psf_search_settings[' + key + ']"]');
	}

	function val(key) {
		if (Object.prototype.hasOwnProperty.call(overrides, key)) {
			return overrides[key] === '' ? D[key] : overrides[key];
		}
		var $f = field(key);
		if (!$f.length) {
			return D[key];
		}
		if ($f.is(':checkbox')) {
			return $f.is(':checked') ? 'on' : 'off';
		}
		var v = $.trim(String($f.val()));
		return v === '' ? D[key] : v;
	}

	function num(key) {
		var n = parseInt(val(key), 10);
		return isNaN(n) || n < 0 ? parseInt(D[key], 10) : n;
	}

	function px(key) {
		return num(key) + 'px';
	}

	function color(key) {
		var v = String(val(key));
		return /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(v) ? v : D[key];
	}

	function choice(key, allowed) {
		var v = String(val(key));
		return allowed.indexOf(v) > -1 ? v : D[key];
	}

	function rgba(hex, alpha) {
		hex = hex.replace('#', '');
		if (hex.length === 3) {
			hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
		}
		return 'rgba(' + parseInt(hex.substr(0, 2), 16) + ',' + parseInt(hex.substr(2, 2), 16) + ',' + parseInt(hex.substr(4, 2), 16) + ',' + alpha + ')';
	}

	function variables() {
		var shadowKeys = Object.keys(SHADOWS),
			weights = ['300', '400', '500', '600', '700'],
			focusStyle = choice('ap_focus_style', ['ring', 'border', 'none']),
			focusColor = color('ap_focus_color'),
			borderColor = color('ap_bar_border_color'),
			barShadow = SHADOWS[choice('ap_bar_shadow', shadowKeys)],
			ddGap = num('ap_dd_gap'),
			font = String(val('ap_font_family')).replace(/[^a-zA-Z0-9 ,'"\-]/g, '').trim(),
			focusParts = [];

		if (focusStyle === 'ring') {
			focusParts.push('0 0 0 3px ' + rgba(focusColor, 0.2));
		}
		if (barShadow !== 'none') {
			focusParts.push(barShadow);
		}

		return {
			'--psfs-bar-h': px('ap_bar_height'),
			'--psfs-bar-radius': px('ap_bar_radius'),
			'--psfs-bar-bw': px('ap_bar_border_width'),
			'--psfs-bar-bc': borderColor,
			'--psfs-bar-bg': color('ap_bar_bg'),
			'--psfs-bar-shadow': barShadow,
			'--psfs-focus-bc': focusStyle === 'none' ? borderColor : focusColor,
			'--psfs-focus-shadow': focusParts.length ? focusParts.join(',') : 'none',
			'--psfs-font': font === '' ? 'inherit' : font,
			'--psfs-fs': px('ap_font_size'),
			'--psfs-fw': choice('ap_font_weight', weights),
			'--psfs-text': color('ap_text_color'),
			'--psfs-ph': color('ap_placeholder_color'),
			'--psfs-ph-style': choice('ap_placeholder_style', ['italic', 'normal']),
			'--psfs-btn-w': px('ap_btn_width'),
			'--psfs-btn-radius': px('ap_btn_radius'),
			'--psfs-btn-gap': px('ap_btn_gap'),
			'--psfs-btn-ico': px('ap_btn_icon_size'),
			'--psfs-btn-bg': color('ap_btn_bg'),
			'--psfs-btn-hover': color('ap_btn_hover_bg'),
			'--psfs-btn-c': color('ap_btn_color'),
			'--psfs-dd-bg': color('ap_dd_bg'),
			'--psfs-dd-hover': color('ap_dd_hover_bg'),
			'--psfs-dd-text': color('ap_dd_text'),
			'--psfs-dd-hl': color('ap_dd_highlight'),
			'--psfs-dd-bw': px('ap_dd_border_width'),
			'--psfs-dd-bc': color('ap_dd_border_color'),
			'--psfs-dd-div': color('ap_dd_divider_color'),
			'--psfs-dd-radius': px('ap_dd_radius'),
			'--psfs-dd-shadow': SHADOWS[choice('ap_dd_shadow', shadowKeys)],
			'--psfs-dd-gap': ddGap + 'px',
			'--psfs-open-radius': ddGap === 0 ? '0px' : px('ap_bar_radius'),
			'--psfs-dd-top-radius': ddGap === 0 ? '0px' : px('ap_dd_radius'),
			'--psfs-dd-top-bw': ddGap === 0 ? '0px' : px('ap_dd_border_width'),
			'--psfs-dd-img': px('ap_dd_img_size'),
			'--psfs-dd-title-fs': px('ap_dd_title_size'),
			'--psfs-dd-title-fw': choice('ap_dd_title_weight', weights),
			'--psfs-dd-price-c': color('ap_dd_price_color'),
			'--psfs-dd-price-fs': px('ap_dd_price_size'),
			'--psfs-om-bg': color('ap_m_overlay_bg'),
			'--psfs-om-bar-bg': color('ap_m_overlay_bar_bg'),
			'--psfs-om-ico': color('ap_m_overlay_icon')
		};
	}

	function apply() {
		var preview = document.querySelector('.js-psf-search-preview'),
			isCustom = $('select[id="psf_search_settings[search_style]"]').val() === 'custom',
			$bars = $('.js-psf-search-preview .psf-search-search-wrapp');

		$('body').toggleClass('psf-search-custom', isCustom);
		$('.js-psf-search-legacy-style').toggleClass('psf-search-ap-hidden', isCustom);
		$('.js-psf-search-ap-inactive-note').toggle(!isCustom);

		if (!preview) {
			return;
		}

		$.each(variables(), function (name, value) {
			preview.style.setProperty(name, value);
		});

		$bars.removeClass('psf-search-btn-attached psf-search-btn-inset psf-search-btn-separate psf-search-btn-content-icon psf-search-btn-content-text psf-search-btn-content-both psf-search-btn-arrow');
		if (isCustom) {
			$bars.addClass('psf-search-btn-' + choice('ap_btn_layout', ['attached', 'inset', 'separate']));
			$bars.addClass('psf-search-btn-content-' + choice('ap_btn_content', ['icon', 'text', 'both']));
			if (val('ap_btn_arrow') === 'on') {
				$bars.addClass('psf-search-btn-arrow');
			}
			// The preview's style switcher hides icons it doesn't know; PSF Custom uses the Solaris icon.
			$('.js-psf-search-preview .js-psf-search-ico-magnifier-solaris').removeClass('psf-search-hidden');
			// Text for "text" / "both" buttons when no label is set.
			var $label = $('.js-psf-search-preview .js-psf-search-search-submit-l');
			if ($.trim($label.text()) === '') {
				$label.text(cfg.submitFallback || 'Search');
			}
		}
	}

	$(function () {
		apply();

		$(document).on('input change', '[id^="psf_search_settings[ap_"], select[id="psf_search_settings[search_style]"], [id="psf_search_settings[show_submit_button]"], [id="psf_search_settings[search_submit_text]"]', function () {
			var key = (this.id || '').replace('psf_search_settings[', '').replace(']', '');
			delete overrides[key];
			setTimeout(apply, 0);
		});

		$(document).on('psf-search:color-change', function (event, $el, value) {
			var key = ($el && $el.attr('id') ? $el.attr('id') : '').replace('psf_search_settings[', '').replace(']', '');
			if (key.indexOf('ap_') === 0) {
				overrides[key] = value || '';
				apply();
			}
		});

		$(document).on('psf_search_settings_group_active', function () {
			setTimeout(apply, 0);
		});
	});
})(jQuery);
