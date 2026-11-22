(function ($) {

    var RADIO_SETTINGS_TOGGLE = {
        inputSel: 'psfs-options-toggle input[type=radio]',
        groupSel: 'psfs_settings-group',
        reloadChoices: function (name) {
            var _this = this,
                $group = $('[name="' + name + '"]').closest('.' + _this.groupSel),
                value = $('[name="' + name + '"]:checked').val(),
                currentClass = '';

            _this.hideAll($group);

            value = value.replace('_', '-');

            if (value.length > 0) {
                currentClass = 'psfs-opt-' + value;
            }

            if ($('.' + currentClass).length > 0) {
                $('.' + currentClass).fadeIn();
            }


        },
        hideAll: function ($group) {
            $group.find('tr[class*="psfs-opt-"]').hide();
        },
        registerListeners: function () {
            var _this = this;

            $('.' + _this.inputSel).on('change', function () {
                _this.reloadChoices($(this).attr('name'));
            });

        },
        init: function () {
            var _this = this,
                $sel = $('.' + _this.inputSel + ':checked');

            if ($sel.length > 0) {
                _this.registerListeners();

                $sel.each(function () {
                    _this.reloadChoices($(this).attr('name'));
                });

            }


        }

    };


    var CHECKBOX_SETTINGS_TOGGLE = {
        inputSel: 'psfs-options-cb-toggle input[type=checkbox]',
        groupSel: 'psfs_settings-group',
        reloadChoices: function ($el) {
            var _this = this,
                checked = $el.is(':checked'),
                groupClass = _this.getGroupSelector($el);

            $('.' + groupClass + ':not(.psfs-options-cb-toggle)').hide();

            if (checked) {
                $('.' + groupClass).each(function () {
                    if (!(
                        $(this).hasClass('js-psfs-adv-settings')
                        && $('.js-psfs-adv-settings-toggle').hasClass('woocommerce-input-toggle--disabled')
                    )
                    ) {
                        $(this).fadeIn();
                    }
                });
            }
        },
        getGroupSelector($el) {
            var $row = $el.closest('.psfs-options-cb-toggle'),
                className = '',
                classList = $row.attr('class').split(/\s+/);

            $.each(classList, function (index, item) {
                if (item.indexOf('js-psfs-cbtgroup-') !== -1) {
                    className = item;
                }
            });

            return className;
        },
        registerListeners: function () {
            var _this = this;

            $(document).on('change', '.' + _this.inputSel, function () {
                _this.reloadChoices($(this));
            });

        },
        refresh: function () {
            var _this = this,
                $sel = $('.' + _this.inputSel);
            if ($sel.length > 0) {
                $sel.each(function () {
                    var checked = $(this).is(':checked'),
                        groupClass = _this.getGroupSelector($(this));

                    if (checked) {
                        $('.' + groupClass).fadeIn();
                    } else {
                        $('.' + groupClass + ':not(.psfs-options-cb-toggle)').hide();
                    }
                });
            }
        },
        init: function () {
            var _this = this,
                $sel = $('.' + _this.inputSel);

            if ($sel.length > 0) {
                _this.registerListeners();

                $sel.each(function () {
                    _this.reloadChoices($(this));
                });

            }


        }

    };

    var CHECKBOX_SETTINGS_TOGGLE_SIBLING = {
        inputSel: 'js-psfs-options-toggle-sibling input[type=checkbox]',

        toogleSibling: function ($el) {
            var _this = this;
            var checked = $el.is(':checked');

            if (checked) {
                $el.closest('label').next().fadeIn();
            } else {
                $el.closest('label').next().hide();
            }
        },

        registerListeners: function () {
            var _this = this;

            $(document).on('change', '.' + _this.inputSel, function () {
                _this.toogleSibling($(this));
            });
        },

        init: function () {
            var _this = this;
            var $sel = $('.' + _this.inputSel);

            if ($sel.length > 0) {
                _this.registerListeners();

                $sel.each(function () {
                    _this.toogleSibling($(this));
                });
            }
        }
    };

    var CONDITIONAL_LAYOUT_SETTINGS = {
        layoutSelect: "select[id*='search_layout']",
        overlayMobile: "input[id*='enable_mobile_overlay']",
        switchLayoutBreakpoint: "input[id*='mobile_breakpoint']",
        mobileOverlayBreakpoint: "input[id*='mobile_overlay_breakpoint']",
        searchIconColor: "input[id*='search_icon_color']",
        $layoutSelectEl: null,
        $overlayMobileEl: null,
        $switchLayoutBreakpointEl: null,
        $mobileOverlayBreakpointEl: null,
        $searchIconColorEl: null,
        setConditions: function () {
            var _this = this,
                layoutVal = _this.$layoutSelectEl.find('option:selected').val(),
                overlayOnMobileVal = _this.$overlayMobileEl.is(':checked'),
                hasAdvSettings = $('.js-psfs-adv-settings-toggle').hasClass('woocommerce-input-toggle--enabled');

            _this.hideOption(_this.$switchLayoutBreakpointEl);
            _this.hideOption(_this.$mobileOverlayBreakpointEl);
            _this.hideOption(_this.$searchIconColorEl);

            $("input[id*='bg_search_icon_color']").closest('tr').show();

            switch (layoutVal) {
                case 'icon':

                    if (hasAdvSettings) {
                        _this.showOption(_this.$searchIconColorEl);
                    }

                    break;
                case 'icon-flexible':
                case 'icon-flexible-inv':

                    if (hasAdvSettings) {
                        _this.showOption(_this.$switchLayoutBreakpointEl);
                        _this.showOption(_this.$searchIconColorEl);
                    }

                    break;
                default:

                    if (hasAdvSettings) {
                        $("input[id*='bg_search_icon_color']").closest('tr').hide();
                    }
                    break;
            }

            if (overlayOnMobileVal) {
                _this.showOption(_this.$mobileOverlayBreakpointEl);
            }

        },
        hideOption: function ($el) {
            $el.closest('tr').hide();
        },
        showOption: function ($el) {
            $el.closest('tr').show();
        },
        registerListeners: function () {
            var _this = this;

            _this.$layoutSelectEl.on('change', function () {
                _this.setConditions();
            });

            _this.$overlayMobileEl.on('change', function () {
                _this.setConditions();
            });

        },
        init: function () {
            var _this = this,
                $layoutSelectEl = $(_this.layoutSelect);

            if ($layoutSelectEl.length > 0) {
                _this.$layoutSelectEl = $layoutSelectEl;
                _this.$overlayMobileEl = $(_this.overlayMobile);
                _this.$switchLayoutBreakpointEl = $(_this.switchLayoutBreakpoint);
                _this.$mobileOverlayBreakpointEl = $(_this.mobileOverlayBreakpoint);
                _this.$searchIconColorEl = $(_this.searchIconColor);
                _this.registerListeners();

                setTimeout(function () {
                    _this.setConditions();
                }, 400);
            }

        }

    };

    var AJAX_BUILD_INDEX = {
        actionTriggerClass: 'js-ajax-build-index',
        actionStopTriggerClass: 'js-ajax-stop-build-index',
        indexingWrapperClass: 'js-psfs-indexing-wrapper',
        indexerTabProgressClass: 'js-psfs-indexer-tab-progress',
        indexerTabErrorClass: 'js-psfs-indexer-tab-error',
        indexerMenuErrorClass: 'psfs-menu-warning-icon',
        getWrapper: function () {
            var _this = this;

            return $('.' + _this.indexingWrapperClass).closest('.psfs-settings-info');
        },
        registerListeners: function () {
            var _this = this;

            $(document).on('click', '.' + _this.actionTriggerClass, function (e) {
                e.preventDefault();

                var $btn = $(this);

                $btn.attr('disabled', 'disabled');

                $('.psfs-settings-info').addClass('psfs-ajax-build-index-wait');
                $('.' + _this.indexerTabErrorClass).removeClass('active');
                $('.' + _this.indexerTabProgressClass).addClass('active');

                var emergency = $btn.hasClass('js-ajax-build-index-emergency') ? true : false;

                if (emergency) {

                    $('.psfs-indexing-header__title').text('[Emergency mode] Wait... Indexing in progress');
                    $('.psfs-indexing-header__troubleshooting, .psfs-indexing-header__actions, .js-psfs-indexer-details').hide();
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'psfs_build_index',
                        emergency: emergency,
                        _wpnonce: psfs.nonces.build_index,
                    },
                    success: function (response) {
                        if (typeof response != 'undefined' && response.success) {
                            _this.getWrapper().html(response.data.html);
                            _this.heartbeat();
                        }
                    },
                    complete: function () {
                        $btn.removeAttr('disabled');
                        $('.psfs-settings-info').removeClass('psfs-ajax-build-index-wait');
                        if (emergency) {
                            window.location.reload();
                        }
                    }
                });
            })

            $(document).on('click', '.' + _this.actionStopTriggerClass, function (e) {
                e.preventDefault();

                var $btn = $(this);

                $btn.attr('disabled', 'disabled');
                _this.getWrapper().attr('data-stopping', '1');

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'psfs_stop_build_index',
                        _wpnonce: psfs.nonces.stop_build_index,
                    },
                    success: function (response) {
                        if (typeof response != 'undefined' && response.success) {
                            _this.getWrapper().html(response.data.html);
                            _this.heartbeat();
                        }
                    },
                    complete: function () {
                        _this.getWrapper().attr('data-stopping', '0');
                    }
                });
            })
        },
        heartbeat: function () {
            var _this = this;

            setTimeout(function () {

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'psfs_build_index_heartbeat',
                        _wpnonce: psfs.nonces.build_index_heartbeat,
                    },
                    success: function (response) {
                        if (typeof response != 'undefined' && response.success) {
                            if (_this.getWrapper().attr('data-stopping') === '1') {
                                return;
                            }
                            _this.getWrapper().html(response.data.html);

                            if (response.data.loop) {
                                _this.heartbeat();
                            } else {
                                $('.' + _this.indexerTabProgressClass).removeClass('active');
                                if (response.data.status === 'error') {
                                    $('.' + _this.indexerTabErrorClass).addClass('active');
                                } else if (response.data.status === 'completed') {
                                    $('.' + _this.indexerMenuErrorClass).remove();
                                }
                            }

                            if (!response.data.loop && response.data.refresh_once.length > 0) {
                                // If refresh cookie non exist and Troubleshooting tab is hidden then reload
                                if (
                                    !document.cookie.split(';').some(function (item) {
                                        return item.trim().indexOf('psfs_refresh_once=' + response.data.refresh_once) === 0;
                                    }) &&
                                    $('#psfs_troubleshooting-tab').css('display') === 'none'
                                ) {
                                    document.cookie = 'psfs_refresh_once=' + response.data.refresh_once;
                                    location.reload();
                                }
                            }

                        }
                    }
                });

            }, 1000);
        },
        detailsToggle: function () {
            var _this = this,
                display;


            $(document).on('click', '.js-psfs-indexing-details-trigger', function (e) {
                e.preventDefault();

                var $details = $('.js-psfs-indexer-details');

                if ($details.hasClass('show')) {
                    $details.removeClass('show');
                    $details.addClass('hide');
                    $('.js-psfs-indexing__showd').addClass('show').removeClass('hide');
                    $('.js-psfs-indexing__hided').addClass('hide').removeClass('show');
                    display = false;
                } else {
                    $details.addClass('show');
                    $details.removeClass('hide');
                    $('.js-psfs-indexing__showd').addClass('hide').removeClass('show');
                    $('.js-psfs-indexing__hided').addClass('show').removeClass('hide');
                    display = true;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: {
                        action: 'psfs_index_details_toggle',
                        _wpnonce: psfs.nonces.index_details_toggle,
                        display: display
                    }
                });
            });


        },
        init: function () {
            var _this = this;
            _this.registerListeners();

            if ($('.' + _this.indexingWrapperClass).length > 0 && typeof psfs['is_premium'] !== 'undefined') {
                _this.heartbeat();
            }
            _this.detailsToggle();
        }
    };

    var SELECTIZE = {
        init: function () {
            var _this = this;

            if ($('.psfs-selectize').length > 0) {
                $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'psfs_settings_list_custom_fields',
                        _wpnonce: $('.psfs-selectize').data('security')

                    },
                    success: function (res) {

                        if (typeof res != 'undefined' && typeof res.data != 'undefined') {
                            _this.initSelectize(res.data);
                        }
                    },
                    error: function () {
                        $('.psfs-selectize').removeClass('loading').parent().find('.psfs_settings-description-field').html(psfs.adminLabels.custom_fields_loading_error).css({'color': '#f04124'});
                    },
                });
            }

        },
        initSelectize: function (loadedOptions) {

            var $inputs = $('.psfs-selectize');


            if ($inputs.length > 0) {
                $inputs.each(function () {

                    var $input = $(this);
                    var disabledBeforeInit = $(this).data('disabled-before-init');
                    var optionsRaw = $input.data('options');
                    var options = loadedOptions;

                    if (disabledBeforeInit) {
                        $input.removeAttr('disabled');
                        $input.removeClass('loading');
                    }

                    if (optionsRaw.length > 0) {
                        optionsRaw = decodeURI(optionsRaw).replace(/"/g, '\\"');
                        optionsRaw = JSON.parse('["' + optionsRaw.replace(/&/g, '","').replace(/=/g, '","') + '"]');

                        var lastKey = '';

                        optionsRaw.forEach(function (el, i) {

                            if ((i + 1) % 2 === 0) {
                                var obj = {value: el, label: lastKey};
                                options.push(obj);
                                lastKey = '';
                            }
                            lastKey = el;
                        });

                    }

                    $(this).selectize({
                        persist: false,
                        maxItems: null,
                        valueField: 'key',
                        labelField: 'label',
                        searchField: ['value', 'label'],
                        options: options,
                        create: function (input) {
                            return {
                                value: input.key,
                                label: input.label
                            }
                        },
                        load: function (query, callback) {
                            if (!query.length) return callback();

                            $.ajax({
                                url: ajaxurl,
                                data: {
                                    action: 'psfs_settings_list_custom_fields',
                                    _wpnonce: $input.data('security')

                                },
                                error: function () {
                                    callback();
                                },
                                success: function (res) {
                                    callback(res.data);
                                }
                            });
                        }
                    });

                });
            }

        }
    };

    var TOOLTIP = {
        init: function () {
            var _this = this;
            var $tooltips = $('.js-psfs-tooltip');

            if ($tooltips.length > 0) {
                $tooltips.each(function () {
                    var element = $(this)[0];
                    var contentEl = $(this).attr('data-tooltip-html-el');
                    var placement = $(this).data('tooltip-placement');

                    // Accept only content containers generated by Helpers::createTooltip().
                    if (typeof contentEl !== 'string' || !/^js-psfs-tooltip-id[a-z0-9_-]*$/.test(contentEl)) {
                        return;
                    }

                    var content = $('div.' + contentEl + ' > .psfs-tooltip-wrapper')[0];

                    if (content) {
                        // The tooltip content must come only from the content container, never from the "title" attribute.
                        element.removeAttribute('title');

                        const instance = new PsfsTooltip(element, {
                            title: content,
                            placement: placement,
                            trigger: "hover",
                            html: true
                        });
                    }

                });
            }

        }
    };

    var ADVANCED_SETTINGS = {
        advClass: 'js-psfs-adv-settings',
        highlightClass: 'psfs-opt-highlight',
        transClass: 'psfs-opt-transition',
        init: function () {
            var _this = this;

            _this.clickListener();
            _this.setStartingState();
        },
        clickListener: function () {
            var _this = this;
            $(document).on('click', '.js-psfs-settings__advanced', function () {
                var $toggleEl = $('.js-psfs-adv-settings-toggle'),
                    choice;

                if ($toggleEl.hasClass('woocommerce-input-toggle--disabled')) {
                    choice = 'show';
                } else {
                    choice = 'hide';
                }

                _this.saveChoice(choice);

            });
        },
        setStartingState: function () {
            var _this = this,
                $options = $('.' + _this.advClass);

            if ($options.length > 0) {
                var showAdvanced = $('.js-psfs-adv-settings-toggle').hasClass('woocommerce-input-toggle--enabled');

                if (!showAdvanced) {
                    $options.hide();
                } else {
                    $options.show();
                    CHECKBOX_SETTINGS_TOGGLE.refresh();
                }
            }
        },
        saveChoice: function (choice) {
            var _this = this;

            $('.js-psfs-settings__advanced').append('<span class="psfs-adv-settings-saving">saving...</span>');

            $.ajax({
                url: ajaxurl,
                data: {
                    _wpnonce: psfs.nonces.advanced_options_switch,
                    action: 'psfs_adv_settings',
                    adv_settings_value: choice
                }
            }).done(function (data) {
                $('.psfs-adv-settings-saving').remove();
            });

            var $el = $('.js-psfs-adv-settings-toggle');

            if (choice === 'show') {
                $el.removeClass('woocommerce-input-toggle--disabled');
                $el.addClass('woocommerce-input-toggle--enabled');
            }

            if (choice === 'hide') {
                $el.removeClass('woocommerce-input-toggle--enabled');
                $el.addClass('woocommerce-input-toggle--disabled');
            }

            _this.toggleAdvancedOpt(choice);

        },
        toggleAdvancedOpt: function (action) {
            var _this = this,
                $options = $('.' + _this.advClass);

            if ($options.length > 0) {
                $options.addClass(_this.highlightClass);
                $options.addClass(_this.transClass);

                if (action === 'show') {

                    $options.fadeIn(500, function () {
                        setTimeout(function () {
                            $options.removeClass(_this.highlightClass);

                            setTimeout(function () {
                                $options.removeClass(_this.transClass);
                                CHECKBOX_SETTINGS_TOGGLE.refresh();
                                CONDITIONAL_LAYOUT_SETTINGS.setConditions();
                            }, 500)

                        }, 500);

                    });

                }

                if (action === 'hide') {
                    setTimeout(function () {
                        $options.removeClass(_this.transClass);
                        $options.fadeOut(500, function () {
                            $options.removeClass(_this.highlightClass);
                        });
                    }, 500);
                }
            }

        },
    };

    var STATS_INTERFACE = {
        placeholderClass: 'js-psfs-stats-placeholder',
        placeholderClassLoaded: 'js-psfs-stats-placeholder-loaded',
        preloaderClass: 'psfs-stats-preloader',
        settingsGroupSel: '#psfs_analytics',
        criticalSearchesLoadMoreClass: 'js-psfs-critical-searches-load-more',
        autocompleteWithResultsLoadMoreClass: 'js-psfs-autocomplete-with-results-load-more',
        searchPageWithResultsLoadMoreClass: 'js-psfs-search-page-with-results-load-more',
        checkPhraseStatusClass: 'js-psfs-stats-critical-check',
        checkPhraseStatusInitClass: 'js-psfs-stats-critical-check-init',
        rowLoadingClass: 'psfs-analytics-row-loading',
        languageSwitcherClass: 'js-psfs-analytics-lang',
        excludePhraseClass: 'js-psfs-analytics-exclude-phrase',
        checkIndexerAction: 'js-psfs-analytics-check-indexer',
        resetAnalyticsAction: 'js-psfs-analytics-reset',
        analyticsExportCSVAction: 'js-psfs-analytics-export-csv',
        init: function () {
            var _this = this;

            // Do nothing if the analytics module is disabled
            if (typeof psfs.analytics == 'undefined' || !psfs.analytics.enabled) {
                return;
            }

            _this.interfaceLoaderListener();
        },
        interfaceLoaderListener: function () {
            var _this = this,
                $languageSelectorEl = $('.' + _this.languageSwitcherClass);

            $(document).on('psfs_settings_group_active', function (event, el) {
                if ($(el).length > 0 && el.id === 'psfs_analytics') {
                    if (!_this.isLoaded()) {
                        _this.loadInterface();
                    }
                }
            });

            if ($languageSelectorEl.length > 0) {
                $languageSelectorEl.on('change', function () {
                    var $canvas = $('.' + _this.placeholderClass);
                    if ($canvas.length > 0) {
                        $canvas.html('');
                        _this.loadInterface();
                    }
                });
            }

        },
        isLoaded: function () {
            var _this = this;
            return $('.' + _this.placeholderClassLoaded).length > 0;
        },
        showPreloader: function () {
            var _this = this,
                $placeholder = $('.' + _this.placeholderClass),
                html = '<img class="' + _this.preloaderClass + '" src="' + psfs.analytics.images.placeholder + '" />';

            if ($placeholder.length) {
                $placeholder.append(html);
            }
        },
        loadInterface: function () {
            var _this = this,
                $lang = $('.' + _this.languageSwitcherClass + ' option:selected');

            _this.showPreloader();

            var data = {
                'action': 'psfs_load_stats_interface',
                '_wpnonce': psfs.analytics.nonce.analytics_load_interface
            };

            if ($lang.length > 0) {
                data.lang = $lang.val();
            }

            $.post(
                ajaxurl,
                data,
                function (response) {
                    var $el = $('.' + _this.placeholderClass);
                    if (typeof response == 'object' && response.success && $el.length > 0) {
                        $el.addClass(_this.placeholderClassLoaded);
                        $el.html(response.data.html);
                        _this.loadCheckCriticalSearchesListeners();
                        _this.loadMoreListeners();
                        _this.resetStatsListener();
                        _this.exportStatsListener();
                    }
                }
            );
        },
        loadCheckCriticalSearchesListeners: function () {
            var _this = this,
                $elements = $('.' + _this.checkPhraseStatusClass + ':not(.' + _this.checkPhraseStatusInitClass + ')');

            // Critical searches - check status
            $elements.on('click', function (e) {
                e.preventDefault();
                $(e.target).after('<img src="' + psfs.images.admin_preloader_url + '" />');
                _this.checkPhraseStatus($(e.target));
            })

            $elements.each(function () {
                $(this).addClass(_this.checkPhraseStatusInitClass);
            });

        },
        loadMoreListeners: function () {
            var _this = this;

            // Critical searches - load more
            $('.' + _this.criticalSearchesLoadMoreClass).on('click', function (e) {
                e.preventDefault();
                $(this).before('<img src="' + psfs.images.admin_preloader_url + '" />');
                $(this).closest('tr').addClass(_this.rowLoadingClass);
                _this.loadMoreCriticalSearches();
            })

            // Autocomplete with results - load more
            $('.' + _this.autocompleteWithResultsLoadMoreClass).on('click', function (e) {
                e.preventDefault();
                _this.loadMorePhrases('autocomplete', $(e.target));
            })

            // Search page with results - load more
            $('.' + _this.searchPageWithResultsLoadMoreClass).on('click', function (e) {
                e.preventDefault();
                _this.loadMorePhrases('search-page', $(e.target));
            })


        },
        resetStatsListener: function () {
            var _this = this;

            $('.' + _this.resetAnalyticsAction).on('click', function (e) {
                var $el = $(this);
                e.preventDefault();
                if (confirm(psfs.analytics.labels.reset_stats_confirm)) {
                    var data = {
                        'action': 'psfs_reset_stats',
                        '_wpnonce': psfs.analytics.nonce.reset_stats
                    };

                    $el.next().addClass('loading');

                    $.post(
                        ajaxurl,
                        data,
                        function (response) {
                            location.reload();
                        }
                    );
                }
            })
        },
        exportStatsListener: function () {
            var _this = this,
                $lang = $('.' + _this.languageSwitcherClass + ' option:selected');

            $('.' + _this.analyticsExportCSVAction).on('click', function (e) {
                var $el = $(this);
                e.preventDefault();
                var url = new URL(psfs.adminurl);
                url.searchParams.append('action', 'psfs_export_stats_csv');
                url.searchParams.append('context', $(this).data('context'));
                url.searchParams.append('_wpnonce', psfs.analytics.nonce.export_stats_csv);
                if ($lang.length > 0) {
                    url.searchParams.append('lang', $lang.val());
                }

                window.location = url;
            })
        },
        checkPhraseStatus: function ($el) {
            var _this = this,
                $lang = $('.' + _this.languageSwitcherClass + ' option:selected');

            var data = {
                'action': 'psfs_check_critical_phrase',
                'phrase': $el.closest('tr').find('td:nth-child(2)').text(),
                '_wpnonce': psfs.analytics.nonce.check_critical_phrase
            };

            if ($lang.length > 0) {
                data.lang = $lang.val();
            }

            $.post(
                ajaxurl,
                data,
                function (response) {
                    if (typeof response == 'object' && response.success) {
                        var $row = $el.closest('tr');
                        $el.closest('td').html(response.data.html);

                        var $excludeEl = $row.find('.' + _this.excludePhraseClass);
                        var $checkIndexerEl = $row.find('.' + _this.checkIndexerAction);

                        // Critical searches - exclude phrase
                        if ($excludeEl.length > 0) {
                            $excludeEl.on('click', function (e) {
                                e.preventDefault();
                                var html = '<p>Processing...</p>';
                                $(e.target).closest('td').html(html);
                                _this.excludePhrase($row);
                            })
                        }

                        // Check the indexer status
                        if ($checkIndexerEl.length > 0) {
                            $('.' + _this.checkIndexerAction).on('click', function (e) {
                                e.preventDefault();
                                var $tab = $('#psfs_performance-tab');
                                if ($tab.length > 0) {
                                    $tab[0].click();
                                    $([document.documentElement, document.body]).animate({
                                        scrollTop: 0
                                    }, 1000);
                                }
                            })
                        }
                    }
                }
            );
        },
        excludePhrase: function ($el) {
            var _this = this,
                $lang = $('.' + _this.languageSwitcherClass + ' option:selected');

            var data = {
                'action': 'psfs_exclude_critical_phrase',
                'phrase': $el.find('td:nth-child(2)').text(),
                '_wpnonce': psfs.analytics.nonce.exclude_critical_phrase
            };

            if ($lang.length > 0) {
                data.lang = $lang.val();
            }

            $.post(
                ajaxurl,
                data,
                function (response) {
                    if (typeof response == 'object' && response.success) {
                        $el.addClass('psfs-analytics-disable-row');
                        $el.find('td:last-child').html(response.data);
                    }
                }
            );
        },
        loadMoreCriticalSearches: function () {
            var _this = this,
                $lang = $('.' + _this.languageSwitcherClass + ' option:selected');

            var data = {
                'action': 'psfs_laod_more_critical_searches',
                'loaded': $('.js-psfs-critical-searches-row').length,
                '_wpnonce': psfs.analytics.nonce.load_more_critical_searches
            };

            if ($lang.length > 0) {
                data.lang = $lang.val();
            }

            $.post(
                ajaxurl,
                data,
                function (response) {
                    if (typeof response == 'object' && response.success) {
                        var $loadMoreRow = $('.' + _this.criticalSearchesLoadMoreClass).closest('tr');

                        if (response.data.html.length > 0) {
                            $loadMoreRow.before(response.data.html);
                        }

                        if (response.data.more > 0) {
                            $loadMoreRow.removeClass(_this.rowLoadingClass);
                            $loadMoreRow.find('img').remove();
                            $('.' + _this.criticalSearchesLoadMoreClass + ' span:first-child').text(response.data.more_label);
                        } else {
                            $loadMoreRow.remove();
                        }
                        _this.loadCheckCriticalSearchesListeners();
                    }
                }
            );
        },
        loadMorePhrases: function (context, $el) {
            var _this = this,
                $lang = $('.' + _this.languageSwitcherClass + ' option:selected');

            $el.before('<img src="' + psfs.images.admin_preloader_url + '" />');
            $el.closest('tr').addClass(_this.rowLoadingClass);

            if (context === 'autocomplete') {
                var data = {
                    'action': 'psfs_laod_more_autocomplete',
                    'loaded': $('.js-psfs-autocomplete-row').length,
                    '_wpnonce': psfs.analytics.nonce.load_more_autocomplete
                };
            } else {
                var data = {
                    'action': 'psfs_laod_more_search_page',
                    'loaded': $('.js-psfs-search-page-row').length,
                    '_wpnonce': psfs.analytics.nonce.load_more_search_page
                };
            }

            if ($lang.length > 0) {
                data.lang = $lang.val();
            }

            $.post(
                ajaxurl,
                data,
                function (response) {
                    if (typeof response == 'object' && response.success) {
                        var $tBody = $el.closest('tbody');
                        $tBody.html(response.data.html);
                    }
                }
            );
        },
    };

    window.PSFS_SEARCH_PREVIEW = {
        previewWrapper: {},
        searchWrapp: {},
        suggestionWrapp: {},
        searchInput: {},
        animateTypingInterval: null,
        init: function () {
            var _this = this;

            _this.previewWrapper = $('.js-psfs-preview');
            _this.searchWrapp = $('.js-psfs-search-wrapp');
            _this.suggestionWrapp = $('.js-psfs-suggestions-wrapp');
            _this.detailsWrapp = $('.js-psfs-details-wrapp');
            _this.searchInput = $('.js-psfs-search-input');

            _this.onChangeHandler();
            _this.onColorHandler();
            _this.onTypeHandler();

            _this.disableSubmit();

            _this.noResultsHandler();
            _this.fixSizesInit();

            _this.keepPreviewVisible();
            _this.animationController();
        },
        isChecked: function ($el) {
            return $el.length > 0 && $el.is(':checked') ? true : false;
        },
        isColor: function (color) {
            return (typeof color === 'string' && color.length === 7 && color.charAt(0) === '#');
        },
        camelCase: function (string) {
            var pieces = string.split('_');
            var camelCase = '';
            for (var i = 0; i < pieces.length; i++) {
                camelCase += pieces[i].charAt(0).toUpperCase() + pieces[i].slice(1);
            }

            return camelCase;
        },
        disableSubmit: function () {
            var timeout,
                $tooltip;

            $('.js-psfs-preview-source').on('click', function (e) {
                e.preventDefault();
                var relativeX = e.pageX - 100,
                    relativeY = e.pageY + 10,
                    tooltipHTML = '<div class="psfs-click-alert">' + window.psfs.adminLabels.preview + '</div>';

                if (typeof timeout != 'undefined') {
                    clearTimeout(timeout);
                    if ($tooltip) {
                        $tooltip.remove();
                    }
                }

                $('body').append(tooltipHTML);
                $tooltip = $('.psfs-click-alert');
                $tooltip.css({left: relativeX, top: relativeY});
                $('.psfs-preview-source').addClass('psfs-preview-source-no-click');

                timeout = setTimeout(function () {
                    $tooltip.fadeOut(500, function () {
                        $(this).remove();
                        $('.psfs-preview-source').removeClass('psfs-preview-source-no-click');
                    });
                }, 2000);

            });
        },
        noResultsHandler: function () {
            var _this = this,
                suggestionsTarget = '.js-psfs-preview .psfs-suggestion:not(.js-psfs-suggestion-nores)',
                noresTarget = '.js-psfs-suggestion-nores',
                target = "textarea[id*='search_no_results_text']";

            $(document).on('focus', target, function () {
                $(suggestionsTarget).addClass('psfs-hide');
                $(noresTarget).removeClass('psfs-hide');
                _this.detailsWrapp.addClass('psfs-hide');
                _this.suggestionWrapp.addClass('psfs-preview-nores');
            });

            $(document).on('blur', target, function () {
                $(suggestionsTarget).removeClass('psfs-hide');
                $(noresTarget).addClass('psfs-hide');
                _this.detailsWrapp.removeClass('psfs-hide');
                _this.suggestionWrapp.removeClass('psfs-preview-nores');
            });

        },
        onChangeHandler: function () {
            var _this = this,
                options = [
                    'show_submit_button',
                    'max_form_width',
                    'search_style',
                    'search_layout',
                    'show_product_image',
                    'show_product_sku',
                    'show_product_desc',
                    'show_product_price',
                    'show_matching_categories',
                    'show_categories_images',
                    'show_matching_tags',
                    'show_matching_brands',
                    'show_brands_images',
                    'show_matching_posts',
                    'show_matching_pages',
                    'suggestions_limit',
                    'show_details_box',
                    'show_matching_post_type',
                    'show_post_type_images'
                ];
            for (var i = 0; i < options.length; i++) {
                var tag = ['search_style', 'search_layout'].includes(options[i]) ? 'select' : 'input',
                    selector = tag + "[id='psfs_settings\\[" + options[i] + "\\]']",
                    altSelector = tag + "[id^='psfs_settings'][data-option-trigger='" + options[i] + "']",
                    $el = $(selector),
                    $altEl = $(altSelector),
                    methodToCall = 'onChange' + _this.camelCase(options[i]);

                if (typeof _this[methodToCall] == 'function' && $el.length > 0) {
                    $.each($el, function (index, $elItem) {
                        _this[methodToCall]($($elItem), $($elItem).val());
                    });

                    $(document).on('change', selector, function () {
                        methodToCall = $(this).attr('id').replace(']', '').replace('psfs_settings[', '');
                        methodToCall = 'onChange' + _this.camelCase(methodToCall);
                        if (typeof (_this[methodToCall]) === 'function') {
                            _this[methodToCall]($(this), this.value);
                        }
                    });
                } else if (typeof _this[methodToCall] == 'function' && $altEl.length > 0) {
                    _this[methodToCall]($altEl, $altEl.val());
                    $.each($altEl, function (index, $altElItem) {
                        _this[methodToCall]($($altElItem), $($altElItem).val());
                    });

                    $(document).on('change', altSelector, function () {
                        methodToCall = $(this).data('option-trigger');
                        methodToCall = 'onChange' + _this.camelCase(methodToCall);
                        if (typeof (_this[methodToCall]) === 'function') {
                            _this[methodToCall]($(this), this.value);
                        }
                    });
                } else {
                    // Fallback for methods related to non-existing elements (eg. brands)
                    _this[methodToCall]('', '');
                }
            }
        },
        onColorHandler: function () {
            var _this = this,
                options = [
                    'search_icon_color',
                    'bg_input_underlay_color',
                    'bg_input_color',
                    'text_input_color',
                    'border_input_color',
                    'bg_submit_color',
                    'text_submit_color',
                    'sug_bg_color',
                    'sug_hover_color',
                    'sug_text_color',
                    'sug_highlight_color',
                    'sug_border_color'
                ];
            for (var i = 0; i < options.length; i++) {
                var selector = "input[id*='" + options[i] + "']";
                var $el = $(selector),
                    methodToCall = 'onColor' + _this.camelCase(options[i]);

                _this[methodToCall]($el, $el.val());

                $(document).on("change", selector, function () {
                    methodToCall = $(this).attr('id').replace(']', '').replace('psfs_settings[', '');
                    methodToCall = 'onColor' + _this.camelCase(methodToCall);
                    _this[methodToCall]($(this), this.value);
                });
            }
        },
        onColorChangeHandler: function ($el, value) {
            var _this = this,
                methodToCall = $el.attr('id').replace(']', '').replace('psfs_settings[', '');
            methodToCall = 'onColor' + _this.camelCase(methodToCall);
            _this[methodToCall]($el, value);
        },
        onTypeHandler: function () {
            var _this = this,
                options = [
                    'search_submit_text',
                    'search_placeholder',
                    'search_no_results_text',
                    'search_see_all_results_text'
                ];
            for (var i = 0; i < options.length; i++) {
                // Scope to the settings form, so elements injected elsewhere on the page (e.g. admin notices) are ignored.
                var elType = options[i] === 'search_no_results_text' ? 'textarea' : 'input',
                    selector = "form.psfs-eq-settings-form " + elType + "[id*='" + options[i] + "']",
                    $el = $(selector),
                    methodToCall = 'onType' + _this.camelCase(options[i],);

                _this[methodToCall]($el, $el.val());

                $(document).on("input", selector, function (a) {
                    methodToCall = $(a.target).attr('id').replace(']', '').replace('psfs_settings[', '');
                    methodToCall = 'onType' + _this.camelCase(methodToCall);
                    _this[methodToCall]($(a.target), this.value);
                });
            }
        },
        onChangeMaxFormWidth: function ($el, value) {
            var _this = this;

            if (value.length > 0 && value != '0') {
                _this.searchWrapp.css('max-width', value + 'px');
                _this.suggestionWrapp.css('max-width', value + 'px');
            } else {
                _this.searchWrapp.css('max-width', '100%');
                _this.suggestionWrapp.css('max-width', '100%');
            }

            _this.onChangeShowDetailsBox($("input[id*='show_details_box']"));
        },
        onChangeShowSubmitButton: function ($el, value) {
            var _this = this,
                $submit = $('.js-psfs-search-submit');

            if (_this.isChecked($el)) {
                _this.searchWrapp.addClass('psfs-has-submit');
                _this.searchWrapp.removeClass('psfs-no-submit');
                $submit.show();
                $('.psfs-sf-wrapp > .psfs-ico-magnifier').hide();

                var $textSubmitBgEl = $("input[id*='bg_submit_color']");
                var $textSubmitTextEl = $("input[id*='text_submit_color']");
                _this.onColorBgSubmitColor($textSubmitBgEl, $textSubmitBgEl.val());
                _this.onColorTextSubmitColor($textSubmitTextEl, $textSubmitTextEl.val());

                setTimeout(function () {
                    _this.positionPreloader();
                }, 10);

            } else {
                _this.searchWrapp.addClass('psfs-no-submit');
                _this.searchWrapp.removeClass('psfs-has-submit');
                $submit.hide();
                $('.psfs-sf-wrapp > .psfs-ico-magnifier').show();
                _this.positionPreloader();
            }


        },
        onChangeShowProductImage: function ($el, value) {
            var _this = this,
                $imageWrapp = $('.js-psfs-si'),
                $contentWrapp = $('.js-psfs-content-wrapp');

            if (_this.isChecked($el)) {
                _this.suggestionWrapp.addClass('psfs-has-img');

                $('.psfs-suggestion-product > .psfs-st').remove();
                $('.psfs-suggestion-product > .psfs-sp').remove();

                $contentWrapp.show();
                $imageWrapp.show();

            } else {
                _this.suggestionWrapp.removeClass('psfs-has-img');

                $contentWrapp.each(function () {
                    $(this).closest('.psfs-suggestion-product').append($(this).html());
                });

                $contentWrapp.hide();
                $imageWrapp.hide();
            }

        },
        onChangeShowProductSku: function ($el, value) {
            var _this = this,
                $skuWrapp = $('.js-psfs-sku');

            if (_this.isChecked($el)) {
                _this.suggestionWrapp.addClass('psfs-has-sku');
                $skuWrapp.show();

            } else {
                _this.suggestionWrapp.removeClass('psfs-has-sku');
                $skuWrapp.hide();
            }

        },
        onChangeShowProductDesc: function ($el, value) {
            var _this = this,
                $descWrapp = $('.js-psfs-sd');

            if (_this.isChecked($el)) {
                _this.suggestionWrapp.addClass('psfs-has-desc');
                $descWrapp.show();

            } else {
                _this.suggestionWrapp.removeClass('psfs-has-desc');
                $descWrapp.hide();
            }

        },
        onChangeShowProductPrice: function ($el, value) {
            var _this = this,
                $priceWrapp = $('.js-psfs-sp');

            if (_this.isChecked($el)) {
                _this.suggestionWrapp.addClass('psfs-has-price');
                $priceWrapp.show();

            } else {
                _this.suggestionWrapp.removeClass('psfs-has-price');
                $priceWrapp.hide();
            }

        },
        onChangeShowMatchingCategories: function ($el, value) {
            var _this = this,
                $headline = $('.psfs-suggestion-headline-cat'),
                $items = $('.psfs-suggestion-cat');

            if (_this.isChecked($el)) {
                $headline.show();
                $items.show();
                $items.removeClass('js-psfs-suggestion-hidden');
            } else {
                $headline.hide();
                $items.hide();
                $items.addClass('js-psfs-suggestion-hidden');
            }

            var $limitInput = $("input[id*='suggestions_limit']");
            _this.onChangeSuggestionsLimit($limitInput, $limitInput.val());

        },
        onChangeShowCategoriesImages: function ($el, value) {
            var _this = this,
                $contentWrapp = $('.js-psfs-suggestion-cat');

            if (_this.isChecked($el)) {
                $contentWrapp.addClass('psfs-has-img');
            } else {
                $contentWrapp.removeClass('psfs-has-img');
            }
        },
        onChangeShowMatchingTags: function ($el, value) {
            var _this = this,
                $headline = $('.psfs-suggestion-headline-tag'),
                $items = $('.psfs-suggestion-tag');

            if (_this.isChecked($el)) {
                $headline.show();
                $items.show();
                $items.removeClass('js-psfs-suggestion-hidden');
            } else {
                $headline.hide();
                $items.hide();
                $items.addClass('js-psfs-suggestion-hidden');
            }

            var $limitInput = $("input[id*='suggestions_limit']");
            _this.onChangeSuggestionsLimit($limitInput, $limitInput.val());

        },
        onChangeShowMatchingBrands: function ($el, value) {
            var _this = this,
                $headline = $('.psfs-suggestion-headline-brand'),
                $items = $('.psfs-suggestion-brand');

            if (_this.isChecked($el)) {
                $headline.show();
                $items.show();
                $items.removeClass('js-psfs-suggestion-hidden');
            } else {
                $headline.hide();
                $items.hide();
                $items.addClass('js-psfs-suggestion-hidden');
            }

            var $limitInput = $("input[id*='suggestions_limit']");
            _this.onChangeSuggestionsLimit($limitInput, $limitInput.val());

        },
        onChangeShowBrandsImages: function ($el, value) {
            var _this = this,
                $contentWrapp = $('.js-psfs-suggestion-brand');

            if (_this.isChecked($el)) {
                $contentWrapp.addClass('psfs-has-img');
            } else {
                $contentWrapp.removeClass('psfs-has-img');
            }
        },
        onChangeShowMatchingPosts: function ($el, value) {
            var _this = this,
                $headline = $('.psfs-suggestion-headline-post'),
                $items = $('.psfs-suggestion-post');

            if (_this.isChecked($el)) {
                $headline.show();
                $items.show();
                $items.removeClass('js-psfs-suggestion-hidden');
            } else {
                $headline.hide();
                $items.hide();
                $items.addClass('js-psfs-suggestion-hidden');
            }

            var $limitInput = $("input[id*='suggestions_limit']");
            _this.onChangeSuggestionsLimit($limitInput, $limitInput.val());

        },
        onChangeShowMatchingPages: function ($el, value) {
            var _this = this,
                $headline = $('.psfs-suggestion-headline-page'),
                $items = $('.psfs-suggestion-page');

            if (_this.isChecked($el)) {
                $headline.show();
                $items.show();
                $items.removeClass('js-psfs-suggestion-hidden');
            } else {
                $headline.hide();
                $items.hide();
                $items.addClass('js-psfs-suggestion-hidden');
            }

            var $limitInput = $("input[id*='suggestions_limit']");
            _this.onChangeSuggestionsLimit($limitInput, $limitInput.val());

        },
        onChangeShowMatchingPostType: function ($el, value) {
            var $postType = $($el).data('post-type');
            var _this = this,
                $headline = $('.psfs-suggestion-headline-' + $postType),
                $items = $('.psfs-suggestion-' + $postType);

            if (_this.isChecked($el)) {
                $headline.show();
                $items.show();
                $items.removeClass('js-psfs-suggestion-hidden');
            } else {
                $headline.hide();
                $items.hide();
                $items.addClass('js-psfs-suggestion-hidden');
            }

            var $limitInput = $("input[id*='suggestions_limit']");
            _this.onChangeSuggestionsLimit($limitInput, $limitInput.val());
        },
        onChangeShowPostTypeImages: function ($el, value) {
            var $postType = $($el).data('post-type');
            var _this = this,
                $contentWrapp = $('.js-psfs-suggestion-' + $postType);

            if (_this.isChecked($el)) {
                $contentWrapp.addClass('psfs-has-img');
            } else {
                $contentWrapp.removeClass('psfs-has-img');
            }
        },
        onChangeSuggestionsLimit: function ($el, value) {
            setTimeout(function () {
                var _this = this,
                    i = 0,
                    limit = 7,
                    $duplicated = $('.psfs-suggestion-duplicated'),
                    types = ['brand', 'cat', 'tag', 'product'].concat(typeof psfs.postTypes === 'undefined' ? ['post', 'page'] : psfs.postTypes);

                if (value.length > 0 && value != '0') {
                    limit = Math.min(Math.abs(value), 15);
                }

                if ($duplicated.length > 0) {
                    $duplicated.remove();
                }

                var prototypes = [];

                for (i = 0; i < types.length; i++) {
                    var prototype = $('.psfs-suggestion-' + types[i] + ':not(.js-psfs-suggestion-hidden)');
                    if (prototype.length > 0) {
                        prototypes.push(prototype);
                    }
                }

                var total = prototypes.length;

                if (prototypes.length > 0) {

                    var slots = limit - prototypes.length;
                    var lastProtoypeIndex = prototypes.length - 1;

                    while (slots > 0) {

                        var $cloned = prototypes[lastProtoypeIndex].clone();
                        $cloned.addClass('psfs-suggestion-duplicated');
                        $cloned.removeClass('psfs-suggestion-selected');
                        prototypes[lastProtoypeIndex].after($cloned);
                        total++

                        lastProtoypeIndex--;
                        if (lastProtoypeIndex < 0) {
                            lastProtoypeIndex = prototypes.length - 1;
                        }
                        slots--;
                    }


                }

                if (total > limit) {
                    $el.val(total);
                }
            }, 10);

        },
        onChangeShowDetailsBox: function ($el, value) {
            var _this = this;

            if (_this.isChecked($el)) {
                _this.detailsWrapp.show();
                _this.searchWrapp.addClass('psfs-is-detail-box');
                _this.previewWrapper.addClass('psfs-is-details');
                _this.previewWrapper.addClass('psfs-details-right');


                setTimeout(function () {

                    $('.psfs-suggestion-product:not(.psfs-suggestion-duplicated)').addClass('psfs-suggestion-selected');

                    var $visibleSearchWrapp = $('.js-psfs-preview-bar-example');
                    var searchWidth = $visibleSearchWrapp.width();

                    if (searchWidth >= 550) {
                        _this.previewWrapper.addClass('psfs-full-width');

                        var realWidth = getComputedStyle($visibleSearchWrapp[0]).width;
                        realWidth = Math.round(parseFloat(realWidth.replace('px', '')));

                        if (realWidth % 2 == 0) {
                            _this.suggestionWrapp.css('width', Math.round(realWidth / 2));
                            _this.detailsWrapp.css('width', Math.round(realWidth / 2));
                        } else {
                            _this.suggestionWrapp.css('width', Math.floor(realWidth / 2));
                            _this.detailsWrapp.css('width', Math.ceil(realWidth / 2));
                        }

                    } else {
                        _this.suggestionWrapp.width(_this.searchWrapp.width());
                    }

                }, 10);


            } else {
                _this.detailsWrapp.hide();
                _this.searchWrapp.removeClass('psfs-is-detail-box');
                _this.previewWrapper.removeClass('psfs-is-details');
                _this.previewWrapper.removeClass('psfs-details-right');
                _this.previewWrapper.removeClass('psfs-full-width');
                $('.psfs-suggestion-product').removeClass('psfs-suggestion-selected');
                _this.suggestionWrapp.css('width', '');
                _this.detailsWrapp.css('width', '');
            }
        },
        onChangeSearchStyle: function ($el, value) {
            var _this = this,
                themes = ['solaris', 'pirx', 'pirx-compact'],
                $inputSubmitButton = $('input[id*="show_submit_button"]'),
                $inputSubmitBgColor = $('label[for*="bg_submit_color"]'),
                i;

            $('.psfs-ico-magnifier').addClass('psfs-hidden');

            for (i = 0; i < themes.length; i++) {
                $('.js-psfs-preview').removeClass('psfs-open-' + themes[i]);
                $('.js-psfs-search-wrapp').removeClass('psfs-style-' + themes[i]);
            }

            $('.js-psfs-ico-magnifier-' + value).removeClass('psfs-hidden');

            $('.js-psfs-preview').addClass('psfs-open-' + value);
            $('.js-psfs-search-wrapp').addClass('psfs-style-' + value);
            if (value === 'pirx-compact') {
                $('.js-psfs-search-wrapp').addClass('psfs-style-pirx');
                $('.js-psfs-ico-magnifier-pirx').removeClass('psfs-hidden');
            }

            $('label[for*="bg_input_underlay_color"]').closest('tr').removeClass('psfs-hidden');

            if (value === 'solaris') {
                $('label[for*="bg_input_underlay_color"]').closest('tr').addClass('psfs-hidden');
                $('label[for*="show_submit_button"] .js-psfs-tooltip').addClass('psfs-hidden');
                $('label[for*="search_submit_text"]').closest('tr').removeClass('psfs-hidden');
                $('input[id*="search_submit_text"]').prop("disabled", false);
                $inputSubmitBgColor.closest('tr').removeClass('psfs-hidden');
                $('label[for*="text_submit_color"] > span:nth-child(1)').removeClass('psfs-hidden');
                $('label[for*="text_submit_color"] > span:nth-child(2)').addClass('psfs-hidden');
                $inputSubmitButton.prop("disabled", false);
                $('.js-psfs-submit-button-pirx-tooltip').removeClass('psfs-hidden');
                $('.psfs-sf-wrapp').css('background-color', '');
                var $biucPicker = $('input[id*="bg_input_underlay_color"]').closest('tr').find('.wp-picker-clear');
                if ($biucPicker.length > 0) {
                    $biucPicker[0].click();
                }

                setTimeout(function () {
                    _this.positionPreloader();
                }, 300);
            }

            if (value === 'pirx' || value === 'pirx-compact') {
                $('label[for*="show_submit_button"] .js-psfs-tooltip').removeClass('psfs-hidden');
                $('label[for*="search_submit_text"]').closest('tr').addClass('psfs-hidden');
                $('input[id*="search_submit_text"]').prop("disabled", true);
                $('label[for*="bg_submit_color"]').closest('tr').addClass('psfs-hidden');
                $('label[for*="text_submit_color"] > span:nth-child(2)').removeClass('psfs-hidden');
                $('label[for*="text_submit_color"] > span:nth-child(1)').addClass('psfs-hidden');
                $('input[id*="search_submit_text"]').val('');

                var $cPicker = $inputSubmitBgColor.closest('tr').find('.wp-picker-clear');
                if ($cPicker.length > 0) {
                    $cPicker[0].click();
                }
                _this.onColorBgSubmitColor();

                if (!$inputSubmitButton.is(':checked')) {
                    $inputSubmitButton[0].click();
                }

                $inputSubmitButton.prop("disabled", true);

                _this.onTypeSearchSubmitText($('input[id*="search_submit_text"]'), '');

                setTimeout(function () {
                    _this.positionPreloader();
                }, 10);

            }
        },
        onChangeSearchLayout: function ($el, value) {
            var _this = this,
                $labels = $('.js-psfs-preview-device-info'),
                $barExample = $('.js-psfs-preview-bar-example'),
                $iconExample = $('.js-psfs-preview-icon-example'),
                i;

            $('.js-psfs-preview-device-info > span').addClass('psfs-hidden');
            $iconExample.removeClass('psfs-preview-icon-only');

            switch (value) {
                case 'classic':
                    $labels.addClass('psfs-hidden');
                    $barExample.removeClass('psfs-hidden');
                    $iconExample.addClass('psfs-hidden');
                    break;
                case 'icon':
                    $labels.addClass('psfs-hidden');
                    $barExample.addClass('psfs-hidden');
                    $iconExample.removeClass('psfs-hidden');
                    $('.psfs-search-form').removeClass('psfs-hidden');
                    $('.psfs-search-icon-arrow').removeClass('psfs-hidden');
                    $iconExample.addClass('psfs-preview-icon-only');
                    break;
                case 'icon-flexible':
                    $labels.removeClass('psfs-hidden');
                    $barExample.removeClass('psfs-hidden');
                    $iconExample.removeClass('psfs-hidden');
                    $iconExample.find('.psfs-search-form').addClass('psfs-hidden');
                    $iconExample.find('.psfs-search-icon-arrow').addClass('psfs-hidden');
                    $('.js-psfs-preview-device-info[data-device="desktop"] span:nth-child(2)').removeClass('psfs-hidden');
                    $('.js-psfs-preview-device-info[data-device="mobile"] span:nth-child(1)').removeClass('psfs-hidden');
                    break;
                case 'icon-flexible-inv':
                    $labels.removeClass('psfs-hidden');
                    $barExample.removeClass('psfs-hidden');
                    $iconExample.removeClass('psfs-hidden');
                    $iconExample.find('.psfs-search-form').addClass('psfs-hidden');
                    $iconExample.find('.psfs-search-icon-arrow').addClass('psfs-hidden');
                    $('.js-psfs-preview-device-info[data-device="desktop"] span:nth-child(1)').removeClass('psfs-hidden');
                    $('.js-psfs-preview-device-info[data-device="mobile"] span:nth-child(2)').removeClass('psfs-hidden');
                    break;
            }
        },
        onColorSearchIconColor: function ($el, value) {
            var _this = this;
            if (_this.isColor(value)) {
                _this.searchWrapp.find('.psfs-ico-magnifier-handler path').css('fill', value);
            } else {
                _this.searchWrapp.find('.psfs-ico-magnifier-handler path').css('fill', '');
            }
        },
        onColorBgInputColor: function ($el, value) {
            var _this = this;
            if (_this.isColor(value)) {
                _this.searchInput.css('background-color', value);
            } else {
                _this.searchInput.css('background-color', '');
            }
        },
        onColorBgInputUnderlayColor: function ($el, value) {
            var _this = this,
                $underlayEl = $('.psfs-style-pirx .psfs-sf-wrapp');
            if (_this.isColor(value)) {
                $underlayEl.css('background-color', value);
            } else {
                $underlayEl.css('background-color', '');
            }
        },
        onColorTextInputColor: function ($el, value) {
            var _this = this,
                styleClass = 'psfs-preview-placeholder-style';

            if (_this.isColor(value)) {

                var style = '<style class="' + styleClass + '">';
                style += '.psfs-search-input {color:' + value + '!important;}';
                style += '.psfs-ico-magnifier path {fill:' + value + '}';
                style += '</style>';

                $('head').append(style);

                _this.searchInput.css('color', value);

            } else {
                _this.searchInput.css('color', '');
                var $styleEl = $('.' + styleClass);
                if ($styleEl.length > 0) {
                    $styleEl.remove();
                }
            }
        },
        onColorBorderInputColor: function ($el, value) {
            var _this = this;
            if (_this.isColor(value)) {
                _this.searchInput.css('border-color', value);
            } else {
                _this.searchInput.css('border-color', '');
            }
        },
        onColorBgSubmitColor: function ($el, value) {
            var _this = this,
                styleClass = 'psfs-preview-submit-style',
                submitEnabled = this.isChecked($("input[id*='show_submit_button']"));

            if (submitEnabled && _this.isColor(value)) {

                var style = '<style class="' + styleClass + '">';
                style += '.psfs-search-submit::before{border-color: transparent ' + value + '!important;}';
                style += '.psfs-search-submit:hover::before{border-right-color: ' + value + '!important;}';
                style += '.psfs-search-submit:focus::before{border-right-color: ' + value + '!important;}';
                style += '.psfs-search-submit{background-color: ' + value + '!important;}';
                style += '.psfs-om-bar .psfs-om-return{background-color: ' + value + '!important;}';
                style += '</style>';

                $('head').append(style);

            } else {
                var $styleEl = $('.' + styleClass);
                if ($styleEl.length > 0) {
                    $styleEl.remove();
                }
            }
        },
        onColorTextSubmitColor: function ($el, value) {
            var _this = this,
                submitEnabled = this.isChecked($("input[id*='show_submit_button']"));

            if (submitEnabled && _this.isColor(value)) {

                $('.js-psfs-search-submit').css('color', value);
                $('.psfs-search-submit .psfs-ico-magnifier path').css('fill', value);

            } else {
                _this.searchInput.css('background-color', '');

                $('.js-psfs-search-submit').css('color', '');
                $('.psfs-search-submit .psfs-ico-magnifier path').css('fill', '');
            }
        },
        onColorSugBgColor: function ($el, value) {
            var _this = this,
                styleClass = 'psfs-preview-sugbgcol-style';

            if (_this.isColor(value)) {

                var style = '<style class="' + styleClass + '">';
                style += '.psfs-suggestions-wrapp,';
                style += '.psfs-details-wrapp';
                style += '{background-color: ' + value + '!important;}';
                style += '</style>';

                $('head').append(style);

            } else {
                var $styleEl = $('.' + styleClass);
                if ($styleEl.length > 0) {
                    $styleEl.remove();
                }
            }
        },
        onColorSugHoverColor: function ($el, value) {
            var _this = this;
            if (_this.isColor(value)) {
                setTimeout(function () {
                    $('.psfs-suggestion-selected').css('background-color', value);
                }, 50);
            } else {
                $('.psfs-suggestion-selected').css('background-color', '');
            }
        },
        onColorSugTextColor: function ($el, value) {
            var _this = this,
                styleClass = 'psfs-preview-sugtextcol-style';

            if (_this.isColor(value)) {

                var style = '<style class="' + styleClass + '">';
                style += '.psfs-suggestions-wrapp *,';
                style += '.psfs-details-wrapp *,';
                style += '.psfs-sd,';
                style += '.psfs-suggestion *';
                style += '{color: ' + value + '!important;}';
                style += '</style>';

                $('head').append(style);

            } else {
                var $styleEl = $('.' + styleClass);
                if ($styleEl.length > 0) {
                    $styleEl.remove();
                }
            }
        },
        onColorSugHighlightColor: function ($el, value) {
            var _this = this,
                styleClass = 'psfs-preview-sughighlight-style';

            if (_this.isColor(value)) {

                var style = '<style class="' + styleClass + '">';
                style += '.psfs-st strong,';
                style += '.psfs-sd strong';
                style += '{color: ' + value + '!important;}';
                style += '</style>';

                $('head').append(style);

            } else {
                var $styleEl = $('.' + styleClass);
                if ($styleEl.length > 0) {
                    $styleEl.remove();
                }
            }
        },
        onColorSugBorderColor: function ($el, value) {
            var _this = this,
                styleClass = 'psfs-preview-sugborder-style';

            if (_this.isColor(value)) {

                var style = '<style class="' + styleClass + '">';
                style += '.psfs-suggestions-wrapp,';
                style += '.psfs-details-wrapp,';
                style += '.psfs-suggestion,';
                style += '.psfs-datails-title,';
                style += '.psfs-details-more-products';
                style += '{border-color: ' + value + '!important;}';
                style += '</style>';

                $('head').append(style);

            } else {
                var $styleEl = $('.' + styleClass);
                if ($styleEl.length > 0) {
                    $styleEl.remove();
                }
            }
        },
        onTypeSearchSubmitText: function ($el, value) {
            var _this = this,
                $label = $('.js-psfs-search-submit-l'),
                $icon = $('.js-psfs-search-submit-m');

            if (value.length > 0) {
                $label.text(value);
                $label.show();
                $icon.hide();
            } else {
                $label.text('');
                $label.hide();
                $icon.show();
            }

            _this.positionPreloader();
        },
        onTypeSearchPlaceholder: function ($el, value) {
            var _this = this;
            if (value.length == 0) {
                value = psfs.labels.search_placeholder;
            }
            _this.searchInput.attr('placeholder', value);
        },
        onTypeSearchNoResultsText: function ($el, value) {
            var _this = this,
                html = value;

            if (value.length == 0) {
                try {
                    html = JSON.parse(psfs.labels.no_results);
                    // Fix invalid HTML
                    var tmpEl = document.createElement('div');
                    tmpEl.innerHTML = html;
                    html = tmpEl.innerHTML;
                } catch (e) {

                }
            }

            if (_this.isHTMLPossiblyDangerous(html)) {
                html = 'You used invalid HTML tags or attributes!';
            }

            $('.js-psfs-suggestion-nores').html(html);
        },
        onTypeSearchSeeAllResultsText: function ($el, value) {
            if (value.length == 0) {
                value = psfs.labels.show_more;
            }
            $('.js-psfs-st-more-label').text(value);
        },
        positionPreloader: function () {
            var $submit = $('.js-psfs-search-wrapp:not(.psfs-hidden) .js-psfs-search-submit'),
                visible = $submit.is(":visible"),
                style = $("select[id*='search_style'] option:selected").val(),
                right = ($submit.width() + 35);

            if (style === 'pirx' || style === 'pirx-compact') {
                right = 38;
            }

            if (!visible && style === 'solaris') {
                right = 7;
            }

            $('.psfs-preloader').css('right', right + 'px');
        },
        fixSizesInit: function () {
            var _this = this;

            $(document).on('click', '#psfs_autocomplete-tab', function () {
                _this.onChangeShowDetailsBox($("input[id*='show_details_box']"));
            });

        },
        keepPreviewVisible: function () {
            var $movingEl = $('.js-psfs-preview-inner'),
                $startEl = $('.js-psfs-preview'),
                $endEl = $('.js-psfs-preview-source'),
                $boundaryEl = $('.psfs-eq-settings-form');

            $(window).on('scroll.autocomplete', function () {
                var currentTop = $(document).scrollTop(),
                    topEdge = $startEl[0].getBoundingClientRect().top,
                    breakPoint = topEdge - 40;
                if (breakPoint < 0) {
                    var offset = (-1 * breakPoint) < 1 ? 0 : (-1 * breakPoint),
                        bottomLimit = $endEl.offset().top + $endEl.outerHeight(false),
                        boundaryBottom = Math.floor($boundaryEl.offset().top + $boundaryEl.outerHeight(false)) - 90;

                    if (bottomLimit <= boundaryBottom) {
                        $movingEl.css('top', offset + 'px');
                    } else {
                        if ((currentTop + 40) < $movingEl.offset().top) {
                            var tmp = $movingEl.css('top').replace(/px/i, '') - 10;
                            $movingEl.css('top', tmp + 'px');
                        }
                    }
                } else {
                    $movingEl.css('top', 0);
                }
            });
        },
        animationController: function () {
            var that = this;

            $(window).on('load', function () {
                var $searchBarSettings = $('#psfs_form_body-tab');
                if ($searchBarSettings.length && $searchBarSettings.hasClass('nav-tab-active')) {
                    that.startAnimateTyping();
                }
            });

            $('.psfs_settings-nav-tab-wrapper > a').on('click', function () {
                if ($(this).attr('id') === 'psfs_form_body-tab') {
                    that.stopAnimateTyping();
                    that.startAnimateTyping();
                } else {
                    that.stopAnimateTyping();
                }
            });

            $('.psfs_settings-nav-tab-wrapper > a').on('click', function () {
                if ($(this).attr('id') === 'psfs_form_body-tab') {
                    that.stopAnimateTyping();
                    that.startAnimateTyping();
                } else {
                    that.stopAnimateTyping();
                }
            });

            $('input[id*="search_placeholder"]').on('focus', function () {
                that.stopAnimateTyping();
                _this.searchInput.val('');
            });

            $('input[id*="search_placeholder"]').on('blur', function () {
                that.startAnimateTyping();
            });

        },
        getSearchLayout: function () {
            return $("select[id*='search_layout'] option:selected").val();
        },
        startAnimateTyping: function () {
            var that = this,
                frame = 0,
                $wrapp = $('.js-psfs-search-wrapp'),
                $searchBar = $('.js-psfs-search-input'),
                $iconStyleForm = $('.js-psfs-preview-icon-example .psfs-search-form'),
                $iconStyleArrow = $('.js-psfs-preview-icon-example .psfs-search-icon-arrow'),
                $iconStyleIcon = $('.js-psfs-preview-icon-example .psfs-search-icon'),
                $closeEl = $('.psfs-preloader'),
                closeSvg = $('.js-psfs-preview-elements-close').html();

            // TODO [refactor] shorten it using a recursive function
            that.animateTypingInterval = setInterval(function () {
                frame++;


                if (that.getSearchLayout() === 'icon') {
                    if (frame === 1) {
                        $iconStyleForm.addClass('psfs-hidden');
                        $iconStyleArrow.addClass('psfs-hidden');
                    }

                    if (frame === 6) {
                        $iconStyleIcon.addClass('psfs-search-icon-handler-click');
                    }


                    if (frame === 7) {
                        $iconStyleIcon.removeClass('psfs-search-icon-handler-click');
                        $iconStyleForm.removeClass('psfs-hidden');
                        $iconStyleArrow.removeClass('psfs-hidden');
                    }
                }

                if (frame === 10) {
                    $searchBar.val('f');
                    $wrapp.addClass('psfs-search-filled');
                    $wrapp.addClass('psfs-search-focused');
                    $closeEl.addClass('psfs-close');
                }

                if (frame === 11) {
                    $searchBar.val('fi');
                }
                if (frame === 12) {
                    $searchBar.val('fib');
                    $closeEl.append(closeSvg);
                    that.positionPreloader();
                }
                if (frame === 13) {
                    $searchBar.val('fibo');
                }
                if (frame === 14) {
                    $searchBar.val('fibo ');
                }
                if (frame === 15) {
                    $searchBar.val('fibo s');
                }
                if (frame === 16) {
                    $searchBar.val('fibo se');
                }
                if (frame === 17) {
                    $searchBar.val('fibo sea');
                }
                if (frame === 18) {
                    $searchBar.val('fibo sear');
                }
                if (frame === 19) {
                    $searchBar.val('fibo searc');
                }
                if (frame === 20) {
                    $searchBar.val('fibo search');
                }
                if (frame === 30) {
                    $searchBar.val('fibo searc');
                }
                if (frame === 31) {
                    $searchBar.val('fibo sear');
                }
                if (frame === 32) {
                    $searchBar.val('fibo sea');
                }
                if (frame === 33) {
                    $searchBar.val('fibo se');
                }
                if (frame === 34) {
                    $searchBar.val('fibo s');
                }
                if (frame === 35) {
                    $searchBar.val('fibo ');
                }
                if (frame === 36) {
                    $searchBar.val('fibo');
                }
                if (frame === 37) {
                    $searchBar.val('fib');
                }
                if (frame === 38) {
                    $searchBar.val('fi');
                    $closeEl.removeClass('psfs-close');
                }
                if (frame === 39) {
                    $searchBar.val('f');
                }
                if (frame === 40) {
                    $searchBar.val('');
                    $closeEl.html('');
                    $wrapp.removeClass('psfs-search-filled');
                }
                if (frame === 45) {
                    frame = 0;
                }
            }, 200);
        },
        stopAnimateTyping: function () {
            var that = this,
                $wrapp = $('.js-psfs-search-wrapp'),
                $searchBar = $('.js-psfs-search-input'),
                $closeEl = $('.psfs-preloader');

            $closeEl.removeClass('psfs-close');
            $searchBar.val('');
            $closeEl.html('');
            $wrapp.removeClass('psfs-search-filled');

            clearInterval(that.animateTypingInterval);
        },
        isHTMLPossiblyDangerous: function (html) {
            // Parse the value into an inert <template>: its content is a document fragment that is
            // never rendered, so scripts do not run, images do not load and handlers do not fire.
            // We then inspect it structurally instead of matching raw text, so ordinary sentences
            // (e.g. "Sale online=cheaper") pass while real event handlers/tags are always caught.
            var template = document.createElement('template');
            template.innerHTML = String(html);

            // Reject elements that execute or embed content once inserted into the page.
            if (template.content.querySelector('script, iframe, embed, object, form, frame, frameset, base, link, meta')) {
                return true;
            }

            var elements = template.content.querySelectorAll('*');
            for (var i = 0; i < elements.length; i++) {
                var attrs = elements[i].attributes;
                for (var j = 0; j < attrs.length; j++) {
                    var name = attrs[j].name.toLowerCase();

                    // Any event-handler attribute (onerror, onload, onclick, ...).
                    if (name.indexOf('on') === 0) {
                        return true;
                    }

                    var value = attrs[j].value.replace(/[\s\u0000-\u001F\u007F]+/g, '').toLowerCase();

                    if (name === 'href' || name === 'src' || name === 'xlink:href' || name === 'data' || name === 'formaction') {
                        if (value.indexOf('javascript:') === 0 || value.indexOf('data:text/html') === 0) {
                            return true;
                        }
                    }

                    if (name === 'style' && (value.indexOf('javascript:') !== -1 || value.indexOf('expression(') !== -1)) {
                        return true;
                    }
                }
            }

            return false;
        }
    };

    var TROUBLESHOOTING = {
        settingsTab: '#psfs_troubleshooting-tab',
        noIssuesClass: '.js-psfs-troubleshooting-no-issues',
        counterClass: '.js-psfs-troubleshooting-count',
        issuesListClass: '.js-psfs-troubleshooting-issues',
        progressBar: '.psfs-troubleshooting-wrapper .progress_bar',
        progressBarInner: '.psfs-troubleshooting-wrapper .progress-bar-inner',
        fixOutofstockButtonName: 'psfs-fix-out-of-stock-relationships',
        maintenanceAnalyticsButtonName: 'psfs-maintenance-analytics',
        switchAlternativeEndpoint: 'psfs-switch-alternative-endpoint',
        asyncActionButtonNameBegining: 'psfs-async-action-',
        init: function () {
            var _this = this;
            if (typeof psfs['troubleshooting'] === 'undefined') {
                return;
            }

            const count = psfs['troubleshooting']['tests']['issues']['critical'] + psfs['troubleshooting']['tests']['issues']['recommended'];
            if (count > 0) {
                $(_this.counterClass).text(count).addClass('active');
                $(_this.settingsTab).addClass('enabled');
            }

            if (psfs.troubleshooting.tests.results_async.length > 0) {
                $.each(psfs.troubleshooting.tests.results_async, function () {
                    _this.appendIssue(this, false);
                });
            }

            if (psfs.troubleshooting.tests.direct.length > 0) {
                $.each(psfs.troubleshooting.tests.direct, function () {
                    _this.appendIssue(this, false);
                });
            }

            if (psfs.troubleshooting.tests.async.length > 0) {
                _this.maybeRunNextAsyncTest();
            }

            $(document).on('click', 'input[name="' + _this.fixOutofstockButtonName + '"]', function (e) {
                $('input[name="' + _this.fixOutofstockButtonName + '"]').attr('disabled', 'disabled').next().addClass('loading');
                var data = {
                    'action': 'psfs_troubleshooting_fix_outofstock',
                    '_wpnonce': psfs.troubleshooting.nonce.troubleshooting_fix_outofstock
                };
                $.post(
                    ajaxurl,
                    data,
                    function () {
                        location.reload();
                    }
                );
                return false;
            });

            $(document).on('click', 'input[name="' + _this.maintenanceAnalyticsButtonName + '"]', function (e) {
                $('input[name="' + _this.maintenanceAnalyticsButtonName + '"]').attr('disabled', 'disabled').next().addClass('loading');
                var data = {
                    'action': 'psfs_troubleshooting_maintenance_analytics',
                    '_wpnonce': psfs.troubleshooting.nonce.troubleshooting_maintenance_analytics
                };
                $.post(
                    ajaxurl,
                    data,
                    function () {
                        location.reload();
                    }
                );
                return false;
            });

            $(document).on('click', 'input[name="' + _this.switchAlternativeEndpoint + '"]', function (e) {
                var action = parseInt($(this).data('switch')) === 1 ? 'enable' : 'disable';
                $('input[name="' + _this.switchAlternativeEndpoint + '"]').attr('disabled', 'disabled').next().addClass('loading');
                var data = {
                    'action': 'psfs_troubleshooting_switch_alternative_endpoint',
                    '_wpnonce': psfs.troubleshooting.nonce.troubleshooting_switch_alternative_endpoint,
                    'switch': action,
                };
                $.post(
                    ajaxurl,
                    data,
                    function () {
                        location.reload();
                    }
                );
                return false;
            });

            $(document).on('click', 'input[name^="' + _this.asyncActionButtonNameBegining + '"]', function (e) {
                var $el = $(this);
                $el.attr('disabled', 'disabled').next('.psfs-ajax-loader').addClass('loading');
                var data = {
                    'action': 'psfs_troubleshooting_async_action',
                    'internal_action': $el.data('internal-action'),
                    'meta': $el.data('meta'),
                    '_wpnonce': psfs.troubleshooting.nonce.troubleshooting_async_action
                };
                $.post(
                    ajaxurl,
                    data,
                    function (response) {
                        var success = typeof response.success === 'boolean' ? response.success : false;
                        var timeout = 0;
                        if (typeof response.data.message === "string" && response.data.message.length > 0) {
                            $el.next()
                                .next('.psfs-async-action-message')
                                .text(response.data.message)
                                .removeClass('success')
                                .removeClass('error')
                                .addClass(success ? 'success' : 'error');
                            timeout = 500;
                        }
                        var url = window.location.href;
                        if (url.lastIndexOf("#") > 0) {
                            url = url.substring(0, url.lastIndexOf("#"));
                        }
                        if (typeof response.data.args === "object") {
                            url = wp.url.addQueryArgs(url, response.data.args);
                        }
                        setTimeout(function () {
                            window.location = url;
                        }, timeout);
                    }
                );
                return false;
            });

            $(document).on('change', '#psfs-send-reports-in-feature', function () {
                $('#psfs-async-action-send-indexer-failure-report').data('meta', JSON.stringify({'auto_send': $(this).is(':checked')}));
            });
        },
        appendIssue: function (issue, incrementCounter) {
            var _this = this;
            // Read the template only from our <script> element inside the settings form. wp.template() looks it up
            // by ID on the whole page, and older WordPress versions accept any element with that ID (e.g. one
            // injected into an admin notice). The settings below are the same as in wp.template().
            var templateEl = document.querySelector(
                'form.psfs-eq-settings-form script#tmpl-psfs-troubleshooting-issue[type="text/template"]'
            );
            if (!templateEl) {
                return;
            }

            var template = _.template(templateEl.innerHTML, {
                    evaluate: /<#([\s\S]+?)#>/g,
                    interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                    escape: /\{\{([^\}]+?)\}\}(?!\})/g,
                    variable: 'data'
                }),
                issueWrapper = $(_this.issuesListClass + '-' + issue.status),
                count;

            if (issue.status === 'good') {
                return;
            }

            $(_this.noIssuesClass).hide();

            if (incrementCounter) {
                psfs.troubleshooting.tests.issues[issue.status]++;
            }

            count = psfs.troubleshooting.tests.issues['critical'] + psfs.troubleshooting.tests.issues['recommended'];

            if (count > 0) {
                $(_this.counterClass).text(count).addClass('active');
                $(_this.settingsTab).addClass('enabled');
            }

            $(issueWrapper).append(template(issue));
        },
        maybeRunNextAsyncTest: function () {
            var _this = this;

            if (psfs.troubleshooting.tests.async.length > 0) {
                $.each(psfs.troubleshooting.tests.async, function () {
                    var data = {
                        'action': 'psfs_troubleshooting_test',
                        'test': this.test,
                        '_wpnonce': psfs.troubleshooting.nonce.troubleshooting_async_test
                    };

                    if (this.completed) {
                        return true;
                    }

                    this.completed = true;

                    $(_this.progressBar).show();

                    $.post(
                        ajaxurl,
                        data,
                        function (response) {
                            if (response.success) {
                                _this.appendIssue(response.data, true)
                            }
                            _this.maybeRunNextAsyncTest();
                        }
                    );

                    return false;
                });
            }

            _this.recalculateProgression();
        },
        recalculateProgression: function () {
            var _this = this;
            var total = psfs.troubleshooting.tests.async.length;
            var completed = 0;

            $.each(psfs.troubleshooting.tests.async, function () {
                if (this.completed) {
                    completed++;
                }
            });
            var progress = Math.ceil((completed / total) * 100);
            $(_this.progressBarInner).css('width', progress + '%');
            if (progress === 100) {
                setTimeout(function () {
                    $(_this.progressBar).slideUp();
                }, 2000);
            }
        },
    }

    var MOVE_OPTIONS = {
        moveOptionClass: '.js-psfs-move-option',
        init: function () {
            // Move only options rendered inside the settings form. Elements with the same class injected elsewhere
            // on the page (e.g. in an admin notice) must never be cloned into the form.
            var $form = $('form.psfs-eq-settings-form');
            var optionsToMove = $form.find(this.moveOptionClass);
            if (optionsToMove.length > 0) {
                $.each(optionsToMove, function (index, el) {
                    // attr() keeps the value a string (data() turns numeric values into numbers), and the destination
                    // is matched by its ID inside the form instead of building a selector from the attribute value.
                    var moveDestId = $(el).attr('data-move-dest');
                    var moveDest = typeof moveDestId === 'string' && moveDestId !== '' ? $form.find('[id]').filter(function () {
                        return this.id === moveDestId;
                    }).first() : $();
                    if (moveDest.length > 0) {
                        if ($(el).closest('tr').hasClass('psfs-premium-only')) {
                            $(el).addClass('psfs-premium-only');
                        }
                        $(el).clone().appendTo(moveDest.closest('td fieldset'));
                    }
                    $(el).closest('tr').remove();
                });
            }
        }
    }

    var SETTINGS_FILTERS_RULES = {
        init: function () {

            if (typeof Vue == 'undefined') {
                return;
            }

            // Mount only inside the settings form and take the template only from a <script> element.
            // Vue compiles the markup of the mount element, so an element with the same ID injected elsewhere
            // on the page (e.g. in an admin notice) must never be used.
            var filtersRulesEl = document.querySelector('form.psfs-eq-settings-form #psfs-settings-filters-rules');
            var filtersRulesTemplate = document.querySelector('script#psfs-settings-filters-rules-rule');

            if (!filtersRulesEl || !filtersRulesTemplate) {
                return;
            }

            var productSearchSettings = function ({nonce, options, type}) {
                return {
                    persist: false,
                    maxItems: null,
                    valueField: 'key',
                    labelField: 'label',
                    searchField: ['label'],
                    options: options,
                    preload: true,
                    create: function (input) {
                        return {
                            value: input.key,
                            label: input.label
                        }
                    },
                    load: function (query, callback) {
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                action: 'psfs_settings_search_terms',
                                query: query,
                                type: type,
                                _wpnonce: nonce
                            },
                            error: function () {
                                callback();
                            },
                            success: function (res) {
                                callback(res.data);
                            }
                        });
                    }
                };
            };

            Vue.component('psfs-rule', {
                // Read the template from the <script type="text/x-template"> element, never from any element with this ID.
                template: filtersRulesTemplate.innerHTML,
                components: {
                    Selectize
                },
                props: ['securitynonce', 'rule', 'rules', 'index'],
                data() {
                    return {
                        isSelectActive: true,
                    }
                },
                computed: {
                    ruleValue(value) {
                        return this.rule.group;
                    },
                },
                watch: {
                    rule: {
                        handler: function () {
                            this.$emit('update:rule', this.index);
                        },
                        deep: true,
                    },
                    ruleValue() {
                        // Reset values on group change
                        var vm = this;
                        this.$emit('change:group', this.index);
                        this.isSelectActive = false;
                        setTimeout(function () {
                            vm.isSelectActive = true;
                        }, 0);
                    },
                },
                methods: {
                    deleteRule() {
                        this.$emit('delete:rule', this.index)
                    },
                    getSelectizeSettings(type) {
                        var options = (typeof psfs_filters_rules_selected_options[type] === 'undefined') ? [] : psfs_filters_rules_selected_options[type];
                        return productSearchSettings({nonce: this.securitynonce, type: type, options: options});
                    },
                },
            });

            var FiltersRules = new Vue({
                el: filtersRulesEl,
                components: {
                    Selectize
                },
                data() {
                    return {
                        rules: []
                    }
                },
                mounted() {
                    try {
                        const rules = JSON.parse(this.$refs['psfs-settings-filters-rules-ref'].value);
                        $.each(rules, function (index, rule) {
                            rules[index].key = Math.random();
                        });
                        this.rules = rules;
                    } catch (e) {
                    }
                    this.updateInput();
                },
                methods: {
                    addRule() {
                        this.rules.push({group: '', values: [], key: Math.random()});
                        this.updateInput();
                    },
                    changeGroup(index) {
                        this.rules[index].values = [];
                        this.updateInput();
                    },
                    deleteRule(index) {
                        this.rules = this.rules.filter(function (item, itemIndex) {
                            return itemIndex !== index;
                        });
                        this.updateInput();
                    },
                    updateInput() {
                        const rules = JSON.parse(JSON.stringify(this.rules));
                        this.$refs['psfs-settings-filters-rules-ref'].value = JSON.stringify(rules.map(function (rule) {
                            if (typeof (rule['key'] !== 'undefined')) {
                                delete (rule['key']);
                            }
                            return rule;
                        }));
                    }
                },
            });
        }
    };

    function automateSettingsColspan() {
        var $el = $('.js-psfs-sgs-autocolspan');
        if ($el.length > 0) {
            $el.find('td').attr('colspan', 2);
        }
    }

    function moveOuterBorderOption() {
        var $elToMove = $('.js-psfs-settings-margin-nob');

        if ($elToMove.length > 0) {

            $elToMove.each(function () {

                var $wrapp = $(this).find('td .psfs-fieldset');

                if ($wrapp.length > 0) {
                    var $parent = $(this).prev('.js-psfs-settings-margin');
                    if ($parent.length > 0) {

                        var classList = $(this).attr('class').split(/\s+/);
                        var className = '';

                        $.each(classList, function (index, item) {
                            if (item.indexOf('js-psfs-cbtgroup-') !== -1) {
                                className = item;
                            }
                        });
                        var $clone = $wrapp.clone(true, true);
                        $clone.addClass('psfs-settings-margin-nob');
                        if (className) {
                            $clone.addClass(className);
                        }
                        $clone.appendTo($parent.find('td'));
                        $(this).remove();
                    }
                }
            });

        }
    }


    $(document).ready(function () {

        moveOuterBorderOption();

        RADIO_SETTINGS_TOGGLE.init();
        CHECKBOX_SETTINGS_TOGGLE.init();
        CONDITIONAL_LAYOUT_SETTINGS.init();

        automateSettingsColspan();

        AJAX_BUILD_INDEX.init();
        SELECTIZE.init();
        TOOLTIP.init();
        ADVANCED_SETTINGS.init();
        TROUBLESHOOTING.init();
        MOVE_OPTIONS.init();
        CHECKBOX_SETTINGS_TOGGLE_SIBLING.init();
        STATS_INTERFACE.init();

        SETTINGS_FILTERS_RULES.init();
        window.PSFS_SEARCH_PREVIEW.init();

    });


})(jQuery);

