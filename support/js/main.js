
/*
 * ==========================================================
 * MAIN SCRIPT
 * ==========================================================
 *
 * Main Javascript file. This file is shared by frond-end and back-end. © 2020 board.support. All rights reserved.
 * 
 */

'use strict';

(function ($) {

    var main;
    var global;
    var upload_target;
    var admin;
    var tickets = false;
    var timeout = false;
    var previous_search;
    var sb_current_user = false;
    var chat;
    var chat_editor;
    var chat_textarea;
    var chat_header;
    var chat_status;
    var chat_emoji;
    var chat_scroll_area;
    var document_title = document.title;
    var CHAT_SETTINGS;
    var mobile = $(window).width() < 426;
    var today = new Date();
    var bot_id;
    var force_action = '';
    var dialogflow_human_takeover;
    var dialogflow_unknow_notify;
    var agents_online = false;
    var ND = 'undefined';
    var cookies_supported = true;
    var login_data = '';

    /*
    * ----------------------------------------------------------
    * EXTERNAL PLUGINS
    * ----------------------------------------------------------
    */

    // Auto Expand Scroll Area | Schiocco
    $.fn.extend({ manualExpandTextarea: function () { var t = this[0]; t.style.height = "auto", t.style.maxHeight = "25px"; window.getComputedStyle(t); t.style.height = t.scrollHeight + "px", t.style.maxHeight = "", $(t).trigger("textareaChanged") }, autoExpandTextarea: function () { var t = this[0]; t.addEventListener("input", function (e) { $(t).manualExpandTextarea() }, !1) } });

    // autolink-js
    (function () { var t = [].slice; String.prototype.autoLink = function () { var n, a, r, i, c, e, l; return e = /(^|[\s\n]|<[A-Za-z]*\/?>)((?:https?|ftp):\/\/[\-A-Z0-9+\u0026\u2019@#\/%?=()~_|!:,.;]*[\-A-Z0-9+\u0026@#\/%=~()_|])/gi, 0 < (c = 1 <= arguments.length ? t.call(arguments, 0) : []).length ? (i = c[0], n = i.callback, r = function () { var t; for (a in t = [], i) l = i[a], "callback" !== a && t.push(" " + a + "='" + l + "'"); return t }().join(""), this.replace(e, function (t, a, i) { return "" + a + (("function" == typeof n ? n(i) : void 0) || "<a href='" + i + "'" + r + ">" + i + "</a>") })) : this.replace(e, "$1<a href='$2'>$2</a>") } }).call(this);

    /*
    * ----------------------------------------------------------
    * # FUNCTIONS
    * ----------------------------------------------------------
    */

    var SBF = {

        // Main Ajax function
        ajax: function (data, onSuccess = false) {
            data['login-cookie'] = this.null(this.cookie('sb-login')) ? login_data : this.cookie('sb-login');
            if (!('user_id' in data) && activeUser()) {
                data['user_id'] = activeUser().id;
            }
            if (typeof SB_LANG != ND) {
                data['language'] = SB_LANG;
            }
            $.ajax({
                method: 'POST',
                url: SB_AJAX_URL,
                data: data
            }).done((response) => {
                let result;
                if (Array.isArray(response)) {
                    result = response;
                } else {
                    try {
                        result = JSON.parse(response);
                    } catch (e) {
                        console.log(response);
                        SBF.error('Json parse error', `SBF.ajax.${data['function']}`);
                    }
                }
                if (result[0] == 'success') {
                    if (onSuccess != false) onSuccess(result[1]);
                } else if (SBF.errorValidation(result)) {
                    if (onSuccess != false) onSuccess(result);
                } else {
                    if (result[1] == 'security-error') {
                        setTimeout(function () {
                            SBF.reset();
                        }, 1000);
                    }
                    SBF.error(result[1] + (SBF.null(result[2]) ? '' : '\nFunction name: ' + result[2]) + (SBF.null(result[3]) ? '' : '\nError message: ' + (typeof result[3] == 'string' ? result[3] : ('error' in result[3] && 'message' in result[3]['error'] ? `${result[3]['error']['message']} in function '${result[3]['error']['function']}'` : result[3]))), `SBF.ajax.${data['function']}`);
                }
            });
        },

        // Cors function
        cors: function (method = 'GET', url, onSuccess) {
            var xhr = new XMLHttpRequest();
            if ('withCredentials' in xhr) {
                xhr.open(method, url, true);
            } else if (typeof XDomainRequest != ND) {
                xhr = new XDomainRequest();
                xhr.open(method, url);
            } else {
                return false;
            }
            xhr.onload = function () {
                onSuccess(xhr.responseText);
            };
            xhr.onerror = function () {
                return false;
            };
            xhr.send();
        },

        // Uploads
        upload: function (form, onSuccess) {
            jQuery.ajax({
                url: SB_URL + '/include/upload.php',
                cache: false,
                contentType: false,
                processData: false,
                data: form,
                type: 'POST',
                success: function (response) {
                    onSuccess(response);
                }
            });
        },

        // Check if a variable is null or empty
        null: function (obj) { if (typeof (obj) !== 'undefined' && obj !== null && obj !== 'null' && obj !== false && (obj.length > 0 || typeof (obj) == 'number' || typeof (obj.length) == 'undefined') && obj !== 'undefined') return false; else return true; },

        // Deactivate and hide the elements
        deactivateAll: function () {
            $(global).find('.sb-popup, .sb-tooltip, .sb-list .sb-menu, .sb-select ul').sbActivate(false);
        },

        // Deselect the content of the target
        deselectAll: function () {
            if (window.getSelection) { window.getSelection().removeAllRanges(); }
            else if (document.selection) { document.selection.empty(); }
        },

        // Get a URL parameter
        getURL: function (name) {
            return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search) || [, ""])[1].replace(/\+/g, '%20') || "");
        },

        // Convert a json encoded string to normal text
        restoreJson: function (value) {
            if (SBF.null(value)) return '';
            return value.replace(/\\n/g, "\n").replace(/\\r/g, "\r").replace(/\\t/g, "\t").replace(/\\f/g, "\f").replace(/\\'/g, "'").replace(/\\"/g, '"').replace(/\\\\/g, "\\");
        },

        // Convert a string to slug and inverse
        stringToSlug: function (string) {
            string = string.trim();
            string = string.toLowerCase();
            var from = "åàáãäâèéëêìíïîòóöôùúüûñç·/_,:;";
            var to = "aaaaaaeeeeiiiioooouuuunc------";
            for (var i = 0, l = from.length; i < l; i++) {
                string = string.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }
            return string.replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-').replace(/^-+/, '').replace(/-+$/, '').replace(/ /g, ' ');
        },

        slugToString: function (string) {
            string = string.replace(/_/g, ' ').replace(/-/g, ' ');
            return string.charAt(0).toUpperCase() + string.slice(1);
        },

        // Random string
        random: function () {
            let chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            let result = '';
            for (var i = 5; i > 0; --i) result += chars[Math.floor(Math.random() * 62)];
            return result;
        },

        // Check if a user type is an agent
        isAgent: function (user_type) {
            return user_type == 'agent' || user_type == 'admin' || user_type == 'bot';
        },

        // Get cors elapsed of a given date from now
        beautifyTime: function (datetime, extended = false, future = false) {
            let date;
            if (datetime == '0000-00-00 00:00:00') return '';
            if (datetime.indexOf('-') > 0) {
                var arr = datetime.split(/[- :]/);
                date = new Date(arr[0], arr[1] - 1, arr[2], arr[3], arr[4], arr[5]);
            } else {
                var arr = datetime.split(/[. :]/);
                date = new Date(arr[2], arr[1] - 1, arr[0], arr[3], arr[4], arr[5]);
            }
            let date_string = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds()));
            let diff_days = Math.round(((new Date()) - date_string) / (1000 * 60 * 60 * 24)) * (future ? -1 : 1);
            let days = [sb_('Sunday'), sb_('Monday'), sb_('Tuesday'), sb_('Wednesday'), sb_('Thursday'), sb_('Friday'), sb_('Saturday')];
            let time = date_string.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            if (diff_days < 1) {
                return (extended ? sb_('Today') + ' ' : '') + time;
            } else if (diff_days < 8) {
                return `<span>${days[date_string.getDay()]}</span>${extended ? ` <span>${time}</span>` : ''}`;
            } else {
                return `<span>${date_string.toLocaleDateString()}</span>${extended ? ` <span>${time}</span>` : ''}`;
            }
        },

        // Generate a string containing the agent location and time
        getLocationTimeString: function (details, onSuccess) {
            if ('timezone' in details) {
                let location = {};
                location['timezone'] = details['timezone']['value'];
                location['country'] = 'country' in details ? details['country']['value'] : location['timezone'].split('/')[0].replace(/_/g, ' ');
                location['city'] = 'city' in details ? details['city']['value'] : location['timezone'].split('/')[1].replace(/_/g, ' ');
                SBF.cors('GET', 'https://worldtimeapi.org/api/timezone/' + location['timezone'], function (response) {
                    response = JSON.parse(response);
                    if (SBF.null(response) || !SBF.null(response['error'])) {
                        SBF.error(response, 'SBF.getLocationTimeString()');
                        onSuccess(responseonSuccess);
                    } else {
                        let datetime = response['datetime'].replace('T', ' ');
                        onSuccess(`${new Date(datetime.substr(0, datetime.indexOf('.'))).toLocaleString([], { hour: '2-digit', minute: '2-digit' })} ${sb_('in')} ${location['city'] != '' ? location['city'] : ''}${location['country'] != '' ? ', ' + location['country'] : ''}`);
                    }
                });
            }
        },

        // Date string
        dateDB: function (date) {
            if (date == 'now') {
                date = (new Date).toISOString().replace('T', ' ');
                if (date.indexOf('.') > 0) {
                    date = date.substr(0, date.indexOf('.'));
                }
                return date;
            } else {
                return `${date.getFullYear()}-${date.getMonth() + 1}-${date.getDate()} ${date.getHours()}:${date.getMinutes()}:${date.getSeconds()}`;
            }
        },

        // Set and get users last activity
        updateUsersActivity: function (user_id, return_user_id, onSuccess) {
            SBF.ajax({
                function: 'update-users-last-activity',
                user_id: user_id,
                return_user_id: return_user_id,
                check_slack: !admin && CHAT_SETTINGS['slack-active']
            }, (response) => {
                if (response === 'online') {
                    onSuccess('online');
                } else {
                    onSuccess('offline');
                }
            });
        },

        // Search functions
        search: function (search, searchFunction) {
            search = search.toLowerCase();
            if (search == previous_search) {
                $(global).find('.sb-search-btn i').sbLoading(false);
                return;
            }
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                previous_search = search;
                searchFunction();
            }, 500);
        },

        searchClear: function (icon, onSuccess) {
            let search = $(icon).next().val();
            if (search != '') {
                $(icon).next().val('');
                onSuccess();
            }
        },

        // Support Board error js reporting
        error: function (message, function_name) {
            if (message instanceof Error) message = message.message;
            if (message[message.length - 1] == '.') message = message.slice(0, -1);
            if (message.indexOf('Support Board Error') > -1) message = message.replace('Support Board Error ', '').replace(']:', ']');
            if (admin && message != '' && !function_name.indexOf('update-users-last-activity') && !function_name.startsWith('security-error')) SBF.dialog(`[Error] [${function_name}] ${message}. Check the console for more details.`, 'info');
            $(global).find('.sb-loading').sbLoading(false);
            SBF.event('SBError', { message: message, function_name: function_name });
            throw new Error(`Support Board Error [${function_name}]: ${message}.`);
        },

        errorValidation: function (response, code = true) {
            return Array.isArray(response) && response[0] === 'validation-error' && (code === true || response[1] == code);
        },

        // Login
        loginForm: function (button, area = false, onSuccess = false) {
            if (!$(button).sbLoading()) {
                if (area === false) area = $(button).closest('.sb-rich-login');
                let email = $(area).find('#email input').val();
                let password = $(area).find('#password input').val();
                if (email == '' || password == '') {
                    $(area).find('.sb-info').html(sb_('Please insert an email and password.')).sbActivate();
                } else {
                    SBF.ajax({
                        function: 'login',
                        email: email,
                        password: password
                    }, (response) => {
                        if (response != false && Array.isArray(response)) {
                            if (!admin && this.isAgent(response[0]['user_type'])) {
                                SBForm.showErrorMessage(area, 'You cannot sign in as an agent.');
                                SBChat.scrollBottom();
                            } else {
                                SBF.loginCookie(response[1]);
                                SBF.event('SBLoginForm', new SBUser(response[0]));
                                if (onSuccess !== false) onSuccess(response);
                            }
                        } else {
                            $(area).find('.sb-info').html(sb_('Invalid email or password.')).sbActivate();
                            if (!admin) SBChat.scrollBottom();
                        }
                        $(button).sbLoading(false);
                    });
                    $(area).find('.sb-info').html('').sbActivate(false);
                    $(button).sbLoading(true);
                }
            }
        },

        // Set the login cookie
        loginCookie: function (value) {
            login_data = value;
            this.cookie('sb-login', value, 3650, 'set');
        },

        // Login
        login: function (email = '', password = '', user_id = '', token = '', onSuccess = false) {
            SBF.ajax({
                function: 'login',
                email: email,
                password: password,
                user_id: user_id,
                token: token
            }, (response) => {
                if (response != false && Array.isArray(response)) {
                    this.loginCookie(response[1]);
                    if (onSuccess != false) {
                        onSuccess(response);
                    }
                    return true;
                } else {
                    return false;
                }
            });
        },

        // Logout
        logout: function (reload = true) {
            SBChat.stopRealTime();
            this.cookie('sb-login', '', '', 'delete');
            storage('open-conversation', '');
            SBChat.conversations = false;
            activeUser(false);
            if (typeof sb_beams_client !== ND) {
                sb_beams_client.stop();
            }
            SBF.ajax({
                function: 'logout'
            }, () => {
                SBF.event('SBLogout');
                if (reload) {
                    setTimeout(() => { location.reload() }, 500);
                }
            });
        },

        // Return the active user
        activeUser: function () {
            return activeUser();
        },

        // Get the active user
        getActiveUser: function (db = false, onSuccess) {
            let app_login = SBApps.login();
            if (!app_login && storage('wp-login')) {
                this.cookie('sb-login', '', '', 'delete');
                activeUser(false);
                storage('wp-login', '');
            }
            SBF.ajax({
                function: 'get-active-user',
                db: db,
                login_app: app_login
            }, (response) => {
                if (!response) {
                    onSuccess();
                    return false;
                } else {
                    if ('user_type' in response) {
                        if (!admin && SBF.isAgent(response['user_type'])) {
                            let message = 'You are logged in as both agent and user. Logout or use another browser, Incognito or Private mode, to login as user. Force a logout by running the function SBF.reset() in the console.';
                            if (!storage('double-login-alert')) {
                                storage('double-login-alert', true);
                                alert(message);
                            }
                            console.warn('Support Board: ' + message);
                            SBF.event('SBDoubleLoginError');
                        } else {
                            activeUser(new SBUser(response));
                            if (app_login && CHAT_SETTINGS['wp-logout']) {
                                storage('wp-login', true);
                            }
                            onSuccess();
                        }
                    }
                }
            });
        },

        // Clean
        reset: function () {
            this.cookie('sb-login', '', 0, false);
            try { localStorage.removeItem('support-board') } catch (e) {}
            this.logout();
        },

        // Dialog box
        dialog: function (text, type, onConfirm) {
            if (admin) {
                sbDialogAdmin(text, type, onConfirm);
            }
        },

        // Lightbox
        lightbox: function (content) {
            let lightbox = $(admin ? global : main).find('.sb-lightbox-media');
            $(lightbox).sbActivate().find(' > div').html(content);
        },

        // Manage the local storage
        storage: function (key, value = ND) {
            try { if (typeof localStorage == ND) return false } catch (e) { return false }
            let settings = localStorage.getItem('support-board');
            if (settings === null) {
                settings = {};
            } else {
                settings = JSON.parse(settings);
            }
            if (value === ND) {
                if (key in settings) {
                    return settings[key];
                } else {
                    return false;
                }
            } else {
                if (value == '') {
                    delete settings[key];
                } else {
                    settings[key] = value;
                }
                localStorage.setItem('support-board', JSON.stringify(settings));
            }
        },

        // Set or get a cookie
        cookie: function (name, value = false, expiration_days = false, action = 'get') {
            let cookie_https = location.protocol == 'https:' ? 'SameSite=None;Secure' : '';
            if (action == 'get') {
                if (!cookies_supported) {
                    return this.storage(name);
                }
                let cookies = document.cookie.split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = cookies[i];
                    while (cookie.charAt(0) == ' ') {
                        cookie = cookie.substring(1);
                    }
                    if (cookie.indexOf(name) == 0) {
                        let value = cookie.substring(name.length + 1, cookie.length);
                        return this.null(value) ? false : value;
                    }
                }
                return false;
            } else if (action == 'set') {
                if (!cookies_supported) {
                    this.storage(name, value);
                } else {
                    let date = new Date();
                    date.setTime(date.getTime() + (expiration_days * 24 * 60 * 60 * 1000));
                    document.cookie = name + "=" + value + ";expires=" + date.toUTCString() + ";path=/;" + cookie_https;
                }
            } else if (this.cookie(name)) {
                if (!cookies_supported) {
                    this.storage(name, '');
                } else {
                    document.cookie = name + "=" + value + ";expires=Thu, 01 Jan 1970 00:00:01 GMT" + ";path=/;" + cookie_https;
                }
            }
        },

        // Save the current time or check if the saved time is older than now plus the given hours
        storageTime: function (key, hours = false) {
            if (hours === false) {
                storage(key, today.getTime());
            } else {
                return storage(key) == false || (today.getTime() - storage(key)) > (3600000 * hours);
            }
        },

        // Return a front setting
        setting: function (key) {
            return typeof CHAT_SETTINGS !== ND && key in CHAT_SETTINGS ? CHAT_SETTINGS[key] : -1;
        },

        // Return the shortcode array
        shortcode: function (shortcode) {
            return SBRichMessages.shortcode(shortcode);
        },

        // Events and webhooks
        event: function (name, parameters) {
            $(document).trigger(name, parameters);
            if (this.setting('webhooks') && ['SBLoginForm', 'SBUserDeleted', 'SBMessageSent', 'SBBotMessage', 'SBEmailSent', 'SBNewMessagesReceived', 'SBNewConversationReceived', 'SBNewConversationCreated', 'SBActiveConversationStatusUpdated', 'SBSlackMessageSent', 'SBMessageDeleted', 'SBRegistrationForm', 'SBRichMessageSubmit', 'SBNewEmailAddress'].includes(name)) {
                SBF.ajax({
                    function: 'webhooks',
                    function_name: name,
                    parameters: parameters
                });
            }
        },

        // Translate a string
        translate: function (string) {
            if (SBF.null(CHAT_SETTINGS)) return string;
            let translations = CHAT_SETTINGS['translations'];
            if (translations != false && string in translations) {
                return translations[string] == '' ? string : translations[string];
            } else {
                return string;
            }
        },

        // Escape a string
        escape: function (string) {
            string = replaceAll(string, '<script', '&lt;script');
            string = replaceAll(string, '</script', '&lt;/script');
            string = replaceAll(string, 'javascript:', '');
            string = replaceAll(string, 'onclick=', '');
            string = replaceAll(string, 'onerror=', '');
            return string;
        },

        // Visibility change function
        visibilityChange: function (visibility = '') {
            if (visibility == 'hidden') {
                if (!admin) {
                    SBChat.stopRealTime();
                }
                SBChat.tab_active = false;
            } else {
                if (activeUser() && !admin) {
                    SBChat.startRealTime();
                }
                SBChat.tab_active = true;
                document.title = document_title;
            }
        },

        // Convert a settings string to an Array
        settingsStringToArray: function (string) {
            if (this.null(string)) return [];
            let result = [];
            string = string.split(',');
            for (var i = 0; i < string.length; i++) {
                let values = string[i].split(':');
                result[values[0]] = values[1] == 'false' ? false : values[1] == 'true' ? true : values[1];
            }
            return result;
        },

        // Open a browser window
        openWindow: function (link, width = 550, height = 350) {
            let left = (screen.width / 2) - (width / 2);
            let top = (screen.height / 2) - (height / 2);
            window.open(link, 'targetWindow', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=' + width + ',height=' + height + ', top=' + top + ', left=' + left);
            return false;
        }
    }

    function replaceAll(string, search, replace) {
        if (SBF.null(string)) return string;
        var esc = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        var reg = new RegExp(esc, 'ig');
        return string.replace(reg, replace);
    }

    function sbDelta(e) {
        let delta = e.originalEvent.wheelDelta;
        if (typeof delta == ND) {
            delta = e.originalEvent.deltaY;
        }
        if (typeof delta == ND) {
            delta = e.originalEvent.detail * -1;
        }
        return delta;
    }

    // Shortcut for local storage function
    function storage(key, value = ND) {
        return SBF.storage(key, value);
    }

    // Support Board js translations
    function sb_(string) {
        return SBF.translate(string);
    }

    // Access the global user variable
    function activeUser(value = -1) {
        if (value === -1) {
            return window.sb_current_user;
        } else {
            window.sb_current_user = value;
        }
    }

    /*
    * ----------------------------------------------------------
    * GLOBAL FUNCTIONS
    * ----------------------------------------------------------
    */

    window.SBF = SBF;
    window.sb_current_user = sb_current_user;

    /*
    * ----------------------------------------------------------
    * JQUERY FUNCTIONS
    * ----------------------------------------------------------
    */

    // Activate or deactivate the element
    $.fn.sbActivate = function (show = true) {
        $(this).setClass('sb-active', show);
        return this;
    };

    // Check if active 
    $.fn.sbActive = function () {
        return $(this).hasClass('sb-active');
    };

    // Loading check, set and unset
    $.fn.sbLoading = function (value = 'check') {
        if (value == 'check') {
            return $(this).hasClass('sb-loading');
        } else {
            $(this).setClass('sb-loading', value);
        }
        return this;
    }

    // Popup
    $.fn.sbTogglePopup = function (button) {
        var showed = true;
        if ($(this).sbActive()) {
            $(this).sbActivate(false);
            $(global).removeClass('sb-popup-active');
            showed = false;
        } else {
            $(global).addClass('sb-popup-active');
            $(global).find('.sb-popup').sbActivate(false);
            $(this).css('left', $(button).offset().left + 15).sbActivate();
            SBF.deselectAll();
        }
        return showed;
    };

    // Uploader
    $.fn.sbUploadFiles = function (onSuccess) {
        let files = $(this).prop('files');
        for (var i = 0; i < files.length; i++) {
            let file = files[i];
            let form = new FormData();
            form.append('file', file);
            SBF.upload(form, onSuccess);
        }
        $(this).value = '';
    }

    // Set profile box
    $.fn.setProfile = function (name = false, profile_image = false) {
        if (SBF.null(name)) name = activeUser() != false ? activeUser().name : '';
        if (SBF.null(profile_image)) profile_image = activeUser() != false ? activeUser().image : SB_URL + '/media/user.svg';
        $(this).find('img').attr('src', profile_image);
        $(this).find('.sb-name').html(name);
        return this;
    }

    // Add or remove a class
    $.fn.setClass = function (class_name, add = true) {
        if (add) {
            $(this).addClass(class_name);
        } else {
            $(this).removeClass(class_name);
        }
    }

    /* 
    * ----------------------------------------------------------
    * # SB USER
    * ----------------------------------------------------------
    */

    class SBUser {
        constructor(details = {}, extra = {}) {
            this.details = details;
            this.extra = extra;
            this.conversations = [];
            this.processArray(details);
        }

        get id() {
            return this.get('id') == '' ? this.get('user_id') : this.get('id');
        }

        get type() {
            return this.get('user_type');
        }

        get name() {
            return this.get('full_name');
        }

        get image() {
            return this.get('profile_image');
        }

        get language() {
            return this.getExtra('browser_language') == '' ? '' : this.getExtra('browser_language')['value'].toLowerCase();
        }

        get(id) {
            if (id == 'full_name') return 'first_name' in this.details ? this.details['first_name'] + (this.details['last_name'] != '' ? ' ' + this.details['last_name'] : '') : '';
            if (id in this.details && !SBF.null(this.details[id])) return this.details[id];
            else return '';
        }

        getExtra(id) {
            if (id in this.extra && !SBF.null(this.extra[id])) return this.extra[id];
            else return '';
        }

        set(id, value) {
            this.details[id] = value;
        }

        setExtra(id, value) {
            this.extra[id] = value;
        }

        // Initialization
        processArray(details) {
            if (details && 'details' in details) {
                for (var i = 0; i < details['details'].length; i++) {
                    this.setExtra(details['details'][i]['slug'], details['details'][i]);
                }
                delete details['details'];
                this.details = details;
            }
        }

        // Update the user details and extra details
        update(onSuccess) {
            if (this.id != '') {
                SBF.ajax({
                    function: 'get-user',
                    user_id: this.id,
                    extra: true
                }, (response) => {
                    this.processArray(response);
                    onSuccess();
                });
            } else {
                SBF.error('Missing user ID', 'SBUser.update');
            }
        }

        // Get user conversations
        getConversations(onSuccess = false, exclude_id) {
            if (this.id != '') {
                SBF.ajax({
                    function: 'get-user-conversations',
                    user_id: this.id,
                    exclude_id: exclude_id
                }, (response) => {
                    let conversations = [];
                    for (var i = 0; i < response.length; i++) {
                        let conversation = new SBConversation([new SBMessage(response[i])], { id: response[i]['conversation_id'], conversation_status_code: response[i]['conversation_status_code'], department: response[i]['department'], title: response[i]['title'] });
                        conversations.push(conversation);
                    }
                    this.conversations = conversations;
                    if (onSuccess != false) {
                        onSuccess(conversations);
                    }
                });
            } else {
                SBF.error('Missing user ID', 'SBUser.getConversations');
            }
        }

        // Get conversations code
        getConversationsCode(conversations = false) {
            let code = '';
            let active_conversation_id = SBChat.conversation != false ? SBChat.conversation.id : -1;
            if (conversations == false) conversations = this.conversations;
            for (var i = 0; i < conversations.length; i++) {
                if (conversations[i] instanceof SBConversation) {
                    code += `<li ${active_conversation_id == conversations[i].id ? 'class="sb-active" ' : ''}data-conversation-status="${conversations[i].get('conversation_status_code')}" data-conversation-id="${conversations[i].id}" data-department="${conversations[i].get('department')}">${conversations[i].getCode()}</li>`;
                } else {
                    SBF.error('Conversation not of type SBConversation', 'SBUser.getConversationsCode');
                }
            }
            if (code == '') {
                code = `<p class="sb-no-results">${sb_('No conversations found.')}</p>`;
            }
            return code;
        }

        // Get single conversation
        getFullConversation(conversation_id = false, onSuccess = false) {
            if (conversation_id !== false) {
                SBF.ajax({
                    function: 'get-conversation',
                    conversation_id: conversation_id
                }, (response) => {
                    let messages = [];
                    for (var i = 0; i < response['messages'].length; i++) {
                        messages.push(new SBMessage(response['messages'][i]));
                    }
                    if (onSuccess != false) {
                        onSuccess(new SBConversation(messages, response['details']));
                    }
                });
            } else {
                SBF.error('Missing conversation ID', 'SBUser.getFullConversation');
            }
        }

        getConversationByID(conversation_id) {
            for (var i = 0; i < this.conversations.length; i++) {
                if (this.conversations[i].id == conversation_id) {
                    return this.conversations[i];
                }
            }
            return false;
        }

        // Add a new conversation
        addConversation(conversation) {
            if (conversation instanceof SBConversation) {
                let conversation_id = conversation.id;
                let is_new = true;
                for (var i = 0; i < this.conversations.length; i++) {
                    if (this.conversations[i].id == conversation_id) {
                        this.conversations[i] = conversation;
                        is_new = false;
                        break;
                    }
                }
                if (is_new) {
                    this.conversations.unshift(conversation);
                }
                return is_new;
            } else {
                SBF.error('Conversation not of type SBConversation', 'SBUser.addConversation');
            }
        }

        // Get the last conversation
        getLastConversation() {
            return this.isConversationsEmpty() ? false : this.conversations[this.conversations.length - 1];
        }

        // Check if the conversation array is empty
        isConversationsEmpty() {
            return this.conversations.length == 0;
        }

        // Check if the extra array is empty
        isExtraEmpty() {
            return Object.keys(this.extra).length === 0 && this.extra.constructor === Object;
        }

        // Delete the user
        delete(onSuccess) {
            if (this.id != '') {
                SBF.ajax({
                    function: 'delete-user',
                    user_id: this.id
                }, () => {
                    SBF.event('SBUserDeleted', this.id);
                    onSuccess();
                    return true;
                });
            } else {
                SBF.error('Missing user ID', 'SBUser.delete');
            }
        }
    }
    window.SBUser = SBUser;

    /* 
    * ----------------------------------------------------------
    * # SB MESSAGE
    * ----------------------------------------------------------
    */

    class SBMessage {
        constructor(details) {
            this.details = details;
            if ('first_name' in this.details) {
                this.details['full_name'] = this.details['first_name'] + ' ' + this.details['last_name'];
            }
            this.set('payload', this.get('payload') != '' ? JSON.parse(this.get('payload')) : {});
        }

        get id() {
            return this.get('id');
        }

        get attachments() {
            return !SBF.null(this.details['attachments']) ? JSON.parse(this.details['attachments']) : [];
        }

        get(id) {
            if (id in this.details && !SBF.null(this.details[id])) return this.details[id];
            else return '';
        }

        set(id, value) {
            this.details[id] = value;
        }

        payload(key = false, value = false) {
            if (key !== false && value !== false) {
                let payload = this.get('payload');
                payload[key] = value;
                this.set('payload', payload);
            }
            return this.get('payload');
        }

        getCode() {
            let agent = SBF.isAgent(this.details['user_type']);
            let admin_menu = admin ? SBAdmin.conversations.messageMenu(agent) : '';
            let message = this.render(this.details['message']);
            let attachments = this.attachments;
            let attachments_code = '';
            let media_code = '';
            let thumb = admin || (agent && !CHAT_SETTINGS['hide-agents-thumb']) || (!agent && CHAT_SETTINGS['display-users-thumb']) ? `<div class="sb-thumb"><img src="${this.details['profile_image']}"><div>${this.details['full_name']}</div></div>` : '';
            let css = (!agent ? 'sb-right' : '') + (thumb == '' ? '' : ' sb-thumb-active');
            let type = '';
            if (message == '' && attachments.length == 0) {
                return '';
            }

            // Rich Messages
            if (agent) {
                let shortcodes = message.match(/\[.+?\]/g) || [];
                let rich = false;
                for (var i = 0; i < shortcodes.length; i++) {
                    let settings = SBRichMessages.shortcode(shortcodes[i]);
                    let rich_message = SBRichMessages.generate(settings[1], settings[0]);
                    if (rich_message != false) {
                        if (message.charAt(0) != '[') {
                            rich_message = rich_message.replace('sb-rich-message', 'sb-rich-message sb-margin-top');
                        }
                        if (message.charAt(message.length - 1) != ']') {
                            rich_message = rich_message.replace('sb-rich-message', 'sb-rich-message sb-margin-bottom');
                        }
                        message = message.replace(shortcodes[i], rich_message);
                        rich = true;
                        type = `data-type="${settings[0]}"`;
                    }
                }
                if (rich) {
                    css += ' sb-rich-cnt';
                    if (shortcodes.length > 1) {
                        type = 'data-type="multiple"';
                    }
                }
            }

            // Attachments
            if (attachments.length) {
                attachments_code = '<div class="sb-message-attachments">';
                for (var i = 0; i < attachments.length; i++) {
                    if (/.jpg|.jpeg|.png|.gif/.test(attachments[i][1])) {
                        media_code += `<div class="sb-image"><img src="${attachments[i][1]}" /></div>`;
                    } else {
                        attachments_code += `<a rel="noopener" target="_blank" href="${attachments[i][1]}">${attachments[i][0]}</a>`;
                    }
                }
                attachments_code += '</div>';
            }

            // Message creation
            return `<div data-id="${this.details['id']}" class="${css}" ${type}>${thumb}<div class="sb-cnt"><div class="sb-message${media_code != '' && message == '' ? ' sb-message-media' : ''}">${message}${media_code}</div>${attachments_code}<div class="sb-time">${SBF.beautifyTime(this.details['creation_time'], true)}</div></div>${admin_menu}</div>`;
        }

        render(message = false) {
            let len = message.length;
            if (message === false) {
                message = '' + this.details['message'];
            }

            // Code block
            let codes = message.match(/```([\s\S]*)```/) || [];
            for (var i = 0; i < codes.length; i++) {
                message = message.replace(codes[i], '[code-' + i + ']');
            }

            // Breakline
            message = message.replace(/(?:\r\n|\r|\n)/g, '<br>');

            // Bold
            message = message.replace(/\*([^\**]+)\*/g, "<b>$1</b>");

            // Italic
            message = message.replace(/\__([^\____]+)\__/g, "<i>$1</i>");

            // Strikethrough
            message = message.replace(/\~([^\~~]+)\~/g, "<del>$1</del>");

            // Code
            message = message.replace(/\`([^\``]+)\`/g, "<code>$1</code>");

            // Unicode
            message = message.replace(/u([0-9a-f]{4,5})/mi, '&#x$1;');

            // Single emoji
            if (((len == 6 || len == 5) && message.startsWith('&#x')) || len < 3 && message.match(/(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/)) {
                message = `<span class="emoji-large">${message}</span>`;
            }

            // Links
            if (message.indexOf('http') > -1) {
                message = message.autoLink({ target: '_blank' });
            }

            // Code block restore
            for (var i = 0; i < codes.length; i++) {
                message = message.replace('[code-' + i + ']', '<pre>' + $.trim($.trim(codes[i]).substr(3, codes[i].length - 6).replace(/(?:\r\n|\r|\n)/g, '<br>'))) + '</pre>';
            }

            return message;
        }
    }
    window.SBMessage = SBMessage;

    /* 
    * ----------------------------------------------------------
    * # SB CONVERSATION
    * ----------------------------------------------------------
    */

    class SBConversation {
        constructor(messages, details) {
            this.details = SBF.null(details) ? {} : details;
            if (Array.isArray(messages)) {
                this.messages = [];
                if (messages.length) {
                    if (messages[0] instanceof SBMessage) {
                        this.messages = messages;
                    } else {
                        SBF.error('Messages not of type SBMessage', 'SBConversation.constructor');
                    }
                }
            } else {
                SBF.error('Message array not of type Array', 'SBConversation.constructor');
            }
        }

        get id() {
            return this.get('id') == '' ? this.get('conversation_id') : this.get('id');
        }

        get(id) {
            if (id in this.details && !SBF.null(this.details[id])) {
                return this.details[id];
            }
            if (id == 'title') {
                if (!SBF.null(this.details['first_name'])) {
                    return this.details['first_name'] + ' ' + this.details['last_name'];
                } else if (this.messages.length) {
                    return this.messages[0].get('full_name');
                }
            }
            return '';
        }

        set(id, value) {
            this.details[id] = value;
        }

        getMessage(id) {
            for (var i = 0; i < this.messages.length; i++) {
                if (this.messages[i].id == id) {
                    return this.messages[i];
                }
            }
            return false;
        }

        getLastMessage() {
            let count = this.messages.length;
            return count > 0 ? this.messages[count - 1] : false;
        }

        getLastUserMessage(index = false) {
            if (index === false) index = this.messages.length - 1;
            for (var i = index; i > -1; i--) {
                if (!SBF.isAgent(this.messages[i].get('user_type'))) {
                    return this.messages[i];
                }
            }
            return false;
        }

        updateMessage(id, message) {
            if (message instanceof SBMessage) {
                for (var i = 0; i < this.messages.length; i++) {
                    if (this.messages[i].id == id) {
                        this.messages[i] = message;
                        return true;
                    }
                }
            } else {
                SBF.error('Message not of type SBMessage', 'SBConversation.updateMessage');
            }
            return false;
        }

        addMessages(messages) {
            if (Array.isArray(messages)) {
                for (var i = 0; i < messages.length; i++) {
                    if (messages[i] instanceof SBMessage) {
                        this.messages.push(messages[i]);
                    }
                }
            } else {
                if (messages instanceof SBMessage) {
                    this.messages.push(messages);
                } else {
                    SBF.error('Messages not of type SBMessage', 'SBConversation.addMessages()');
                }
            }
        }

        getCode() {
            let count = this.messages.length;
            if (count) {
                let message = this.messages[count - 1];
                let text = message.get('message');
                if (text.indexOf('[') !== false) {
                    let shortcodes = text.match(/\[.+?\]/g) || [];
                    if (shortcodes.length) {
                        let shortcode = SBRichMessages.shortcode(shortcodes[0]);
                        text = text.replace(shortcodes[0], sb_(SBF.null(shortcode[1]['message']) ? (SBF.null(shortcode[1]['title']) ? '' : shortcode[1]['title']) : shortcode[1]['message']));
                    }
                }
                let title = tickets && !CHAT_SETTINGS['tickets-conversations-title-user'] ? this.get('title') : sb_(!admin && message.get('last_name').charAt(0) == '#' ? 'You' : message.get('full_name'));
                return `<div class="sb-conversation-item" data-user-id="${message.get('user_id')}"><img src="${message.get('profile_image')}"><div><span class="sb-name">${title}</span><span class="sb-time">${SBF.beautifyTime(message.get('creation_time'))}</span></div><div class="sb-message">${text.length > 114 ? text.substr(0, 114) + ' ...' : text}</div></div>`;
            }
            return '';
        }

        deleteMessage(id) {
            for (var i = 0; i < this.messages.length; i++) {
                if (this.messages[i].id == id) {
                    this.messages.splice(i, 1);
                    return true;
                }
            }
            return false;
        }

        searchMessages(search, exact_match = false) {
            let results = [];
            for (var i = 0; i < this.messages.length; i++) {
                let message = this.messages[i].get('message');
                if ((exact_match && message == search) || (!exact_match && message.indexOf(search) > -1)) {
                    results.push[messages[i]];
                }
            }
            return results;
        }

        getAttachments() {
            let list = [];
            for (var i = 0; i < this.messages.length; i++) {
                let attachments = this.messages[i].attachments;
                for (var j = 0; j < attachments.length; j++) {
                    let link = attachments[j][1];
                    list.push([attachments[j][0], link, link.substr(link.lastIndexOf('.') + 1), this.messages[i].id]);
                }
            }
            return list;
        }
    }
    window.SBConversation = SBConversation;

    /* 
    * ----------------------------------------------------------
    * # SB CHAT
    * ----------------------------------------------------------
    */

    var SBChat = {

        // Variables
        rich_messages_list: ['chips', 'buttons', 'select', 'inputs', 'table', 'list'],
        emoji_options: { range: 0, range_limit: 47, list: [], list_now: [], touch: false },
        initialized: false,
        editor_listening: false,
        conversation: false,
        is_busy: false,
        is_busy_update: false,
        chat_open: false,
        real_time: false,
        agent_id: -1,
        agent_online: false,
        user_online: false,
        expanded: false,
        main_header: true,
        start_header: false,
        desktop_notifications: false,
        flash_notifications: false,
        datetime_last_message: '2000-01-01 00:00:00',
        datetime_last_message_conversation: '2000-01-01 00:00:00',
        audio: false,
        audio_out: false,
        tab_active: true,
        notifications: [],
        typing_settings: { typing: false, sent: false, timeout: false },
        email_sent: false,
        dialogflow_token: storage('dialogflow-token') == false ? -1 : storage('dialogflow-token'),
        dashboard: false,
        articles: false,
        slack_channel: [-1, -1],
        skip: false,
        queue_interval: false,
        departments: false,
        default_department: null,

        // Send a message
        sendMessage: function (user_id = -1, message = '', attachments = [], onSuccess = false, payload = false, conversation_status_code = false) {
            let bot_unknow_notify = (dialogflow_unknow_notify || dialogflow_human_takeover != false) && SBApps.dialogflow.active();
            let bot_status_code = bot_unknow_notify && SBApps.dialogflow.active() ? 0 : 2;
            let last_message = this.conversation ? this.conversation.getLastMessage() : false;

            // Check settings and contents
            conversation_status_code = conversation_status_code === false ? (last_message ? (admin ? 1 : bot_status_code) : (admin ? 1 : -1)) : conversation_status_code;
            if (this.conversation == false) {
                let last_conversation = admin ? false : activeUser().getLastConversation();
                if (last_conversation && force_action != 'new-conversation') {
                    this.openConversation(last_conversation.id);
                    this.setConversation(last_conversation);
                    force_action = false;
                } else {
                    this.newConversation(conversation_status_code, user_id, '', [], (admin && SB_ACTIVE_AGENT['department'] != '' ? SB_ACTIVE_AGENT['department'] : null), null, () => {
                        return this.sendMessage(user_id, message, attachments, onSuccess, payload);
                    });
                    return false;
                }
            }
            if (user_id == -1) {
                user_id = admin ? SB_ACTIVE_AGENT['id'] : activeUser().id;
            }
            if (message == '') {
                message = $(chat_textarea).val().trim();
            }
            if (attachments.length == 0) {
                $(chat_editor).find('.sb-attachments > div').each(function () {
                    attachments.push([$(this).attr('data-name'), $(this).attr('data-value')]);
                });
            }

            // Send message
            if (message != '' || attachments.length || payload) {
                let is_user = user_id != bot_id;
                this.busy(true);
                $(chat_textarea).val('').css('height', '');
                $(chat_editor).find('.sb-attachments').html('');
                this.activateBar(false);

                SBF.ajax({
                    function: 'send-message',
                    user_id: user_id,
                    conversation_id: this.conversation.id,
                    message: message,
                    attachments: attachments,
                    conversation_status_code: conversation_status_code,
                    queue: !admin && CHAT_SETTINGS['queue'] && is_user,
                    payload: payload
                }, (response) => {
                    let is_conversation = this.conversation != false;
                    let send_slack = admin || !bot_unknow_notify

                    // Update the chat current conversation
                    if ((admin && !this.user_online) || (!admin && !this.agent_online)) {
                        this.update();
                    }

                    // Update the dashboard conversations
                    if (!admin && this.dashboard && user_id == bot_id) {
                        this.updateConversations();
                    }

                    // Follow up
                    if (!admin && is_user && CHAT_SETTINGS['follow']) {
                        this.followUp();
                    }

                    // Email notifications
                    if (is_user && (!bot_unknow_notify || !SBF.null(payload['skip-dialogflow'])) && ((!admin && CHAT_SETTINGS['notify-agent-email']) || (admin && !this.user_online && CHAT_SETTINGS['notify-user-email'] && activeUser().get('email') != ''))) {
                        this.sendEmail(message, attachments);
                    }

                    // Push notifications
                    if (!admin && is_user && !bot_unknow_notify && CHAT_SETTINGS['push-notifications'] && (!agents_online || !this.agent_online)) {
                        this.pushNotification(message);
                    }

                    // Dialogflow
                    if (is_user && (!payload || (payload['id'] != 'sb-human-takeover' && SBF.null(payload['skip-dialogflow'])))) {
                        this.sendBotMessage(message, attachments);
                    }

                    // Slack and visitor to lead
                    if (!admin && is_user && activeUser().type == 'visitor') {
                        SBF.ajax({ function: 'update-user-to-lead', user_id: user_id }, () => {
                            activeUser().set('user_type', 'lead');
                            if (CHAT_SETTINGS['slack-active'] && send_slack) {
                                this.slackMessage(user_id, activeUser().name, activeUser().image, message, attachments);
                            }
                        });
                    } else if (CHAT_SETTINGS['slack-active'] && send_slack && !this.skip) {
                        if (admin) {
                            this.slackMessage(activeUser().id, SB_ACTIVE_AGENT['full_name'], SB_ACTIVE_AGENT['profile_image'], message, attachments);
                        } else {
                            this.slackMessage(activeUser().id, (is_user ? activeUser().name : CHAT_SETTINGS['bot-name']), (is_user ? activeUser().image : CHAT_SETTINGS['bot-image']), message, attachments);
                        }
                    }

                    // Timetable
                    if (is_user && !admin && CHAT_SETTINGS['timetable'] && (!CHAT_SETTINGS['office-hours'] || !agents_online) && SBF.storageTime('timetable', 1)) {
                        setTimeout(() => {
                            if (is_conversation) {
                                this.sendMessage(bot_id, CHAT_SETTINGS['timetable-hide'] ? CHAT_SETTINGS['timetable-message'] : '[timetable]');
                                this.scrollBottom();
                                SBF.storageTime('timetable');
                            }
                        }, 5000);
                    }

                    // Articles
                    if (this.articles && !admin && CHAT_SETTINGS['articles'] && !CHAT_SETTINGS['office-hours'] && !this.isInitDashboard()) {
                        setTimeout(() => {
                            if (is_conversation) {
                                this.sendMessage(bot_id, '[articles]');
                                this.scrollBottom();
                                this.articles = false;
                            }
                        }, 5000);
                    }

                    // Queue
                    if (response['queue']) {
                        this.queue(this.conversation.id);
                    }

                    // Event
                    let message_response = { user_id: user_id, conversation_id: this.conversation.id, conversation_status_code: conversation_status_code, message_id: response['message-id'], message: message, attachments: attachments };
                    SBF.event('SBMessageSent', message_response);
                    if (onSuccess !== false) {
                        onSuccess(message_response);
                    }

                    // Miscellaneous
                    if (this.skip) this.skip = false;
                    this.busy(false);
                });

                // Display the message as sending in progress
                if (is_user) {
                    message = SBF.escape(message);
                    $(chat).append((new SBMessage({ id: 'sending', profile_image: (admin ? SB_ACTIVE_AGENT['profile_image'] : activeUser().image), full_name: activeUser().name, creation_time: '0000-00-00 00:00:00', message: message, user_type: (admin ? 'agent' : 'user') })).getCode().replace('<div class="sb-time"></div>', `<div class="sb-time">${sb_('Sending')}<i></i></div>`));
                    if (!this.dashboard) this.scrollBottom();
                }

                // Sounds
                if ((!admin && this.chat_open && is_user && ['a', 'aa'].includes(CHAT_SETTINGS['chat-sound'])) || (admin && SB_ADMIN_SETTINGS['sounds'] == 'a')) {
                    this.audio_out.play();
                }
            }
        },

        // Dialogflow bot message
        sendBotMessage: function (message = '', attachments = []) {
            if (SBApps.dialogflow.active()) {
                timeout = setTimeout(() => {
                    this.typing(-1, 'start');
                    setTimeout(() => {
                        this.typing(-1, 'stop');
                    }, 10000);
                }, 1000);
                setTimeout(() => {
                    SBF.ajax({
                        function: 'send-bot-message',
                        conversation_id: this.conversation.id,
                        message: message,
                        attachments: attachments,
                        token: this.dialogflow_token
                    }, (response) => {
                        if (!SBF.errorValidation(response)) {
                            let is_unknow = !SBF.null(response['response']['queryResult']) && response['response']['queryResult']['action'] == 'input.unknown';
                            let send_notification = false;
                            let messages = 'messages' in response && Array.isArray(response['messages']) ? response['messages'] : [];

                            this.typing(-1, 'stop');
                            clearTimeout(timeout);
                            if (this.dialogflow_token != response['token']) {
                                this.dialogflow_token = response['token'];
                                storage('dialogflow-token', response['token']);
                            }
                            if (is_unknow && (dialogflow_unknow_notify || CHAT_SETTINGS['dialogflow-disable'])) {
                                SBApps.dialogflow.active(false);
                            }
                            if (is_unknow && dialogflow_human_takeover != false && SBF.storageTime('human-takeover', 1)) {
                                setTimeout(() => {
                                    this.sendMessage(bot_id, `[chips id="sb-human-takeover" options="${dialogflow_human_takeover['confirm']},${dialogflow_human_takeover['cancel']}" message="${dialogflow_human_takeover['message'].replace(/"/g, '')}"]`);
                                }, 1100);
                            }
                            if ('queryResult' in response['response']) {
                                let result = response['response']['queryResult'];

                                // Actions
                                if ('action' in result) {
                                    let action = result['action'];
                                    if (action == 'end') {
                                        SBApps.dialogflow.active(false);
                                    }
                                    SBF.event('SBBotAction', action);
                                }

                                // Payload
                                for (var i = 0; i < messages.length; i++) {
                                    if ('payload' in messages[i]) {
                                        let payloads = ['human-takeover', 'redirect', 'woocommerce-update-cart', 'woocommerce-checkout', 'open-article'];
                                        let payload = messages[i]['payload'];
                                        if (SBF.null(payload)) payload = [];
                                        if (payloads[0] in payload && payload[payloads[0]] === true) {
                                            send_notification = true;
                                            if (CHAT_SETTINGS['dialogflow-disable']) SBApps.dialogflow.active(false);
                                        }
                                        if (payloads[1] in payload) {
                                            setTimeout(function () {
                                                document.location = payload[payloads[1]];
                                            }, 500);
                                        }
                                        if (payloads[2] in payload) {
                                            let payload_value = payload[payloads[2]];
                                            SBApps.woocommerce.updateCart(payload_value[0], payload_value[1], (response) => {
                                                if (response) {
                                                    SBF.event('SBWoocommerceCartUpdated', { 'action': payload_value[0], 'product': payload_value[1] });
                                                }
                                            });
                                        }
                                        if (payloads[3] in payload && payload[payloads[3]] === true) {
                                            SBApps.wordpress.ajax('url', { url_name: 'checkout' }, (response) => {
                                                setTimeout(function () {
                                                    document.location = response;
                                                }, 500);
                                            });
                                        }
                                        if (payloads[4] in payload) {
                                            SBChat.showArticles(payload[payloads[4]]);
                                        }
                                        SBF.event('SBBotPayload', payload);
                                    }
                                }

                                // Diagnostic Info
                                if ('diagnosticInfo' in result) {
                                    let info = result['diagnosticInfo'];

                                    // End conversation
                                    if ('end_conversation' in info && info['end_conversation']) {
                                        SBApps.dialogflow.active(false);
                                    }
                                }
                            }

                            // Slack
                            if (CHAT_SETTINGS['slack-active'] && messages && (!dialogflow_unknow_notify || is_unknow || send_notification)) {
                                if (is_unknow || send_notification) {
                                    let last_user_message = this.conversation.getLastUserMessage();
                                    this.slackMessage(activeUser().id, activeUser().name, activeUser().image, last_user_message.get('message'));
                                }
                                for (var i = 0; i < messages.length; i++) {
                                    this.slackMessage(activeUser().id, CHAT_SETTINGS['bot-name'], CHAT_SETTINGS['bot-image'], messages[i]['message'], messages[i]['attachments']);
                                }
                            }

                            // Email notification to agents if unknow answer
                            if (send_notification || (!admin && CHAT_SETTINGS['notify-agent-email'] && dialogflow_unknow_notify && is_unknow)) {
                                SBApps.dialogflow.unknowEmail(message);
                            }

                            // Push notification to agents if unknow answer
                            if (send_notification || (!admin && CHAT_SETTINGS['push-notifications'] && dialogflow_unknow_notify && is_unknow)) {
                                this.pushNotification(message);
                            }

                            SBF.event('SBBotMessage', { response: response, message: message });
                        }
                    });
                }, CHAT_SETTINGS['bot-delay'] == 0 ? 2000 : parseInt(CHAT_SETTINGS['bot-delay']));
            }
        },

        // Update message
        updateMessage: function (message_id, message = '') {
            SBF.ajax({
                function: 'update-message',
                message_id: message_id,
                message: message
            });
        },

        // Email notifications
        sendEmail: function (message, attachments) {
            if (!this.email_sent) {
                let recipent_id = admin ? activeUser().id : (this.lastAgent(false) != false ? this.lastAgent(false)['user_id'] : -1);
                if (!admin && recipent_id != -1 && this.agent_online) {
                    return false;
                }
                SBF.ajax({
                    function: 'create-email',
                    recipient_id: recipent_id,
                    sender_name: admin ? SB_ACTIVE_AGENT['full_name'] : activeUser().name,
                    sender_profile_image: admin ? SB_ACTIVE_AGENT['profile_image'] : activeUser().image,
                    message: message,
                    attachments: attachments,
                    conversation_id: SBChat.conversation ? SBChat.conversation.id : false
                }, () => {
                    this.email_sent = true;
                    SBF.event('SBEmailSent', { recipient_id: recipent_id, message: message, attachments: attachments });
                });
            }
        },

        // Push notifications
        pushNotification: function (message) {
            SBF.ajax({
                function: 'push-notification',
                title: admin ? SB_ACTIVE_AGENT['full_name'] : activeUser().name,
                message: message,
                icon: admin ? SB_ACTIVE_AGENT['profile_image'] : (activeUser().image.indexOf('user.svg') > 0 ? CHAT_SETTINGS['notifications-icon'] : activeUser().image),
                interests: admin ? activeUser().id : (this.lastAgent(false) != false ? this.lastAgent(false)['user_id'] : (SBF.null(this.conversation.get('agent_id')) ? 'agents' : this.conversation.get('agent_id'))),
                conversation_id: this.conversation == false ? false : this.conversation.id
            });
        },

        // Desktop notifications
        desktopNotification: function (title, message, icon, conversation_id = false, user_id = false) {
            if (Notification.permission !== 'granted') {
                Notification.requestPermission();
            } else {
                let notify = new Notification(title, {
                    body: message,
                    icon: icon.indexOf('user.svg') > 0 ? CHAT_SETTINGS['notifications-icon'] : icon
                });
                notify.onclick = () => {
                    if (admin) {
                        if (conversation_id) {
                            SBAdmin.conversations.openConversation(conversation_id, user_id == false ? activeUser().id : user_id);
                        }
                    } else {
                        this.start();
                    }
                    window.focus();
                }
            }
        },

        // Editor submit message
        submit: function () {
            if (!this.is_busy) {
                if (activeUser() == false && !admin) {
                    this.addUserAndLogin(() => {
                        this.newConversation(2, -1, '', [], null, null, () => {
                            this.sendMessage();
                        });
                    });
                    this.busy(true);
                    return;
                } else {
                    this.sendMessage();
                }
            }
        },

        // Initialize the chat
        initChat: function () {
            SBF.getActiveUser(true, () => {
                let active = activeUser() !== false;
                let user_type = active ? activeUser().type : false;
                if (!admin && !tickets && CHAT_SETTINGS['popup'] && !storage('popup') && (!mobile || !CHAT_SETTINGS['popup-mobile-hidden'])) {
                    this.popup();
                }
                if (!admin && !tickets && CHAT_SETTINGS['privacy'] && !CHAT_SETTINGS['registration-required'] && !storage('privacy-approved')) {
                    this.privacy();
                    return;
                }
                if (typeof Notification !== ND && (CHAT_SETTINGS['desktop-notifications'] == 'all' || (!admin && CHAT_SETTINGS['desktop-notifications'] == 'users') || (admin && CHAT_SETTINGS['desktop-notifications'] == 'agents'))) {
                    this.desktop_notifications = true;
                }
                if (CHAT_SETTINGS['flash-notifications'] == 'all' || (!admin && CHAT_SETTINGS['flash-notifications'] == 'users') || (admin && CHAT_SETTINGS['flash-notifications'] == 'agents')) {
                    this.flash_notifications = true;
                }
                if (this.registration(true) && !tickets) {
                    this.registration();
                    return;
                }
                if (!active && (typeof SB_WP_WAITING_LIST !== ND || CHAT_SETTINGS['visitors-registration'] || CHAT_SETTINGS['welcome'] || CHAT_SETTINGS['subscribe'] || tickets) && (!tickets || !CHAT_SETTINGS['tickets-registration-required'])) {
                    this.addUserAndLogin(() => {
                        this.welcome();
                        this.subscribe();
                        SBApps.woocommerce.waitingList();
                        this.finalizeInit();
                    });
                } else if (this.conversation == false && active) {
                    this.populateConversations();
                } else {
                    this.finalizeInit();
                }
                if (CHAT_SETTINGS['header-name'] && active && user_type == 'user' && !tickets) {
                    $(chat_header).find('.sb-title').html(`${sb_('Hello')} ${activeUser().name}!`);
                }
                this.welcome();
                this.subscribe();
                SBApps.woocommerce.waitingList();
                setInterval(() => {
                    this.updateConversations();
                    this.updateUsersActivity();
                }, 10000);
                this.scrollBottom(true);
            });
        },

        finalizeInit: function () {
            if (!this.initialized) {
                $(main).attr('style', '');
                if (activeUser() != false) {
                    SBF.ajax({
                        function: 'current-url',
                        url: window.location.href
                    });
                }
                if (!admin && !tickets) {
                    if (this.isInitDashboard()) {
                        this.showDashboard();
                    }
                    if (!mobile && window.innerHeight < 760) {
                        $(main).find(' > .sb-body').css('max-height', (window.innerHeight - 130) + 'px');
                    }
                }
                this.initialized = true;
                if (!admin) {
                    if (storage('open-conversation') && activeUser() !== false && !this.registration(true)) {
                        this.openConversation(storage('open-conversation'));
                    }
                    if (!this.chat_open && storage('chat-open')) {
                        setTimeout(() => { this.start(); }, 500);
                    }
                    if (CHAT_SETTINGS['woocommerce-returning-visitor']) {
                        if (storage('returning-visitor') === false) {
                            SBF.storageTime('returning-visitor');
                        } else if (SBF.storageTime('returning-visitor', 24) && !storage('returning-visitor-processed')) {
                            setTimeout(() => {
                                SBF.ajax({
                                    function: 'woocommerce-returning-visitor'
                                }, () => {
                                    storage('returning-visitor-processed', true);
                                });
                            }, 15000);
                        }
                    }
                }
                SBF.event('SBInit');
            }
        },

        // Initialize the chat settings and open the chat
        start: function () {
            if (this.initialized) {
                this.populate();
                this.headerAgent();
                this.updateUsersActivity();
                this.startRealTime();
                this.chat_open = true;
                this.popup(true);
                if (this.conversation != false) {
                    this.updateNotifications('remove', this.conversation.id);
                }
                $(main).sbActivate();
                $('body').addClass('sb-chat-open');
                if (CHAT_SETTINGS['welcome-trigger'] == 'open') {
                    this.welcome();
                }
            }
        },

        // Open or close the chat
        open: function (open = true) {
            if (open && !this.chat_open) {
                this.start();
                this.chat_open = true;
                this.startRealTime();
                $(main).sbActivate();
                $('body').addClass('sb-chat-open');
                storage('chat-open', true);
                SBF.event('SBChatOpen');
            } else if (!open && this.chat_open) {
                $(main).sbActivate(false);
                this.stopRealTime();
                this.chat_open = false;
                storage('chat-open', false);
                $('body').removeClass('sb-chat-open');
                SBF.event('SBChatClose');
            }
        },

        // Get a full conversation and display it in the chat
        openConversation: function (conversation_id) {
            activeUser().getFullConversation(conversation_id, (response) => {
                if (response['id'] == '') {
                    storage('open-conversation', '');
                    return false;
                }
                this.setConversation(response);
                this.hideDashboard();
                this.populate();
                this.main_header = false;
                if ((this.chat_open || tickets) && response.get('conversation_status_code') != 0) {
                    this.updateNotifications('remove', conversation_id);
                }
                storage('open-conversation', conversation_id);
                SBF.event('SBConversationOpen', response);
            });
        },

        // Update the active conversation with the latest messages
        update: function () {
            if (this.conversation != false && !this.is_busy_update) {
                let last_message = this.conversation.getLastMessage();
                let is_update = false;
                SBF.ajax({
                    function: 'get-new-messages',
                    conversation_id: this.conversation.id,
                    datetime: this.datetime_last_message_conversation
                }, (response) => {
                    let count = response.length;
                    this.is_busy_update = false;
                    if (this.conversation != false) {
                        if (Array.isArray(response) && count > 0 && (!last_message || last_message.id != response[count - 1]['id'] || last_message.get('message') != response[count - 1]['message'])) {
                            let code = '';
                            let messages = [];
                            let id_check = [];

                            // Generate and add the new messages
                            for (var i = 0; i < count; i++) {
                                if (!id_check.includes(response[i]['id'])) {
                                    let message = new SBMessage(response[i]);
                                    let payload = message.payload();
                                    this.datetime_last_message_conversation = message.get('creation_time');

                                    // Payload
                                    if (typeof payload !== 'boolean' && 'event' in payload) {
                                        let event = payload['event'];
                                        if ((event == 'delete-message' && this.conversation.getMessage(message.id) !== false) || (!admin && message.get('message') == '' && !message.attachments.length)) {
                                            this.deleteMessage(message.id);
                                        }
                                        if (event == 'chips-click' && payload['id'] == 'sb-human-takeover' && dialogflow_human_takeover['confirm'] == payload['result']) {
                                            if (CHAT_SETTINGS['dialogflow-disable']) SBApps.dialogflow.active(false);
                                            SBApps.dialogflow.unknowEmail(this.conversation.getLastUserMessage().get('message'));
                                            this.setConversationStatus(2);
                                            SBF.storageTime('human-takeover');
                                            setTimeout(() => {
                                                if (dialogflow_human_takeover['email'] && SBF.null(activeUser().get('email'))) {
                                                    this.sendMessage(bot_id, `[email id="sb-human-takeover-email" message="${dialogflow_human_takeover['email-message']}" placeholder="${CHAT_SETTINGS['follow']['placeholder']}" name="${CHAT_SETTINGS['follow']['name']}" last-name="${CHAT_SETTINGS['follow']['last-name']}" success="${dialogflow_human_takeover['success']}"]`);
                                                } else if (dialogflow_human_takeover['message-confirmation'] != '') {
                                                    this.sendMessage(bot_id, dialogflow_human_takeover['success']);
                                                }
                                            }, 1100);
                                        }
                                        if (event == 'woocommerce-update-cart' && !admin) {
                                            SBApps.woocommerce.updateCart(payload['action'], payload['id']);
                                        }
                                    }

                                    // Message
                                    if (this.conversation.getMessage(response[i]['id']) == false) {
                                        this.conversation.addMessages(message);
                                        code += message.getCode();
                                    } else {
                                        this.conversation.updateMessage(message.id, message);
                                        $(chat).find(`[data-id="${message.id}"]`).replaceWith(message.getCode());
                                        is_update = true;
                                    }
                                    messages.push(message);
                                    id_check.push(message.id);
                                }
                            }
                            $(chat).append(code);

                            // Update status code
                            let last_message = this.conversation.getLastMessage();
                            let user_type = last_message.get('user_type');
                            if (!admin && this.chat_open && SBF.isAgent(user_type) && user_type != 'bot') {
                                if (last_message.get('message').indexOf('sb-rich-success') == -1) {
                                    this.setConversationStatus(0);
                                }
                                if (CHAT_SETTINGS['follow']) {
                                    clearTimeout(timeout);
                                }
                                SBApps.dialogflow.active(false);
                            }

                            // Miscellaneous
                            $(chat).find('[data-id="sending"]').remove();
                            this.headerAgent();
                            if (!this.dashboard && !is_update) {
                                this.scrollBottom();
                                setTimeout(() => {
                                    this.scrollBottom();
                                }, 300);
                            }
                            this.typing(-1, 'stop');
                            if ((!admin && this.chat_open && (SBF.isAgent(user_type) || user_type == 'bot') && ['aa', 'ia'].includes(CHAT_SETTINGS['chat-sound'])) || (admin && !SBF.isAgent(user_type) && ['a', 'i', 'ic'].includes(SB_ADMIN_SETTINGS['sounds']))) {
                                this.audio.play();
                            }
                            SBF.event('SBNewMessagesReceived', messages);
                        }
                    }
                });
                this.is_busy_update = true;
            }
        },

        // Update the user conversations list with the latest conversations and messages
        updateConversations: function () {
            if (activeUser() != false) {
                SBF.ajax({
                    function: 'get-new-user-conversations',
                    datetime: this.datetime_last_message
                }, (response) => {
                    if (response.length) {
                        this.datetime_last_message = response[0]['creation_time'];
                        for (var i = 0; i < response.length; i++) {
                            let conversation_id = response[i]['conversation_id'];
                            let message = new SBMessage(response[i]);
                            let status_code = response[i]['conversation_status_code'];
                            let conversation = new SBConversation([message], { id: conversation_id, conversation_status_code: status_code, department: response[i]['department'], title: response[i]['title'] });

                            // Add or update the conversation
                            let is_new = activeUser().addConversation(conversation);
                            if (this.conversation == false) {
                                this.conversation = conversation;
                            }

                            // Red notifications
                            if (response[i]['user_id'] != activeUser().id && (this.conversation.id != conversation_id || !this.chat_open)) {
                                this.updateNotifications('add', conversation_id);
                                if (CHAT_SETTINGS['auto-open']) {
                                    this.start();
                                }
                            }

                            // Payload
                            let payload = message.payload();
                            if (typeof payload !== 'boolean' && 'event' in payload) {
                                let event = payload['event'];
                                if (event == 'open-chat') {
                                    if (this.conversation.id != conversation_id || this.dashboard) {
                                        this.openConversation(conversation_id);
                                    }
                                    setTimeout(() => { this.open() }, 500);
                                }
                                if (message.get('message') == '' && !message.attachments.length) {
                                    continue;
                                }
                            }

                            if (!this.tab_active) {

                                // Desktop notifications
                                if (this.desktop_notifications) {
                                    SBChat.desktopNotification(message.get('full_name'), message.get('message'), message.get('profile_image'));
                                }

                                // Flash notificaitons
                                if (this.flash_notifications) {
                                    document.title = sb_('New message...');
                                }
                            }
                            if (is_new) {
                                SBF.event('SBNewConversationReceived', conversation);
                            }
                        }
                        if (['a', 'aa', 'i'].includes(CHAT_SETTINGS['chat-sound']) && (!this.chat_open || !this.tab_active || this.dashboard)) {
                            this.audio.play();
                        }
                        $(main).find('.sb-user-conversations').html(activeUser().getConversationsCode());
                    }
                });
            }
        },

        // Generate the conversation code and display it
        populate: function () {
            if (this.conversation != false) {
                let code = '';
                let notify = $(chat).find(' > .sb-notify-message');
                for (var i = 0; i < this.conversation.messages.length; i++) {
                    code += this.conversation.messages[i].getCode();
                }

                $(chat).html((notify.length ? $(notify)[0].outerHTML : '') + code);
                if (!this.dashboard) {
                    this.scrollBottom();

                }
            } else if (activeUser() != false && !activeUser().isConversationsEmpty()) {
                this.showDashboard();
            }
        },

        // Populate the dashboard with all the updated conversations
        populateConversations: function (onSuccess = false) {
            if (activeUser() != false) {
                activeUser().getConversations((response) => {
                    let count = response.length;
                    if (count) {
                        this.datetime_last_message = response[0]['messages'][0].get('creation_time');
                        for (var i = 0; i < count; i++) {
                            if (response[i].get('conversation_status_code') == 1) {
                                this.updateNotifications('add', response[i].id);
                            }
                        }
                        $(main).removeClass('sb-no-conversations');
                        $(main).find('.sb-user-conversations').html(activeUser().getConversationsCode());
                    }
                    if ((!this.initialized || force_action == 'open-conversation') && count == 1 && !this.isInitDashboard() && !storage('open-conversation')) {
                        this.openConversation(activeUser().conversations[0].id);
                        if (force_action == 'open-conversation') force_action = '';
                    }
                    if (onSuccess !== false) {
                        onSuccess(response);
                    }
                    this.finalizeInit();
                });
            }
        },

        // Create a new conversation and optionally send the first message
        newConversation: function (status_code, user_id = -1, message = '', attachments = [], department = null, agent_id = null, onSuccess = false) {
            if (activeUser() != false) {
                SBF.ajax({
                    function: 'new-conversation',
                    status_code: status_code,
                    title: tickets ? $(main).find('.sb-ticket-title input').val() : null,
                    department: SBF.null(department) ? this.default_department : department,
                    agent_id: agent_id,
                    routing: CHAT_SETTINGS['routing']
                }, (response) => {
                    if (SBF.errorValidation(response, 'user-not-found')) {
                        this.addUserAndLogin(() => {
                            this.newConversation(status_code, user_id, message, attachments, department, agent_id, onSuccess);
                        });
                        return;
                    }
                    let conversation = new SBConversation([], response['details']);
                    this.setConversation(conversation);
                    if (message != '' || attachments.length > 0) {
                        this.sendMessage(user_id, message, attachments, false, (tickets ? { 'skip-dialogflow': true } : false));
                    }
                    if (user_id != bot_id) {
                        this.queue(conversation.id);
                    }
                    if (onSuccess !== false) {
                        onSuccess(conversation);
                    }
                    SBF.event('SBNewConversationCreated', conversation);
                });
            } else {
                SBF.error('activeUser() not setted', 'SBChat.newConversation');
            }
        },

        // Set an existing conversation as active conversation
        setConversation: function (conversation) {
            if (conversation instanceof SBConversation) {
                this.conversation = conversation;
                this.datetime_last_message_conversation = this.conversation.getLastMessage() == false ? '2000-01-01 00:00:00' : this.conversation.getLastMessage().get('creation_time');
                if (conversation.id != this.conversation.id) {
                    this.queue(conversation.id);
                }
                storage('open-conversation', conversation.id);
                SBF.event('SBActiveConversationChanged', conversation);
            } else {
                SBF.error('Value not of type SBConversation', 'SBChat.setConversation');
            }
        },

        // Manage all the queue functionalities
        queue: function (conversation_id) {
            if (!admin && CHAT_SETTINGS['queue']) {
                SBF.ajax({
                    function: 'queue',
                    conversation_id: conversation_id,
                    department: this.conversation.get('department')
                }, (response) => {
                    $(chat).find(' > .sb-notify-message').remove();
                    if (response == 0) {
                        this.sendMessage(bot_id, sb_(CHAT_SETTINGS['queue-message-success'] == '' ? 'It\'s your turn! An agent will reply to you shortly.' : CHAT_SETTINGS['queue-message-success']));
                        $(main).removeClass('sb-notify-active');
                        clearInterval(this.queue_interval);
                        this.queue_interval = false;
                        storage('queue', '');
                    } else {
                        let time = (CHAT_SETTINGS['queue-response-time'] == '' ? 5 : parseInt(CHAT_SETTINGS['queue-response-time'])) * response;
                        let text = sb_(CHAT_SETTINGS['queue-message'] == '' ? 'Please wait for an agent. You are number {position} in the queue. Your waiting time is approximately {minutes} minutes.' : CHAT_SETTINGS['queue-message']).replace('{position}', '<b>' + response + '</b>').replace('{minutes}', '<b>' + time + '</b>');
                        $(chat).prepend(`<div class="sb-notify-message sb-rich-cnt"><div class="sb-cnt"><div class="sb-message">${text}</div></div></div>`);
                        if (this.queue_interval === false) {
                            this.queue_interval = setInterval(() => { this.queue(conversation_id) }, 10000);
                            $(main).addClass('sb-notify-active');
                            storage('queue', conversation_id);
                        }
                    }
                    SBF.event('SBQueueUpdate', response);
                });
            }
        },

        // Get the departments details and generate the department code
        getDepartmentCode(department_id, onSuccess) {
            if (this.departments != false) {
                if (department_id == 'all') {
                    let code = '';
                    for (var key in this.departments) {
                        this.getDepartmentCode(this.departments[key]['id'], (response) => { code += response; });
                    };
                    onSuccess(code);
                } else {
                    onSuccess(`<div data-color="${this.departments[department_id]['color']}">${this.departments[department_id]['image'] == '' ? `<span></span>` : `<img src="${this.departments[department_id]['image']}" />`}<div>${this.departments[department_id]['name']}<div></div>`);
                }
            } else {
                SBF.ajax({
                    function: 'get-departments'
                }, (response) => {
                    if (response != false) {
                        this.departments = response;
                        this.getDepartmentCode(department_id, onSuccess);
                    }
                });
            }
        },

        // Start and stop the real time check of new messages
        startRealTime: function () {
            this.stopRealTime();
            this.real_time = setInterval(() => {
                if (!admin || (admin && this.user_online)) {
                    this.update();
                }
                this.typing(admin ? (activeUser() != false ? activeUser().id : -1) : this.agent_id, 'check');
            }, 1000);
        },

        stopRealTime: function () {
            clearInterval(this.real_time);
        },

        // Check if the agent is online and set the online status of the active user
        updateUsersActivity: function () {
            if (activeUser() != false) {
                SBF.updateUsersActivity(activeUser().id, this.agent_id, (response) => {
                    if (!this.typing_settings['typing']) {
                        if (response == 'online' || this.agent_id == bot_id) {
                            $(chat_status).addClass('sb-status-online').html(sb_('Online'));
                            this.agent_online = true;
                        } else {
                            $(chat_status).removeClass('sb-status-online').html(sb_('Away'));
                            this.agent_online = false;
                        }
                    }
                });
            }
        },

        // Show the loading icon and put the chat in busy mode
        busy: function (value) {
            $(chat_editor).find('.sb-loader').sbActivate(value);
            this.is_busy = value;
            SBF.event('SBBusy', value);
        },

        // Display the top header of the agent and hide the initial one
        headerAgent: function () {
            if (!admin && !tickets && !this.dashboard && this.conversation != false && (this.agent_id == -1 || (this.conversation.getLastMessage() != false && SBF.isAgent(this.conversation.getLastMessage().get('user_type')) && this.conversation.getLastMessage().get('user_id') != this.agent_id))) {
                let agent = this.lastAgent();
                if (agent != false) {
                    this.agent_id = agent['user_id'];
                    this.headerReset();
                    $(chat_header).addClass('sb-header-agent').attr('data-agent-id', this.agent_id).html(`<div class="sb-dashboard-btn sb-icon-arrow-left"></div><div class="sb-profile"><img src="${agent['profile_image']}" /><div><span class="sb-name">${agent['full_name']}</span><span class="sb-status">${sb_('Away')}</span></div></div>`);
                    chat_status = $(chat_header).find('.sb-status');
                    this.updateUsersActivity();
                }
            }
        },

        headerReset: function () {
            if (this.start_header == false) {
                this.start_header = [$(chat_header).html(), $(chat_header).attr('class')];
            }
            $(chat_header).removeClass('sb-header-main sb-header-brand sb-header-agent');
            this.main_header = false;
        },

        // Return the last agent of the active conversation
        lastAgent: function (bot = true) {
            let agent = false;
            if (this.conversation != false) {
                for (var i = this.conversation.messages.length - 1; i > -1; i--) {
                    let user_type = this.conversation.messages[i].get('user_type');
                    if (SBF.isAgent(user_type) && (bot || (!bot && user_type != 'bot'))) {
                        agent = { user_id: this.conversation.messages[i].get('user_id'), full_name: this.conversation.messages[i].get('full_name'), profile_image: this.conversation.messages[i].get('profile_image') };
                        break;
                    }
                }
            }
            return agent;
        },

        // Scroll the chat to the bottom
        scrollBottom: function (top = false) {
            setTimeout(() => {
                $(chat_scroll_area).scrollTop(top ? 0 : $(chat_scroll_area)[0].scrollHeight);
                this.scrollHeader();
            }, 20);
        },

        // Dashboard header animation
        scrollHeader: function () {
            if (this.main_header && this.dashboard) {
                let scroll = $(chat_scroll_area).scrollTop();
                if (scroll > -1 && scroll < 1000) {
                    $(chat_header).find('.sb-content').css({ 'opacity': (1 - (scroll / 500)), 'top': (scroll / 10 * -1) + 'px' });
                };
            }
        },

        // Display the dashboard area 
        showDashboard: function () {
            if (!admin && !tickets) {
                $(main).addClass('sb-dashboard-active');
                $(chat_header).removeClass('sb-header-agent');
                this.hidePanel()
                if (this.start_header != false) {
                    $(chat_header).html(this.start_header[0]).addClass(this.start_header[1]);
                }
                $(chat_scroll_area).find(' > div').sbActivate(false);
                $(main).find('.sb-dashboard').sbActivate();
                this.populateConversations();
                this.conversation = false;
                this.agent_id = -1;
                this.stopRealTime();
                this.dashboard = true;
                this.main_header = true;
                this.scrollBottom(true);
                SBF.event('SBDashboard');
            }
        },

        // Hide the dashboard area
        hideDashboard: function () {
            if (!admin && !tickets) {
                $(chat).sbActivate();
                $(main).removeClass('sb-dashboard-active').find('.sb-dashboard').sbActivate(false);
                this.dashboard = false;
                this.headerAgent();
                this.scrollHeader(0);
                if (this.chat_open) {
                    this.startRealTime();
                }
                SBF.event('SBDashboardClosed');
            }
        },

        // Show a chat panel
        showPanel: function (name, title) {
            if (tickets) return SBTickets.showPanel(name, title);
            let panel = $(chat_scroll_area).find(' > .sb-panel-' + name);
            if (panel.length) {
                $(chat_scroll_area).find(' > div').sbActivate(false);
                $(panel).sbActivate();
                if (this.start_header == false) {
                    this.start_header = [$(chat_header).html(), $(chat_header).attr('class')];
                }
                $(chat_header).attr('class', 'sb-header sb-header-panel').html(`${sb_(title)}<div class="sb-dashboard-btn sb-icon-close"></div>`);
                $(main).addClass('sb-panel-active');
                this.dashboard = true;
            }
            SBF.event('SBPanelActive', name);
        },

        hidePanel: function () {
            $(main).removeClass('sb-panel-active');
            $(chat_header).removeClass('sb-header-panel');
        },

        // Clear the conversation area and the active conversation
        clear: function () {
            this.conversation = false;
            $(chat).html('');
        },

        // Update the red notification counter of the chat
        updateNotifications: function (action = 'add', conversation_id) {
            if (action == 'add' && !this.notifications.includes(conversation_id)) {
                this.notifications.push(conversation_id);
            }
            if (action == 'remove') {
                for (var i = 0; i < this.notifications.length; i++) {
                    if (this.notifications[i] == conversation_id) {
                        this.notifications.splice(i, 1);
                        if (['1', '2', 1, 2].includes(this.conversation.get('conversation_status_code'))) {
                            this.setConversationStatus(0);
                        }
                        break;
                    }
                }
            }
            let count = this.notifications.length;
            $(main).find('.sb-chat-btn span').attr('data-count', count).html(count > -1 ? count : 0);
            SBF.event('SBNotificationsUpdate', { action: action, conversation_id: conversation_id });
        },

        // Set the active conversation status
        setConversationStatus: function (status_code) {
            if (this.conversation != false) {
                SBF.ajax({
                    function: 'update-conversation-status',
                    conversation_id: this.conversation.id,
                    status_code: status_code
                }, () => {
                    this.conversation.set('conversation_status_code', status_code);
                    SBF.event('SBActiveConversationStatusUpdated', { conversation_id: this.conversation.id, status_code: status_code });
                });
                return true;
            }
            return false;
        },

        // Typing status
        typing: function (user_id = -1, action = 'check') {
            if (this.conversation != false) {
                if (action == 'check' && user_id != -1 && user_id != bot_id && (this.agent_online || (admin && this.user_online))) {
                    SBF.ajax({
                        function: 'is-typing',
                        user_id: user_id,
                        conversation_id: this.conversation.id
                    }, (response) => {
                        if (response && !this.typing_settings['typing']) {
                            this.typing(-1, 'start');
                        } else if (!response && this.typing_settings['typing']) {
                            this.typing(-1, 'stop');
                        }
                    });
                } else if (action == 'set') {
                    if (!this.typing_settings['sent']) {
                        this.typing_settings['sent'] = true;
                        SBF.ajax({
                            function: 'set-typing',
                            user_id: user_id,
                            conversation_id: this.conversation.id
                        });
                        this.typing(user_id, 'set');
                    } else {
                        clearTimeout(this.typing_settings['timeout']);
                        this.typing_settings['timeout'] = setTimeout(() => {
                            SBF.ajax({
                                function: 'set-typing',
                                user_id: user_id,
                                conversation_id: -1
                            }, () => {
                                this.typing_settings['sent'] = false;
                            });
                        }, 2000);
                    }
                } else if (action == 'start' || action == 'stop') {
                    let start = action == 'start';
                    if (!admin) {
                        if (start) {
                            $(chat_status).addClass('sb-status-typing').html(sb_('Typing'));
                        } else {
                            $(chat_status).removeClass('sb-status-typing').addClass('sb-status-online').html(sb_('Online'));
                        }
                    }
                    this.typing_settings['typing'] = start;
                    SBF.event('SBTyping', start);
                }
            }
        },

        // Articles
        showArticles: function (id = -1) {
            let panel = tickets ? $(main).find('.sb-panel-main .sb-panel') : $(chat_scroll_area).find(' > .sb-panel-articles');
            let code = '';
            $(panel).html('').sbLoading(true);
            this.showPanel('articles', CHAT_SETTINGS['articles-title'] == '' ? 'Help Center' : CHAT_SETTINGS['articles-title']);
            this.getArticles(id, (articles) => {
                if (id == -1) {
                    for (var i = 0; i < articles.length; i++) {
                        code += `<div data-id="${articles[i]['id']}"><div>${articles[i]['title']}</div><span>${articles[i]['content']}</span></div>`;
                    }
                    $(panel).html(`<div class="sb-articles">${code}</div>`);
                } else {
                    if (articles !== false) {
                        $(panel).html(`<div data-id="${articles['id']}" class="sb-article"><div class="sb-title">${articles['title']}</div><div class="sb-content">${articles['content'].replace(/(?:\r\n|\r|\n)/g, '<br>')}</div>${articles['link'] == '' ? '' : `<a href="${articles['link']}" target="_blank" class="sb-btn-text"><i class="sb-icon-plane"></i>${sb_('Read more')}</a>`}</div>`);
                    }
                }
                $(panel).sbLoading(false);
                SBF.event('SBArticles', { id: id, articles: articles });
            });
        },

        getArticles: function (id = -1, onSuccess = false) {
            if (this.articles === false || id != -1) {
                SBF.ajax({
                    function: 'get-articles',
                    id: id
                }, (response) => {
                    if (id == -1) {
                        this.articles = response;
                    }
                    if (onSuccess === false) {
                        return response;
                    } else {
                        onSuccess(response);
                    }
                });
            } else {
                if (onSuccess === false) {
                    return this.articles;
                } else {
                    onSuccess(this.articles);
                }
            }
        },

        searchArticles: function (search, btn) {
            if (search != '') {
                $(btn).sbLoading(true);
                SBF.ajax({
                    function: 'search-articles',
                    search: search
                }, (articles) => {
                    let code = '';
                    let count = articles.length;
                    if (count == 0) {
                        code += `<p class="sb-no-results">${sb_('No articles found.')}</p>`;
                    } else {
                        for (var i = 0; i < articles.length; i++) {
                            code += `<div data-id="${articles[i]['id']}"><div>${articles[i]['title']}</div><span>${articles[i]['content']}</span></div>`;
                        }
                    }
                    $(main).find('.sb-dashboard-articles .sb-articles').html(code);
                    $(btn).sbLoading(false);
                });
            }
        },

        // Emoji methods
        categoryEmoji: function (category) {
            let list = this.emoji_options['list'];
            if (category == 'all') {
                this.emoji_options['list_now'] = list;
            } else {
                this.emoji_options['list_now'] = [];
                for (var i = 0; i < list.length; i++) {
                    if (list[i]['category'].startsWith(category)) {
                        this.emoji_options['list_now'].push(list[i]);
                    }
                }
            }
            this.emoji_options['range'] = 0;
            this.populateEmoji(0);
            this.populateEmojiBar();
        },

        mouseWheelEmoji: function (e) {
            let range = this.emoji_options['range'];
            if (sbDelta(e) > 0 || (mobile && typeof e.originalEvent.changedTouches !== ND && this.emoji_options['touch'] < e.originalEvent.changedTouches[0].clientY)) {
                range -= (range < 1 ? 0 : 1);
            } else {
                range += (range > this.emoji_options['range_limit'] ? 0 : 1);
            }
            $(chat_emoji).find('.sb-emoji-bar > div').sbActivate(false).eq(range).sbActivate();
            this.emoji_options['range'] = range;
            this.populateEmoji(range);
            e.preventDefault();
        },

        insertEmoji: function (emoji) {
            if (emoji.indexOf('.svg') > 0) {
                emoji = $.parseHTML(emoji)[0]['alt'];
            }
            this.insertText(emoji);
            $(chat_emoji).sbTogglePopup();
        },

        showEmoji: function (button) {
            if ($(chat_emoji).sbTogglePopup(button)) {
                if (!admin) {
                    $(chat_emoji).css({ left: $(chat_editor).offset().left + (tickets ? 68 : 20), top: $(chat_editor).offset().top - window.scrollY - (tickets ? $(chat_editor).height() - 330 : 304) });
                }
                if ($(chat_emoji).find('.sb-emoji-list > ul').html() == '') {
                    jQuery.ajax({
                        method: 'POST',
                        url: SB_AJAX_URL,
                        data: {
                            function: 'emoji'
                        }
                    }).done((response) => {
                        this.emoji_options['list'] = JSON.parse(response);
                        this.emoji_options['list_now'] = this.emoji_options['list'];
                        this.populateEmoji(0);
                        this.populateEmojiBar();
                    });
                }
                SBF.deselectAll();
            }
        },

        populateEmoji: function (range) {
            let code = '';
            let per_page = mobile ? 42 : 48;
            let limit = range * per_page + per_page;
            let list_now = this.emoji_options['list_now'];
            if (limit > list_now.length) limit = list_now.length;
            this.emoji_options['range_limit'] = list_now.length / per_page - 1;
            this.emoji_options['range'] = range;
            for (var i = (range * per_page); i < limit; i++) {
                code += `<li>${list_now[i]['char']}</li>`;
            }
            $(chat_emoji).find('.sb-emoji-list').html(`<ul>${code}</ul>`);
        },

        populateEmojiBar: function () {
            let code = '<div class="sb-active"></div>';
            let per_page = mobile ? 42 : 49;
            for (var i = 0; i < this.emoji_options['list_now'].length / per_page - 1; i++) {
                code += '<div></div>';
            }
            this.emoji_options['range'] = 0;
            $(chat_emoji).find('.sb-emoji-bar').html(code);
        },

        clickEmojiBar: function (item) {
            let range = $(item).index();
            this.populateEmoji(range);
            this.emoji_options['range'] = range;
            $(chat_emoji).find('.sb-emoji-bar > div').sbActivate(false).eq(range).sbActivate();
        },

        searchEmoji: function (search) {
            SBF.search(search, () => {
                if (search.length > 1) {
                    let list = this.emoji_options['list'];
                    let list_now = [];
                    for (var i = 0; i < list.length; i++) {
                        if (list[i]['category'].toLowerCase().indexOf(search) > -1 || list[i]['name'].toLowerCase().indexOf(search) > -1) {
                            list_now.push(list[i]);
                        }
                    }
                    this.emoji_options['list_now'] = list_now;
                } else {
                    this.emoji_options['list_now'] = this.emoji_options['list'];
                }
                this.emoji_options['range'] = 0;
                this.populateEmoji(0);
                this.populateEmojiBar();
            });
        },

        // Editor methods
        textareaChange: function (textarea) {
            let value = $(textarea).val();

            // Saved replies
            if (admin) {
                SBAdmin.conversations.savedReplies(textarea, value);
            }

            // Typing
            if (value != '') {
                this.typing((admin ? SB_ACTIVE_AGENT['id'] : activeUser().id), 'set');
                this.activateBar();
            } else {
                this.activateBar(false);
            }
        },

        insertText: function (text) {
            let textarea = $(chat_textarea).get(0);
            let index = 0;
            if (this.dashboard) return false;
            if ('selectionStart' in textarea) {
                index = textarea.selectionStart;
            } else if ('selection' in document) {
                textarea.focus();
                let selection = document.selection.createRange();
                var selection_length = document.selection.createRange().text.length;
                selection.moveStart('character', -textarea.value.length);
                index = selection.text.length - selection_length;
            }
            $(textarea).val($(textarea).val().substr(0, index) + text + $(textarea).val().substr(index));
            $(textarea).focus();
            $(textarea).manualExpandTextarea();
            this.activateBar();
        },

        enabledAutoExpand: function () {
            if (chat_textarea.length) $(chat_textarea).autoExpandTextarea();
        },

        // Privacy message
        privacy: function () {
            SBF.ajax({
                function: 'get-block-setting',
                value: 'privacy'
            }, (response) => {
                $(chat_scroll_area).append(`<div class="sb-privacy sb-init-form" data-decline="${response['decline']}"><div class="sb-title">${response['title']}</div><div class="sb-text">${response['message']}</div>` + (response['link'] != '' ? `<a target="_blank" href="${response['link']}">${response['link-name']}</a>` : '') + `<div class="sb-buttons"><a class="sb-btn sb-approve">${response['btn-approve']}</a><a class="sb-btn sb-decline">${response['btn-decline']}</a></div></div>`);
                this.finalizeInit();
                SBF.event('SBPrivacy');
            });
            if (!this.dashboard) {
                this.showDashboard();
            }
            this.dashboard = true;
            $(main).addClass('sb-init-form-active');
        },

        // Popup message 
        popup: function (close = false, content = false) {
            let popup = $(main).find('.sb-popup-message');
            if (popup.length) {
                $(popup).remove();
            }
            if (close) {
                storage('popup', true);
                return;
            }
            if (!this.chat_open) {
                if (content == false) {
                    SBF.ajax({
                        function: 'get-block-setting',
                        value: 'popup'
                    }, (response) => {
                        setTimeout(() => {
                            if (!this.chat_open) {
                                this.popup(close, response);
                            }
                        }, 1000);
                    });
                } else {
                    $(main).append(`<div class="sb-popup-message">` + ('image' in content && content['image'] != '' ? `<img src="${content['image']}" />` : '') + ('title' in content && content['title'] != '' ? `<div class="sb-top">${content['title']}</div>` : '') + `<div class="sb-text">${content['message']}</div><div class="sb-icon-close"></div></div>`);
                    SBF.event('SBPopup', content);
                }
            }
        },

        // Follow up message
        followUp: function () {
            if (this.followUpCheck()) {
                if (timeout != false) {
                    clearTimeout(timeout);
                }
                timeout = setTimeout(() => {
                    if (this.followUpCheck()) {
                        let settings = CHAT_SETTINGS['follow'];
                        this.sendMessage(bot_id, `[email title="${settings['title']}" message="${settings['message']}" placeholder="${settings['placeholder']}" name="${settings['name']}" last-name="${settings['last-name']}" success="${settings['success']}"]`);
                        this.scrollBottom();
                        SBF.storageTime('email');
                        SBF.event('SBFollowUp');
                    }
                }, SBF.null(CHAT_SETTINGS['follow']['follow-delay']) ? (CHAT_SETTINGS['office-hours'] || agents_online ? 15000 : (SBApps.dialogflow.active() ? 8000 : 5000)) : parseInt(CHAT_SETTINGS['follow-delay']));
            }
        },

        followUpCheck: function () {
            return !admin && this.conversation != false && activeUser() != false && activeUser().get('email') == '' && SBF.storageTime('email', 24);
        },

        // Welcome message
        welcome: function () {
            if ((CHAT_SETTINGS['welcome-trigger'] != 'open' || this.chat_open) && CHAT_SETTINGS['welcome'] && !storage('welcome') && activeUser()) {
                SBF.ajax({
                    function: 'get-block-setting',
                    value: 'welcome'
                }, (response) => {
                    setTimeout(() => {
                        if (CHAT_SETTINGS['dialogflow-welcome']) {
                            if (this.conversation === false) {
                                this.newConversation(3, -1, '', [], null, null, function () { SBApps.dialogflow.welcome() });
                            } else {
                                SBApps.dialogflow.welcome();
                            }
                        } else {
                            this.sendMessage(bot_id, response['message'], [], false, false, 3);
                        }
                        if (response['open']) {
                            this.start();
                        }
                        if (response['sound']) {
                            this.audio.play();
                        }
                        this.skip = true;
                        SBF.event('SBWelcomeMessage');
                    }, parseInt(CHAT_SETTINGS['welcome-delay']));
                    storage('welcome', true);
                });
            }
        },

        // Subscribe message
        subscribe: function () {
            if (CHAT_SETTINGS['subscribe'] && !storage('subscribe') && activeUser() != false && SBF.null(activeUser().get('email')) && activeUser()) {
                setTimeout(() => {
                    SBF.ajax({
                        function: 'get-block-setting',
                        value: 'subscribe'
                    }, (response) => {
                        this.sendMessage(bot_id, response['message'], [], false, { event: 'open-chat' }, -1);
                        if (response['sound']) {
                            this.audio.play();
                        }
                        this.skip = true;
                        storage('subscribe', true);
                    });
                }, parseInt(CHAT_SETTINGS['subscribe-delay']));
            }
        },

        // Send Slack message
        slackMessage: function (user_id, full_name, profile_image, message, attachments = []) {
            if (this.conversation == false) return false;
            let conversation_id = this.conversation.id;
            SBF.ajax({
                function: 'send-slack-message',
                user_id: user_id,
                full_name: full_name,
                profile_image: profile_image,
                conversation_id: conversation_id,
                message: message,
                attachments: attachments,
                channel: this.slack_channel[0] == activeUser().id ? this.slack_channel[1] : -1
            }, (response) => {
                this.slack_channel = [activeUser().id, response[1]];
                SBF.event('SBSlackMessageSent', { message: message, conversation_id: conversation_id, slack_channel: response[1] });
            });
        },

        // Delete message
        deleteMessage: function (message_id) {
            SBF.ajax({
                function: 'delete-message',
                message_id: message_id
            }, () => {
                this.conversation.deleteMessage(message_id);
                $(chat).find(`[data-id="${message_id}"]`).remove();
                SBF.event('SBMessageDeleted', message_id);
            });
        },

        // Registration form
        registration: function (check = false, type = CHAT_SETTINGS['registration-required']) {
            if (check) return CHAT_SETTINGS['registration-required'] != '' && (!CHAT_SETTINGS['registration-offline'] || !agents_online) && (!CHAT_SETTINGS['registration-timetable'] || !CHAT_SETTINGS['office-hours']) && (activeUser() === false || ['visitor', 'lead'].includes(activeUser().type));
            $(chat_scroll_area).append(SBRichMessages.generate({}, CHAT_SETTINGS['registration-link'] != '' ? 'login' : type, 'sb-init-form'));
            if (!this.dashboard) {
                this.showDashboard();
            }
            this.dashboard = true;
            this.finalizeInit();
            $(main).addClass('sb-init-form-active');
        },

        // Display the send button
        activateBar: function (show = true) {
            $(chat_editor).find('.sb-bar').sbActivate(show);
        },

        // Shortcut for add user and login function
        addUserAndLogin: function (onSuccess = false) {
            SBF.ajax({
                function: 'add-user-and-login',
                login_app: SBApps.login()
            }, (response) => {
                SBF.loginCookie(response[1]);
                activeUser(new SBUser(response[0]));
                if (onSuccess !== false) {
                    onSuccess(response);
                }
            });
        },

        // Check if the dashboard must be showed
        isInitDashboard: function () {
            return CHAT_SETTINGS['init-dashboard'] || (activeUser() != false && activeUser().conversations.length > 1);
        },

        // Upload response
        uploadResponse: function (response) {
            response = JSON.parse(response);
            if (response[0] == 'success') {
                if (response[1] == 'extension_error') {
                    SBF.dialog('The file you are trying to upload has an extension that is not allowed.', 'info');
                } else if ($(upload_target).hasClass('sb-input-image')) {
                    $(upload_target).find('.image').attr('data-value', '').css('background-image', '');
                    setTimeout(() => {
                        $(upload_target).find('.image').attr('data-value', response[1]).css('background-image', `url("${response[1]}?v=${SBF.random()}")`).append('<i class="sb-icon-close"></i>');
                        upload_target = false;
                    }, 500);
                } else {
                    let name = response[1].substr(response[1].lastIndexOf('/') + 1);
                    $(chat_editor).find('.sb-attachments').append(`<div data-name="${name}" data-value="${response[1]}">${name}<i class="sb-icon-close"></i></div>`);
                    SBChat.activateBar();
                }
            } else {
                SBF.error(response[1], 'sb-upload-files.change');
            }
            this.busy(false);
        }
    }
    window.SBChat = SBChat;

    /* 
    * ----------------------------------------------------------
    * # RICH MESSAGES
    * ----------------------------------------------------------
    */

    var SBRichMessages = {
        rich_messsages: {
            'email': '',
            'button': '',
            'video': '',
            'image': '',
            'woocommerce-button': '',
            'chips': '<div class="sb-buttons">[options]</div>',
            'buttons': '<div class="sb-buttons">[options]</div>',
            'select': '<div class="sb-select"><p></p><ul>[options]</ul></div>',
            'list': '<div class="sb-text-list">[values]</div>',
            'list-image': '<div class="sb-image-list">[values]</div>',
            'table': '<table><tbody>[header][values]</tbody></table>',
            'inputs': '<div class="sb-form">[values]</div>',
            'rating': `<div class="sb-rating"><div class="sb-input sb-input-textarea"><textarea></textarea></div><div><div data-rating="positive" class="sb-submit"><i class="sb-icon-like"></i>${sb_('Good')}</div><div data-rating="negative" class="sb-submit"><i class="sb-icon-dislike"></i>${sb_('Bad')}</div></div></div>`,
            'card': '<div class="sb-card">[settings]</div>',
            'share': '<div class="sb-social-buttons">[settings]</div>',
            'slider': '<div class="sb-slider"><div>[items]</div></div><div class="sb-slider-arrow sb-icon-arrow-left[class]"></div><div class="sb-slider-arrow sb-icon-arrow-right sb-active[class]"></div>',
            'slider-images': '<div class="sb-slider sb-slider-images"><div>[items]</div></div><div class="sb-slider-arrow sb-icon-arrow-left[class]"></div><div class="sb-slider-arrow sb-icon-arrow-right sb-active[class]"></div>'
        },
        cache: {},

        // Generate a rich message
        generate: function (settings, name, css = '') {
            let content;
            let next = true;
            let id = 'id' in settings ? settings['id'] : SBF.random();

            // Check if the rich message exist
            if (name in this.rich_messsages) {
                content = this.rich_messsages[name];
            } else if (name in this.cache) {
                content = this.cache[name];
            } else if (CHAT_SETTINGS['rich-messages'].includes(name)) {
                content = '<div class="sb-rich-loading sb-loading"></div>';
                SBF.ajax({
                    function: 'get-rich-message',
                    name: name
                }, (response) => {
                    response = this.initInputs(response);
                    if (name == 'timetable') response = this.timetable(response);
                    $(main).find(`.sb-rich-message[id="${id}"]`).html(`<div class="sb-content">${response}</div>`);
                    this.cache[name] = response;
                    SBChat.scrollBottom(SBChat.dashboard);
                });
                next = false;
            } else {
                return false;
            }

            // Generate the rich message
            if (next) {
                let options;
                let code = '';
                switch (name) {
                    case 'email':
                        let user_name = 'last-name' in settings ? activeUser().get('first_name') : activeUser().name;
                        let last_name = activeUser().get('last_name');
                        let email = activeUser().get('email');
                        if (last_name.charAt(0) == '#') {
                            user_name = last_name = '';
                        }
                        content = ('name' in settings && settings['name'] == 'true' ? `<div id="first_name" data-type="text" class="sb-input sb-input-text"><span class="${user_name == '' ? '' : 'sb-active sb-filled'}">${sb_('last-name' in settings && settings['last-name'] == 'true' ? 'First name' : 'Name')}</span><input value="${user_name}" autocomplete="false" type="text" required></div>` : '') + ('last-name' in settings && settings['last-name'] == 'true' ? `<div id="last_name" data-type="text" class="sb-input sb-input-text"><span class="${last_name == '' ? '' : 'sb-active sb-filled'}">${sb_('Last name')}</span><input value="${last_name}" autocomplete="false" type="text" required></div>` : '') + `<div id="email" data-type="email" class="sb-input sb-input-btn"><span class="${email == '' ? '' : 'sb-active sb-filled'}">${sb_('placeholder' in settings ? settings['placeholder'] : 'Email')}</span><input value="${email}" autocomplete="off" type="email" required><div class="sb-submit sb-icon-arrow-right"></div></div>`;
                        break;
                    case 'image':
                        content = `<div class="sb-image"><img src="${settings['url']}"></div>`;
                        break;
                    case 'video':
                        content = `<iframe${'height' in settings ? ` height="${settings['height']}"` : ''} src="${settings['type'] == 'youtube' ? '//www.youtube.com/embed/' : '//player.vimeo.com/video/'}${settings['id']}" allowfullscreen></iframe>`;
                        break;
                    case 'select':
                        options = settings['options'].split(',');
                        for (var i = 0; i < options.length; i++) {
                            code += `<li data-value="${SBF.stringToSlug(options[i])}">${sb_(options[i])}</li>`;
                        }
                        content = content.replace('[options]', code);
                        break;
                    case 'chips':
                    case 'buttons':
                        options = settings['options'].split(',');
                        for (var i = 0; i < options.length; i++) {
                            code += `<div class="sb-btn sb-submit">${sb_(options[i])}</div>`;
                        }
                        content = content.replace('[options]', code);
                        break;
                    case 'button':
                        content = `<a href="${settings['link'].replace(/<i>/g, '_').replace(/<\/i>/g, '_')}"${'target' in settings ? ' target="_blank"' : ''} class="sb-rich-btn sb-btn${settings['style'] == 'link' ? '-text' : ''}">${sb_(settings['name'])}</a>`;
                        break;
                    case 'list':
                        options = settings['values'].split(',');
                        let list = name == 'list';
                        let list_double = list && options.length && options[0].indexOf(':') > 0;
                        if (list && !list_double) {
                            content = content.replace('sb-text-list', 'sb-text-list sb-text-list-single');
                        }
                        for (var i = 0; i < options.length; i++) {
                            code += list_double ? `<div><div>${sb_(options[i].split(':')[0])}</div><div>${sb_(options[i].split(':')[1])}</div></div>` : `<div>${$.trim(sb_(options[i]))}</div>`;
                        }
                        content = content.replace('[values]', code);
                        break;
                    case 'list-image':
                        options = settings['values'].split(',');
                        for (var i = 0; i < options.length; i++) {
                            let item = options[i].replace('://', '///').split(':');
                            code += `<div><div class="sb-thumb" style="background-image:url('${item[0].replace('///', '://')}')"></div><div class="sb-list-title">${item[1]}</div><div>${item[2]}</div></div>`;
                        }
                        content = content.replace('[values]', code);
                        break;
                    case 'table':
                        options = settings['header'].split(',');
                        code += '<tr>';
                        for (var i = 0; i < options.length; i++) {
                            code += `<th>${options[i]}</th>`;
                        }
                        code += '</tr>';
                        content = content.replace('[header]', code);
                        code = '';
                        options = settings['values'].split(',');
                        for (var i = 0; i < options.length; i++) {
                            let tds = options[i].split(':');
                            code += '<tr>';
                            for (var i = 0; i < tds.length; i++) {
                                code += `<td>${tds[i]}</td>`;
                            }
                            code += '</tr>';
                        }
                        content = content.replace('[values]', code);
                        break;
                    case 'inputs':
                        options = settings['values'].split(',');
                        for (var i = 0; i < options.length; i++) {
                            code += `<div id="${SBF.stringToSlug(options[i])}" data-type="text" class="sb-input sb-input-text"><span>${sb_(options[i])}</span><input autocomplete="false" type="text" required></div>`;
                        }
                        code += '<div class="sb-btn sb-submit">' + sb_('button' in settings ? settings['button'] : 'Send now') + '</div>';
                        content = content.replace('[values]', code);
                        break;
                    case 'card':
                        code = `${'image' in settings ? `<div class="sb-card-img" style="background-image:url(${settings['image']})"></div>` : ''}<div class="sb-card-header">${settings['header']}</div>${'extra' in settings ? `<div class="sb-card-extra">${settings['extra']}</div>` : ''}${'description' in settings ? `<div class="sb-card-description">${settings['description']}</div>` : ''}${'link' in settings ? `<a class="sb-card-btn" href="${settings['link']}">${sb_(settings['link-text'])}</a>` : ''}`;
                        content = content.replace('[settings]', code);
                        break;
                    case 'share':
                        let channels = 'channels' in settings ? settings['channels'].split(',') : ['fb', 'tw', 'li', 'wa', 'pi'];
                        let link = '';
                        for (var i = 0; i < channels.length; i++) {
                            let channel = channels[i];
                            switch (channel) {
                                case 'fb':
                                    link = 'www.facebook.com/sharer.php?u=';
                                    break;
                                case 'tw':
                                    link = 'twitter.com/intent/tweet?url=';
                                    break;
                                case 'li':
                                    link = 'www.linkedin.com/sharing/share-offsite/?url=';
                                    break;
                                case 'wa':
                                    link = 'web.whatsapp.com/send?text=';
                                    break;
                                case 'pi':
                                    link = 'www.pinterest.com/pin/create/button/?url=';
                                    break;
                            }
                            code += `<div class="sb-${channel} sb-icon-social-${channel}" data-link="https://${link}${settings['link']}"></div>`;
                        }
                        content = content.replace('[settings]', code);
                        break;
                    case 'slider':
                        let count = 0;
                        for (var i = 1; i < 16; i++) {
                            if (('header-' + i) in settings) {
                                code += `<div>${('image-' + i) in settings ? `<div class="sb-card-img" style="background-image:url(${settings['image-' + i]})"></div>` : ''}<div class="sb-card-header">${settings['header-' + i]}</div>${('extra-' + i) in settings ? `<div class="sb-card-extra">${settings['extra-' + i]}</div>` : ''}${('description-' + i) in settings ? `<div class="sb-card-description">${settings['description-' + i]}</div>` : ''}${('link-' + i) in settings ? `<a class="sb-card-btn" href="${settings['link-' + i]}">${sb_(settings['link-text-' + i])}</a>` : ''}</div>`;
                                count++;
                            } else {
                                break;
                            }
                        }
                        content = content.replace('[items]', code).replace(/\[class\]/g, count == 1 ? ' sb-hide' : '');
                        break;
                    case 'slider-images':
                        if ('images' in settings) {
                            let images = settings['images'].split(',');
                            for (var i = 0; i < images.length; i++) {
                                code += `<div class="sb-card-img" data-value="${images[i]}" style="background-image:url(${images[i]})"></div>`;
                            }
                            content = content.replace(/\[class\]/g, images.length == 1 ? ' sb-hide' : '');
                        }
                        content = content.replace('[items]', code);
                        break;
                    case 'woocommerce-button':
                        settings['settings'] = `checkout:${settings['checkout']},coupon:${settings['coupon']}`;
                        content = `<a href="#" data-ids="${settings['ids']}" class="sb-rich-btn sb-btn">${settings['name']}</a>`;
                        break;
                }
            }
            return `<div id="${id}" data-type="${name}"${'disabled' in settings ? 'disabled="true"' : ''}${'settings' in settings ? ` data-settings="${settings['settings']}"` : ''}class="sb-rich-message sb-rich-${name} ${css}">` + ('title' in settings ? `<div class="sb-top">${sb_(settings['title'])}</div>` : '') + ('message' in settings ? `<div class="sb-text">${sb_(settings['message'])}</div>` : '') + `<div class="sb-content">${content}</div><div data-success="${'success' in settings ? settings['success'].replace(/"/g, '') : ''}" class="sb-info"></div></div>`;
        },

        // Function of built-in rich messages
        submit: function (area, type, element) {
            if (!admin && !$(element).sbLoading() && !this.is_busy) {
                let error = '';
                let shortcode = '';
                let parameters = {};
                let success = $(area).find('[data-success]').length ? $(area).find('[data-success]').attr('data-success') : '';
                let rich_message_id = $(area).closest('.sb-rich-message').attr('id');
                let message_id = $(area).closest('[data-id]').attr('data-id');
                let message = '';
                let payload = { 'rich-messages': {} };
                let user_settings = activeUser() == false ? { 'profile_image': ['', ''], 'first_name': ['', ''], 'last_name': ['', ''], 'email': ['', ''], 'password': ['', ''], 'user_type': ['', ''] } : { 'profile_image': [activeUser().image, ''], 'first_name': [activeUser().get('first_name'), ''], 'last_name': [activeUser().get('last_name'), ''], 'email': [activeUser().get('email'), ''], 'password': ['', ''], 'user_type': ['', ''] };
                let settings = {};
                let input = element;
                let dialogflow_response = '';
                let active_conversation = SBChat.conversation !== false;

                if (SBF.null(message_id)) {
                    message_id = -1;
                } else {
                    let item = SBChat.conversation.getMessage(message_id);
                    message = item.get('message');
                    payload = item.payload();
                    if (!('rich-messages' in payload)) {
                        payload['rich-messages'] = {};
                    }
                }
                if (!$(element).hasClass('sb-btn') && !$(element).hasClass('sb-select') && !$(element).hasClass('sb-submit')) {
                    input = $(element).closest('.sb-btn,.sb-select');
                }
                $(input).sbLoading(true);
                $(area).find('.sb-info').html('').sbActivate(false);

                switch (type) {
                    case 'email':
                        settings = SBForm.getAll(area);
                        if ('first_name' in settings) {
                            user_settings['user_type'] = ['user', ''];
                            if (!('last_name' in settings)) {
                                user_settings['last_name'] = ['', ''];
                            }
                        }
                        $.extend(user_settings, settings);
                        error = 'Please insert a valid email.' + ('first_name' in settings ? '' : ' All fields are required.');
                        success = success == '' ? success : sb_(success) + ` *${user_settings['email'][0]}*`;
                        payload['rich-messages'][rich_message_id] = { type: type, result: settings };
                        payload['event'] = 'update-user';
                        parameters = { function: 'update-user-and-message', settings: user_settings, payload: payload };
                        dialogflow_response = 'email-complete';
                        break;
                    case 'chips':
                    case 'select':
                    case 'buttons':
                        settings = SBF.escape($(element).html());
                        success = success == '' ? success : sb_(success) + ` *${settings}*`;
                        payload['rich-messages'][rich_message_id] = { type: type, result: settings };
                        parameters = { function: 'update-message', payload: payload };
                        dialogflow_response = settings;
                        if (type == 'chips') {
                            SBChat.sendMessage(activeUser().id, settings, [], false, { id: rich_message_id, event: 'chips-click', result: settings });
                            $(element).closest('.sb-content').remove();
                        }
                        break;
                    case 'inputs':
                        settings = SBForm.getAll(area);
                        error = 'All fields are required.';
                        if (success != '') {
                            success = sb_(success) + ' [list values="';
                            for (var key in settings) {
                                success += `${sb_(settings[key][1].replace(/:|,/g, ''))}:${settings[key][0].replace(/:|,/g, '')},`;
                            }
                            success = success.slice(0, -1) + '"]';
                        }
                        payload['rich-messages'][rich_message_id] = { type: type, result: settings };
                        parameters = { function: 'update-message', payload: payload };
                        dialogflow_response = 'inputs-complete';
                        break;
                    case 'registration':
                        let settings_extra = SBForm.getAll(area.find('.sb-form-extra'));
                        let payload_settings;
                        $.extend(user_settings, SBForm.getAll(area.find('.sb-form-main')));
                        payload_settings = $.extend({}, user_settings);
                        success = `${sb_(success == '' ? 'Your account details are as follows:' : success)} [list values="`;
                        for (var key in user_settings) {
                            let value = user_settings[key][0].replace(/:|,/g, '');
                            if (value != '') {
                                if (key == 'profile_image') {
                                    value = value.substr(value.lastIndexOf('/') + 1);
                                }
                                if (key == 'password' || key == 'password-check') {
                                    value = '********';
                                    payload_settings[key] = '********';
                                }
                                success += user_settings[key][1] == '' ? '' : `${sb_(user_settings[key][1].replace(/:|,/g, ''))}:${value},`;
                            }
                        }
                        for (var key in settings_extra) {
                            if (settings_extra[key][0] != '') {
                                success += `${sb_(settings_extra[key][1].replace(/:|,/g, ''))}:${settings_extra[key][0].replace(/:|,/g, '')},`;
                            }
                        }
                        user_settings['user_type'] = ['user', ''];
                        success = success.slice(0, -1) + '"]';
                        payload['rich-messages'][rich_message_id] = { type: type, result: { user: payload_settings, extra: settings_extra } };
                        payload['event'] = 'update-user';
                        parameters = { function: activeUser() != false ? 'update-user-and-message' : 'add-user-and-login', settings: user_settings, settings_extra: settings_extra, payload: payload };
                        error = SBForm.getRegistrationErrorMessage(area);
                        dialogflow_response = 'registration-complete';
                        break;
                    case 'rating':
                        let rating = $(element).attr('data-rating');
                        success = `${sb_('Your rating is')} *${sb_(rating)}*. ${sb_(success == '' ? 'Thank you for your feedback!' : success)}`;
                        settings = { conversation_id: SBChat.conversation.id, agent_id: SBChat.agent_id, user_id: activeUser().id, message: area.find('textarea').val(), rating: (rating == 'positive' ? 1 : -1) };
                        payload['rich-messages'][rich_message_id] = { type: type, result: settings };
                        parameters = { function: 'set-rating', payload: payload, settings: settings };
                        dialogflow_response = rating;
                        break;

                }

                shortcode = message.substr(message.indexOf('[' + type))
                shortcode = shortcode.substr(0, shortcode.indexOf(']') + 1);

                if (error != '' && SBForm.errors(area)) {
                    SBForm.showErrorMessage(area, error);
                    $(input).sbLoading(false);
                    if (SBChat.dashboard || (active_conversation && SBChat.conversation.getLastMessage().id == message_id)) {
                        SBChat.scrollBottom();
                    }
                    return false;
                }
                if (success == '') {
                    let shortcode_settings = this.shortcode(shortcode);
                    let title = 'title' in shortcode_settings[1] ? `title="${shortcode_settings[1]['title']}"` : '';
                    let message = 'message' in shortcode_settings[1] ? `message="${shortcode_settings[1]['message']}"` : '';
                    let value = '';
                    if (['inputs', 'email'].includes(type)) {
                        for (var key in settings) {
                            value += settings[key][0] + ',';
                        }
                        value = `values="${value.slice(0, -1)}"`
                    } else {
                        value = `options="${settings}"`;
                    }
                    success = `[${type == 'email' ? 'inputs' : type} ${title} ${message} ${value} disabled="true"]`;
                }
                if (message_id != -1) {
                    $.extend(parameters, {
                        message_id: message_id,
                        message: message != '' ? message.replace(shortcode, success) : success,
                        payload: payload
                    });
                }
                SBF.ajax(parameters, (response) => {
                    if (response != false && !SBF.errorValidation(response)) {
                        switch (type) {
                            case 'email':
                                for (var key in user_settings) {
                                    activeUser().set(key, user_settings[key][0]);
                                }
                                if (active_conversation && SBChat.conversation.getLastUserMessage() !== false && CHAT_SETTINGS['notify-agent-email']) {
                                    SBChat.sendEmail(SBChat.conversation.getLastUserMessage().get('message'));
                                }
                                SBF.loginCookie(response[1]);
                                if (rich_message_id == 'sb-subscribe-form') {
                                    SBF.ajax({
                                        function: 'subscribe-email',
                                        email: activeUser().get('email')
                                    });
                                }
                                SBF.event('SBNewEmailAddress', { id: rich_message_id, name: activeUser().name, email: activeUser().get('email') });
                                break;
                            case 'registration':
                                SBF.loginCookie(response[1]);
                                if (activeUser() == false) {
                                    activeUser(new SBUser(response[0]));
                                    SBChat.initChat();
                                    SBChat.sendMessage(bot_id, success);
                                } else {
                                    for (var key in user_settings) {
                                        activeUser().set(key, user_settings[key][0]);
                                    }
                                }
                                if (SBChat.dashboard) {
                                    $(main).removeClass('sb-init-form-active');
                                    $(area).remove();
                                    if (!SBChat.isInitDashboard()) {
                                        SBChat.hideDashboard();
                                    }
                                }
                                delete this.cache['registration'];
                                SBF.event('SBRegistrationForm', { id: rich_message_id, user: user_settings, extra: payload['rich-messages'][rich_message_id]['result']['extra'] });
                                SBF.event('SBNewEmailAddress', { id: rich_message_id, name: activeUser().name, email: activeUser().get('email') });
                                break;
                        }
                        if (message_id == -1) {
                            $(element).closest('.sb-rich-message').html(success);
                        } else if ((!('type' in payload) || payload['type'] != 'close-message') && (!dialogflow_unknow_notify && dialogflow_human_takeover == false)) {
                            SBChat.setConversationStatus(2);
                        }
                        if (!['email', 'registration', 'login', 'chips'].includes(type)) {
                            SBChat.sendBotMessage(`${rich_message_id}|${type}|${dialogflow_response}`);
                        }
                        if (CHAT_SETTINGS['slack-active']) {
                            SBChat.slackMessage(activeUser().id, activeUser().name, activeUser().image, success);
                        }
                        SBF.event('SBRichMessageSubmit', { result: response, data: payload['rich-messages'][rich_message_id], id: rich_message_id });
                    } else {
                        SBForm.showErrorMessage(area, SBForm.getRegistrationErrorMessage(response, 'response'));
                        if (SBChat.dashboard) {
                            SBChat.scrollBottom();
                        }
                        $(input).sbLoading(false);
                    }
                });
            }
        },

        // Return the shortcode name and the shortcode settings
        shortcode: function (shortcode) {
            if (shortcode.indexOf(' ') < 0) {
                return [shortcode.slice(1, -1), {}];
            }
            let result = {};
            let shortcode_name = shortcode.substr(1, shortcode.indexOf(' ') - 1);
            shortcode = shortcode.slice(1, -1).substr(shortcode_name.length + 1);
            let settings = shortcode.split('" ');
            for (var i = 0; i < settings.length; i++) {
                if (settings[i].indexOf('=') > -1) {
                    let item = [settings[i].substr(0, settings[i].indexOf('=')), settings[i].substr(settings[i].indexOf('=') + 2)];
                    result[$.trim(item[0])] = item[1].replace(/"/g, '');
                }
            }
            return [shortcode_name, result];
        },

        // Init the rich message inputs
        initInputs: function (code) {
            code = $.parseHTML('<div>' + code + '</div>');
            $(code).find('.sb-input input').each(function () {
                if ($(this).val() != '') {
                    $(this).siblings().addClass('sb-active sb-filled');
                }
            });
            return $(code).html();
        },

        // Timetable shortcode
        timetable: function (code) {
            let table = $.parseHTML(`<div>${code}</div>`);
            let offset = $(table).find('[data-offset]').attr('data-offset');
            offset = offset == '' ? 0 : parseInt(offset);
            $(table).find('[data-time]').each(function () {
                let times = $(this).attr('data-time').split('|');
                code = ''
                for (var i = 0; i < times.length; i++) {
                    if (times[i] == 'closed') {
                        code += sb_('Closed');
                        break;
                    } else if (times[i] != '') {
                        let hours = parseInt(times[i].split(':')[0]) + offset - (today.getTimezoneOffset() / 60);
                        let minutes = times[i].split(':')[1];
                        if (hours > 24) {
                            hours -= 24;
                        }
                        if (hours < 0) {
                            hours = 24 - hours;
                        }
                        let time = new Date(`01/01/2000 ${hours}:${minutes} UTC`);
                        code += time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) + (i == 0 || i == 2 ? `<span>${sb_('to')}</span>` : i == 1 && times[i + 1] != '' ? `<br />` : '');
                    }
                }
                $(table).find(' > div > span').html(`<i class="sb-icon-clock"></i> ${sb_('Time zone')} ${Intl.DateTimeFormat().resolvedOptions().timeZone}`);
                $(this).html(code);
            });
            return $(table).html();
        },

        // Slider
        sliderChange: function (id, direction = 'left') {
            let slider = $(chat).find(`#${id}`);
            if (slider.length && !$(slider).hasClass('sb-moving')) {
                let items = $(slider).find('.sb-slider > div > div');
                let item = $(items).eq(0);
                let width = Math.ceil($(item).closest('.sb-slider').width());
                let negative = (direction == 'right' ? -1 : 1);
                let margin = parseFloat(parseFloat(parseFloat($(item).css('margin-left')) + (width * negative)).toFixed(2)) + (-0.5 * negative);
                let check = width * (items.length - 1) * -1;
                if (margin < 1 && margin >= check) {
                    $(item).css('margin-left', margin + 'px');
                    $(slider).addClass('sb-moving');
                    setTimeout(() => { $(slider).removeClass('sb-moving'); }, 1200);
                }
                $(slider).find('.sb-icon-arrow-right').sbActivate(!(check > (margin - 15) && check < (margin + 15)));
                $(slider).find('.sb-icon-arrow-left').sbActivate(margin < -10);
            }
        }
    }

    /* 
    * ----------------------------------------------------------
    * FORM METHODS
    * ----------------------------------------------------------
    */

    var SBForm = {

        // Get all settings
        getAll: function (area) {
            let settings = {};
            $(area).find('.sb-input[id]').each((i, element) => {
                settings[$(element).attr('id')] = this.get(element);
            });
            return settings;
        },

        // Get a single setting
        get: function (input) {
            let type = $(input).data('type');
            let name = sb_(SBF.escape($(input).find(' > span').html()));
            switch (type) {
                case 'image':
                    let url = $(input).find('.image').attr('data-value');
                    return [SBF.null(url) ? '' : url, name];
                    break;
                case 'select':
                    return [SBF.escape($(input).find('select').val()), name];
                    break;
                default:
                    let target = $(input).find('input');
                    return [SBF.escape($(target).length ? $(target).val() : $(input).find('[data-value]').attr('data-value')), name];
                    break;
            }
        },

        // Set a single setting
        set: function (item, value) {
            if ($(item).length) {
                let type = $(item).data('type');
                switch (type) {
                    case 'image':
                        if (value == '') {
                            $(item).find('.image').removeAttr('data-value').removeAttr('style');
                        } else {
                            $(item).find('.image').attr('data-value', value).css('background-image', `url("${value}")`);
                        }
                        break;
                    case 'select':
                        $(item).find('select').val(value);
                        break;
                    default:
                        $(item).find('input').val(value);
                        break;
                }
                return true;
            }
            return false;
        },

        // Clear all the input values
        clear: function (area) {
            $(area).find('.sb-input').each((i, element) => {
                this.set(element, '');
            });
            this.set($(area).find('#user_type'), 'user');
        },

        // Check for errors on user input
        errors: function (area) {
            let have_errors = false;
            let items = $(area).find('[required]').removeClass('sb-error');
            $(items).each(function (i) {
                let type = $(this).attr('type');
                let value = $(this).val();
                if (value == '') {
                    have_errors = true;
                } else if (type == 'password') {
                    if (type == 'password' && (value.length < 8 || (items.length > (i + 1) && $(items[i + 1]).attr('type') == 'password' && $(items[i + 1]).val() != value))) {
                        have_errors = true;
                    }
                } else if (type == 'email' && (value.indexOf('@') < 0 || value.indexOf('.') < 0)) {
                    have_errors = true;
                }
                if (have_errors) {
                    $(this).addClass('sb-error');
                }
            });
            items = $(area).find('[data-required]').removeClass('sb-error');
            $(items).each(function () {
                if (SBF.null($(this).attr('data-value'))) {
                    $(this).addClass('sb-error');
                    have_errors = true;
                }
            });
            return have_errors;
        },

        // Display a error message
        showErrorMessage: function (area, message) {
            $(area).find('.sb-info').html(sb_(message)).sbActivate();
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                $(area).find('.sb-info').sbActivate(false);
            }, 7000);
        },

        // Display a success message
        showSuccessMessage: function (area, message) {
            $(area).find('.sb-info').remove();
            $(area).addClass('sb-success').find('.sb-content').html(`<div class="sb-text">${message}</div>`);
        },

        // Return the registration error message
        getRegistrationErrorMessage(area_or_response, type = 'validation') {
            if (type == 'response') return SBF.errorValidation(area_or_response, 'duplicate-email') ? 'This email is already in use. Please use another email.' : 'Error. Please check your information and try again.'
            let name = $(area_or_response).find('#last_name').length ? 'first name, last name' : 'name';
            return $(area_or_response).find('#password').length ? `Please insert your ${name}, a valid email, and a password (8 character minimum).` : `Please insert your ${name}, and a valid email.`;
        }
    }
    window.SBForm = SBForm;

    /* 
    * ----------------------------------------------------------
    * # APPS
    * ----------------------------------------------------------
    */

    var SBApps = {

        // Get the login data 
        login: function () {
            if (this.is('wp')) {
                return CHAT_SETTINGS['wp-users-system'] == 'wp' ? (typeof SB_WP_ACTIVE_USER != ND ? SB_WP_ACTIVE_USER : false) : false
            }
            return false;
        },

        // Check if an app is installed
        is: function (name) {
            if (name == 'wordpress' || name == 'wp') return CHAT_SETTINGS['wp'];
            return false;
        },

        // WordPress
        wordpress: {

            // Ajax
            ajax: function (action, data, onSuccess) {
                $.ajax({
                    method: 'POST',
                    url: SB_WP_AJAX_URL,
                    data: $.extend({ action: 'sb_wp_ajax', type: action }, data)
                }).done((response) => {
                    if (onSuccess !== false) {
                        onSuccess(response);
                    }
                });
            },

            // Check if there is a WordPress user logged-in
            isLogin: function () {
                return CHAT_SETTINGS['wp'] && typeof SB_WP_ACTIVE_USER != ND && SB_WP_ACTIVE_USER !== false;
            },
        },

        // WooCoommerce
        woocommerce: {

            // Update the cart
            updateCart: function (action, product_id, onSuccess = false) {
                SBApps.wordpress.ajax(action, { product_id: product_id }, onSuccess);
            },

            // Waiting list
            waitingList: function (action = 'request', product_id = false) {
                if (typeof SB_WP_WAITING_LIST !== ND && (action != 'request' || SB_WP_WAITING_LIST && SBF.storageTime('waiting-list-' + SB_WP_PAGE_ID, 24)) && activeUser()) {
                    SBF.ajax({
                        function: 'woocommerce-waiting-list',
                        product_id: product_id === false ? SB_WP_PAGE_ID : product_id,
                        conversation_id: SBChat.conversation.id,
                        action: action,
                        token: SBChat.dialogflow_token
                    }, (response) => {
                        if (response) {
                            SBF.storageTime('waiting-list-' + SB_WP_PAGE_ID);
                            if (action == 'request' && (!SBChat.chat_open || SBChat.dashboard)) {
                                SBChat.updateConversations();
                            }
                        }
                    });
                }
            }
        },

        // Dialogflow
        dialogflow: {

            // Check if Dialogflow is active or deactivate it
            active: function (active = true) {
                if (active === false) {
                    CHAT_SETTINGS['dialogflow-active'] = false;
                    SBF.storageTime('dialogflow');
                    return false;
                }
                return CHAT_SETTINGS['dialogflow-active'] && !admin && SBF.storageTime('dialogflow', 1) && (!CHAT_SETTINGS['bot-office-hours'] || !CHAT_SETTINGS['office-hours']);
            },

            // Return the notification email text
            unknowEmail: function (message) {
                return SBChat.sendEmail(`${message}<br><br><span style="color:#a8a8a8;font-size: 12px;">${sb_('This message has been sent because the Dialogflow Bot does not know the answer to the user\'s question.')}</span>`);
            },

            // Trigger the welcome Intent
            welcome: function () {
                if (CHAT_SETTINGS['dialogflow-welcome']) {
                    SBF.ajax({
                        function: 'send-bot-message',
                        message: '',
                        conversation_id: SBChat.conversation.id,
                        token: SBChat.dialogflow_token,
                        event: 'Welcome'
                    });
                }
            }
        }
    }

    /*
    * ----------------------------------------------------------
    * # INIT
    * ----------------------------------------------------------
    */

    $(document).ready(function () {

        main = $('body').find('.sb-admin, .sb-chat, .sb-tickets');
        admin = $(main).hasClass('sb-admin') || !main.length;
        tickets = $(main).hasClass('sb-tickets');

        // Initalize the chat and the user
        if (main.length && typeof SB_AJAX_URL != ND) {
            chat = $(main).find('.sb-list');
            chat_editor = $(main).find('.sb-editor');
            chat_textarea = $(chat_editor).find('textarea');
            chat_scroll_area = $(main).find(admin || tickets ? '.sb-list' : '> div > .sb-scroll-area');
            chat_header = $(chat_scroll_area).find('.sb-header');
            chat_emoji = $(chat_editor).find('.sb-emoji');
            chat_status = tickets ? $(main).find('.sb-profile-agent .sb-status') : null;
            SBChat.enabledAutoExpand();
            SBChat.audio = $(main).find("#sb-audio")[0];
            SBChat.audio_out = $(main).find("#sb-audio-out")[0];

            // Check if cookies works
            SBF.cookie('sb-check', 'ok', 1, 'set');
            if (SBF.cookie('sb-check') != 'ok') {
                cookies_supported = false;
                console.warn('Support Board: cookies not available.');
            } else {
                SBF.cookie('sb-check', false, false, false);
            }

            // Compatibility code. This code will be removed in the future versions
            if (storage('login') != false) {
                SBF.loginCookie(storage('login'));
                localStorage.removeItem('login');
            }

            SBF.ajax({
                function: 'get-front-settings'
            }, (response) => {
                CHAT_SETTINGS = response;
                bot_id = CHAT_SETTINGS['bot-id'];
                dialogflow_human_takeover = CHAT_SETTINGS['dialogflow-human-takeover'];
                dialogflow_unknow_notify = CHAT_SETTINGS['bot-unknow-notify'];
                agents_online = CHAT_SETTINGS['agents-online'];
                if (!admin && !CHAT_SETTINGS['chat-manual-init'] && (!CHAT_SETTINGS['disable-offline'] || agents_online) && (!CHAT_SETTINGS['disable-office-hours'] || CHAT_SETTINGS['office-hours']) && (!CHAT_SETTINGS['chat-login-init'] || (!SBApps.is('wp') && SBF.cookie('sb-login')) || (SBApps.is('wp') && SBApps.wordpress.isLogin()))) {
                    SBChat.initChat();
                }
                if (CHAT_SETTINGS['cron']) {
                    setTimeout(function () {
                        SBF.ajax({ function: 'cron-jobs' });
                    }, 10000);
                }
                SBF.event('SBReady');
            });

            $(chat_editor).on('keydown', 'textarea', function (e) {
                if (e.which == 13 && !tickets && (!admin || !e.ctrlKey)) {
                    SBChat.submit();
                    e.preventDefault;
                    return false;
                }
                if (admin && e.which == 13 && e.ctrlKey) {
                    SBChat.insertText('\n');
                }
            });
            if (!admin) {
                SBChat.audio_out.volume = 0.4;
            }
            if (typeof SB_DEFAULT_DEPARTMENT !== ND) {
                SBChat.default_department = SB_DEFAULT_DEPARTMENT;
            }
        } else {
            CHAT_SETTINGS = [];
            SBF.event('SBReady');
        }

        // Disable real-time if browser tab not active
        document.addEventListener('visibilitychange', function () {
            SBF.visibilityChange(document.visibilityState);
        }, false);

        $(main).on('click', function () {
            if (SBChat.tab_active == false) {
                SBF.visibilityChange();
            }
        });

        // Set the global container for both admin and front
        if (admin) {
            global = main;
            main = $(main).find('.sb-conversation');
        } else {
            global = main;
        }

        // Scroll detection
        $(chat_scroll_area).on('scroll', function (e) {
            SBChat.scrollHeader();
        });

        // Show the message menu
        $(chat).on('click', '.sb-menu-btn', function () {
            let menu = $(this).parent().find('.sb-menu');
            let active = $(menu).sbActive();
            SBF.deactivateAll();
            if (!active) {
                $(menu).sbActivate();
                SBF.deselectAll();
            }
        });

        // Mobile
        if (mobile) {
            $(chat_editor).on('click', '.sb-textarea', function () {
                $(main).addClass('sb-header-hidden');
                $(this).find('textarea').focus();
                if (chat_scroll_area[0].scrollTop === (chat_scroll_area[0].scrollHeight - chat_scroll_area[0].offsetHeight)) {
                    SBChat.scrollBottom();
                    setTimeout(() => {
                        SBChat.scrollBottom();
                    }, 200);
                }
            });

            $(chat_editor).on('focusout', '.sb-textarea', function () {
                setTimeout(() => {
                    $(main).removeClass('sb-header-hidden');
                }, 300);
            });

            $(chat_editor).on('click', '.sb-submit', function () {
                $(chat_textarea).blur();
            });
        }

        // Hide the message menu
        $(chat).on('click', '.sb-menu li', function () {
            $(this).parent().sbActivate(false);
        });

        // Delete a message
        $(chat).on('click', '.sb-menu [data-value="delete"]', function () {
            let message = $(this).closest('[data-id]');
            let message_id = $(message).attr('data-id');
            if (SBChat.user_online) {
                SBF.ajax({
                    function: 'update-message',
                    message_id: message_id,
                    message: '',
                    attachments: [],
                    payload: { 'event': 'delete-message' }
                }, () => {
                    SBChat.conversation.deleteMessage(message_id);
                    $(chat).find(`[data-id="${message_id}"]`).remove();
                });
            } else {
                SBChat.deleteMessage(message_id);
            }
        });

        // Send a message
        $(chat_editor).on('click', '.sb-submit', function () {
            SBChat.submit();
        });

        // Open the chat
        $('body').on('click', '.sb-chat-btn,.sb-responsive-close-btn,#sb-open-chat,.sb-open-chat', function () {
            SBChat.open(!SBChat.chat_open);
        });

        // Show the dashboard
        $(main).on('click', '.sb-dashboard-btn', function () {
            SBChat.showDashboard();
            storage('open-conversation', 0);
            force_action = false;
        });

        // Open a conversation from the dashboard
        $(main).on('click', '.sb-user-conversations li', function () {
            SBChat.openConversation($(this).attr('data-conversation-id'));
        });

        // Start a new conversation from the dashboard
        $(main).on('click', '.sb-btn-new-conversation,.sb-departments-list > div', function () {
            if (!SBF.null($(this).data('id'))) {
                SBChat.default_department = parseInt($(this).data('id'));
            }
            force_action = 'new-conversation';
            SBChat.clear();
            SBChat.hideDashboard();
        });

        // Events uploader
        $(chat_editor).on('click', '.sb-btn-attachment', function () {
            if (!SBChat.is_busy) {
                $(chat_editor).find('.sb-upload-files').val('').click();
            }
        });

        $(chat_editor).on('click', '.sb-attachments > div > i', function (e) {
            $(this).parent().remove();
            if ($(chat_textarea).val() == '' && $(chat_editor).find('.sb-attachments > div').length == 0) {
                SBChat.activateBar(false);
            }
            e.preventDefault();
            return false;
        });

        $(chat_editor).on('change', '.sb-upload-files', function (data) {
            SBChat.busy(true);
            $(this).sbUploadFiles(function (response) {
                SBChat.uploadResponse(response);
            });
            SBF.event('SBAttachments');
        });

        $(chat_editor).on('dragover', function () {
            $(this).addClass('sb-drag');
            clearTimeout(timeout);
        });

        $(chat_editor).on('dragleave', function () {
            timeout = setTimeout(() => {
                $(this).removeClass('sb-drag');
            }, 200);
        });

        $(chat_editor).on('drop', function (e) {
            let files = e.originalEvent.dataTransfer.files;
            e.preventDefault();
            e.stopPropagation();
            if (files.length > 0) {
                for (var i = 0; i < files.length; ++i) {
                    let form = new FormData();
                    form.append('file', files[i]);
                    SBF.upload(form, function (response) { SBChat.uploadResponse(response) });
                }
            }
            $(this).removeClass('sb-drag');
        });

        // Articles
        $(main).on('click', '.sb-btn-all-articles', function () {
            SBChat.showArticles();
        });

        $(main).on('click', '.sb-articles > div', function () {
            SBChat.showArticles($(this).attr('data-id'));
        });

        $(main).on('click', '.sb-dashboard-articles .sb-input-btn .sb-submit-articles', function () {
            SBChat.searchArticles($(this).parent().find('input').val(), this);
        });

        $(chat).on('click', '.sb-rich-button a', function (e) {
            let link = $(this).attr('href');
            if (link.indexOf('#') === 0) {
                if (link.indexOf('#article-') === 0) {
                    SBChat.showArticles(link.replace('#article-', ''));
                    e.preventDefault();
                    return false;
                }
            }
        });

        // Lightbox
        $(global).on('click', '.sb-lightbox-media > i', function () {
            $(global).find('.sb-lightbox-media').sbActivate(false);
            return false;
        });

        $(main).on('click', '.sb-image', function () {
            SBF.lightbox($(this).html());
        });

        $(main).on('click', '.sb-slider-images .sb-card-img', function () {
            SBF.lightbox(`<img src="${$(this).attr('data-value')}" />`);
        });

        // Event: on conversation loaded
        $(window).on('SBConversationLoaded', function () {
            if (storage('queue')) {
                SBChat.queue(storage('queue'));
            }
        });

        // Events emoji
        $(chat_editor).on('click', '.sb-btn-emoji', function () {
            SBChat.showEmoji(this);
        });

        $(chat_emoji).on('click', '.sb-emoji-list li', function (e) {
            SBChat.insertEmoji($(this).html());
            if (mobile) {
                clearTimeout(timeout);
            }
        });

        $(chat_emoji).find('.sb-emoji-list').on('touchend', function (e) {
            timeout = setTimeout(() => {
                SBChat.mouseWheelEmoji(e);
            }, 50);
        });

        $(chat_emoji).find('.sb-emoji-list').on('mousewheel DOMMouseScroll', function (e) {
            SBChat.mouseWheelEmoji(e);
        });

        $(chat_emoji).find('.sb-emoji-list').on('touchstart', function (e) {
            SBChat.emoji_options['touch'] = e.originalEvent.touches[0].clientY;
        });

        $(chat_emoji).on('click', '.sb-emoji-bar > div', function () {
            SBChat.clickEmojiBar(this);
        });

        $(chat_emoji).on('click', '.sb-select li', function () {
            SBChat.categoryEmoji($(this).data('value'));
        });

        $(chat_emoji).find('.sb-search-btn input').on('change keyup paste', function () {
            SBChat.searchEmoji($(this).val());
        });

        // Textarea
        $(chat_textarea).on('keyup', function () {
            SBChat.textareaChange(this);
        });

        // Privacy message
        $(main).on('click', '.sb-privacy .sb-approve', function () {
            storage('privacy-approved', true);
            $(this).closest('.sb-privacy').remove();
            $(main).removeClass('sb-init-form-active');
            $(chat_header).find(' > div').css({ 'opacity': 1, 'top': 0 });
            SBChat.initChat();
            if (tickets) {
                SBTickets.showPanel('new-ticket');
            } else if (!SBChat.isInitDashboard()) {
                SBChat.hideDashboard();
            }
        });

        $(main).on('click', '.sb-privacy .sb-decline', function () {
            let privacy = $(this).closest('.sb-privacy');
            $(privacy).find('.sb-text').html($(privacy).attr('data-decline'));
            $(privacy).find('.sb-decline').remove();
            SBChat.scrollBottom(true);
        });

        // Popup message
        $(main).on('click', '.sb-popup-message .sb-icon-close', function () {
            SBChat.popup(true);
        });

        // Rich messages 
        $(main).on('click', '.sb-rich-message .sb-submit,.sb-rich-message .sb-select ul li', function () {
            let message = $(this).closest('.sb-rich-message');
            if (!message[0].hasAttribute('disabled')) {
                SBRichMessages.submit(message, message.attr('data-type'), this);
            };
        });

        $(main).on('click', '.sb-rich-message .sb-input > span', function () {
            $(this).sbActivate();
            $(this).siblings().focus();
        });
        $(main).on('focus', '.sb-rich-message .sb-input input', function () {
            $(this).siblings().sbActivate();
        });

        $(main).on('click', '.sb-rich-message .sb-input input', function () {
            $(this).siblings().removeClass('sb-filled');
        });

        $(main).on('focusout', '.sb-rich-message .sb-input input', function () {
            if ($(this).val() == '') {
                $(this).siblings().sbActivate(false);
            } else {
                $(this).siblings().addClass('sb-filled sb-active');
            }
        });

        $(main).on('click', '.sb-slider-arrow', function () {
            SBRichMessages.sliderChange($(this).closest('[id]').attr('id'), $(this).hasClass('sb-icon-arrow-right') ? 'right' : 'left');
        });

        // Registration and Login
        $(main).on('click', '.sb-rich-registration .sb-login-area', function () {
            $(this).closest('.sb-rich-registration').replaceWith(SBRichMessages.generate({}, 'login', 'sb-init-form'));
            SBChat.scrollBottom(true);
        });

        $(main).on('click', '.sb-rich-login .sb-registration-area', function () {
            if (CHAT_SETTINGS['registration-link'] != '') {
                document.location = CHAT_SETTINGS['registration-link'];
            } else {
                $(this).closest('.sb-rich-login').replaceWith(SBRichMessages.generate({}, 'registration', 'sb-init-form'));
                SBChat.scrollBottom(true);
            }
        });

        $(main).on('click', '.sb-rich-login .sb-submit-login', function () {
            SBF.loginForm(this, false, (response) => {
                activeUser(new SBUser(response[0]));
                $(main).removeClass('sb-init-form-active');
                $(this).closest('.sb-rich-login').remove();
                force_action = 'open-conversation';
                SBChat.initChat();
                if (!SBChat.isInitDashboard()) {
                    SBChat.hideDashboard();
                }
            });
        });

        // Social share buttons
        $(chat).on('click', '.sb-social-buttons div', function () {
            SBF.openWindow($(this).attr('data-link'));
        });

        // WooCommerce
        $(chat).on('click', '.sb-rich-woocommerce-button a, [href="#"].sb-card-btn', function (e) {
            if (!$(this).sbLoading()) {
                let settings = SBF.settingsStringToArray($(this).closest('.sb-rich-message').attr('data-settings'));
                let checkout = settings['link-type'] == 'checkout' || settings['checkout'];
                let product_ids = $(this)[0].hasAttribute('data-ids') ? $(this).attr('data-ids').split(',') : [settings['id'].split('|')[$(this).parent().index()]];
                if (product_ids.length) {
                    $(this).sbLoading(true);
                    SBApps.wordpress.ajax('button-purchase', { product_ids: product_ids, checkout: checkout, coupon: settings['coupon'] }, (response) => {
                        if (checkout) {
                            document.location = response;
                        } else {
                            $(this).addClass('sb-icon-check').sbLoading(false);
                        }
                    });
                }
            }
            e.preventDefault();
            return false;
        });

        $(chat).on('click', '#sb-waiting-list .sb-submit', function () {
            if ($(this).index() == 0) {
                setTimeout(() => { SBApps.woocommerce.waitingList('submit') }, 1000);
            }
        });

        $(window).on('SBNewEmailAddress', function (e, response) {
            if (response['id'] == 'sb-waiting-list-email') {
                SBApps.woocommerce.waitingList('submit');
            }
        });


        /*
        * ----------------------------------------------------------
        * COMPONENTS
        * ----------------------------------------------------------
        */

        // Search
        $(global).on('click', '.sb-search-btn > i', function () {
            $(this).parent().toggleClass('sb-active');
            $(this).parent().find('input').focus();
            $(global).find('.sb-select ul').sbActivate(false);
        });

        // Select
        $(global).on('click', '.sb-select', function () {
            $(this).find('ul').toggleClass('sb-active');
        });

        $(global).on('click', '.sb-select li', function () {
            let select = $(this).closest('.sb-select');
            let value = $(this).data('value');
            var item = $(select).find(`[data-value="${value}"]`);
            $(select).find('li').sbActivate(false);
            $(select).find('p').attr('data-value', value).html($(item).html());
            $(item).sbActivate();
        });

        // Image uploader
        $(global).on('click', '.sb-input-image .image', function () {
            upload_target = $(this).parent();
            $(chat_editor).find('.sb-upload-files').click();
        });

        $(global).on('click', '.sb-input-image .image > .sb-icon-close', function (e) {
            $(this).parent().removeAttr('data-value').css('background-image', '');
            e.preventDefault();
            return false;
        });
    });
}(jQuery));