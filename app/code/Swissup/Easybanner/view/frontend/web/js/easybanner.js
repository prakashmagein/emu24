/*eslint max-depth: ["error", 4]*/
define([
    'jquery',
    'underscore',
    'mage/cookies'
], function ($, _) {
    'use strict';

    var Easybanner = {},
        prototypes = {};

    prototypes.cookie = function (cookieName) {
        var _cookie = {},
            isRead = false,
            isWritable,
            expires = new Date();

        expires.setFullYear(expires.getFullYear() + 1);

        /** Write cookies */
        function write() {
            $.mage.cookies.set(
                cookieName,
                JSON.stringify(_cookie),
                {
                    expires: expires
                }
            );
        }

        /** Read cookies */
        function read() {
            var jsonString = $.mage.cookies.get(cookieName);

            isRead = true;
            isWritable = false;

            if (!jsonString) {
                write();
            }

            jsonString = $.mage.cookies.get(cookieName);

            if (jsonString) {
                isWritable = true;
                try {
                    _cookie = JSON.parse(jsonString);
                } catch (e) {}
            }
        }

        return {
            isWritable: () => isWritable,

            /**
             * Get banner parameters from cookie
             *
             * @param {Number} bannerId
             * @param {String} key
             * @param {*} defaultValue
             * @returns {*}
             */
            get: function (bannerId, key, defaultValue) {
                if (!isRead) {
                    read();
                }

                defaultValue = defaultValue || 0;

                if (typeof _cookie[bannerId] === 'undefined') {
                    _cookie[bannerId] = {};
                }

                if (key) {
                    if (_cookie[bannerId][key] !== undefined) {
                        return _cookie[bannerId][key];
                    }

                    return defaultValue;
                }

                return _cookie[bannerId];
            },

            /**
             * Set parameter in cookie
             *
             * @param {Number} bannerId
             * @param {String} key
             * @param {*} value
             */
            set: function (bannerId, key, value) {
                if (!isRead) {
                    read();
                }

                _cookie[bannerId][key] = value;
                write();
            }
        };
    };

    prototypes.exitIntent = function () {
        var _state = false,
            scrollStart = $(window).scrollTop();

        /**
         * Check how much amout has been scrolled
         */
        function checkExitIntentStateByScrollTop() {
            var scrollAmount = scrollStart - $(window).scrollTop();

            scrollStart = $(window).scrollTop();

            if (scrollAmount > 63) {
                _state = true;
            } else {
                _state = false;
            }
        }

        $(document).on('mouseleave', function (e) {
            var from = e.relatedTarget || e.toElement;

            if (!from || from.nodeName === 'HTML') {
                _state = true;
            }
        });

        $(document).on('mouseenter', function () {
            _state = false;
        });

        // Exit intent for mobile
        if ('ontouchstart' in window || navigator.msMaxTouchPoints) {
            $(document).on('scroll', _.debounce(checkExitIntentStateByScrollTop, 200));
        }

        return {
            /**
             * @return {Boolean}
             */
            getState: function () {
                var result = _state;

                _state = false;

                return result;
            }
        };
    };

    prototypes.timer = function () {
        var _frequency = 200, // ms
            _savedValue = parseFloat(localStorage.getItem('easybanner_timer_browsing')),
            _timers    = {
                inactivity: 0,
                activity: 0,
                exitIntent: 0,
                browsing: isNaN(_savedValue) || !isFinite(_savedValue) ? 0 : _savedValue
            },
            _conditions = {
                exitIntent: prototypes.exitIntent().getState
            },
            events = ['mousemove', 'click', 'scroll', 'keyup'];

        /**
         * Reset timer
         * @param {String} timer
         */
        function reset(timer) {
            if (!_.isArray(timer)) {
                timer = [timer];
            }

            _.each(timer, function (name) {
                _timers[name] = 0;
            });
        }

        /**
         * Timer's tick
         */
        function tick() {
            _timers = _.mapObject(_timers, function (value, key) {
                if (!_conditions[key] || _conditions[key]()) {
                    value += _frequency / 1000;
                }

                return value;
            });

            if (_timers.inactivity >= 10) {
                reset('activity');
            }
        }

        setInterval(tick.bind(this), _frequency);

        _.each(events, function (eventName) {
            $(document).on(eventName, reset.bind(this, ['inactivity', 'exitIntent']));
        }.bind(this));

        $(document).ready(function () {
            // reset browsing time, if last visit was more that two hours ago
            var lastVisit = localStorage.getItem('easybanner_last_visit'),
                now = new Date();

            localStorage.setItem('easybanner_last_visit', now.toISOString());

            if (!lastVisit) {
                return;
            }

            lastVisit = new Date(lastVisit);

            if (isNaN(lastVisit.getTime())) {
                return;
            }

            if (Math.abs(now - lastVisit) / 1000 / 60 > 120) {
                reset('browsing');
            }
        });

        $(window).on('beforeunload', function () {
            localStorage.setItem('easybanner_timer_browsing', _timers.browsing);
        });

        return {
            /**
             * @returns {Number}
             */
            getInactivityTime: function () {
                return _timers.inactivity;
            },

            /**
             * @returns {Number}
             */
            getActivityTime: function () {
                return _timers.activity;
            },

            /**
             * @returns {Number}
             */
            getBrowsingTime: function () {
                return _timers.browsing;
            },

            /**
             * @return {Number}
             */
            getExitIntentTime: function () {
                return _timers.exitIntent;
            }
        };
    };

    prototypes.rule = function (conditions) {
        var _conditions = {},
            _timer      = prototypes.timer(),
            _cookie     = Easybanner.Cookie,
            _currentId;

        _.each(conditions, function (value, key) {
            _conditions[key] = value;
        });

        /**
         * Compare conditions
         * @param {*} v1
         * @param {*} v2
         * @param {String} op
         * @returns {Boolean}
         * @private
         */
        function _compareCondition(v1, v2, op) {
            var result = false;

            switch (op) {
                case '>':
                    result = parseFloat(v2) > parseFloat(v1);
                    break;

                case '<':
                    result = parseFloat(v2) < parseFloat(v1);
                    break;
            }

            return result;
        }

        /**
         * @param {Object} filter
         * @return {Number}
         */
        function _getDisplayPerCustomerComparator(filter) {
            var comparator,
                counter = filter.attribute.replace('_per_customer', ''),
                timeCounterCookie = counter + '_time',
                compareDate = new Date(_cookie.get(_currentId, timeCounterCookie)),
                currentDate = new Date();

            switch (counter) {
                case 'display_count_per_day':
                    // compareDate.setSeconds(compareDate.getSeconds() + 5);
                    compareDate.setDate(compareDate.getDate() + 1);
                    break;

                case 'display_count_per_week':
                    compareDate.setDate(compareDate.getDate() + 7);
                    break;

                case 'display_count_per_month':
                    compareDate.setMonth(compareDate.getMonth() + 1);
                    break;
            }

            comparator = _cookie.get(_currentId, counter);

            if (compareDate <= currentDate) {
                _cookie.set(_currentId, counter, 0);
                comparator = 0;
            }

            return comparator;
        }

        /**
         * Validate banner conditions
         * @param {Object} filter
         * @returns {Boolean}
         * @private
         */
        function _validateConditions(filter) {
            var result = true,
                comparator,
                condition,
                i;

            if (filter.aggregator && filter.conditions) {
                for (i = 0; i < filter.conditions.length; i++) {
                    condition = filter.conditions[i];
                    result = _validateConditions(condition, filter.aggregator, filter.value);

                    if (filter.aggregator === 'all' && filter.value == '1' && !result ||    /*eslint eqeqeq: "off"*/
                        filter.aggregator === 'any' && filter.value == '1' && result) {     /*eslint eqeqeq: "off"*/

                        break;
                    } else if (filter.aggregator === 'all' && filter.value == '0' && result ||
                        filter.aggregator === 'any' && filter.value == '0' && !result) {

                        result = !result;
                        break;
                    }
                }
            } else if (filter.attribute) {
                switch (filter.attribute) {
                    case 'browsing_time':
                        comparator = _timer.getBrowsingTime();
                        break;

                    case 'inactivity_time':
                        comparator = _timer.getInactivityTime();
                        break;

                    case 'activity_time':
                        comparator = _timer.getActivityTime();
                        break;

                    case 'exit_intent':
                        comparator = _timer.getExitIntentTime();
                        break;

                    case 'display_count_per_customer':
                        comparator = _cookie.get(_currentId, 'display_count');
                        break;

                    case 'display_count_per_customer_per_day':
                    case 'display_count_per_customer_per_week':
                    case 'display_count_per_customer_per_month':
                        comparator = _getDisplayPerCustomerComparator(filter);
                        break;

                    case 'scroll_offset':
                        comparator = $(window).scrollTop();
                        break;

                    default:
                        return true;
                }

                result = _compareCondition(filter.value, comparator, filter.operator);
            }

            return result;
        }

        return {
            /**
             * @param {*} id
             * @returns {Boolean}
             */
            validate: function (id) {
                _currentId = id;

                return _validateConditions(_conditions[id]);
            }
        };
    };

    prototypes.popup = function () {
        var _cookie = Easybanner.Cookie,
            _rule   = Easybanner.Rule,
            _bannerIds = [],
            _lightbox,
            _awesomebar;

        _lightbox = {
            overlayId: 'easybanner-overlay-el',
            id: 'easybanner-lightbox-el',
            markup: [
                '<div id="easybanner-overlay-el" class="easybanner-overlay-el" style="display:none;"></div>',
                '<div id="easybanner-lightbox-el" class="easybanner-lightbox-el" style="display:none;">',
                    '<span class="easybanner-close easybanner-close-icon"></span>',
                    '<div class="easybanner-lightbox-content"></div>',
                '</div>'
            ].join(''),

            /**
             * Add markup to the body
             */
            create: function () {
                $('body').append(this.markup);
                this.overlay = $('#' + this.overlayId);
                this.el      = $('#' + this.id);
            },

            /**
             * Prepare popup observers
             */
            addObservers: function () {
                if (!this._onKeyPressBind) {
                    this._onKeyPressBind = this._onKeyPress.bind(this);
                    this._hideBind = this.hide.bind(this);
                    this._dontShowBind = this.dontShow.bind(this);
                }

                $(this.el).find('.easybanner-close').on('click', this._hideBind);
                $(this.el).find('.easybanner-close-permanent').on('click', this._dontShowBind);

                $(this.el).find('img').each(function () {
                    $(this).onload = this.center.bind(this);
                }.bind(this));

                $(document).off('keyup', this._onKeyPressBind);
                $(document).on('keyup', this._onKeyPressBind);

                if ('addEventListener' in window) {
                    window.addEventListener('resize', this.center.bind(this));
                } else {
                    window.attachEvent('onresize', this.center.bind(this));
                }
            },

            /**
             * Get popup content
             * @returns {*|jQuery}
             */
            getContentEl: function () {
                return $(this.el).children('.easybanner-lightbox-content');
            },

            /**
             * Show html in popup
             * @param {String} html
             */
            show: function (html) {
                if (!html) {
                    return;
                }

                if (!this.el) {
                    this.create();
                }
                this.getContentEl().append(html);

                // update class names to include all names of current banner
                $(this.el)
                    .removeClass()
                    .addClass('easybanner-lightbox-el')
                    .addClass(this.getContentEl().children().first().data('class'));

                this.addObservers();
                $(this.overlay).show();
                $(this.el).show();
                this.center();

                $(this.overlay).addClass('shown');
                $(this.el).addClass('shown');
            },

            /**
             * Hide popup
             */
            dontShow: function (e) {
                var id = this.getContentEl().children().first().attr('id');

                e.preventDefault();

                if (id) {
                    _cookie.set(id, 'dont_show', 1);
                }

                this.hide();
            },

            /**
             * Hide popup
             */
            hide: function () {
                if (this._onKeyPressBind) {
                    $(document).off('keyup', this._onKeyPressBind);
                }
                $('.easybanner-popup-banner').first().append(
                    this.getContentEl().children().first()
                );
                $(this.overlay).hide().removeClass('shown');

                $(this.el)
                    .hide()
                    .removeClass()
                    .addClass('easybanner-lightbox-el');
            },

            /**
             * Reset popup layout
             */
            resetLayout: function () {
                this.getContentEl().css({
                    height: 'auto'
                });
                $(this.el).css({
                    width: 0,
                    height: 0
                });
                $(this.el).css({
                    width: 'auto',
                    height: 'auto',
                    margin: 0,
                    left: 0,
                    top: 0
                });
            },

            /**
             * Align popup window to the center of viewport
             */
            center: function () {
                var viewportSize,
                    width,
                    height,
                    gap = {
                        horizontal: 50,
                        vertical: 50
                    };

                this.resetLayout();

                viewportSize = {
                    'width': $(window).width(),
                    'height': $(window).height()
                };
                width = $(this.el).outerWidth();

                if (viewportSize.width < width + gap.horizontal) {
                    width = viewportSize.width - gap.horizontal;
                }

                $(this.el).css({
                    width: width + 'px',
                    left: '50%',
                    marginLeft: -width / 2 + 'px'
                });

                height = $(this.el).outerHeight();

                if (viewportSize.height < height + gap.vertical) {
                    height = viewportSize.height - gap.vertical;
                }
                this.getContentEl().css({
                    height: height -
                        parseInt($(this.el).css('paddingTop'), 10) -
                        parseInt($(this.el).css('paddingBottom'), 10) + 'px'
                });

                $(this.el).css({
                    top: '50%',
                    marginTop: -height / 2 + 'px'
                });
            },

            /**
             * Key press observer
             * @param {Event} e
             * @private
             */
            _onKeyPress: function (e) {
                if (e.keyCode === 27) {
                    this.hide();
                }
            }
        };

        _awesomebar = {
            id: 'easybanner-awesomebar-el',
            markup: [
                '<div id="easybanner-awesomebar-el" class="easybanner-awesomebar-el" style="display:none;">',
                    '<span class="easybanner-close easybanner-close-icon"></span>',
                    '<div class="easybanner-awesomebar-content"></div>',
                '</div>'
            ].join(''),

            /**
             * Prepare html markup
             */
            create: function () {
                $('body').append(this.markup);
                this.el = $('#' + this.id);
            },

            /**
             * Add event observers
             */
            addObservers: function () {
                if (!this._hideBind) {
                    this._hideBind = this.hide.bind(this);
                    this._dontShowBind = this.dontShow.bind(this);
                }

                $(this.el).find('.easybanner-close').on('click', this._hideBind);
                $(this.el).find('.easybanner-close-permanent').on('click', this._dontShowBind);
            },

            /**
             * @returns {*|jQuery}
             */
            getContentEl: function () {
                return $(this.el).children('.easybanner-awesomebar-content');
            },

            /**
             * @returns {Number}
             */
            getTransitionDuration: function () {
                var duration = $(this.el).css('transition-duration');

                if (duration) {
                    duration = parseFloat(duration) * 1000;
                } else {
                    return 0;
                }

                return duration;
            },

            /**
             * Show content in awesomebar panel
             * @param {String} html
             */
            show: function (html) {
                if (!html) {
                    return;
                }

                if (!this.el) {
                    this.create();
                }

                this.getContentEl().append(html);

                // update class names to include all names of current banner
                $(this.el)
                    .removeClass()
                    .addClass('easybanner-awesomebar-el')
                    .addClass(this.getContentEl().children().first().data('class'));

                this.addObservers();

                $(this.el).show();
                setTimeout(function () {
                    $(this.el).css({
                        top: 0
                    });
                }.bind(this), 10);
            },

            /**
             * Hide popup
             */
            dontShow: function (e) {
                var id = this.getContentEl().children().first().attr('id');

                e.preventDefault();

                if (id) {
                    _cookie.set(id, 'dont_show', 1);
                }

                this.hide();
            },

            /**
             * Hide awesomebar
             */
            hide: function () {
                $(this.el).css({
                    top: -$(this.el).outerHeight() - 30 + 'px'
                });

                // time to hide the bar before move it
                setTimeout(function () {
                    $('.easybanner-popup-banner').append({
                        bottom: this.getContentEl().children().first()
                    });

                    $(this.el)
                        .hide()
                        .removeClass()
                        .addClass('easybanner-awesomebar-el');
                }.bind(this), this.getTransitionDuration());
            }
        };

        return {
            /**
             * Collect all rendered banners and add them into array
             */
            init: function () {
                $('.easybanner-popup-banner .easybanner-banner').each(function () {
                    _bannerIds.push($(this).attr('id'));
                });
                this.initBanners();
            },

            /**
             * 1. Show banner if needed.
             * 2. Call every second for conditional banners
             */
            initBanners: function () {
                var shownIds = [],
                    limit = _bannerIds.length,
                    i;

                if (Easybanner.Cookie.isWritable() === false) {
                    return;
                }

                for (i = 0; i < limit; ++i) {
                    if (_cookie.get(_bannerIds[i], 'dont_show')) {
                        shownIds.push(_bannerIds[i]);
                    } else if (_rule.validate(_bannerIds[i])) {
                        this.show(_bannerIds[i]);
                        shownIds.push(_bannerIds[i]);
                    }
                }

                for (i = 0; i < shownIds.length; ++i) {
                    _bannerIds.splice(_bannerIds.indexOf(shownIds[i]), 1);
                }

                if (_bannerIds.length) {
                    setTimeout(this.initBanners.bind(this), 220);
                }
            },

            /**
             * Show banner by its ID
             * @param {String} id
             */
            show: function (id) {
                var el = $('#' + id),
                    popupObject,
                    count,
                    counters = [
                        'display_count',
                        'display_count_per_day',
                        'display_count_per_week',
                        'display_count_per_month'
                    ];

                if (!el || Easybanner.Cookie.isWritable() === false) {
                    return;
                }

                if ($(el).hasClass('placeholder-lightbox')) {
                    popupObject = _lightbox;
                } else if ($(el).hasClass('placeholder-awesomebar')) {
                    popupObject = _awesomebar;
                } else {
                    return;
                }

                // show only one banner at once
                if (popupObject.el && popupObject.el.is(':visible')) {
                    return;
                }
                popupObject.show(el);

                $.each(counters, function (i, counter) {
                    var timeCounterCookie, currentDate;

                    count = _cookie.get(id, counter);

                    if (!count) {
                        count = 0;
                    }

                    if (counter !== 'display_count') {
                        timeCounterCookie = counter + '_time';
                        currentDate = new Date();
                        _cookie.set(id, timeCounterCookie, currentDate.getTime());
                    }

                    _cookie.set(id, counter, ++count);
                });
            },

            /**
             * Hide banner by its ID
             * @param {String} id
             */
            hide: function (id) {
                var el = $('#' + id),
                    popupObject;

                if (el.up('.easybanner-lightbox-el')) {
                    popupObject = _lightbox;
                } else if (el.up('.easybanner-awesomebar-el')) {
                    popupObject = _awesomebar;
                } else {
                    return;
                }

                popupObject.hide();
            },

            hideAll: function () {
                _lightbox.hide();
                _awesomebar.hide();
            }
        };
    };

    function initialize(settings) {
        Easybanner.Cookie = prototypes.cookie(settings.cookieName || 'easybanner');
        Easybanner.Rule = prototypes.rule(settings.conditions || settings);
        Easybanner.Popup = prototypes.popup();
        Easybanner.Popup.init();
    }

    $(document).on('breeze:mount:easybanner', function (event, data) {
        new initialize(data.settings);
    });

    $(document).on('breeze:destroy', function () {
        Easybanner.Popup?.hideAll();
    });

    return initialize;
});
