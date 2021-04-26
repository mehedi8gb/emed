
/*
 * ==========================================================
 * ADMINISTRATION SCRIPT
 * ==========================================================
 *
 * Main Javascript admin file. © 2020 board.support. All rights reserved.
 * 
 */

'use strict';
(function ($) {

    // Global
    var admin;
    var header;

    // Conversation  
    var conversations = [];
    var conversations_area;
    var conversations_admin_list;
    var conversations_admin_list_ul;
    var saved_replies = false;
    var saved_replies_list = false;
    var woocommerce_products_box = false;
    var woocommerce_products_box_ul = false;
    var pagination = 1;

    // Users
    var users_area;
    var users_table;
    var users_table_menu;
    var users = {};
    var users_pagination = 1;
    var profile_area;
    var profile_edit_area;

    // Settings
    var settings_area;
    var articles = [];

    // Miscellaneus
    var upload_target;
    var timeout;
    var alertOnConfirmation;
    var responsive = $(window).width() < 426;
    var scrolls = { last: 0, header: true, always_hidden: false };
    var localhost = location.hostname === 'localhost' || location.hostname === '127.0.0.1';
    var today = new Date();
    var is_departments = false;
    var is_busy = false;
    var agent_online = true;
    var active_interval = [0, true];
    var temp;
    var overlay;
    var SITE_URL;

    /*
    * ----------------------------------------------------------
    * EXTERNAL PLUGINS
    * ----------------------------------------------------------
    */

    // miniTip 1.5.3 | (c) 2011, James Simpson | Dual licensed under the MIT and GPL
    $.fn.miniTip = function (t) { var e = $.extend({ title: '', content: !1, delay: 300, anchor: 'n', event: 'hover', fadeIn: 200, fadeOut: 200, aHide: !0, maxW: '250px', offset: 5, stemOff: 0, doHide: !1 }, t); 0 == $(admin).find('#miniTip').length && $(admin).append('<div id="miniTip" class="sb-tooltip"><div></div></div>'); var n = $(admin).find('#miniTip'), a = n.find('div'); return e.doHide ? (n.stop(!0, !0).fadeOut(e.fadeOut), !1) : this.each(function () { var t = $(this), o = e.content ? e.content : t.attr('title'); if ('' != o && void 0 !== o) { window.delay = !1; var i = !1, r = !0; e.content || t.removeAttr('title'), 'hover' == e.event ? (t.hover(function () { n.removeAttr('click'), r = !0, s.call(this) }, function () { r = !1, d() }), e.aHide || n.hover(function () { i = !0 }, function () { i = !1, setTimeout(function () { !r && !n.attr('click') && d() }, 20) })) : 'click' == e.event && (e.aHide = !0, t.click(function () { return n.attr('click', 't'), n.data('last_target') !== t ? s.call(this) : 'none' == n.css("display") ? s.call(this) : d(), n.data('last_target', t), $('html').unbind('click').click(function (t) { 'block' == n.css('display') && !$(t.target).closest('#miniTip').length && ($('html').unbind('click'), d()) }), !1 })); var s = function () { e.show && e.show.call(this, e), e.content && '' != e.content && (o = e.content), a.html(o), e.render && e.render(n), n.hide().width('').width(n.width()).css('max-width', e.maxW); var i = t.is('area'); if (i) { var r, s = [], d = [], c = t.attr('coords').split(','); function h(t, e) { return t - e } for (r = 0; r < c.length; r++)s.push(c[r++]), d.push(c[r]); var f = t.parent().attr('name'), l = $('img[usemap=\\#' + f + ']').offset(), p = parseInt(l.left, 10) + parseInt((parseInt(s.sort(h)[0], 10) + parseInt(s.sort(h)[s.length - 1], 10)) / 2, 10), u = parseInt(l.top, 10) + parseInt((parseInt(d.sort(h)[0], 10) + parseInt(d.sort(h)[d.length - 1], 10)) / 2, 10) } else u = parseInt(t.offset().top, 10), p = parseInt(t.offset().left, 10); var m = i ? 0 : parseInt(t.outerWidth(), 10), I = i ? 0 : parseInt(t.outerHeight(), 10), v = n.outerWidth(), w = n.outerHeight(), g = Math.round(p + Math.round((m - v) / 2)), T = Math.round(u + I + e.offset + 8), b = Math.round(v - 16) / 2 - parseInt(n.css('borderLeftWidth'), 10), y = 0, H = p + m + v + e.offset + 8 > parseInt($(window).width(), 10), W = v + e.offset + 8 > p, k = w + e.offset + 8 > u - $(window).scrollTop(), M = u + I + w + e.offset + 8 > parseInt($(window).height() + $(window).scrollTop(), 10), x = e.anchor; W || 'e' == e.anchor && !H ? 'w' != e.anchor && 'e' != e.anchor || (x = 'e', y = Math.round(w / 2 - 8 - parseInt(n.css('borderRightWidth'), 10)), b = -8 - parseInt(n.css('borderRightWidth'), 10), g = p + m + e.offset + 8, T = Math.round(u + I / 2 - w / 2)) : (H || 'w' == e.anchor && !W) && ('w' != e.anchor && 'e' != e.anchor || (x = 'w', y = Math.round(w / 2 - 8 - parseInt(n.css('borderLeftWidth'), 10)), b = v - parseInt(n.css('borderLeftWidth'), 10), g = p - v - e.offset - 8, T = Math.round(u + I / 2 - w / 2))), M || 'n' == e.anchor && !k ? 'n' != e.anchor && 's' != e.anchor || (x = 'n', y = w - parseInt(n.css('borderTopWidth'), 10), T = u - (w + e.offset + 8)) : (k || 's' == e.anchor && !M) && ('n' != e.anchor && 's' != e.anchor || (x = 's', y = -8 - parseInt(n.css('borderBottomWidth'), 10), T = u + I + e.offset + 8)), 'n' == e.anchor || 's' == e.anchor ? v / 2 > p ? (g = g < 0 ? b + g : b, b = 0) : p + v / 2 > parseInt($(window).width(), 10) && (g -= b, b *= 2) : k ? (T += y, y = 0) : M && (T -= y, y *= 2), delay && clearTimeout(delay), delay = setTimeout(function () { n.css({ 'margin-left': g + 'px', 'margin-top': T + 'px' }).stop(!0, !0).fadeIn(e.fadeIn) }, e.delay), n.attr('class', 'sb-tooltip ' + x) }, d = function () { (!e.aHide && !i || e.aHide) && (delay && clearTimeout(delay), delay = setTimeout(function () { c() }, e.delay)) }, c = function () { !e.aHide && !i || e.aHide ? (n.stop(!0, !0).fadeOut(e.fadeOut), e.hide && e.hide.call(this)) : setTimeout(function () { d() }, 200) } } }) };

    /*
    * ----------------------------------------------------------
    * # FUNCTIONS
    * ----------------------------------------------------------
    */

    // Update user and agent activity status
    function updateUsersActivity() {
        SBF.updateUsersActivity(agent_online ? SB_ACTIVE_AGENT['id'] : -1, activeUser() != false ? activeUser().id : -1, function (response) {
            if (response == 'online') {
                if (!SBChat.user_online) {
                    $(conversations_area).find('.sb-conversation .sb-top > .sb-labels').prepend(`<span class="sb-status-online">${sb_('Online')}</span>`);
                    SBChat.user_online = true;
                }
            } else {
                $(conversations_area).find('.sb-conversation .sb-top .sb-status-online').remove();
                SBChat.user_online = false;
            }
        });
    }

    // Display the bottom card information box
    function showResponse(title, text, type) {
        var card = $(admin).find('.sb-info-card');
        var code = '';
        if (!SBF.null(type) && type == 'error') {
            $(card).addClass('sb-info-card-error');
        } else {
            $(card).removeClass('sb-info-card-error');
        }
        if (!SBF.null(text)) {
            code = `<p>${sb_(text)}</p>`;
        }
        $(card).html(`<h3>${sb_(title)}</h3>${sb_(code)}`).sbActivate();
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            $(card).sbActivate(false);
        }, 5000);
    }

    // Access the global user variable
    function activeUser(value) {
        if (typeof value == 'undefined') {
            return window.sb_current_user;
        } else {
            window.sb_current_user = value;
        }
    }

    // Lightbox
    $.fn.sbShowLightbox = function (popup = false, action = '') {
        $(admin).find('.sb-lightbox').sbActivate(false);
        $(overlay).sbActivate();
        $(this).sbActivate();
        if (popup) {
            $(this).addClass('sb-popup-lightbox').attr('data-action', action);
        } else {
            $(this).css({ 'margin-top': ($(this).outerHeight() / -2) + 'px', 'margin-left': ($(this).outerWidth() / -2) + 'px' })
        }
        this.preventDefault;
    };

    $.fn.sbHideLightbox = function () {
        $(this).find('.sb-lightbox,.sb-popup-lightbox').sbActivate(false).removeClass('sb-popup-lightbox').removeAttr('data-action');
        $(overlay).sbActivate(false);
    };

    // Show alert and information lightbox
    function dialog(text, type, onConfirm) {
        let box = $(admin).find('.sb-dialog-box').attr('data-type', type);
        $(box).find('p').html((type == 'alert' ? sb_('Are you sure?') + ' ' : '') + sb_(text));
        $(box).sbActivate().css({ 'margin-top': ($(box).outerHeight() / -2) + 'px', 'margin-left': ($(box).outerWidth() / -2) + 'px' });
        $(overlay).sbActivate();
        alertOnConfirmation = onConfirm;
    }

    function sbDialogAdmin(text, type, onConfirm) {
        dialog(text, type, onConfirm);
    }

    window.sbDialogAdmin = sbDialogAdmin

    // Loading box
    function loading(show = true, is_overlay = true) {
        $(admin).find('.sb-loading-global').sbActivate(show);
        if (is_overlay) $(overlay).sbActivate(show);
    }

    // Support Board js translations
    function sb_(text) {
        if (SB_TRANSLATIONS != false && text in SB_TRANSLATIONS) {
            return SB_TRANSLATIONS[text];
        } else {
            return text;
        }
    }

    // PWA functions
    function isPWA() {
        return (window.matchMedia('(display-mode: standalone)').matches) || (window.navigator.standalone) || document.referrer.includes('android-app://');
    }

    function clearCache() {
        if (typeof caches !== 'undefined') caches.delete('sb-pwa-cache');
    }

    // Reset the interval for the active tab check
    function resetActiveInterval() {
        active_interval[0] = setInterval(function () {
            SBChat.tab_active = false;
        }, 10000);
    }

    // Collapse
    function collapse(target, max_height) {
        let content = $(target).find(' > div, > ul');
        if ($(target).hasClass('sb-collapse') && $(content).prop('scrollHeight') > max_height) {
            $(target).sbActivate().attr('data-height', max_height);
            $(target).append(`<a class="sb-btn-text sb-collapse-btn">${sb_('View more')}</a>`);
            $(content).css({ 'height': max_height + 'px', 'max-height': max_height + 'px' });
        };
    }

    function searchInput(input, searchFunction) {
        let icon = $(input).parent().find('i');
        let search = $(input).val();
        if (!$(icon).sbLoading()) {
            SBF.search(search, () => {
                $(icon).sbLoading(true);
                searchFunction(search, icon);
            });
        };
    }

    function scrollPagination(area, check = false) {
        if (check) return $(area).scrollTop() + $(area).innerHeight() >= $(area)[0].scrollHeight;
        $(area).scrollTop($(area)[0].scrollHeight);
    }

    /*
    * ----------------------------------------------------------
    * # SB APPS METHODS
    * ----------------------------------------------------------
    */

    var SBApps = {

        ump: {

            panel: false,

            // Conversation panel
            conversationPanel: function () {
                if (!this.panel) this.panel = $(conversations_area).find('.sb-panel-ump');
                if (!$(this.panel).sbLoading()) {
                    let code = '';
                    let subscriptions;
                    SBF.ajax({
                        function: 'ump-get-conversation-details'
                    }, (response) => {
                        subscriptions = response['subscriptions'];
                        if (subscriptions.length) {
                            code = '<i class="sb-icon-refresh"></i><h3>Membership</h3><div class="sb-list-names">';
                            for (var i = 0; i < subscriptions.length; i++) {
                                code += `<div${subscriptions[i]['expired'] ? ' class="sb-expired"' : ''}><span>${subscriptions[i]['label']}</span><span>${sb_('Expires on')} ${SBF.beautifyTime(subscriptions[i]['expire_time'], true, !subscriptions[i]['expired'])}</span></div>`;
                            }
                            code += `</div><span class="sb-title">${sb_('Total spend')} ${response['currency_symbol']}${response['total']}</span>`;
                        }
                        $(this.panel).html(code).sbLoading(false);
                        collapse(this.panel, 160);
                    });
                    $(this.panel).sbLoading(true);
                }
            }
        },

        woocommerce: {

            popupPaginationNumber: 1,
            popupLanguage: '',
            popupCache: [],
            panel: false,
            timeout: false,

            // Products popup
            popupCode: function (items, label = true) {
                let code = '';
                for (var i = 0; i < items.length; i++) {
                    code += `<li data-id="${items[i]['id']}"><div class="sb-image" style="background-image:url('${items[i]['image']}')"></div><div><span>${items[i]['name']}</span><span>${SB_ADMIN_SETTINGS['currency']}${items[i]['price']}</span></div></li>`;
                }
                return label ? (code == '' ? `<p>${sb_('No products found')}</p>` : code) : code;
            },

            popupSearch: function (input) {
                searchInput(input, (search, icon) => {
                    if (search == '') {
                        this.popupPopulate(function () {
                            $(icon).sbLoading(false);
                        });
                    } else {
                        this.popupPaginationNumber = 1;
                        SBF.ajax({
                            function: 'woocommerce-search-products',
                            search: search
                        }, (response) => {
                            $(woocommerce_products_box_ul).html(this.popupCode(response));
                            $(icon).sbLoading(false);
                        });
                    }
                });
            },

            popupFilter: function (item) {
                if (!$(woocommerce_products_box_ul).sbLoading()) {
                    $(woocommerce_products_box_ul).html('').sbLoading(true);
                    this.popupPaginationNumber = 1;
                    SBF.ajax({
                        function: 'woocommerce-get-products',
                        user_language: this.popupLanguage,
                        filters: { taxonomy: $(item).data('value') }
                    }, (response) => {
                        $(woocommerce_products_box_ul).html(this.popupCode(response)).sbLoading(false);
                    });
                }
            },

            popupPopulate: function (onSuccess = false) {
                this.popupLanguage = activeUser() != false && SB_ADMIN_SETTINGS['languages'].includes(activeUser().language) ? activeUser().language : '';
                this.popupPaginationNumber = 1;
                $(woocommerce_products_box_ul).html('').sbLoading(true);
                SBF.ajax({
                    function: 'woocommerce-products-popup',
                    user_language: this.popupLanguage
                }, (response) => {
                    let code = '';
                    let select = $(woocommerce_products_box).find('.sb-select');
                    for (var i = 0; i < response[1].length; i++) {
                        code += `<li data-value="${response[1][i]['id']}">${response[1][i]['name']}</li>`;
                    }
                    $(select).find('> p').html(sb_('All'));
                    $(select).find('ul').html(`<li data-value="" class="sb-active">${sb_('All')}</li>` + code);
                    $(woocommerce_products_box_ul).html(this.popupCode(response[0])).sbLoading(false);
                    if (onSuccess !== false) onSuccess();
                });
            },

            popupPagination: function (area) {
                $(woocommerce_products_box_ul).sbLoading(area);
                SBF.ajax({
                    function: 'woocommerce-get-products',
                    filters: { taxonomy: $(area).parent().find('.sb-select p').attr('data-value') },
                    pagination: this.popupPaginationNumber,
                    user_language: this.popupLanguage
                }, (response) => {
                    $(woocommerce_products_box_ul).append(this.popupCode(response, false)).sbLoading(false);
                    this.popupPaginationNumber++;
                    scrollPagination(area);
                });
            },

            // Conversation panel
            conversationPanel: function () {
                if (!this.panel) this.panel = $(conversations_area).find('.sb-panel-woocommerce');
                if (!$(this.panel).sbLoading()) {
                    let code = '';
                    SBF.ajax({
                        function: 'woocommerce-get-conversation-details'
                    }, (response) => {
                        code = `<i class="sb-icon-refresh"></i><h3>WooCommerce</h3><div><div class="sb-split"><div><div class="sb-title">${sb_('Number of orders')}</div><span>${response['orders_count']} ${sb_('orders')}</span></div><div><div class="sb-title">${sb_('Total spend')}</div><span>${response['currency_symbol']}${response['total']}</span></div></div><div class="sb-title">${sb_('Cart')}<i class="sb-add-cart-btn sb-icon-plus"></i></div><div class="sb-list-items sb-list-links sb-woocommerce-cart">`;
                        for (var i = 0; i < response['cart'].length; i++) {
                            let product = response['cart'][i];
                            code += `<a href="${product['url']}" target="_blank" data-id="${product['id']}"><span>#${product['id']}</span> <span>${product['quantity']} x</span> <span>${product['name']}</span><i class="sb-icon-close"></i></a>`;
                        }
                        code += (response['cart'].length ? '' : '<p>' + sb_('The cart is currently empty.') + '</p>') + '</div>';
                        if (response['orders'].length) {
                            code += `<div class="sb-title">${sb_('Orders')}</div><div class="sb-list-items sb-woocommerce-orders sb-accordion">`;
                            for (var i = 0; i < response['orders'].length; i++) {
                                let order = response['orders'][i];
                                let id = order['id'];
                                code += `<div data-id="${id}"><span><span>#${id}</span> <span>${SBF.beautifyTime(order['date'], true)}</span><a href="${SITE_URL}/wp-admin/post.php?post=${id}&action=edit" target="_blank" class="sb-icon-next"></a></span><div></div></div>`;
                            }
                            code += '</div>';
                        }
                        $(this.panel).html(code).sbLoading(false);
                        collapse(this.panel, 160);
                    });
                    $(this.panel).sbLoading(true);
                }
            },


            // Conversation panel order details
            conversationPanelOrder: function (order_id) {
                let accordion = $(this.panel).find(`[data-id="${order_id}"] > div`);
                $(accordion).html('');
                SBF.ajax({
                    function: 'woocommerce-get-order',
                    order_id: order_id
                }, (response) => {
                    let code = '';
                    let collapse = $(this.panel).find('.sb-collapse-btn:not(.sb-active)');
                    if (response) {
                        let products = response['products'];
                        code += `<div class="sb-title">${sb_('Order total')}: <span>${response['currency_symbol']}${response['total']}<span></div><div class="sb-title">${sb_('Order status')}: <span>${SBF.slugToString(response['status'].replace('wc-', ''))}<span></div><div class="sb-title">${sb_('Date')}: <span>${SBF.beautifyTime(response['date'], true)}<span></div><div class="sb-title">${sb_('Products')}</div>`;
                        for (var i = 0; i < products.length; i++) {
                            code += `<a href="${SITE_URL}?p=${products[i]['id']}" target="_blank"><span>#${products[i]['id']}</span> <span>${products[i]['quantity']} x</span> <span>${products[i]['name']}</span></a>`;
                        }
                        for (var i = 0; i < 2; i++) {
                            let key = i == 0 ? 'shipping' : 'billing';
                            if (response[key + '_address'] != '') {
                                code += `<div class="sb-title">${sb_((i == 0 ? 'Shipping' : 'Billing') + ' address')}</div><div class="sb-multiline">${response[key + '_address'].replace(/\\n/g, '<br>')}</div>`;
                            }
                        }
                    }
                    if ($(collapse).length) {
                        $(collapse).click();
                    }
                    $(accordion).html(code);
                });
            },

            conversationPanelUpdate: function (product_id, action = 'added') {
                let busy = false;
                let count = 0;
                this.timeout = setInterval(() => {
                    if (!busy) {
                        SBF.ajax({
                            function: 'woocommerce-get-conversation-details'
                        }, (response) => {
                            let removed = true;
                            for (var i = 0; i < response['cart'].length; i++) {
                                if (response['cart'][i]['id'] == product_id) {
                                    if (action == 'added') count = 61; else removed = false;
                                }
                            }
                            if (count > 60 || removed) {
                                this.conversationPanel();
                                $(conversations_area).find('.sb-add-cart-btn,.sb-woocommerce-cart > a i').sbLoading(false);
                                clearInterval(this.timeout);
                            }
                            count++;
                            busy = false;
                        });
                        busy = true;
                    }
                }, 1000);
            }
        },

        is: function (name) {
            let undefined = 'undefined';
            if (typeof SB_VERSIONS == undefined) return false;
            switch (name) {
                case 'ump':
                case 'woocommerce':
                case 'dialogflow':
                case 'slack':
                case 'tickets': return typeof SB_VERSIONS[name] != undefined && SB_VERSIONS[name] != -1;
                case 'wordpress': return typeof SB_WP != undefined;
                case 'sb': return true;
            }
            return false;
        }
    }

    /*
    * ----------------------------------------------------------
    * # SB SETTINGS METHODS
    * ----------------------------------------------------------
    */

    var SBSettings = {
        init: false,
        translations_to_update: [],

        save: function (btn) {
            if (!$(btn).sbLoading()) {
                let external_settings = {};
                let settings = {};
                let tab = $(settings_area).find(' > .sb-tab > .sb-nav .sb-active').attr('id');
                $(btn).sbLoading(true);
                switch (tab) {
                    case 'tab-articles':
                        if (articles.length) {
                            this.articles('save');
                            SBF.ajax({
                                function: 'save-articles',
                                articles: articles
                            }, () => {
                                showResponse('Articles saved');
                                $(btn).sbLoading(false);
                            });
                        } else {
                            $(btn).sbLoading(false);
                        }
                        break;
                    case 'tab-translations':
                        let translations_all = {};
                        let count = this.translations_to_update.length;
                        if (count) {
                            for (var i = 0; i < count; i++) {
                                let code = this.translations_to_update[i];
                                let area = $(settings_area).find('.sb-translations .sb-content [data-code="' + code + '"]');
                                let translations_array = { 'front': {}, 'admin': {} };
                                for (var key in translations_array) {
                                    $(area).find(' > [data-area="' + key + '"] .sb-input-setting:not(.sb-new-translation)').each(function () {
                                        translations_array[key][$(this).find('label').html()] = $(this).find('input').val();
                                    });
                                }
                                $(area).find('.sb-new-translation').each(function () {
                                    let original = $(this).find('input:first-child').val();
                                    let value = $(this).find('input:last-child').val();
                                    if (original != '' && value != '') {
                                        translations_array['front'][original] = value;
                                    }
                                });
                                translations_all[code] = translations_array;
                            }
                            SBF.ajax({
                                function: 'save-translations',
                                translations: translations_all
                            }, () => {
                                showResponse('Settings saved');
                                $(btn).sbLoading(false);
                            });
                        } else {
                            dialog('No translations to update. Please click the language of the translations you want to save and try again.', 'info');
                            $(btn).sbLoading(false);
                        }
                        break;
                    default:
                        $(settings_area).find('.sb-setting').each((i, element) => {
                            let setting = this.get(element);
                            let data_setting = $(element).data('setting');
                            if (setting[0] != '') {
                                let value = [setting[1], setting[2]];
                                if (typeof data_setting != 'undefined') {
                                    if (!(data_setting in external_settings)) external_settings[data_setting] = {};
                                    external_settings[data_setting][setting[0]] = value;
                                } else {
                                    settings[setting[0]] = value;
                                }
                            }
                        });
                        SBF.ajax({
                            function: 'save-settings',
                            settings: settings,
                            external_settings: external_settings
                        }, () => {
                            showResponse('Settings saved');
                            $(btn).sbLoading(false);
                        });
                        break;
                }
            }
        },

        initPlugins: function () {
            $(settings_area).find('textarea').each(function () {
                $(this).autoExpandTextarea();
                $(this).manualExpandTextarea();
            });
        },

        initHTML: function (response) {
            if ('slack-agents' in response) {
                let code = '';
                for (var key in response['slack-agents'][0]) {
                    code += `<div data-id="${key}"><select><option value="${response['slack-agents'][0][key]}"></option></select></div>`;
                }
                $(settings_area).find('#slack-agents .input').html(code);
            }
        },

        get: function (item) {
            let id = $(item).attr('id');
            let type = $(item).data('type');
            switch (type) {
                case 'upload':
                case 'range':
                case 'number':
                case 'text':
                case 'password':
                case 'color':
                    return [id, $(item).find('input').val(), type];
                    break;
                case 'textarea':
                    return [id, $(item).find('textarea').val(), type];
                    break;
                case 'select':
                    return [id, $(item).find('select').val(), type];
                    break;
                case 'checkbox':
                    return [id, $(item).find('input').is(':checked'), type];
                    break;
                case 'radio':
                    let value = $(item).find('input:checked').val();
                    if (SBF.null(value)) value = '';
                    return [id, value, type];
                    break;
                case 'upload-image':
                    let url = $(item).find('.image').attr('data-value');
                    if (SBF.null(url)) url = '';
                    return [id, url, type];
                    break;
                case 'multi-input':
                    let multiInputs = {};
                    $(item).find('.input > div').each((i, element) => {
                        let setting = this.get(element);
                        multiInputs[setting[0]] = [setting[1], setting[2]];
                    });
                    return [id, multiInputs, type];
                    break;
                case 'select-image':
                    return [id, $(item).find('.thumbs > .active').data('id'), type];
                    break;
                case 'repeater':
                    return [id, this.repeater('get', $(item).find('.repeater-item'), ''), type];
                    break;
                case 'double-select':
                    let selects = {};
                    $(item).find('.input > div').each(function () {
                        let value = $(this).find('select').val();
                        if (value != -1) {
                            selects[$(this).attr('data-id')] = [value];
                        }
                    });
                    return [id, selects, type];
                    break;
                case 'timetable':
                    let times = {};
                    $(item).find('.sb-timetable > [data-day]').each(function () {
                        let day = $(this).attr('data-day');
                        let hours = [];
                        $(this).find('> div > div').each(function () {
                            let name = $(this).html()
                            let value = $(this).attr('data-value');
                            if (SBF.null(value)) {
                                hours.push(['', '']);
                            } else if (value == 'closed') {
                                hours.push(['closed', 'Closed']);
                            } else {
                                hours.push([value, name]);
                            }
                        });
                        times[day] = hours;
                    });
                    return [id, times, type];
                    break;
                case 'color-palette':
                    return [id, $(item).attr('data-value'), type];
                    break;

            }
            return ['', '', ''];
        },

        set: function (id, setting) {
            let type = $(setting)[1];
            let value = $(setting)[0];
            id = `#${id}`;
            switch (type) {
                case 'color':
                case 'upload':
                case 'number':
                case 'text':
                case 'password':
                    $(settings_area).find(`${id} input`).val(SBF.restoreJson(value));
                    break;
                case 'textarea':
                    $(settings_area).find(`${id} textarea`).val(SBF.restoreJson(value));
                    break;
                case 'select':
                    $(settings_area).find(`${id} select`).val(SBF.restoreJson(value));
                    break;
                case 'checkbox':
                    $(settings_area).find(`${id} input`).prop('checked', (value == 'false' ? false : value));
                    break;
                case 'radio':
                    $(settings_area).find(`${id} input[value="${SBF.restoreJson(value)}"]`).prop('checked', true);
                    break;
                case 'upload-image':
                    if (value != '') {
                        $(settings_area).find(id + ' .image').attr('data-value', SBF.restoreJson(value)).css('background-image', `url("${SBF.restoreJson(value)}")`);
                    }
                    break;
                case 'multi-input':
                    for (var key in value) {
                        this.set(key, value[key]);
                    }
                    break;
                case 'range':
                    let range_value = SBF.restoreJson(value);
                    $(settings_area).find(id + ' input').val(range_value);
                    $(settings_area).find(id + ' .range-value').html(range_value);
                    break;
                case 'select-image':
                    $(settings_area).find(id + ' .thumbs > [data-id]').removeClass('active');
                    $(settings_area).find(id + ` .thumbs > [data-id="${SBF.restoreJson(value)}"]`).addClass('active');
                    break;
                case 'repeater':
                    let content = this.repeater('set', value, $(settings_area).find(id + ' .repeater-item:last-child'));
                    if (content != '') {
                        $(settings_area).find(id + ' .sb-repeater').html(content);
                    }
                    break;
                case 'double-select':
                    for (var key in value) {
                        $(settings_area).find(`${id} .input > [data-id="${key}"] select`).val(value[key]);
                    }
                    break;
                case 'timetable':
                    for (var key in value) {
                        let hours = $(settings_area).find(`${id} [data-day="${key}"] > div > div`);
                        for (var i = 0; i < hours.length; i++) {
                            $(hours[i]).attr('data-value', value[key][i][0]).html(value[key][i][1]);
                        }
                    }
                    break;
                case 'color-palette':
                    if (value != '') {
                        $(settings_area).find(id).attr('data-value', value);
                    }
                    break;
            }
        },

        repeater: function (action, items, content) {
            $(content).find('.sb-icon-close').remove();
            content = $(content).html();
            if (action == 'set') {
                var html = '';
                if (items.length > 0) {
                    for (var i = 0; i < items.length; i++) {
                        let item = $.parseHTML(`<div>${content}</div>`);
                        for (var key in items[i]) {
                            this.setInput($(item).find(`[data-id="${key}"]`), items[i][key]);
                        }
                        html += `<div class="repeater-item">${$(item).html()}<i class="sb-icon-close"></i></div>`;
                    }
                }
                return html;
            }
            if (action == 'get') {
                let items_array = [];
                let me = this;
                $(items).each(function () {
                    let item = {};
                    let empty = true;
                    $(this).find('[data-id]').each(function () {
                        let value = me.getInput(this);
                        if (empty && value != '' && $(this).attr('type') != 'hidden' && $(this).attr('data-type') != 'auto-id') {
                            empty = false;
                        }
                        item[$(this).attr('data-id')] = value;
                    });
                    if (!empty) {
                        items_array.push(item);
                    }
                });
                return items_array;
            }
        },

        repeaterAdd: function (item) {
            let parent = $(item).parent();
            item = $.parseHTML(`<div>${$(parent).find('.repeater-item:last-child').html()}</div>`);
            $(item).find('[data-id]').each(function () {
                SBSettings.resetInput(this);
                if ($(this).data('type') == 'auto-id') {
                    let larger = 1;
                    $(parent).find('[data-type="auto-id"]').each(function () {
                        let index = parseInt($(this).val());
                        if (index > larger) {
                            larger = index;
                        }
                    });
                    $(this).attr('value', larger + 1);
                }
            });
            $(parent).find('.sb-repeater').append(`<div class="repeater-item">${$(item).html()}</div>`);
        },

        repeaterDelete: function (item) {
            if ($(item).parent().parent().find('.repeater-item').length > 1) {
                $(item).parent().remove();
            }
        },

        setInput: function (input, value) {
            value = $.trim(value);
            if ($(input).is('select')) {
                $(input).find(`option[value="${value}"]`).attr('selected', '');
            } else {
                if ($(input).is('checkbox') && value) {
                    $(input).attr('checked', '');
                } else {
                    if ($(input).is('textarea')) {
                        $(input).html(value);
                    } else {
                        let div = $(input).is('div');
                        if (div || $(input).is('i') || $(input).is('li')) {
                            $(input).attr('data-value', value);
                            if (div && $(input).hasClass('image')) {
                                $(input).css('background-image', `url("${value}")`);
                            }
                        } else {
                            $(input).attr('value', value);
                        }
                    }
                }
            }
        },

        getInput: function (input) {
            if ($(input).is("checkbox")) {
                return $(input).is(':checked');
            } else {
                if ($(input).is("div") || $(input).is("i") || $(input).is("li")) {
                    let value = $(input).attr('data-value');
                    return SBF.null(value) ? '' : value;
                } else {
                    return $(input).val();
                }
            }
            return '';
        },

        resetInput: function (input) {
            if ($(input).is("select")) {
                $(input).val('').find('[selected]').removeAttr('selected');
            } else {
                if ($(input).is("checkbox") && value) {
                    $(input).removeAttr('checked').prop('checked', false);
                } else {
                    if ($(input).is("textarea")) {
                        $(input).html('');
                    } else {
                        $(input).removeAttr('value').removeAttr('data-value').val('');
                    }
                }
            }
        },

        articles: function (action = '') {
            let area = $(settings_area).find('.sb-articles-area');
            let id = $(area).find('.sb-content').attr('data-article-id');
            if (action == 'save') {
                if (id != -1) {
                    for (var i = 0; i < articles.length; i++) {
                        if (articles[i]['id'] == id) {
                            articles[i] = { id: id, title: $(area).find('.sb-article-title input').val(), content: $(area).find('.sb-article-content textarea').val(), link: $(area).find('.sb-article-link input').val() };
                            break;
                        }
                    }
                }
            }
        }
    }

    /*
    * ----------------------------------------------------------
    * SB USERS METHODS
    * ----------------------------------------------------------
    */

    var SBUsers = {
        real_time: null,
        datetime_last_user: '2000-01-01 00:00:00',
        sorting: ['creation_time', 'DESC'],
        user_types: ['visitor', 'lead', 'user'],
        search_query: '',
        init: false,
        busy: false,

        // Table menu filter
        filter: function (type) {
            if (type == 'all') {
                type = ['visitor', 'lead', 'user'];
            } else if (type == 'agent') {
                type = ['agent', 'admin'];
            } else {
                type = [type];
            }
            this.user_types = type;
            this.loading();
            users_pagination = 1;
            SBF.ajax({
                function: type == 'online' ? 'get-online-users' : 'get-users',
                exclude_id: 'online' ? SB_ACTIVE_AGENT['id'] : -1,
                sorting: this.sorting,
                user_types: type,
                search: this.search_query
            }, (response) => {
                this.populate(response);
                this.loading(false);
            });
        },

        // Table menu filter
        sort: function (field, direction = 'DESC') {
            this.sorting = [field, direction];
            this.loading();
            users_pagination = 1;
            SBF.ajax({
                function: 'get-users',
                sorting: this.sorting,
                user_types: this.user_types,
                search: this.search_query
            }, (response) => {
                this.populate(response);
                this.loading(false);
            });
        },

        // Search users
        search: function (input) {
            searchInput(input, (search, icon) => {
                users_pagination = 1;
                SBF.ajax({
                    function: search.length > 1 ? 'search-users' : 'get-users',
                    search: search,
                    user_types: this.user_types,
                    sorting: this.sorting,
                }, (response) => {
                    this.user_types = ['visitor', 'lead', 'user'];
                    this.populate(response);
                    this.search_query = search;
                    $(icon).sbLoading(false);
                    $(users_table_menu).find('li').sbActivate(false).eq(0).sbActivate();
                });
            });
        },

        // Populate the table
        populate: function (response) {
            let code = '';
            let count = response.length;
            if (count) {
                for (var i = 0; i < count; i++) {
                    code += this.getRow(new SBUser(response[i]));
                }
            } else {
                code = `<p class="sb-no-results">${sb_('No users found.')}</p>`;
            }
            $(users_table).find('tbody').html(code);
        },

        // Update users and table with new users
        update: function () {
            if (!this.busy) {
                let populate = ['user', 'visitor', 'lead'].includes(this.user_types[0]) && this.search_query == '';
                let filter = $(users_table_menu).find('.sb-active').data('type');
                if (filter == 'online') {
                    this.filter(filter);
                } else {
                    this.busy = true;
                    SBF.ajax({
                        function: 'get-new-users',
                        datetime: this.datetime_last_user
                    }, (response) => {
                        let count = response.length;
                        this.busy = false;
                        if (count > 0) {
                            let code = '';
                            for (var i = 0; i < count; i++) {
                                let user = new SBUser(response[i]);
                                users[user.id] = user;
                                this.updateMenu('add', user.type);
                                if (populate) {
                                    code += this.getRow(user);
                                }
                            }
                            if (code != '') {

                                $(users_table).find('tbody').prepend(code);
                                if (filter == 'user') {
                                    filter = '[data-user-type="visitor"], [data-user-type="lead"]';
                                } else if (filter == 'visitor') {
                                    filter = '[data-user-type="user"], [data-user-type="lead"]';
                                } else {
                                    filter = '';
                                }
                                if (filter != '') {
                                    $(users_table).find(filter).remove();
                                }
                            }
                            this.datetime_last_user = response[0]['creation_time'];
                        }
                    });
                }  
            }
        },

        // Get a user row code
        getRow: function (user) {
            if (user instanceof SBUser) {
                return `<tr data-user-id="${user.id}" data-user-type="${user.type}"><td><input type="checkbox" /></td><td class="sb-td-profile"><a class="sb-profile"><img src="${user.image}" /><span>${user.get('full_name')}</span></a></td><td class="sb-td-email">${user.get('email')}</td><td class="sb-td-ut">${user.type}</td><td>${SBF.beautifyTime(user.get('last_activity'))}</td><td>${user.get('creation_time')}</td></tr>`;
            } else {
                SBF.error('User not of type SBUser', 'SBUsers.getRow');
                return false;
            }
        },

        // Update a user row
        updateRow: function (user) {
            let row = $(users_table).find(`[data-user-id="${user.id}"]`);
            if (row.length) {
                let menu_active = $(users_table_menu).find('.sb-active').data('type');
                if ((user.type != menu_active) && !(user.type == 'admin' && menu_active == 'agent') && menu_active != 'all') {
                    let counter = $(admin).find(`[data-type="${user.type == 'admin' ? 'agent' : user.type}"] span`);
                    let count = parseInt($(counter).attr('data-count'));
                    $(counter).html(count + 1).attr('data-count', count + 1);
                    $(row).remove();
                } else {
                    $(row).replaceWith(this.getRow(user));
                }
            } else {
                $(users_table).find('tbody').append(this.getRow(user));
            }
        },

        // Update users table menu
        updateMenu: function (action = 'all', type = false) {
            let user_types = ['all', 'user', 'lead', 'visitor'];
            if (action == 'all') {
                SBF.ajax({
                    function: 'count-users'
                }, (response) => {
                    for (var i = 0; i < user_types.length; i++) {
                        this.updateMenuItem(users_table_menu, 'set', user_types[i], response[user_types[i]]);
                    }
                });
            } else {
                this.updateMenuItem(users_table_menu, action, type);
            }
        },

        updateMenuItem: function (menu, action = 'set', type = false, count = 1) {
            let item = $(menu).find(`[data-type="${type}"] span`);
            if (action != 'set') {
                count = parseInt($(item).attr('data-count')) + (1 * (action == 'add' ? 1 : -1));
            }
            if (action != 'set' && type != 'all') {
                let item_all = $(menu).find(`[data-type="all"] span`);
                let count_all = parseInt($(item_all).attr('data-count'));
                count_all += 1 * (action == 'add' ? 1 : -1);
                $(item_all).html(`(${count_all})`).attr('data-count', count_all);
            }
            $(item).html(`(${count})`).attr('data-count', count);
        },

        // Delete a user
        delete: function (user_ids) {
            this.loading();
            if (Array.isArray(user_ids)) {
                SBF.ajax({
                    function: 'delete-users',
                    user_ids: user_ids
                }, () => {
                    for (var i = 0; i < user_ids.length; i++) {
                        delete users[user_ids[i]];
                        $(users_table).find(`[data-user-id="${user_ids[i]}"]`).remove();
                        $(conversations_admin_list_ul).find(`[data-user-id="${user_ids[i]}"]`).remove();
                    }
                    if ($(users_table).find('[data-user-id]').length == 0) {
                        this.filter($(users_table_menu).find('.sb-active').data('type'));
                    }
                    showResponse('Users deleted');
                    this.updateMenu();
                    this.loading(false);
                });
            } else {
                users[user_ids].delete(() => {
                    if (activeUser().id == user_ids) {
                        activeUser(false);
                    }
                    delete users[user_ids];
                    $(users_table).find(`[data-user-id="${user_ids}"]`).remove();
                    $(conversations_admin_list_ul).find(`[data-user-id="${user_ids}"]`).remove();
                    $(admin).sbHideLightbox();
                    showResponse('User deleted');
                    this.updateMenu();
                    this.loading(false);
                });
            }
        },

        // Start or stop the real time update of the users and table
        startRealTime: function () {
            this.stopRealTime();
            this.real_time = setInterval(() => {
                this.update();
            }, 1000);
        },

        stopRealTime: function () {
            clearInterval(this.real_time);
        },

        // Table loading
        loading: function (show = true) {
            let loading = $(users_area).find('.sb-loading-table');
            if (show) $(loading).sbActivate();
            else $(loading).sbActivate(false);
        },

        // CSV generation and download
        csv: function () {
            SBF.ajax({ function: 'csv-users' }, (response) => window.open(response));
        }
    }

    /*
    * ----------------------------------------------------------
    * # SB CONVERSATIONS METHODS
    * ----------------------------------------------------------
    */

    var SBConversations = {
        real_time: null,
        datetime_last_conversation: '2000-01-01 00:00:00',
        user_typing: false,
        desktop_notifications: false,
        flash_notifications: false,
        busy: false,
        is_search: false,

        // Open the conversations tab
        open: function (conversation_id = -1, user_id) {
            if (conversation_id != -1) {
                this.openConversation(conversation_id, user_id);
            }
            $(admin).sbHideLightbox();
            $(header).find('.sb-admin-nav a').sbActivate(false).parent().find('#sb-messages').sbActivate();
            $(admin).find(' > main > div').sbActivate(false);
            $(conversations_area).sbActivate().find('.sb-board').removeClass('sb-no-conversation');
            this.startRealTime();
        },

        // Open a single conversation
        openConversation: function (conversation_id, user_id = false, scroll = true) {
            if (user_id === false) {
                SBF.ajax({
                    function: 'get-conversation',
                    conversation_id: conversation_id
                }, (response) => {
                    this.openConversation(conversation_id, response['details']['user_id'], scroll);
                });
            } else {
                let area = $(conversations_area).find('.sb-conversation .sb-list');
                let new_user = SBF.null(users[user_id]);

                $(area).html('');
                $(area).sbLoading(true);

                // Init the user
                if (new_user) {
                    activeUser(new SBUser({ 'id': user_id }));
                    activeUser().update(() => {
                        users[user_id] = activeUser();
                        this.updateUserDetails();
                    });
                } else {
                    activeUser(users[user_id]);
                }

                // Open the conversation
                $(conversations_admin_list_ul).find('li').sbActivate(false);
                $(conversations_area).find(`[data-conversation-id="${conversation_id}"]`).sbActivate();
                if (conversation_id != -1) {
                    activeUser().getFullConversation(conversation_id, (response) => {
                        let conversation = (conversations_area).find(`[data-conversation-id="${conversation_id}"]`);
                        let conversation_status_code = response.get('conversation_status_code');
                        let select = $(conversations_admin_list).find('.sb-select');

                        SBChat.setConversation(response);
                        SBChat.populate();
                        $(conversations_area).find('.sb-top > a').html(response.get('title'));
                        $(conversations_area).find('.sb-top [data-value="read"]').sbActivate(conversation_status_code == 2);

                        // Departments
                        if (is_departments) {
                            let department = 'department' in response['details'] && !SBF.null(response['details']['department']) ? response['details']['department'] : -1;
                            let select = $(conversations_area).find('#conversation-department');
                            let item = $(select).find(`[data-id="${department}"]`);
                            $(select).find(' > p').attr('data-value', $(item).data('value')).html($(item).html());
                        }

                        // Activate conversation
                        if ([1, 2].includes(conversation_status_code)) {
                            conversation_status_code = 0;
                        }
                        if ($(select).find('.sb-active').attr('data-value') != conversation_status_code) {
                            $(select).find(`[data-value="${conversation_status_code}"]`).click();
                            $(select).find('ul').sbActivate(false);
                        }
                        if (responsive) {
                            this.mobileOpenConversation();
                        }
                        $(conversation).sbActivate();
                        if (scroll) {
                            this.scrollTo();
                        }

                        // Check if another agent is replying
                        SBF.ajax({
                            function: 'is-agent-typing',
                            conversation_id: conversation_id
                        }, (response) => {
                            if (!SBF.null(response)) {
                                $(conversations_area).find('.sb-editor > .sb-labels').prepend(`<span class="sb-status-warning sb-status-agent-typing">${response['first_name']} ${response['last_name']} ${sb_('is writing an answer...')}</span>`);
                                setTimeout(() => {
                                    $(conversations_area).find('.sb-status-agent-typing').remove();
                                }, 5000);
                            }
                        });

                        // Woocommerce
                        if (SBApps.is('woocommerce')) {
                            SBApps.woocommerce.conversationPanel();
                        }

                        // UMP
                        if (SBApps.is('ump')) {
                            SBApps.ump.conversationPanel();
                        }

                        // Remove the red label of new conversations
                        setTimeout(() => {
                            $(conversation).find(' > span').remove();
                        }, 3000);

                        $(area).sbLoading(false);
                    });
                } else {
                    SBChat.clear();
                    $(conversations_admin_list_ul).find('li').sbActivate(false);
                    $(area).sbLoading(false);
                }

                // User details
                if (!new_user) {
                    this.updateUserDetails();
                }

                // Populate user conversations on the bottom right area
                activeUser().getConversations(function (response) {
                    $(conversations_area).find('.sb-user-conversations').html(activeUser().getConversationsCode(response));
                });

                // More settings
                $(conversations_area).find('.sb-board').removeClass('sb-no-conversation');
                updateUsersActivity();
                this.startRealTime();
                SBChat.email_sent = false;
            }
        },

        // [Deprecated] this method is obsolete and it will be removed soon
        populate: function (conversation_id, user_id, scroll) {
            this.openConversation(conversation_id, user_id, scroll);
        },

        // Populate conversations
        populateList: function (response) {
            let code = '';
            conversations = [];
            for (var i = 0; i < response.length; i++) {
                code += this.getListCode(response[i]);
                conversations.push(response[i]);
            }
            if (code == '') {
                code = `<p class="sb-no-results">${sb_('No conversations found.')}</p>`;
            }
            $(conversations_admin_list_ul).html(code);
            this.updateMenu();
        },

        // Update the conversations list with new conversations or messages 
        update: function () {
            if (!this.busy) {
                this.busy = true;
                SBF.ajax({
                    function: 'get-new-conversations',
                    datetime: this.datetime_last_conversation,
                    routing: SB_ADMIN_SETTINGS['routing']
                }, (response) => {
                    this.busy = false;
                    if (response.length) {
                        let code_pending = '';
                        let code_not_pending = '';
                        let active_conversation_id = SBChat.conversation != false ? SBChat.conversation.id : -1;
                        let item_not_pending;
                        let scroll_to_conversation = false;
                        let id_check = [];
                        this.datetime_last_conversation = response[0]['creation_time'];

                        for (var i = 0; i < response.length; i++) {
                            if (!id_check.includes(response[i]['conversation_id'])) {
                                let item = response[i];
                                let status = item['conversation_status_code'];
                                let user_id = item['user_id'];
                                let conversation_id = item['conversation_id'];
                                let active_conversation = active_conversation_id == conversation_id;
                                let conversation_code = this.getListCode(item, null, !active_conversation);
                                let conversation = $(conversations_admin_list_ul).find(`[data-conversation-id="${conversation_id}"]`);
                                let conversation_index = conversation.index();
                                let conversation_length = conversation.length;
                                let payload = SBF.null(response[i]['payload']) ? {} : JSON.parse(response[i]['payload']);

                                // Active conversation
                                if (active_conversation) {
                                    conversation_code = conversation_code.replace('<li', '<li class="sb-active"');
                                    if (conversation_length) {
                                        if (item['message'] != '') {
                                            $(conversation).replaceWith(conversation_code);
                                        }
                                        conversations[conversation_index]['conversation_status_code'] = status;
                                        $(conversations_area).find('.sb-top [data-value="read"]').sbActivate(status == 2);
                                    } else {
                                        scroll_to_conversation = true;
                                    }
                                } else if (conversation_length) {
                                    // Conversation already in list but not active
                                    conversations[conversation_index] = item;
                                    $(conversations_admin_list_ul).find(`[data-conversation-id="${conversation_id}"]`).remove();
                                }

                                // Add the user to the global users array if it doesn't exists
                                if (!(user_id in users)) {
                                    users[user_id] = new SBUser({ 'id': user_id, 'first_name': item['first_name'], 'last_name': item['last_name'], 'profile_image': item['profile_image'], 'user_type': item['user_type'] });
                                }

                                // New conversation 
                                if (!active_conversation || conversation_length == 0) {
                                    if (status == 2) {
                                        code_pending += conversation_code;
                                        conversations.unshift(item);
                                    } else if (status == 0 || status == 1) {
                                        item_not_pending = $(conversations_admin_list_ul).find('[data-conversation-status="2"]').last();
                                        if (item_not_pending.length == 0) {
                                            code_pending += conversation_code;
                                        } else {
                                            conversations.splice(item_not_pending.index() + 1, 0, item);
                                            code_not_pending += conversation_code;
                                        }
                                    }
                                    if (conversation_length == 0 && ['a', 'c', 'ic'].includes(SB_ADMIN_SETTINGS['sounds'])) {
                                        SBChat.audio.play();
                                    }
                                    if (user_id == activeUser().id) {
                                        activeUser().getConversations(function (response) {
                                            $(conversations_area).find('.sb-user-conversations').html(activeUser().getConversationsCode(response));
                                        });
                                    }
                                }

                                // Payload
                                if ('event' in payload && payload['event'] == 'update-user') {
                                    activeUser().update(() => {
                                        this.updateUserDetails();
                                    });
                                }

                                // Desktop and flash notifications
                                if (!SBChat.tab_active && (!SBF.isAgent(item['message_user_type']) || item['message_user_type'] == 'bot')) {
                                    if (this.desktop_notifications) {
                                        SBChat.desktopNotification(item['first_name'] + ' ' + item['last_name'], item['message'], (item['profile_image'].indexOf('user.svg') > 0 ? SB_ADMIN_SETTINGS['notifications-icon'] : item['profile_image']), conversation_id, user_id);
                                    }
                                    if (this.flash_notifications) {
                                        document.title = sb_('New message...');
                                    }
                                }
                                id_check.push(conversation_id);
                            }
                        }
                        if (code_pending != '') {
                            $(conversations_admin_list_ul).prepend(code_pending);
                        }
                        if (code_not_pending != '') {
                            $(code_not_pending).insertAfter(item_not_pending);
                        }
                        if (scroll_to_conversation) {
                            this.scrollTo();
                        }
                        this.updateMenu();
                    }
                });
            }
        },

        // Update the top left filter
        updateMenu: function () {
            $(conversations_admin_list).find('.sb-select > p span').html('(' + $(conversations_admin_list_ul).find('[data-conversation-status="2"]').length + ')');
        },

        // Return the code of the message menu
        messageMenu: function (agent) {
            return `<i class="sb-menu-btn sb-icon-menu"></i><ul class="sb-menu">${SB_VERSIONS['dialogflow'] ? `<li data-value="bot">${sb_('Send to Dialogflow')}</li>` : ''}${agent ? `<li data-value="delete">${sb_('Delete')}</li>` : ''}</ul>`;
        },

        // Update the users details of the conversations area
        updateUserDetails() {
            if (!activeUser()) return;
            $(conversations_area).find(`[data-user-id="${activeUser().id}"] .sb-name,.sb-top > a`).html(activeUser().get('full_name'));
            $(conversations_area).find('.sb-user-details .sb-profile').setProfile();
            SBProfile.populate(activeUser(), $(conversations_area).find('.sb-profile-list'));
        },

        // Return the conversation code of the left conversations list
        getListCode: function (conversation_array, status, label = false) {
            let message = conversation_array['message'];
            if (SBF.null(status)) status = conversation_array['conversation_status_code'];
            if (message.length > 110) {
                message = message.substr(0, 110) + ' ...';
            }
            if (message == '' && !SBF.null(conversation_array['attachments'])) {
                let files = JSON.parse(conversation_array['attachments']);
                if (Array.isArray(files)) {
                    for (var i = 0; i < files.length; i++) {
                        message += files[i][0] + ' ';
                    }
                    if (message.length > 114) {
                        message = message.substr(0, 114) + ' ...';
                    }
                }
            }
            return `<li data-user-id="${conversation_array['user_id']}" data-conversation-id="${conversation_array['conversation_id']}" data-conversation-status="${status}">` + (label && status == 2 ? '<span>' + sb_('New') + '</span>' : '') + `<div class="sb-profile"><img src="${conversation_array['profile_image']}"><span class="sb-name">${conversation_array['first_name']} ${conversation_array['last_name']}</span><span class="sb-time">${SBF.beautifyTime(conversation_array['creation_time'])}</span></div><p>${message}</p></li>`;
        },

        // Start or stop the real time update of left conversations list and chat 
        startRealTime: function () {
            this.stopRealTime();
            this.real_time = setInterval(() => {
                this.update();
                this.updateCurrentURL();
            }, 10000);
            SBChat.startRealTime();
        },
        stopRealTime: function () {
            clearInterval(this.real_time);
            SBChat.stopRealTime();
        },

        // CSV generation and download
        csv: function (conversation_id = -1) {
            SBF.ajax({
                function: 'csv-conversations',
                conversation_id: conversation_id
            }, (response) => window.open(response));
        },

        // Set the typing status
        typing: function (typing) {
            if (typing) {
                if (SBChat.user_online && !this.user_typing) {
                    $(conversations_area).find('.sb-conversation .sb-top > .sb-labels').append('<span class="sb-status-typing">' + sb_('Typing') + '</span>');
                    this.user_typing = true;
                }
            } else if (this.user_typing) {
                $(conversations_area).find('.sb-conversation .sb-top .sb-status-typing').remove();
                this.user_typing = false;
            }
        },

        // Scroll the left conversations list to the active conversation
        scrollTo: function () {
            let active = $(conversations_admin_list_ul).find('.sb-active');
            let offset = active.length ? $(active)[0].offsetTop : 0;
            $(conversations_admin_list_ul).parent().scrollTop(offset - (responsive ? 120 : 70));
        },

        // Search conversations
        search: function (input) {
            searchInput(input, (search, icon) => {
                if (search.length > 1) {
                    SBF.ajax({
                        function: 'search-conversations',
                        search: search,
                        routing: SB_ADMIN_SETTINGS['routing']
                    }, (response) => {
                        SBConversations.populateList(response);
                        $(icon).sbLoading(false);
                        this.scrollTo();
                        this.is_search = true;
                    });
                } else {
                    pagination = 1;
                    SBF.ajax({
                        function: 'get-conversations',
                        status_code: $(conversations_admin_list).find('.sb-select li.sb-active').data('value'),
                        routing: SB_ADMIN_SETTINGS['routing']
                    }, (response) => {
                        SBConversations.populateList(response);
                        $(icon).sbLoading(false);
                        this.is_search = false;
                    });
                }
            });
        },

        // Get the page url of the user
        updateCurrentURL: function () {
            if (SBChat.user_online && activeUser() != false) {
                let url = $(conversations_area).find('.sb-profile-list [data-id="current_url"] label');
                if (url.length) {
                    SBF.ajax({
                        function: 'current-url'
                    }, (response) => {
                        if (response != false) {
                            $(url).attr('data-value', response).html(response.replace('https://', '').replace('http://', '').replace(/\/$/, ''));
                        }
                    });
                }
            }
        },

        // Show Dialogflow Intent Box
        showDialogflowIntent: function (message_id) {
            let box = $(admin).find('.sb-dialogflow-intent-box');
            let messages = SBChat.conversation.messages;
            let expression = '';
            let response = '';
            for (var i = messages.length - 1; i > 0; i--) {
                if (messages[i].id == message_id) {
                    response = messages[i].get('message');
                    if (SBF.isAgent(messages[i].get('user_type'))) {
                        expression = SBChat.conversation.getLastUserMessage(i - 1);
                        if (expression == false) {
                            expression = '';
                        } else {
                            expression = expression.get('message');
                        }
                    } else {
                        expression = response;
                        response = '';
                    }
                    break;
                }
            }
            $(box).find('.sb-type-text:not(.sb-first)').remove();
            $(box).find('input').val(expression);
            $(box).find('textarea').val(response);
            $(box).sbShowLightbox();
        },

        // Send a Dialogflow intent
        sendDialogflowIntent: function (button) {
            if (!$(button).sbLoading()) {
                $(button).sbLoading(true);
                let box = $(admin).find('.sb-dialogflow-intent-box');
                let expressions = [];
                let response = $(box).find('textarea').val();
                $(box).find('.sb-type-text input').each(function () {
                    if ($(this).val() != '') {
                        expressions.push($(this).val());
                    }
                });
                if (response == '' || expressions.length == 0) {
                    SBForm.showErrorMessage(box, 'Please insert the bot response and at least one user expression.');
                } else {
                    SBF.ajax({
                        function: 'dialogflow-intent',
                        expressions: expressions,
                        response: response,
                        agent_language: $(box).find('.sb-dialogflow-languages select').val()
                    }, (response) => {
                        if (response === true) {
                            $(admin).sbHideLightbox();
                            showResponse('Intent created');
                        } else {
                            let message = 'Error';
                            if ('error' in response && 'message' in response['error']) {
                                message = response['error']['message'];
                            }
                            SBForm.showErrorMessage(box, message);
                        }
                        $(button).sbLoading(false);
                    });
                }
            }
        },

        // Update the department of a conversation
        assignDepartment: function (conversation_id, department, onSuccess) {
            SBF.ajax({
                function: 'update-conversation-department',
                conversation_id: conversation_id,
                department: department,
                message: SBChat.conversation.getLastMessage().get('message')
            }, (response) => {
                onSuccess(response);
            });
        },

        // Mobile conversations menu
        mobileOpenConversation: function () {
            $(conversations_area).find('.sb-admin-list').sbActivate(false);
            $(conversations_area).find('.sb-conversation').sbActivate();
            $(header).addClass('sb-hide');
        },

        mobileCloseConversation: function () {
            $(conversations_admin_list_ul).find('li.sb-active').sbActivate(false);
            $(conversations_area).find('.sb-admin-list').sbActivate();
            $(conversations_area).find('.sb-conversation').sbActivate(false);
            $(header).removeClass('sb-hide');
        },

        // Trigger the click event of the first conversation
        clickFirst: function () {
            $(conversations_admin_list_ul).find('li:first-child').click();
        },

        // Saved replies
        savedReplies(textarea, value) {
            let last_char = value.charAt(textarea.selectionStart - 1);
            if (last_char == '#') {
                SBChat.editor_listening = true;
            }
            if (SBChat.editor_listening && last_char == ' ') {
                let keyword = value.substr(value.lastIndexOf('#') + 1).replace(' ', '');
                SBChat.editor_listening = false;
                for (var i = 0; i < saved_replies_list.length; i++) {
                    if (saved_replies_list[i]['reply-name'] == keyword) {
                        $(textarea).val(value.substr(0, value.lastIndexOf('#')) + saved_replies_list[i]['reply-text']);
                        return;
                    }
                }
            }
        }
    }

    /* 
    * ----------------------------------------------------------
    * # SB PROFILE
    * ----------------------------------------------------------
    */

    var SBProfile = {

        // Get all profile settings
        getAll: function (profile_area) {
            return SBForm.getAll(profile_area);
        },

        // Get a single setting
        get: function (input) {
            return SBForm.get(input);
        },

        // Set a single setting
        set: function (item, value) {
            return SBForm.set(item, value);
        },

        // Display the user box
        show: function (user_id) {
            loading();
            activeUser(new SBUser({ 'id': user_id }));
            activeUser().update(() => {
                this.populate(activeUser(), $(profile_area).find('.sb-profile-list'));
                $(profile_area).find('.sb-profile').setProfile();
                activeUser().getConversations((response) => {
                    let user_type = activeUser().type;
                    if (SBF.isAgent(user_type)) {
                        this.agentData();
                    }
                    $(profile_area).find('.sb-user-conversations').html(activeUser().getConversationsCode(response));
                    this.boxClasses(profile_area, user_type);
                    $(profile_area).attr('data-user-id', activeUser().id).sbShowLightbox();
                    loading(false, false);
                });
                users[user_id] = activeUser();
            });
        },

        showEdit: function (user) {
            if (user instanceof SBUser) {
                let password = $(profile_edit_area).find('#password input');
                let current_user_type = user.type;
                let select = $(profile_edit_area).find('#user_type select');
                let email = $(profile_edit_area).find('#email input');
                $(profile_edit_area).removeClass('sb-user-new').attr('data-user-id', user.id);
                $(profile_edit_area).find('.sb-top-bar .sb-save').html(`<i class="sb-icon-check"></i>${sb_('Save changes')}`);
                $(profile_edit_area).find('.sb-profile').setProfile();
                this.populateEdit(user, profile_edit_area);
                if (current_user_type == 'visitor' || current_user_type == 'lead') {
                    $(email).removeAttr('required')
                } else {
                    $(email).prop('required', true);
                }

                // User type select
                if (SB_ACTIVE_AGENT['user_type'] == 'admin' && SBF.isAgent(current_user_type)) {
                    $(select).html('<option value="agent">Agent</option><option value="admin"' + (current_user_type == 'admin' ? ' selected' : '') + '>Admin</option>');
                }

                // Password
                if ($(password).val() != '') {
                    $(password).val('********');
                }

                // Show the edit box
                this.boxClasses(profile_edit_area, current_user_type);
                $(profile_edit_area).sbShowLightbox();
            } else {
                SBF.error('User not of type SBUser', 'SBUsers.showEdit');
                return false;
            }
        },

        // Populate profile
        populate: function (user, profile_area) {
            let exclude = ['first_name', 'last_name', 'password', 'profile_image'];
            let code = $(profile_area).hasClass('sb-profile-list-conversation') ? this.profileRow('conversation-id', $(conversations_admin_list_ul).find('.sb-active').attr('data-conversation-id'), sb_('Conversation ID')) : '';
            if (SB_ACTIVE_AGENT['user_type'] != 'admin') {
                exclude.push('token');
            }
            for (var key in user.details) {
                if (!exclude.includes(key)) {
                    code += this.profileRow(key, user.get(key));
                }
            }
            if (user.isExtraEmpty()) {
                SBF.ajax({
                    function: 'get-user-extra',
                    user_id: user.id
                }, (response) => {
                    for (var i = 0; i < response.length; i++) {
                        let slug = response[i]['slug'];
                        user.setExtra(slug, response[i]);
                        code += this.profileRow(slug, response[i]['value'], response[i]['name']);
                    }
                    $(profile_area).html(`<ul>${code}</ul>`);
                    collapse(profile_area, 145);
                });
            } else {
                for (var key in user.extra) {
                    let info = user.getExtra(key);
                    code += this.profileRow(key, info['value'], info['name']);
                }
                $(profile_area).html(`<ul>${code}</ul>`);
                collapse(profile_area, 145);
            }
        },

        profileRow: function (key, value, name = key) {
            if (value == '') return '';
            let icons = { 'id': 'padlock', 'conversation-id': 'padlock', 'full_name': 'user', 'email': 'envelope', 'phone': 'phone', 'user_type': 'user', 'last_activity': 'calendar', 'creation_time': 'calendar', 'token': 'shuffle', 'currency': 'currency', 'browser': 'smartphone', 'ip': 'internet', 'location': 'marker', 'country': 'marker', 'address': 'marker', 'city': 'marker', 'os': 'desktop', 'current_url': 'next', 'timezone': 'clock' };
            let icon = `<i class="sb-icon sb-icon-${key in icons ? icons[key] : 'plane'}"></i>`;
            let lowercase;
            let image = false;
            switch (key) {
                case 'last_activity':
                case 'creation_time':
                    value = SBF.beautifyTime(value);
                    break;
                case 'user_type':
                    value = SBF.slugToString(value);
                    break;
                case 'country_code':
                case 'browser_language':
                    icon = `<img src="${SB_URL}/media/flags/${value.toLowerCase()}.png" />`;
                    break;
                case 'browser':
                    lowercase = value.toLowerCase();
                    if (lowercase.indexOf('chrome') > -1) {
                        image = 'chrome';
                    } else if (lowercase.indexOf('edge') > -1) {
                        image = 'edge';
                    } else if (lowercase.indexOf('firefox') > -1) {
                        image = 'firefox';
                    } else if (lowercase.indexOf('opera') > -1) {
                        image = 'opera';
                    } else if (lowercase.indexOf('safari') > -1) {
                        image = 'safari';
                    }
                    if (image) {
                        icon = `<img src="${SB_URL}/media/devices/${image}.svg" />`;
                    }
                    break;
                case 'os':
                    lowercase = value.toLowerCase();
                    if (lowercase.indexOf('windows') > -1) {
                        image = 'windows';
                    } else if (lowercase.indexOf('mac') > -1 || lowercase.indexOf('apple') > -1 || lowercase.indexOf('ipad') > -1 || lowercase.indexOf('iphone') > -1) {
                        image = 'apple';
                    } else if (lowercase.indexOf('android') > -1) {
                        image = 'android';
                    }
                    if (image) {
                        icon = `<img src="${SB_URL}/media/devices/${image}.svg" />`;
                    }
                    break;
            }
            return `<li data-id="${key}">${icon}<span>${sb_(SBF.slugToString(name))}</span><label>${value}</label></li>`;
        },

        // Populate profile edit box
        populateEdit: function (user, profile_edit_area) {
            $(profile_edit_area).find('.sb-details .sb-input').each((i, element) => {
                this.set(element, user.details[$(element).attr('id')]);
            });
            $(profile_edit_area).find('.sb-additional-details .sb-input').each((i, element) => {
                let key = $(element).attr('id');
                if (key in user.extra) {
                    this.set(element, user.extra[key]['value']);
                } else {
                    this.set(element, '');
                }
            });
        },

        // Clear the profile edit area
        clear: function (profile_edit_area) {
            SBForm.clear(profile_edit_area);
        },

        // Check for errors on user input
        errors: function (profile_edit_area) {
            return SBForm.errors($(profile_edit_area).find('.sb-details'));
        },

        // Display a error message
        showErrorMessage: function (profile_edit_area, message) {
            SBForm.showErrorMessage(profile_edit_area, message);
        },

        // Agents data area
        agentData: function () {
            let code = `<div class="sb-title">${sb_('Feedback rating')}</div><div class="sb-rating-area sb-loading"></div>`;
            let area = $(profile_area).find('.sb-agent-area');
            $(area).html(code);
            SBF.ajax({
                function: 'get-rating'
            }, (response) => {
                if (response[0] == 0 && response[1] == 0) {
                    code = `<p class="sb-no-results">${sb_('No ratings yet.')}</p>`;
                } else {
                    let total = response[0] + response[1];
                    let positive = response[0] * 100 / total;
                    let negative = response[1] * 100 / total;
                    code = `<div><div>${sb_('Positive')}</div><span data-count="${response[0]}" style="width: ${Math.round(positive * 2)}px"></span><div>${positive.toFixed(2)} %</div></div><div><div>${sb_('Negative')}</div><span data-count="${response[1]}" style="width: ${Math.round(negative * 2)}px"></span><div>${negative.toFixed(2)} %</div></div><p class="sb-rating-count">${total} ${sb_('Ratings')}</p>`;
                }
                $(area).find('.sb-rating-area').html(code).sbLoading(false);
            });
        },

        boxClasses: function (box, user_type = false) {
            $(box).removeClass('sb-type-admin sb-type-agent sb-type-lead sb-type-user sb-type-visitor').addClass(`${user_type != false ? `sb-type-${user_type}` : ''} sb-agent-${SB_ACTIVE_AGENT['user_type']}`);
        }
    }

    /*
    * ----------------------------------------------------------
    * # INIT AND GLOBAL
    * ----------------------------------------------------------
    */

    var SBAdmin = {
        conversations: SBConversations,
        users: SBUsers,
        settings: SBSettings,
        profile: SBProfile
    }
    window.SBAdmin = SBAdmin;

    $(document).ready(function () {

        admin = $('.sb-admin');
        header = $(admin).find('> .sb-header');
        conversations_area = $(admin).find('.sb-area-conversations');
        conversations_admin_list = $(conversations_area).find('.sb-admin-list');
        conversations_admin_list_ul = $(conversations_admin_list).find('.sb-scroll-area ul');
        users_area = $(admin).find('.sb-area-users');
        users_table = $(users_area).find('.sb-table-users');
        users_table_menu = $(users_area).find('.sb-menu-users');
        profile_area = $(admin).find('.sb-profile-box');
        profile_edit_area = $(admin).find('.sb-profile-edit-box');
        settings_area = $(admin).find('.sb-area-settings');
        is_departments = $(conversations_area).find('#conversation-department').length;
        saved_replies = $(conversations_area).find('.sb-replies');
        overlay = $(admin).find('.sb-lightbox-overlay');
        SITE_URL = typeof SB_URL != 'undefined' ? SB_URL.substr(0, SB_URL.indexOf('-content') - 3) : '';
        woocommerce_products_box = $(conversations_area).find('.sb-woocommerce-products');
        woocommerce_products_box_ul = $(woocommerce_products_box).find(' > div > ul');

        if (!admin.length) return;
        $(admin).removeAttr('style');
        if (isPWA()) {
            $(admin).addClass('sb-pwa');
        }
        if (SBApps.is('woocommerce')) {
            woocommerce_products_box = $(conversations_area).find('.sb-woocommerce-products');
            woocommerce_products_box_ul = $(woocommerce_products_box).find(' > div > ul');
        }
        if (localhost) {
            clearCache();
        }
        if ($(admin).find(' > .sb-rich-login').length) {
            return;
        }

        // Installation
        if (typeof SB_ADMIN_SETTINGS == 'undefined') {
            let area = $(admin).find('.sb-intall');
            $(admin).on('click', '.sb-submit-installation', function () {
                if (!$(this).sbLoading()) {
                    let message = false;
                    let account = $(area).find('#first-name').length;
                    $(this).sbLoading(true);
                    if (SBForm.errors(area)) {
                        message = account ? 'All fields are required. Minimum password length is 8 characters. Be sure you\'ve entered a valid email.' : 'All fields are required.';
                    } else {
                        if (account && $(area).find('#password input').val() != $(area).find('#password-check input').val()) {
                            message = 'The passwords do not match.';
                        } else {
                            let url = window.location.href.replace('/admin', '').replace('.php', '').replace(/#$|\/$/, '').replace(/#$|\/$/, '');
                            $.ajax({
                                method: 'POST',
                                url: url + '/include/ajax.php',
                                data: {
                                    function: 'installation',
                                    details: $.extend(SBForm.getAll(area), { url: url })
                                }
                            }).done((response) => {
                                response = JSON.parse(response);
                                if (response != false) {
                                    response = response[1];
                                    if (response === true) {
                                        setTimeout(() => {
                                            window.location.href = window.location.href + '?refresh=true';
                                        }, 1000);
                                        return;
                                    } else {
                                        switch (response) {
                                            case 'connection-error':
                                                message = 'Support Board cannot connect to the database. Please check the database information and try again.';
                                                break;
                                            case 'missing-details':
                                                message = 'Missing database details! Please check the database information and try again.';
                                                break;
                                            case 'missing-url':
                                                message = 'Support Board cannot get the plugin URL.';
                                                break;
                                            default:
                                                message = response;
                                        }
                                    }
                                } else {
                                    message = response;
                                }
                                if (message !== false) {
                                    SBForm.showErrorMessage(area, message);
                                    $('html, body').animate({ scrollTop: 0 }, 500);
                                }
                                $(this).sbLoading(false);
                            });
                        }
                    }
                    if (message !== false) {
                        SBForm.showErrorMessage(area, message);
                        $('html, body').animate({ scrollTop: 0 }, 500);
                        $(this).sbLoading(false);
                    }
                }
            });
            return;
        }

        // Keyboard shortcuts
        $(window).keydown(function (e) {
            let code = e.which;
            let valid = false;
            if ([13, 27, 37, 38, 39, 40, 46].includes(code)) {
                if ($(admin).find('.sb-dialog-box').sbActive()) {
                    let target = $(admin).find('.sb-dialog-box');
                    switch (code) {
                        case 46:
                        case 27:
                            $(target).find('.sb-cancel').click();
                            break;
                        case 13:
                            $(target).find($(target).attr('data-type') != 'info' ? '.sb-confirm' : '.sb-close').click();
                            break;
                    }
                    valid = true;
                } else if ([38, 40, 46].includes(code) && $(conversations_area).sbActive()) {
                    if (code == 46) {
                        let target = $(conversations_area).find(' > div > .sb-conversation');
                        $(target).find('.sb-top [data-value="' + ($(target).attr('data-conversation-status') == 3 ? 'delete' : 'archive') + '"]').click();
                    } else if (e.ctrlKey) {
                        let target = $(conversations_admin_list_ul).find('.sb-active');
                        if (code == 40) {
                            $(target).next().click();
                        } else {
                            $(target).prev().click();
                        }
                        SBConversations.scrollTo();
                    }
                    valid = true;
                } else if ([37, 39].includes(code) && $(users_area).sbActive() && $(admin).find('.sb-lightbox').sbActive()) {
                    let target = $(users_table).find(`[data-user-id="${activeUser().id}"]`);
                    target = code == 39 ? $(target).next() : $(target).prev();
                    if ($(target).length) {
                        $(admin).sbHideLightbox();
                        SBProfile.show($(target).attr('data-user-id'));
                    }
                    valid = true;
                } else if ([46, 27].includes(code) && $(admin).find('.sb-lightbox').sbActive()) {
                    $(admin).sbHideLightbox();
                    valid = true;
                }
                if (valid) {
                    e.preventDefault();
                }
            }
        });

        // Check if the admin is active
        resetActiveInterval();
        $(document).on('click keydown mousemove', function () {
            if (!SBChat.tab_active) {
                SBF.visibilityChange();
            }
            SBChat.tab_active = true;
            if (active_interval[1]) {
                active_interval[1] = false;
                resetActiveInterval();
                setTimeout(function () {
                    active_interval[1] = true;
                }, 1000);
            }
        });

        // Updates 
        $(header).on('click', '.sb-version', function () {
            let box = $(admin).find('.sb-updates-box');
            SBF.ajax({
                function: 'get-versions'
            }, (response) => {
                let code = '';
                let names = { 'sb': 'Support Board', 'slack': 'Slack', 'dialogflow': 'Dialogflow', 'tickets': 'Tickets', 'woocommerce': 'Woocommerce', 'ump': 'Ultimate Membership Pro' };
                let updates = false;
                for (var key in response) {
                    if (SBApps.is(key)) {
                        let updated = SB_VERSIONS[key] == response[key];
                        if (!updated) {
                            updates = true;
                        }
                        code += `<div class="sb-input"><span>${names[key]}</span><div${updated ? ' class="sb-green"' : ''}>${updated ? sb_('You are running the latest version.') : sb_('Update available! Please update now.')} ${sb_('Your version is')} V ${SB_VERSIONS[key]}.</div></div>`;
                    }
                }
                if (updates) {
                    $(box).find('.sb-update').removeClass('sb-hide');
                } else {
                    $(box).find('.sb-update').addClass('sb-hide');
                }
                loading(false);
                $(box).find('.sb-main').prepend(code);
                $(box).sbShowLightbox();
            });
            loading(true);
            $(box).sbActivate(false);
            $(box).find('.sb-input').remove();
        });

        $(admin).on('click', '.sb-updates-box .sb-update', function () {
            let box = $(admin).find('.sb-updates-box');
            if (!$(this).sbLoading()) {
                SBF.ajax({
                    function: 'update'
                }, (response) => {
                    let error = '';
                    if (SBF.errorValidation(response, 'envato-purchase-code-not-found')) {
                        error = 'Please go to Settings > Miscellaneous and insert your Envato Purchase Code.'
                    } else if (Array.isArray(response) && !response.length) {
                        error = 'Invalid Envato Purchase Code.'
                    } else {
                        let success = true;
                        for (var key in response) {
                            if (response[key] != 'success') {
                                success = false;
                                break;
                            }
                        }
                        if (!success) {
                            error = JSON.stringify(response);
                        }
                    }
                    if (error == '') {
                        clearCache();
                        location.reload();
                    } else {
                        SBForm.showErrorMessage(box, error);
                    }
                    $(this).sbLoading(false);
                });
                $(this).sbLoading(true);
            }
        });

        setTimeout(function () {
            if (SB_ADMIN_SETTINGS['auto-updates']) {
                let last = SBF.storage('last-update-check');
                let today_arr = [today.getMonth(), today.getDate()];
                if (last == false || today_arr[0] != last[0] || (today_arr[1] > (last[1] + 10))) {
                    SBF.storage('last-update-check', today_arr);
                    SBF.ajax({
                        function: 'update'
                    }, (response) => {
                        if (typeof response !== 'string' && !Array.isArray(response)) {
                            showResponse('Automatic update complete. Reload the admin area to apply the update.');
                            clearCache();
                        }
                    });
                }
            }
        }, 1000);

        // Apps
        $(admin).on('click', '.sb-apps > div', function () {
            let box = $(admin).find('.sb-app-box');
            let app_name = $(this).data('app');
            let ga = '?utm_source=plugin&utm_medium=admin_area&utm_campaign=plugin';
            SBF.ajax({
                function: 'app-get-key',
                app_name: app_name
            }, (response) => {
                $(box).find('input').val(response);
            });
            $(box).find('input').val('');
            $(box).find('.sb-top-bar > div:first-child').html($(this).find('h2').html());
            $(box).find('p').html($(this).find('p').html());
            $(box).attr('data-app', app_name);
            $(box).find('.sb-btn-app-puchase').attr('href', 'https://board.support/shop/' + app_name + ga);
            $(box).find('.sb-btn-app-details').attr('href', 'https://board.support/' + app_name + ga);
            $(box).sbShowLightbox();
        });

        $(admin).on('click', '.sb-app-box .sb-activate', function () {
            let box = $(admin).find('.sb-app-box');
            let key = $(box).find('input').val();
            if (key != '') {
                if (!$(this).sbLoading()) {
                    SBF.ajax({
                        function: 'app-activation',
                        app_name: $(box).attr('data-app'),
                        key: key
                    }, (response) => {
                        if (SBF.errorValidation(response)) {
                            let error = '';
                            response = response[1];
                            if (response == 'invalid-key') {
                                error = 'It looks like your license key is invalid. If you believe this is an error, please contact support.';
                            } else if (response == 'expired') {
                                error = 'Your license key is expired. Please purchase a new license. If you believe this is an error, please contact support.';
                            } else {
                                error = 'Error: ' + response;
                            }
                            SBForm.showErrorMessage(box, error);
                            $(this).sbLoading(false);
                        } else {
                            showResponse('Activation complete! Page reload in progress...');
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    });
                    $(this).sbLoading(true);
                }
            } else {
                SBForm.showErrorMessage(box, 'Please insert the license key.');
            }
        });

        // Desktop and flash notifications
        if (typeof Notification !== 'undefined' && (SB_ADMIN_SETTINGS['desktop-notifications'] == 'all' || SB_ADMIN_SETTINGS['desktop-notifications'] == 'agents') && !SB_ADMIN_SETTINGS['push-notifications']) {
            SBConversations.desktop_notifications = true;
        }

        if (SB_ADMIN_SETTINGS['flash-notifications'] == 'all' || SB_ADMIN_SETTINGS['flash-notifications'] == 'agents') {
            SBConversations.flash_notifications = true;
        }

        // Cron jobs
        if (today.getDate() != SBF.storage('admin-clean')) {
            setTimeout(function () {
                SBF.ajax({ function: 'cron-jobs' });
                SBF.storage('admin-clean', today.getDate());
            }, 10000);
        }

        // Collapse button
        $(admin).on('click', '.sb-collapse-btn', function () {
            let active = $(this).sbActive();
            let height = active ? $(this).parent().data('height') + 'px' : '';
            $(this).html(sb_(active ? 'View more' : 'Close'));
            $(this).parent().find(' > div, > ul').css({ 'height': height, 'max-height': height });
            $(this).sbActivate(!active);
        });

        // Close lightbox popup
        $(admin).on('click', '.sb-popup-close', function () {
            $(admin).sbHideLightbox(true);
        });

        /*
        * ----------------------------------------------------------
        * # RESPONSIVE AND MOBILE
        * ----------------------------------------------------------
        */

        if (responsive) {
            $(admin).on('click', '.sb-menu-mobile > i', function () {
                $(this).toggleClass('sb-active');
            });

            $(admin).on('click', '.sb-menu-mobile a', function () {
                $(this).closest('.sb-menu-mobile').find(' > i').sbActivate(false);
            });

            $(admin).on('click', '.sb-menu-wide,.sb-nav', function () {
                $(this).toggleClass('sb-active');
            });

            $(admin).on('click', '.sb-menu-wide > ul > li, .sb-nav > ul > li', function (e) {
                let menu = $(this).parent().parent();
                $(menu).find('li').sbActivate(false);
                $(menu).find('> div').html($(this).html());
                $(menu).sbActivate(false);
                e.preventDefault();
                return false;
            });

            $(admin).find('.sb-admin-list .sb-scroll-area, main > div > .sb-scroll-area,.sb-area-settings > .sb-tab > .sb-scroll-area').on('scroll', function () {
                let scroll = $(this).scrollTop();
                if (scrolls['last'] < (scroll - 10) && scrolls['header']) {
                    $(admin).addClass('sb-header-hidden');
                    scrolls['header'] = false;
                } else if (scrolls['last'] > (scroll + 10) && !scrolls['header'] && !scrolls['always_hidden']) {
                    $(admin).removeClass('sb-header-hidden');
                    scrolls['header'] = true;
                }
                scrolls['last'] = scroll;
            });

            $(admin).on('click', '.sb-search-btn i', function () {
                if ($(this).parent().sbActive()) {
                    $(admin).addClass('sb-header-hidden');
                    scrolls['always_hidden'] = true;
                } else {
                    scrolls['always_hidden'] = false;
                    if ($(conversations_admin_list_ul).parent().scrollTop() < 10) {
                        $(admin).removeClass('sb-header-hidden');
                    }
                }
            });

            $(conversations_area).on('click', '.sb-top .sb-btn-back', function () {
                SBConversations.mobileCloseConversation();
            });

            $(users_table).find('th:first-child').html(sb_('Order by'));

            $(users_table).on('click', 'th:first-child', function () {
                $(this).parent().toggleClass('sb-active');
            });
        }

        /*
        * ----------------------------------------------------------
        * # LEFT NAV
        * ----------------------------------------------------------
        */

        $(header).on('click', ' .sb-admin-nav a', function () {
            let id = $(this).attr('id');
            $(header).find('.sb-admin-nav a').sbActivate(false);
            $(admin).find(' > main > div').sbActivate(false).eq($(this).index()).sbActivate();
            $(this).sbActivate();
            SBF.deactivateAll();
            if (id == 'sb-messages') {
                if (!responsive) {
                    SBConversations.clickFirst();
                }
                SBConversations.update();
                SBConversations.startRealTime();
                SBUsers.stopRealTime();
            } else if (id == 'sb-users') {
                SBUsers.startRealTime();
                SBConversations.stopRealTime();
                if (!SBUsers.init) {
                    loading();
                    users_pagination = 1;
                    SBF.ajax({
                        function: 'get-users',
                        user_types: ['user', 'visitor', 'lead']
                    }, (response) => {
                        SBUsers.populate(response);
                        SBUsers.updateMenu();
                        SBUsers.init = true;
                        SBUsers.datetime_last_user = SBF.dateDB('now');
                        loading(false);
                    });
                }
            } else if (id == 'sb-settings') {
                if (!SBSettings.init) {
                    loading();
                    SBF.ajax({
                        function: 'get-all-settings'
                    }, (response) => {
                        SBSettings.initHTML(response);
                        for (var key in response) {
                            SBSettings.set(key, response[key]);
                        }
                        SBSettings.initPlugins();
                        SBSettings.init = true;
                        loading(false);
                    });
                }
                SBUsers.stopRealTime();
                SBConversations.stopRealTime();
            }
        });

        $(header).on('click', '.sb-profile', function () {
            $(this).next().toggleClass('sb-active');
        });

        $(header).on('click', '[data-value="logout"],.logout', function () {
            SBUsers.stopRealTime();
            SBConversations.stopRealTime();
            SBF.logout();
        });

        $(header).on('click', '[data-value="edit-profile"],.edit-profile', function () {
            loading();
            let user = new SBUser({ 'id': SB_ACTIVE_AGENT['id'] });
            user.update(() => {
                activeUser(user);
                $(conversations_area).find('.sb-board').addClass('sb-no-conversation');
                $(conversations_admin_list_ul).find('.sb-active').sbActivate(false);
                SBProfile.showEdit(user);
            });
        });

        $(header).on('click', '[data-value="status"]', function () {
            agent_online = !$(this).hasClass('sb-online');
            $(this).html(sb_(agent_online ? 'Online' : 'Offline')).attr('class', agent_online ? 'sb-online' : 'sb-offline');
        });

        $(header).find('.sb-account').setProfile(SB_ACTIVE_AGENT['full_name'], SB_ACTIVE_AGENT['profile_image']);

        /*
        * ----------------------------------------------------------
        * # CONVERSATIONS AREA
        * ----------------------------------------------------------
        */

        // Init
        setInterval(function () {
            updateUsersActivity();
        }, 10000);

        loading();

        // Initialize the conversations list
        SBF.ajax({
            function: 'get-conversations',
            routing: SB_ADMIN_SETTINGS['routing']
        }, (response) => {
            if (response.length == 0) {
                $(conversations_area).find('.sb-board').addClass('sb-no-conversation');
            }
            SBConversations.populateList(response);
            if (responsive) {
                $(conversations_area).find('.sb-admin-list').sbActivate();
            }
            if (SBF.getURL('conversation')) {
                SBConversations.openConversation(SBF.getURL('conversation'));
            } else if (!responsive) {
                SBConversations.clickFirst();
            }
            SBConversations.startRealTime();
            SBConversations.datetime_last_conversation = SBF.dateDB('now');
            loading(false);
        });

        // Open the conversation clicked on the left menu
        $(conversations_admin_list_ul).on('click', 'li', function () {
            SBConversations.openConversation($(this).attr('data-conversation-id'), $(this).attr('data-user-id'), false);
            SBF.deactivateAll();
        });

        // Open the user conversation clicked on the bottom right area or user profile box
        $(admin).on('click', '.sb-user-conversations li', function () {
            SBConversations.openConversation($(this).attr('data-conversation-id'), activeUser().id, $(this).attr('data-conversation-status'));
            SBF.deactivateAll();
        });

        // Archive, delete or restore conversations
        $(conversations_area).on('click', '.sb-top ul a', function () {
            let status_code = -1;
            let message = 'The conversation will be ';
            let value = $(this).attr('data-value');
            let conversation_id = SBChat.conversation.id;
            switch (value) {
                case 'inbox':
                    status_code = 0;
                    message += 'restored.';
                    break;
                case 'archive':
                    message += 'archived.';
                    status_code = 3;
                    break;
                case 'delete':
                    message += 'deleted.';
                    status_code = 4;
                    break;
                case 'empty-trash':
                    status_code = 5;
                    message = 'ALL conversations in the trash (including their messages) will be deleted permanently.'
                    break;
                case 'csv':
                    SBConversations.csv(conversation_id);
                    break;
                case 'read':
                    status_code = 0;
                    message += 'marked as read.';
                    break;
            }
            if (status_code != -1) {
                dialog(message, 'alert', function () {
                    SBF.ajax({
                        function: 'update-conversation-status',
                        conversation_id: conversation_id,
                        status_code: status_code
                    }, () => {
                        if ([0, 3, 4].includes(status_code)) {
                            for (var i = 0; i < conversations.length; i++) {
                                if (conversations[i]['conversation_id'] == conversation_id) {
                                    conversations[i]['conversation_status_code'] = status_code;
                                    break;
                                }
                            }
                        }
                        if (value == 'read') {
                            $(conversations_admin_list_ul).find('.sb-active').attr('data-conversation-status', 0);
                            $(conversations_area).find('.sb-top [data-value="read"]').sbActivate(false);
                        } else {
                            $(conversations_admin_list).find('.sb-select li.sb-active').click();
                            $(conversations_admin_list).find('.sb-select ul').sbActivate(false);
                        }
                        if (SB_ADMIN_SETTINGS['close-message'] && status_code == 3) {
                            SBF.ajax({ function: 'close-message', bot_id: SB_ADMIN_SETTINGS['bot-id'], conversation_id: conversation_id });
                        }
                    });
                });
            }
        });

        // Saved replies
        SBF.ajax({
            function: 'saved-replies'
        }, (response) => {
            let code = `<p class="sb-no-results">${sb_('No saved replies found. Add new saved replies via Settings > Miscellaneous.')}</p>`;
            if (Array.isArray(response)) {
                if (response.length > 0 && response[0]['reply-name'] != '') {
                    code = '';
                    saved_replies_list = response;
                    for (var i = 0; i < response.length; i++) {
                        code += `<li><div>${response[i]['reply-name']}</div><div>${response[i]['reply-text']}</div></li>`;
                    }
                }
            }
            $(saved_replies).find('.sb-replies-list > ul').html(code).sbLoading(false);
        });

        $(conversations_area).on('click', '.sb-btn-saved-replies', function () {
            $(saved_replies).sbTogglePopup(this);
        });

        $(saved_replies).on('click', '.sb-replies-list li', function () {
            SBChat.insertText($(this).find('div:last-child').html());
            SBF.deactivateAll();
            $(admin).removeClass('sb-popup-active');
        });

        $(saved_replies).on('input', '.sb-search-btn input', function () {
            let search = $(this).val();
            SBF.search(search, () => {
                let code = '';
                let all = search.length > 1 ? false : true;
                for (var i = 0; i < saved_replies_list.length; i++) {
                    if (all || saved_replies_list[i]['reply-name'].toLowerCase().indexOf(search) > -1 || saved_replies_list[i]['reply-text'].toLowerCase().indexOf(search) > -1) {
                        code += `<li><div>${saved_replies_list[i]['reply-name']}</div><div>${saved_replies_list[i]['reply-text']}</div></li>`;
                    }
                }
                $(saved_replies).find('.sb-replies-list > ul').html(code);
            });
        });

        // Pagination for conversations
        $(conversations_admin_list).find('.sb-scroll-area').on('scroll', function () {
            if (!is_busy && !SBConversations.is_search && scrollPagination(this, true)) {
                let parent = $(conversations_area).find('.sb-admin-list');
                is_busy = true;
                $(parent).append('<div class="sb-loading-global sb-loading"></div>');
                SBF.ajax({
                    function: 'get-conversations',
                    pagination: pagination,
                    status_code: $(parent).find(' > .sb-top > .sb-select p').attr('data-value'),
                    routing: SB_ADMIN_SETTINGS['routing']
                }, (response) => {
                    let code = '';
                    is_busy = false;
                    for (var i = 0; i < response.length; i++) {
                        code += SBConversations.getListCode(response[i]);
                        conversations.push(response[i]);
                    }
                    pagination++;
                    $(conversations_admin_list_ul).append(code);
                    $(parent).find(' > .sb-loading').remove();
                    scrollPagination(this);
                });
            }
        });

        // Event: message deleted
        $(document).on('SBMessageDeleted', function () {
            let last_message = SBChat.conversation.getLastMessage();
            if (last_message != false) {
                $(conversations_admin_list_ul).find('li.sb-active p').html(last_message.get('message'));
            } else {
                $(conversations_admin_list_ul).find('li.sb-active').remove();
                SBConversations.clickFirst();
                SBConversations.scrollTo();
            }
        });

        // Event: message sent
        $(document).on('SBMessageSent', function (e, response) {
            let item = $(conversations_admin_list_ul).find(`[data-conversation-id="${response['conversation_id']}"]`);
            if (response['message'] != '') {
                $(item).find('p').html(response['message']);
            }
            if (response['conversation_status_code'] != -1) {
                $(item).attr('data-conversation-status', response['conversation_status_code']);
                SBConversations.updateMenu();
            }
        });

        // Event: new message of active chat conversation received
        $(document).on('SBNewMessagesReceived', function (e, messages) {
            setTimeout(function () {
                $(conversations_area).find('.sb-conversation .sb-top .sb-status-typing').remove();
            }, 300);
            SBConversations.update();
        });

        // Event: new conversation created 
        $(document).on('SBNewConversationCreated', function () {
            SBConversations.update();
        });

        // Event: email notification sent
        $(document).on('SBEmailSent', function () {
            showResponse('A notificaton email has been sent to the user.');
        });

        // Event: user typing status change
        $(document).on('SBTyping', function (e, response) {
            SBConversations.typing(response);
        });

        // Conversations search
        $(conversations_admin_list).on('input', '.sb-search-btn input', function () {
            SBConversations.search(this);
        });

        $(conversations_area).on('click', '.sb-admin-list .sb-search-btn i', function () {
            SBF.searchClear(this, () => { SBConversations.search($(this).next()) });
        });

        // Conversations filter
        $(conversations_area).on('click', '.sb-admin-list .sb-select li', function () {
            let parent = $(conversations_admin_list_ul).parent();
            let status_code = $(this).data('value');
            if ($(parent).sbLoading()) return;
            pagination = 1;
            $(parent).sbLoading(true);
            SBF.ajax({
                function: 'get-conversations',
                status_code: status_code,
                routing: SB_ADMIN_SETTINGS['routing']
            }, (response) => {
                SBConversations.populateList(response);
                $(conversations_area).find('.sb-conversation').attr('data-conversation-status', status_code);
                if (response.length) {
                    if (!responsive) {
                        let first = true;
                        if (SBChat.conversation != false) {
                            let conversation = $(conversations_admin_list_ul).find(`[data-conversation-id="${SBChat.conversation.id}"]`);
                            if (conversation.length) {
                                $(conversation).sbActivate();
                                first = false;
                            }
                        }
                        if (first) {
                            SBConversations.clickFirst();
                        }
                        SBConversations.scrollTo();
                    }
                } else {
                    $(conversations_area).find('.sb-board').addClass('sb-no-conversation');
                    SBChat.conversation = false;
                }
                $(parent).sbLoading(false);
            });
        });

        // Display the user details box
        $(conversations_area).on('click', '.sb-user-details .sb-scroll-area > .sb-profile,.sb-top > a', function () {
            let user_id = $(conversations_admin_list_ul).find('.sb-active').attr('data-user-id');
            if (activeUser().id != user_id) {
                activeUser(users[user_id]);
            }
            SBProfile.show(activeUser().id);
        });

        // Right profile list methods
        $(admin).on('click', '.sb-profile-list [data-id="location"]', function () {
            let location = $(this).find('label').html().replace(', ', '+');
            dialog('<iframe src="https://maps.google.com/maps?q=' + location + '&output=embed"></iframe>', 'map');
        });

        $(admin).on('click', '.sb-profile-list [data-id="timezone"]', function () {
            SBF.getLocationTimeString(activeUser().extra, (response) => {
                loading(false);
                dialog(response, 'info');
            });
        });

        $(admin).on('click', '.sb-profile-list [data-id="current_url"]', function () {
            let label = $(this).find('label');
            window.open(SBF.null($(label).attr('data-value')) ? $(label).html() : $(label).attr('data-value'));
        });

        // Dialogflow Intent
        $(conversations_area).on('click', '.sb-menu [data-value="bot"]', function () {
            SBConversations.showDialogflowIntent($(this).closest('[data-id]').attr('data-id'));
        });

        $(admin).on('click', '.sb-dialogflow-intent-box .sb-intent-add i', function () {
            $(admin).find('.sb-dialogflow-intent-box .sb-type-text').last().after('<div class="sb-input-setting sb-type-text"><input type="text"></div>');
        });

        $(admin).on('click', '.sb-dialogflow-intent-box .sb-send', function () {
            SBConversations.sendDialogflowIntent(this);
        });

        $(conversations_area).on('click', '#conversation-department li', function (e) {
            let select = $(this).parent().parent();
            if ($(this).data('value') == $(select).find(' > p').attr('data-value')) {
                return true;
            }
            if (SBChat.conversation == false) {
                $(this).parent().sbActivate(false);
                e.preventDefault();
                return false;
            }
            if (!$(select).sbLoading()) {
                dialog(`All agents assigned to the new department will be notified via email. The new department will be ${$(this).html()}.`, 'alert', () => {
                    $(select).sbLoading(true);
                    SBConversations.assignDepartment(SBChat.conversation.id, $(this).data('id'), () => {
                        showResponse('Department updated. The agents have been notified via email.');
                        $(select).find(' > p').attr('data-value', $(this).data('value')).html($(this).html()).next().sbActivate(false);
                        if (SB_ACTIVE_AGENT['department'] != '' && SB_ACTIVE_AGENT['department'] != $(this).data('id')) {
                            $(conversations_admin_list_ul).find(`[data-conversation-id="${SBChat.conversation.id}"]`).remove();
                            SBConversations.clickFirst();
                        }
                        $(select).sbLoading(false);
                    });
                });
            }
            e.preventDefault();
            return false;
        });

        /*
        * ----------------------------------------------------------
        * # USERS AREA
        * ----------------------------------------------------------
        */

        // Checkbox selector
        $(users_table).on('click', 'th :checkbox', function () {
            $(users_table).find('td :checkbox').prop('checked', $(this).prop('checked'));
        });

        $(users_table).on('click', ':checkbox', function () {
            let button = $(users_area).find('[data-value="delete"]');
            if ($(users_table).find('td input:checked').length) {
                $(button).removeAttr('style');
            } else {
                $(button).hide();
            }
        });

        // Table menu filter
        $(users_table_menu).on('click', 'li', function () {
            SBUsers.filter($(this).data('type'));
        });

        // Search users
        $(users_area).on('input', '.sb-search-btn input', function () {
            SBUsers.search(this);
        });

        $(users_area).on('click', '.sb-search-btn i', function () {
            SBF.searchClear(this, () => { SBUsers.search($(this).next()) });
        });

        // Sorting
        $(users_table).on('click', 'th:not(:first-child)', function () {
            let direction = $(this).hasClass('sb-order-asc') ? 'DESC' : 'ASC';
            $(this).toggleClass('sb-order-asc');
            $(this).siblings().sbActivate(false);
            $(this).sbActivate(true);
            SBUsers.sort($(this).data('field'), direction);
        });

        // Pagination for users
        $(users_table).parent().on('scroll', function () {
            if (!is_busy && SBUsers.search_query == '' && scrollPagination(this, true)) {
                is_busy = true;
                $(users_area).append('<div class="sb-loading-global sb-loading sb-loading-pagination"></div>');
                SBF.ajax({
                    function: 'get-users',
                    pagination: users_pagination,
                    sorting: SBUsers.sorting,
                    user_types: SBUsers.user_types,
                    search: SBUsers.search_query
                }, (response) => {
                    let code = '';
                    is_busy = false;
                    users_pagination++;
                    for (var i = 0; i < response.length; i++) {
                        let user = new SBUser(response[i]);
                        code += SBUsers.getRow(user);
                        users[user.id] = user;
                    }
                    $(users_table).find('tbody').append(code);
                    $(users_area).find(' > .sb-loading-pagination').remove();
                    scrollPagination(this);
                });
            }
        });

        // Csv file 
        $(users_area).on('click', '[data-value="csv"]', function () {
            SBUsers.csv();
        });

        // Delete users
        $(profile_edit_area).on('click', '.sb-delete', function () {
            dialog('This user will be deleted permanently including all linked data, conversations, and messages.', 'alert', function () {
                SBUsers.delete(activeUser().id);
            });
        });

        $(users_area).on('click', '[data-value="delete"]', function () {
            dialog('All selected users will be deleted permanently including all linked data, conversations, and messages.', 'alert', () => {
                let users_ids = [];
                $(users_table).find('tr').each(function () {
                    if ($(this).find('td input[type="checkbox"]').is(':checked')) {
                        users_ids.push($(this).attr('data-user-id'));
                    }
                });
                SBUsers.delete(users_ids);
                $(this).hide();
                $(users_table).find('th:first-child input').prop('checked', false);
            });
        });

        // Display user box
        $(users_table).on('click', 'td:not(:first-child)', function () {
            SBProfile.show($(this).parent().attr('data-user-id'));
        });

        // Display edit box
        $(profile_area).on('click', '.sb-top-bar .sb-edit', function () {
            SBProfile.showEdit(activeUser());
        });

        // Display new user box
        $(users_area).on('click', '.sb-new-user', function () {
            $(profile_edit_area).addClass('sb-user-new');
            $(profile_edit_area).find('.sb-top-bar .sb-profile span').html(sb_('Add new user'));
            $(profile_edit_area).find('.sb-top-bar .sb-save').html(`<i class="sb-icon-check"></i>${sb_('Add user')}`);
            $(profile_edit_area).find('#email input').prop('required', true);
            if (SB_ACTIVE_AGENT['user_type'] == 'admin') {
                $(profile_edit_area).find('#user_type').find('select').html('<option value="user">User</option><option value="agent">Agent</option><option value="admin">Admin</option>');
            }
            SBProfile.clear(profile_edit_area);
            SBProfile.boxClasses(profile_edit_area);
            $(profile_edit_area).sbShowLightbox();
        });

        // Add or update user
        $(profile_edit_area).on('click', '.sb-save', function () {
            let new_user = ($(profile_edit_area).hasClass('sb-user-new') ? true : false);
            let user_id = $(profile_edit_area).attr('data-user-id');

            if (!$(this).sbLoading()) {
                $(this).sbLoading(true);

                // Get settings
                let settings = SBProfile.getAll($(profile_edit_area).find('.sb-details'));
                let settings_extra = SBProfile.getAll($(profile_edit_area).find('.sb-additional-details'));

                // Errors check
                if (SBProfile.errors(profile_edit_area)) {
                    SBProfile.showErrorMessage(profile_edit_area, ['user', 'agent', 'admin'].includes($(profile_edit_area).find('#user_type :selected').val()) ? 'First name, last name, and a valid email are required.' : 'First name and last name are required.');
                    $(this).sbLoading(false);
                    return;
                }

                // Save the settings
                SBF.ajax({
                    function: (new_user ? 'add-user' : 'update-user'),
                    user_id: user_id,
                    settings: settings,
                    settings_extra: settings_extra
                }, (response) => {
                    if (SBF.errorValidation(response, 'duplicate-email')) {
                        SBProfile.showErrorMessage(profile_edit_area, 'This email is already in use.');
                        $(this).sbLoading(false);
                        return;
                    }
                    if (new_user) {
                        user_id = response;
                        activeUser(new SBUser({ 'id': user_id }));
                    }
                    activeUser().update(() => {
                        users[user_id] = activeUser();
                        if (new_user) {
                            SBProfile.clear(profile_edit_area);
                            SBUsers.update();
                        } else {
                            SBUsers.updateRow(activeUser());
                            if ($(conversations_area).sbActive()) {
                                SBConversations.updateUserDetails();
                            }
                            if (user_id == SB_ACTIVE_AGENT['id']) {
                                SBF.loginCookie(response[1]);
                                SB_ACTIVE_AGENT['full_name'] = activeUser().name;
                                SB_ACTIVE_AGENT['profile_image'] = activeUser().image;
                                $(header).find('.sb-account').setProfile();
                            }
                        }
                        $(this).sbLoading(false);
                        $(profile_edit_area).find('.sb-profile').setProfile();
                        showResponse(new_user ? 'New user added' : 'User updated');
                    });
                    SBF.event('SBUserUpdated', { new_user: new_user, user_id: user_id });
                });
            }
        });

        // Set and unset required visitor fields
        $(profile_edit_area).on('change', '#user_type', function () {
            SBProfile.boxClasses(profile_edit_area, $(this).find("option:selected").val());
        });

        // Open a user conversation
        $(profile_area).on('click', '.sb-user-conversations li', function () {
            SBConversations.open($(this).attr('data-conversation-id'), activeUser().id);
        });

        // Start a new user conversation
        $(profile_area).on('click', '.sb-start-conversation', function () {
            SBConversations.open(-1, activeUser().id);
            SBConversations.openConversation(-1, activeUser().id);
        });

        /*
        * ----------------------------------------------------------
        * # SETTINGS AREA
        * ----------------------------------------------------------
        */

        // Upload image
        $(settings_area).on('click', '[data-type="upload-image"] .image', function () {
            upload_target = this;
            $(admin).find('.sb-upload-form-admin .sb-upload-files').click();
        });

        $(settings_area).on('click', '[data-type="upload-image"] .image > i', function (e) {
            $(this).parent().removeAttr('data-value').css('background-image', '');
            e.preventDefault();
            return false;
        });

        // Repeater
        $(settings_area).on('click', '.sb-repeater-add', function () {
            SBSettings.repeaterAdd(this);
        });

        $(settings_area).on('click', '.repeater-item > i', function () {
            SBSettings.repeaterDelete(this);
        });

        // Color picker
        $(settings_area).find('.sb-type-color input').colorPicker({
            renderCallback: function (t, toggled) {
                $(t.context).closest('.input').find('input').css('background-color', t.text);
            }
        });

        $(settings_area).find('[data-save="color"]').focusout(function () {
            let t = $(this).closest('.input-color');
            let color = $(t).find('input').val();
            setTimeout(function () { $(t).find('input').val(''); $(t).find('.color-preview').css('background-color', color); }, 300);
            SBSettings.set($(this).attr('id'), color);
        });

        $(settings_area).on('click', '.sb-type-color .input i', function (e) {
            $(this).parent().find('input').removeAttr('style').val('');
        });

        // Color palette
        $(settings_area).on('click', '.sb-color-palette span', function () {
            let active = $(this).hasClass('sb-active');
            $(this).closest('.sb-repeater').find('.sb-active').sbActivate(false);
            $(this).sbActivate(!active);
        });

        $(settings_area).on('click', '.sb-color-palette ul li', function () {
            $(this).parent().parent().attr('data-value', $(this).data('value')).find('span').sbActivate(false);
        });

        // Save
        $(settings_area).on('click', '.sb-save-changes', function () {
            SBSettings.save(this);
        });

        // Miscellaneous
        $(settings_area).on('change', '#user-additional-fields [data-id="extra-field-slug"], #saved-replies [data-id="reply-name"], [data-id="rich-message-name"]', function () {
            $(this).val(SBF.stringToSlug($(this).val()));
        });

        $(settings_area).on('click', '#timetable-utc input', function () {
            if ($(this).val() == '') {
                $(this).val(Math.round(today.getTimezoneOffset() / 60));
            }
        });

        $(settings_area).find('#dialogflow-button .sb-btn').attr('href', 'https://board.support/synch/?service=dialogflow&plugin_url=' + SB_URL);

        $(settings_area).on('click', '#test-email-user .sb-btn, #test-email-agent .sb-btn', function () {
            let email = $(this).parent().find('input').val();
            if (email != '' && email.indexOf('@') > 0 && !$(this).sbLoading()) {
                $(this).sbLoading(true);
                SBF.ajax({
                    function: 'send-test-email',
                    to: email,
                    email_type: $(this).parent().parent().attr('id') == 'test-email-user' ? 'user' : 'agent'
                }, () => {
                    dialog('Email successfully sent. Check your emails!', 'info');
                    $(this).sbLoading(false);
                });
            }
        });

        $(settings_area).on('click', '.sb-timetable > div > div > div', function () {
            let timetable = $(this).closest('.sb-timetable');
            let active = $(this).sbActive();
            $(timetable).find('.sb-active').sbActivate(false);
            if (active) {
                $(this).sbActivate(false).find('.sb-custom-select').remove();
            } else {
                let select = $(timetable).find('> .sb-custom-select').html();
                $(timetable).find(' > div .sb-custom-select').remove();
                $(this).append(`<div class="sb-custom-select">${select}</div>`).sbActivate(true);
            }
        });

        $(settings_area).on('click', '.sb-timetable .sb-custom-select span', function () {
            let value = [$(this).html(), $(this).attr('data-value')];
            $(this).closest('.sb-timetable').find('> div > div > .sb-active').html(value[0]).attr('data-value', value[1]);
            $(this).parent().sbActivate(false);
        });

        $(settings_area).on('click', '#system-requirements a', function (e) {
            let box = $(admin).find('.sb-requirements-box');
            let code = '';
            SBF.ajax({
                function: 'system-requirements'
            }, (response) => {
                for (var key in response) {
                    code += `<div class="sb-input"><span>${sb_(SBF.slugToString(key))}</span><div${response[key] ? ' class="sb-green"' : ''}>${response[key] ? sb_('Success') : sb_('Error')}</div></div>`;
                }
                loading(false);
                $(box).find('.sb-main').html(code);
                $(box).sbShowLightbox();
            });
            $(box).sbActivate(false);
            loading(true);
            e.preventDefault();
            return false;
        });

        $(settings_area).on('click', '#delete-leads a', function (e) {
            if (!$(this).sbLoading()) {
                dialog('All leads, including all the linked conversations and messages, will be deleted permanently.', 'alert', () => {
                    $(this).sbLoading(true);
                    SBF.ajax({
                        function: 'delete-leads'
                    }, () => {
                        dialog('Leads and conversations successfully deleted.', 'info');
                        $(this).sbLoading(false);
                    });
                });
            }
            e.preventDefault();
            return false;
        });

        // Slack  
        $(settings_area).find('#slack-button .sb-btn').attr('href', 'https://board.support/synch/?service=slack&plugin_url=' + SB_URL);

        $(settings_area).on('click', '#slack-test .sb-btn', function (e) {
            if (!$(this).sbLoading()) {
                $(this).sbLoading(true);
                SBF.ajax({
                    function: 'send-slack-message',
                    user_id: -1,
                    full_name: SB_ACTIVE_AGENT['full_name'],
                    profile_image: SB_ACTIVE_AGENT['profile_image'],
                    message: 'Lorem ipsum dolor sit amete consectetur adipiscing elite incidido labore et dolore magna aliqua.',
                    attachments: [['Example link', SB_URL + '/media/user.svg'], ['Example link two', SB_URL + '/media/user.svg']]
                }, (response) => {
                    if (SBF.errorValidation(response)) {
                        if (response[1] == 'slack-not-active') {
                            dialog('Please first activate Slack, then save the settings and reload the admin area.', 'info');
                        } else {
                            dialog('Error. Response: ' + JSON.stringify(response), 'info');
                        }
                    } else {
                        dialog('Slack message successfully sent. Check Slack!', 'info');
                    }
                    $(this).sbLoading(false);
                });
            }
            e.preventDefault();
            return false;
        });

        $(settings_area).on('click', '#tab-slack', function () {
            let input = $(settings_area).find('#slack-agents .input');
            $(input).html('<div class="sb-loading"></div>');
            SBF.ajax({
                function: 'slack-users'
            }, (response) => {
                let code = '';
                if (SBF.errorValidation(response, 'slack-token-not-found')) {
                    code = `<p>${sb_('Synchronize Slack and save changes before linking agents.')}</p>`;
                } else {
                    let select = '<option value="-1"></option>';
                    for (var i = 0; i < response['agents'].length; i++) {
                        select += `<option value="${response['agents'][i]['id']}">${response['agents'][i]['name']}</option>`;
                    }
                    for (var i = 0; i < response['slack_users'].length; i++) {
                        code += `<div data-id="${response['slack_users'][i]['id']}"><label>${response['slack_users'][i]['name']}</label><select>${select}</select></div>`;
                    }
                }
                $(input).html(code);
                SBSettings.set('slack-agents', [response['saved'], 'double-select']);
            });
        });

        $(settings_area).on('click', '#slack-archive-channels .sb-btn', function (e) {
            if (!$(this).sbLoading()) {
                $(this).sbLoading(true);
                SBF.ajax({
                    function: 'archive-slack-channels'
                }, (response) => {
                    if (response === true) {
                        dialog('Slack channels archived successfully!', 'info');
                    }
                    $(this).sbLoading(false);
                });
            }
            e.preventDefault();
        });

        // WordPress
        if (SBApps.is('wordpress')) {
            $(settings_area).on('click', '#wp-synch .sb-btn', function (e) {
                if (!$(this).sbLoading()) {
                    $(this).sbLoading(true);
                    SBF.ajax({
                        function: 'wp-synch'
                    }, (response) => {
                        if (response === true) {
                            SBUsers.update();
                            dialog('WordPress users successfully imported.', 'info');
                        } else {
                            dialog('Error. Response: ' + JSON.stringify(response), 'info');
                        }
                        $(this).sbLoading(false);
                    });
                }
                e.preventDefault();
            });

            $('body').on('click', '#wp-admin-bar-logout', function () {
                SBF.logout(false);
            });
        }

        // Translations
        $(settings_area).on('click', '#tab-translations', function () {
            SBF.ajax({
                function: 'get-translations'
            }, (translations) => {
                let code = '';
                let code_nav = '';
                let areas;
                for (var key in translations) {
                    let front = translations[key]['front'];
                    let admin = translations[key]['admin'];
                    code += `<div data-code="${key}"><div data-area="front"><h2>${sb_('Front End')}<a class="sb-btn sb-icon sb-add-translation"><i class="sb-icon-plus"></i>${sb_('New translation')}</a></h2><div>`;
                    code_nav += `<li data-code="${key}"><img src="${SB_URL}/media/flags/${key}.png" />${translations[key]['name']}</li>`;
                    for (var key_front in front) {
                        code += `<div class="sb-input-setting sb-type-text"><label>${key_front}</label><div><input type="text" value="${front[key_front]}"></div></div>`;
                    }
                    code += `</div></div><div data-area="admin"><h2>${sb_('Admin')}</h2><div>`;
                    for (var key_admin in admin) {
                        code += `<div class="sb-input-setting sb-type-text"><label>${key_admin}</label><div><input type="text" value="${admin[key_admin]}"></div></div>`;
                    }
                    code += '</div></div></div>';
                }
                $(settings_area).find('.sb-translations').html(`<div class="sb-nav"><div></div><ul>${code_nav}</ul></div><div class="sb-content">${code}</div>`);
                areas = $(settings_area).find('.sb-translations > div > div:first-child');
                $(areas).sbActivate();
                loading(false);
            });
            loading();
        });

        $(settings_area).on('click', '.sb-add-translation', function () {
            $(this).closest('[data-area="front"]').find(' > div').prepend(`<div class="sb-input-setting sb-type-text sb-new-translation"><input type="text" placeholder="${sb_('Insert original text...')}"><input type="text" placeholder="${sb_('Insert translation...')}"></div></div>`);
        });

        $(settings_area).on('input', '.sb-search-translation input', function () {
            let search = $(this).val().toLowerCase();
            SBF.search(search, () => {
                if (search.length > 1) {
                    $(settings_area).find('.sb-translations .sb-content > .sb-active label').each(function () {
                        let value = $(this).html().toLowerCase();
                        if (value.indexOf(search) > -1 && value != temp) {
                            let scroll_area = $(settings_area).find('.sb-scroll-area');
                            $(scroll_area)[0].scrollTop = 0;
                            $(scroll_area)[0].scrollTop = $(this).position().top - 80;
                            temp = value;
                            return false;
                        }
                    });
                }
            });
        });

        $(settings_area).on('click', '.sb-translations .sb-nav li', function () {
            let code = $(this).data('code');
            if (!SBSettings.translations_to_update.includes(code)) {
                SBSettings.translations_to_update.push(code);
            }
        });

        // Articles
        $(settings_area).on('click', '#tab-articles', function () {
            SBF.ajax({
                function: 'get-articles',
                full: true
            }, (response) => {
                if (response !== false && Array.isArray(response)) {
                    let code = '';
                    let count = response.length;
                    if (count) {
                        articles = response;
                        for (var i = 0; i < count; i++) {
                            code += `<li data-article-id="${articles[i]['id']}">${articles[i]['title']}<i class="sb-icon-delete"></i></li>`;
                        }
                    } else {
                        code = `<li class="sb-no-articles">${sb_('No articles found.')}</li>`;
                    }
                    $(settings_area).find('.sb-articles-area .sb-nav ul').html(code);
                } else {
                    SBF.error('Articles response not of type array or false', 'get-articles');
                }
                loading(false);
            });
            loading();
        });

        $(settings_area).on('click', '.sb-add-article', function () {
            let area = $(settings_area).find('.sb-articles-area');
            let nav = $(area).find('.sb-nav > ul');
            let id = SBF.random();
            SBSettings.articles('save');
            articles.push({ id: id, title: '', content: '', link: '' });
            $(nav).find('.sb-active').sbActivate(false);
            $(nav).find('.sb-no-articles').remove();
            $(nav).append(`<li class="sb-active" data-article-id="${id}">${sb_('Article')} ${$(nav).find('li').length + 1}<i class="sb-icon-delete"></i></li>`);
            $(area).find('input, textarea').val('');
            $(area).find('.sb-content').attr('data-article-id', id);
        });

        $(settings_area).on('click', '.sb-articles-area .sb-nav i', function () {
            dialog('The article will be deleted permanently.', 'alert', () => {
                let nav = $(this).closest('.sb-nav').find(' > ul');
                articles.splice($(this).parent().index(), 1);
                if ($(nav).find('li').length > 1) {
                    $(this).parent().remove();
                } else {
                    $(nav).html(`<li class="sb-no-articles">${sb_('No articles found.')}</li>`);
                }
            })
        });

        $(settings_area).on('click', '.sb-articles-area .sb-nav li', function () {
            let area = $(settings_area).find('.sb-articles-area');
            let id = $(this).attr('data-article-id');
            let contents = [-1, '', '', ''];
            SBSettings.articles('save');
            for (var i = 0; i < articles.length; i++) {
                if (articles[i]['id'] == id) {
                    contents = [id, articles[i]['title'], articles[i]['content'], articles[i]['link']];
                    $(this).siblings().sbActivate(false);
                    $(this).sbActivate();
                    break;
                }
            }
            $(area).find('.sb-content').attr('data-article-id', contents[0]);
            $(area).find('.sb-article-title input').val(contents[1]);
            $(area).find('.sb-article-content textarea').val(contents[2]);
            $(area).find('.sb-article-link input').val(contents[3]);
            $(area).find('#sb-article-id').html(`ID <span>${contents[0]}</span>`);
        });

        /*
        * ----------------------------------------------------------
        * # WOOCOMMERCE
        * ----------------------------------------------------------
        */

        // Panel reload button
        $(conversations_area).on('click', '.sb-panel-woocommerce > i', function () {
            SBApps.woocommerce.conversationPanel();
        });

        // Get order details
        $(conversations_area).on('click', '.sb-woocommerce-orders > div > span', function (e) {
            let parent = $(this).parent();
            if (!$(e.target).is('span')) return;
            if (!$(parent).sbActive()) {
                SBApps.woocommerce.conversationPanelOrder($(parent).attr('data-id'));
            }
        });

        // Products popup 
        $(conversations_area).on('click', '.sb-btn-woocommerce', function () {
            if ($(woocommerce_products_box_ul).sbLoading() || (activeUser() != false && activeUser().language != SBApps.woocommerce.popupLanguage)) {
                SBApps.woocommerce.popupPopulate();
            }
            $(woocommerce_products_box).sbTogglePopup(this);
        });

        // Products popup pagination
        $(woocommerce_products_box).find('.sb-woocommerce-products-list').on('scroll', function () {
            if (scrollPagination(this, true)) {
                SBApps.woocommerce.popupPagination(this);
            }
        });

        // Products popup filter
        $(woocommerce_products_box).on('click', '.sb-select li', function () {
            SBApps.woocommerce.popupFilter(this);
        });

        // Products popup search
        $(woocommerce_products_box).on('input', '.sb-search-btn input', function () {
            SBApps.woocommerce.popupSearch(this);
        });

        $(woocommerce_products_box).on('click', '.sb-search-btn i', function () {
            SBF.searchClear(this, () => { SBApps.woocommerce.popupSearch($(this).next()) });
        });

        // Cart popup insert product
        $(woocommerce_products_box).on('click', '.sb-woocommerce-products-list li', function () {
            let action = $(woocommerce_products_box).attr('data-action');
            let id = $(this).data('id');
            if (SBF.null(action)) {
                SBChat.insertText(`{product_card id="${id}"}`);
            } else {
                $(woocommerce_products_box_ul).sbLoading(true);
                $(conversations_area).find('.sb-add-cart-btn').sbLoading(true);
                SBChat.sendMessage(-1, '', [], (response) => {
                    if (response) {
                        SBApps.woocommerce.conversationPanelUpdate(id);
                        $(admin).sbHideLightbox();
                    }
                }, { 'event': 'woocommerce-update-cart', 'action': 'cart-add', 'id': id });
            }
            SBF.deactivateAll();
            $(admin).removeClass('sb-popup-active');
        });

        // Cart add product
        $(conversations_area).on('click', '.sb-panel-woocommerce .sb-add-cart-btn', function () {
            if ($(this).sbLoading()) return;
            if (SBChat.user_online) {
                SBApps.woocommerce.popupPopulate();
                $(woocommerce_products_box).sbShowLightbox(true, 'cart-add');
            } else {
                dialog('The user is offline. Only the carts of online users can be updated.', 'info');
            }
        });

        // Cart remove product
        $(conversations_area).on('click', '.sb-panel-woocommerce .sb-list-items > a > i', function (e) {
            let id = $(this).parent().attr('data-id');
            SBChat.sendMessage(-1, '', [], () => {
                SBApps.woocommerce.conversationPanelUpdate(id, 'removed');
            }, { 'event': 'woocommerce-update-cart', 'action': 'cart-remove', 'id': id });
            $(this).sbLoading(true);
            e.preventDefault();
            return false;
        });

        // Settings
        $(settings_area).on('click', '#wc-dialogflow-synch a, #wc-dialogflow-create-intents a', function (e) {
            if (SBApps.is('dialogflow')) {
                if (!$(this).sbLoading()) {
                    let id = $(this).parent().attr('id');
                    $(this).sbLoading(true);
                    SBF.ajax({
                        function: 'woocommerce-dialogflow-' + (id == 'wc-dialogflow-synch' ? 'entities' : 'intents')
                    }, (response) => {
                        $(this).sbLoading(false);
                        dialog(response ? 'Dialogflow synchronization completed.' : 'Error. Something went wrong.', 'info');
                    });
                }
            } else {
                dialog('This feature requires the Dialogflow App. Get it from the apps area.', 'info');
            }
            e.preventDefault();
            return false;
        });

        /*
        * ----------------------------------------------------------
        * # ULTIMATE MEMBERSHIP PRO
        * ----------------------------------------------------------
        */

        // Panel reload button
        $(conversations_area).on('click', '.sb-panel-ump > i', function () {
            SBApps.ump.conversationPanel();
        });

        /*
        * ----------------------------------------------------------
        * # COMPONENTS
        * ----------------------------------------------------------
        */

        // Lightbox
        $(admin).on('click', '.sb-lightbox .sb-top-bar .sb-close', function () {
            $(admin).sbHideLightbox();
        });

        $(admin).on('click', '.sb-lightbox .sb-info', function () {
            $(this).sbActivate(false);
        });

        // Alert and information box
        $(admin).on('click', '.sb-dialog-box a', function () {
            let lightbox = $(this).closest('.sb-lightbox');
            if ($(this).hasClass('sb-confirm')) {
                alertOnConfirmation();
            }
            if ($(admin).find('.sb-lightbox.sb-active').length == 1) {
                $(overlay).sbActivate(false);
            }
            $(lightbox).sbActivate(false);
        });

        // Miscellaneous
        $(admin).on('click', '.sb-menu-wide li, .sb-nav li', function () {
            $(this).siblings().sbActivate(false);
            $(this).sbActivate();
        });

        $(admin).on('click', '.sb-nav li', function () {
            let area = $(this).closest('.sb-tab');
            let tab = $(area).find(' > .sb-content > div').sbActivate(false).eq($(this).index());
            $(tab).sbActivate();
            $(tab).find('textarea').each(function () {
                $(this).autoExpandTextarea();
                $(this).manualExpandTextarea();
            });
            $(area).find('.sb-scroll-area').scrollTop(0);
        });

        $(admin).find('[data-sb-tooltip]').each(function () {
            $(this).miniTip({
                content: $(this).attr('data-sb-tooltip'),
                anchor: 's',
                delay: 500
            });
        });

        $(admin).on('click', '[data-button="toggle"]', function () {
            $(admin).find('.' + $(this).data('show')).addClass('sb-show-animation').show();
            $(admin).find('.' + $(this).data('hide')).hide();
        });

        $(admin).on('click', '.sb-info-card', function () {
            $(this).sbActivate(false);
        });

        $(admin).on('change', '.sb-upload-form-admin .sb-upload-files', function (data) {
            $(this).sbUploadFiles(function (response) {
                response = JSON.parse(response);
                if (response[0] == 'success') {
                    if ($(upload_target).closest('[data-type]').data('type') == 'upload-image') {
                        $(upload_target).attr('data-value', response[1]).css('background-image', `url("${response[1]}")`);
                    }
                } else {
                    console.log(response[1]);
                }
            });
        });

        $(admin).on('click', '.sb-accordion > div > span', function (e) {
            let parent = $(this).parent();
            let active = $(parent).sbActive();
            if (!$(e.target).is('span')) return;
            $(parent).siblings().sbActivate(false);
            $(parent).sbActivate(!active);
        });
    });

}(jQuery));

// tinyColorPicker v1.1.1 2016-08-30 

!function (a, b) { "object" == typeof exports ? module.exports = b(a) : "function" == typeof define && define.amd ? define("colors", [], function () { return b(a) }) : a.Colors = b(a) }(this, function (a, b) { "use strict"; function c(a, c, d, f, g) { if ("string" == typeof c) { var c = v.txt2color(c); d = c.type, p[d] = c[d], g = g !== b ? g : c.alpha } else if (c) for (var h in c) a[d][h] = k(c[h] / l[d][h][1], 0, 1); return g !== b && (a.alpha = k(+g, 0, 1)), e(d, f ? a : b) } function d(a, b, c) { var d = o.options.grey, e = {}; return e.RGB = { r: a.r, g: a.g, b: a.b }, e.rgb = { r: b.r, g: b.g, b: b.b }, e.alpha = c, e.equivalentGrey = n(d.r * a.r + d.g * a.g + d.b * a.b), e.rgbaMixBlack = i(b, { r: 0, g: 0, b: 0 }, c, 1), e.rgbaMixWhite = i(b, { r: 1, g: 1, b: 1 }, c, 1), e.rgbaMixBlack.luminance = h(e.rgbaMixBlack, !0), e.rgbaMixWhite.luminance = h(e.rgbaMixWhite, !0), o.options.customBG && (e.rgbaMixCustom = i(b, o.options.customBG, c, 1), e.rgbaMixCustom.luminance = h(e.rgbaMixCustom, !0), o.options.customBG.luminance = h(o.options.customBG, !0)), e } function e(a, b) { var c, e, k, q = b || p, r = v, s = o.options, t = l, u = q.RND, w = "", x = "", y = { hsl: "hsv", rgb: a }, z = u.rgb; if ("alpha" !== a) { for (var A in t) if (!t[A][A]) { a !== A && (x = y[A] || "rgb", q[A] = r[x + "2" + A](q[x])), u[A] || (u[A] = {}), c = q[A]; for (w in c) u[A][w] = n(c[w] * t[A][w][1]) } z = u.rgb, q.HEX = r.RGB2HEX(z), q.equivalentGrey = s.grey.r * q.rgb.r + s.grey.g * q.rgb.g + s.grey.b * q.rgb.b, q.webSave = e = f(z, 51), q.webSmart = k = f(z, 17), q.saveColor = z.r === e.r && z.g === e.g && z.b === e.b ? "web save" : z.r === k.r && z.g === k.g && z.b === k.b ? "web smart" : "", q.hueRGB = v.hue2RGB(q.hsv.h), b && (q.background = d(z, q.rgb, q.alpha)) } var B, C, D, E = q.rgb, F = q.alpha, G = "luminance", H = q.background; return B = i(E, { r: 0, g: 0, b: 0 }, F, 1), B[G] = h(B, !0), q.rgbaMixBlack = B, C = i(E, { r: 1, g: 1, b: 1 }, F, 1), C[G] = h(C, !0), q.rgbaMixWhite = C, s.customBG && (D = i(E, H.rgbaMixCustom, F, 1), D[G] = h(D, !0), D.WCAG2Ratio = j(D[G], H.rgbaMixCustom[G]), q.rgbaMixBGMixCustom = D, D.luminanceDelta = m.abs(D[G] - H.rgbaMixCustom[G]), D.hueDelta = g(H.rgbaMixCustom, D, !0)), q.RGBLuminance = h(z), q.HUELuminance = h(q.hueRGB), s.convertCallback && s.convertCallback(q, a), q } function f(a, b) { var c = {}, d = 0, e = b / 2; for (var f in a) d = a[f] % b, c[f] = a[f] + (d > e ? b - d : -d); return c } function g(a, b, c) { return (m.max(a.r - b.r, b.r - a.r) + m.max(a.g - b.g, b.g - a.g) + m.max(a.b - b.b, b.b - a.b)) * (c ? 255 : 1) / 765 } function h(a, b) { for (var c = b ? 1 : 255, d = [a.r / c, a.g / c, a.b / c], e = o.options.luminance, f = d.length; f--;)d[f] = d[f] <= .03928 ? d[f] / 12.92 : m.pow((d[f] + .055) / 1.055, 2.4); return e.r * d[0] + e.g * d[1] + e.b * d[2] } function i(a, c, d, e) { var f = {}, g = d !== b ? d : 1, h = e !== b ? e : 1, i = g + h * (1 - g); for (var j in a) f[j] = (a[j] * g + c[j] * h * (1 - g)) / i; return f.a = i, f } function j(a, b) { var c = 1; return c = a >= b ? (a + .05) / (b + .05) : (b + .05) / (a + .05), n(100 * c) / 100 } function k(a, b, c) { return a > c ? c : b > a ? b : a } var l = { rgb: { r: [0, 255], g: [0, 255], b: [0, 255] }, hsv: { h: [0, 360], s: [0, 100], v: [0, 100] }, hsl: { h: [0, 360], s: [0, 100], l: [0, 100] }, alpha: { alpha: [0, 1] }, HEX: { HEX: [0, 16777215] } }, m = a.Math, n = m.round, o = {}, p = {}, q = { r: .298954, g: .586434, b: .114612 }, r = { r: .2126, g: .7152, b: .0722 }, s = function (a) { this.colors = { RND: {} }, this.options = { color: "rgba(0,0,0,0)", grey: q, luminance: r, valueRanges: l }, t(this, a || {}) }, t = function (a, d) { var e, f = a.options; u(a); for (var g in d) d[g] !== b && (f[g] = d[g]); e = f.customBG, f.customBG = "string" == typeof e ? v.txt2color(e).rgb : e, p = c(a.colors, f.color, b, !0) }, u = function (a) { o !== a && (o = a, p = a.colors) }; s.prototype.setColor = function (a, d, f) { return u(this), a ? c(this.colors, a, d, b, f) : (f !== b && (this.colors.alpha = k(f, 0, 1)), e(d)) }, s.prototype.setCustomBackground = function (a) { return u(this), this.options.customBG = "string" == typeof a ? v.txt2color(a).rgb : a, c(this.colors, b, "rgb") }, s.prototype.saveAsBackground = function () { return u(this), c(this.colors, b, "rgb", !0) }, s.prototype.toString = function (a, b) { return v.color2text((a || "rgb").toLowerCase(), this.colors, b) }; var v = { txt2color: function (a) { var b = {}, c = a.replace(/(?:#|\)|%)/g, "").split("("), d = (c[1] || "").split(/,\s*/), e = c[1] ? c[0].substr(0, 3) : "rgb", f = ""; if (b.type = e, b[e] = {}, c[1]) for (var g = 3; g--;)f = e[g] || e.charAt(g), b[e][f] = +d[g] / l[e][f][1]; else b.rgb = v.HEX2rgb(c[0]); return b.alpha = d[3] ? +d[3] : 1, b }, color2text: function (a, b, c) { var d = c !== !1 && n(100 * b.alpha) / 100, e = "number" == typeof d && c !== !1 && (c || 1 !== d), f = b.RND.rgb, g = b.RND.hsl, h = "hex" === a && e, i = "hex" === a && !h, j = "rgb" === a || h, k = j ? f.r + ", " + f.g + ", " + f.b : i ? "#" + b.HEX : g.h + ", " + g.s + "%, " + g.l + "%"; return i ? k : (h ? "rgb" : a) + (e ? "a" : "") + "(" + k + (e ? ", " + d : "") + ")" }, RGB2HEX: function (a) { return ((a.r < 16 ? "0" : "") + a.r.toString(16) + (a.g < 16 ? "0" : "") + a.g.toString(16) + (a.b < 16 ? "0" : "") + a.b.toString(16)).toUpperCase() }, HEX2rgb: function (a) { return a = a.split(""), { r: +("0x" + a[0] + a[a[3] ? 1 : 0]) / 255, g: +("0x" + a[a[3] ? 2 : 1] + (a[3] || a[1])) / 255, b: +("0x" + (a[4] || a[2]) + (a[5] || a[2])) / 255 } }, hue2RGB: function (a) { var b = 6 * a, c = ~~b % 6, d = 6 === b ? 0 : b - c; return { r: n(255 * [1, 1 - d, 0, 0, d, 1][c]), g: n(255 * [d, 1, 1, 1 - d, 0, 0][c]), b: n(255 * [0, 0, d, 1, 1, 1 - d][c]) } }, rgb2hsv: function (a) { var b, c, d, e = a.r, f = a.g, g = a.b, h = 0; return g > f && (f = g + (g = f, 0), h = -1), c = g, f > e && (e = f + (f = e, 0), h = -2 / 6 - h, c = m.min(f, g)), b = e - c, d = e ? b / e : 0, { h: 1e-15 > d ? p && p.hsl && p.hsl.h || 0 : b ? m.abs(h + (f - g) / (6 * b)) : 0, s: e ? b / e : p && p.hsv && p.hsv.s || 0, v: e } }, hsv2rgb: function (a) { var b = 6 * a.h, c = a.s, d = a.v, e = ~~b, f = b - e, g = d * (1 - c), h = d * (1 - f * c), i = d * (1 - (1 - f) * c), j = e % 6; return { r: [d, h, g, g, i, d][j], g: [i, d, d, h, g, g][j], b: [g, g, i, d, d, h][j] } }, hsv2hsl: function (a) { var b = (2 - a.s) * a.v, c = a.s * a.v; return c = a.s ? 1 > b ? b ? c / b : 0 : c / (2 - b) : 0, { h: a.h, s: a.v || c ? c : p && p.hsl && p.hsl.s || 0, l: b / 2 } }, rgb2hsl: function (a, b) { var c = v.rgb2hsv(a); return v.hsv2hsl(b ? c : p.hsv = c) }, hsl2rgb: function (a) { var b = 6 * a.h, c = a.s, d = a.l, e = .5 > d ? d * (1 + c) : d + c - c * d, f = d + d - e, g = e ? (e - f) / e : 0, h = ~~b, i = b - h, j = e * g * i, k = f + j, l = e - j, m = h % 6; return { r: [e, l, f, f, k, e][m], g: [k, e, e, l, f, f][m], b: [f, f, k, e, e, l][m] } } }; return s }), function (a, b) { "object" == typeof exports ? module.exports = b(a, require("jquery"), require("colors")) : "function" == typeof define && define.amd ? define(["jquery", "colors"], function (c, d) { return b(a, c, d) }) : b(a, a.jQuery, a.Colors) }(this, function (a, b, c, d) { "use strict"; function e(a) { return a.value || a.getAttribute("value") || b(a).css("background-color") || "#FFF" } function f(a) { return a = a.originalEvent && a.originalEvent.touches ? a.originalEvent.touches[0] : a, a.originalEvent ? a.originalEvent : a } function g(a) { return b(a.find(r.doRender)[0] || a[0]) } function h(c) { var d = b(this), f = d.offset(), h = b(a), k = r.gap; c ? (s = g(d), s._colorMode = s.data("colorMode"), p.$trigger = d, (t || i()).css(r.positionCallback.call(p, d) || { left: (t._left = f.left) - ((t._left += t._width - (h.scrollLeft() + h.width())) + k > 0 ? t._left + k : 0), top: (t._top = f.top + d.outerHeight()) - ((t._top += t._height - (h.scrollTop() + h.height())) + k > 0 ? t._top + k : 0) }).show(r.animationSpeed, function () { c !== !0 && (y.toggle(!!r.opacity)._width = y.width(), v._width = v.width(), v._height = v.height(), u._height = u.height(), q.setColor(e(s[0])), n(!0)) }).off(".tcp").on(D, ".cp-xy-slider,.cp-z-slider,.cp-alpha", j)) : p.$trigger && b(t).hide(r.animationSpeed, function () { n(!1), p.$trigger = null }).off(".tcp") } function i() { return b("head")[r.cssPrepend ? "prepend" : "append"]('<style type="text/css" id="tinyColorPickerStyles">' + (r.css || I) + (r.cssAddon || "") + "</style>"), b(H).css({ margin: r.margin }).appendTo("body").show(0, function () { p.$UI = t = b(this), F = r.GPU && t.css("perspective") !== d, u = b(".cp-z-slider", this), v = b(".cp-xy-slider", this), w = b(".cp-xy-cursor", this), x = b(".cp-z-cursor", this), y = b(".cp-alpha", this), z = b(".cp-alpha-cursor", this), r.buildCallback.call(p, t), t.prepend("<div>").children().eq(0).css("width", t.children().eq(0).width()), t._width = this.offsetWidth, t._height = this.offsetHeight }).hide() } function j(a) { var c = this.className.replace(/cp-(.*?)(?:\s*|$)/, "$1").replace("-", "_"); (a.button || a.which) > 1 || (a.preventDefault && a.preventDefault(), a.returnValue = !1, s._offset = b(this).offset(), (c = "xy_slider" === c ? k : "z_slider" === c ? l : m)(a), n(), A.on(E, function () { A.off(".tcp") }).on(C, function (a) { c(a), n() })) } function k(a) { var b = f(a), c = b.pageX - s._offset.left, d = b.pageY - s._offset.top; q.setColor({ s: c / v._width * 100, v: 100 - d / v._height * 100 }, "hsv") } function l(a) { var b = f(a).pageY - s._offset.top; q.setColor({ h: 360 - b / u._height * 360 }, "hsv") } function m(a) { var b = f(a).pageX - s._offset.left, c = b / y._width; q.setColor({}, "rgb", c) } function n(a) { var b = q.colors, c = b.hueRGB, e = (b.RND.rgb, b.RND.hsl, r.dark), f = r.light, g = q.toString(s._colorMode, r.forceAlpha), h = b.HUELuminance > .22 ? e : f, i = b.rgbaMixBlack.luminance > .22 ? e : f, j = (1 - b.hsv.h) * u._height, k = b.hsv.s * v._width, l = (1 - b.hsv.v) * v._height, m = b.alpha * y._width, n = F ? "translate3d" : "", p = s[0].value, t = s[0].hasAttribute("value") && "" === p && a !== d; v._css = { backgroundColor: "rgb(" + c.r + "," + c.g + "," + c.b + ")" }, w._css = { transform: n + "(" + k + "px, " + l + "px, 0)", left: F ? "" : k, top: F ? "" : l, borderColor: b.RGBLuminance > .22 ? e : f }, x._css = { transform: n + "(0, " + j + "px, 0)", top: F ? "" : j, borderColor: "transparent " + h }, y._css = { backgroundColor: "#" + b.HEX }, z._css = { transform: n + "(" + m + "px, 0, 0)", left: F ? "" : m, borderColor: i + " transparent" }, s._css = { backgroundColor: t ? "" : g, color: t ? "" : b.rgbaMixBGMixCustom.luminance > .22 ? e : f }, s.text = t ? "" : p !== g ? g : "", a !== d ? o(a) : G(o) } function o(a) { v.css(v._css), w.css(w._css), x.css(x._css), y.css(y._css), z.css(z._css), r.doRender && s.css(s._css), s.text && s.val(s.text), r.renderCallback.call(p, s, "boolean" == typeof a ? a : d) } var p, q, r, s, t, u, v, w, x, y, z, A = b(document), B = b(), C = "touchmove.tcp mousemove.tcp pointermove.tcp", D = "touchstart.tcp mousedown.tcp pointerdown.tcp", E = "touchend.tcp mouseup.tcp pointerup.tcp", F = !1, G = a.requestAnimationFrame || a.webkitRequestAnimationFrame || function (a) { a() }, H = '<div class="cp-color-picker"><div class="cp-z-slider"><div class="cp-z-cursor"></div></div><div class="cp-xy-slider"><div class="cp-white"></div><div class="cp-xy-cursor"></div></div><div class="cp-alpha"><div class="cp-alpha-cursor"></div></div></div>', I = ".cp-color-picker{position:absolute;overflow:hidden;padding:6px 6px 0;background-color:#444;color:#bbb;font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:400;cursor:default;border-radius:5px}.cp-color-picker>div{position:relative;overflow:hidden}.cp-xy-slider{float:left;height:128px;width:128px;margin-bottom:6px;background:linear-gradient(to right,#FFF,rgba(255,255,255,0))}.cp-white{height:100%;width:100%;background:linear-gradient(rgba(0,0,0,0),#000)}.cp-xy-cursor{position:absolute;top:0;width:10px;height:10px;margin:-5px;border:1px solid #fff;border-radius:100%;box-sizing:border-box}.cp-z-slider{float:right;margin-left:6px;height:128px;width:20px;background:linear-gradient(red 0,#f0f 17%,#00f 33%,#0ff 50%,#0f0 67%,#ff0 83%,red 100%)}.cp-z-cursor{position:absolute;margin-top:-4px;width:100%;border:4px solid #fff;border-color:transparent #fff;box-sizing:border-box}.cp-alpha{clear:both;width:100%;height:16px;margin:6px 0;background:linear-gradient(to right,#444,rgba(0,0,0,0))}.cp-alpha-cursor{position:absolute;margin-left:-4px;height:100%;border:4px solid #fff;border-color:#fff transparent;box-sizing:border-box}", J = function (a) { q = this.color = new c(a), r = q.options, p = this }; J.prototype = { render: n, toggle: h }, b.fn.colorPicker = function (c) { var d = this, f = function () { }; return c = b.extend({ animationSpeed: 150, GPU: !0, doRender: !0, customBG: "#FFF", opacity: !0, renderCallback: f, buildCallback: f, positionCallback: f, body: document.body, scrollResize: !0, gap: 4, dark: "#222", light: "#DDD" }, c), !p && c.scrollResize && b(a).on("resize.tcp scroll.tcp", function () { p.$trigger && p.toggle.call(p.$trigger[0], !0) }), B = B.add(this), this.colorPicker = p || new J(c), this.options = c, b(c.body).off(".tcp").on(D, function (a) { -1 === B.add(t).add(b(t).find(a.target)).index(a.target) && h() }), this.on("focusin.tcp click.tcp", function (a) { p.color.options = b.extend(p.color.options, r = d.options), h.call(this, a) }).on("change.tcp", function () { q.setColor(this.value || "#FFF"), d.colorPicker.render(!0) }).each(function () { var a = e(this), d = a.split("("), f = g(b(this)); f.data("colorMode", d[1] ? d[0].substr(0, 3) : "HEX").attr("readonly", r.preventFocus), c.doRender && f.css({ "background-color": a, color: function () { return q.setColor(a).rgbaMixBGMixCustom.luminance > .22 ? c.dark : c.light } }) }) }, b.fn.colorPicker.destroy = function () { b("*").off(".tcp"), p.toggle(!1), B = b() } });