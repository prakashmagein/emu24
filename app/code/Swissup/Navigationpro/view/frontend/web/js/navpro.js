define([
    'jquery',
    'underscore',
    'mage/translate',
    'Swissup_Navigationpro/js/touch',
    'Swissup_Navigationpro/js/menu',
    'jquery-ui-modules/position'
], function ($, _, $t, touch) {
    'use strict';

    var menuWidgetName = $.fn.navpromenu ? 'navpromenu' : 'menu';

    $(window).on('resize', _.debounce(function () {
        $('body').trigger('navpro:windowResize');
    }, 100));

    $(document).on('click', '.navpro-overlay-element', function () {
        if ($('html').hasClass('nav-open')) {
            $('.nav-toggle').first().trigger('click');
        }
    });

    $.widget('swissup.navpro', {
        component: 'navpro',
        options: {
            position: {
                my: 'left top',
                at: 'right top',
                collision: 'flipfit fit'
            },
            level0: {
                position: {
                    my: 'center top',
                    at: 'center bottom',
                    collision: 'fit none'
                }
            }
        },

        /** [create description] */
        _create: function () {
            if (!this.options.mediaBreakpoint) {
                this.options.mediaBreakpoint = '(max-width: '
                    + window.getComputedStyle(document.body).getPropertyValue('--navpro-accordion-max-width')
                    + ')';
            }

            this.initDefaultMenu();

            this.mql = window.matchMedia(this.options.mediaBreakpoint);

            // Postponing initialization on mobile until menu became visible
            if (this.mql.matches) {
                this.mql.addEventListener('change', this.initMenu.bind(this), { once: true });
                $(document).one('beforeOpenSlideout menuSlideout:beforeOpen', this.initMenu.bind(this));
            } else {
                this.initMenu();
            }
        },

        initMenu: function () {
            if (this.inited) {
                return;
            }
            this.inited = true;

            this._expandHomeMenu();

            $(document).on('navpro:windowResize', function () {
                this._updateDimensions();
            }.bind(this));
        },

        _expandHomeMenu: function () {
            var element = this.element,
                menu = element[menuWidgetName]('instance'),
                width,
                mql;

            if (!element.find('li.home-expanded').length ||
                !$('body').hasClass('cms-index-index') ||
                $('body').hasClass('theme-editor-sticky')
            ) {
                return;
            }

            function toggleMenu(result) {
                if (result.matches) {
                    $('li.home-expanded', element).each(function () {
                        $(this).addClass('persistent');
                        $(this).children('.navpro-dropdown').addClass('persistent');
                        menu.open($(this).children('.navpro-dropdown'));
                    });
                } else {
                    $('li.home-expanded', element).each(function () {
                        $(this).removeClass('persistent');
                        $(this).children('.navpro-dropdown').removeClass('persistent');
                        menu.close($(this).children('.navpro-dropdown'));
                    });
                }
            }

            width = window.getComputedStyle(document.body).getPropertyValue('--navpro-initially-expanded-min-width');
            mql = matchMedia('(min-width: ' + width + ')');
            mql.addListener(toggleMenu);

            setTimeout(() => {
                toggleMenu(mql);
            });
        },

        /** Initialize default menu with navpro additions */
        initDefaultMenu: function () {
            var self = this,
                clickable = this.element.hasClass('navpro-click') ||
                    this.element.closest('.navpro').hasClass('navpro-accordion');

            this.element
                .on('menuaftertoggledesktopmode menu:afterToggleDesktopMode', function () {
                    self.element.on('click.navpro', 'li.parent', function (event) {
                        var menu = self.element[menuWidgetName]('instance'),
                            dropdown = $(this).children(menu.options.dropdown),
                            isOpened = dropdown.hasClass('shown'),
                            // Interval between mouseenter and click events.
                            // When this interval is short, we assume that the
                            // customer wanted to open dropdown using touch device.
                            timeSpent = new Date() - self.timeOpened;

                        if ($(event.target).hasClass('navpro-close') || !touch.touching()) {
                            return;
                        }

                        if (!dropdown.length || isOpened && timeSpent > 10) {
                            return;
                        }

                        if (!isOpened) {
                            menu.open(dropdown);
                        }

                        event.preventDefault();
                    });
                })
                .on('menuaftertogglemobilemode menu:afterToggleMobileMode', function () {
                    self.element.off('click.navpro');
                })
                .on('menubeforeopen menu:beforeOpen', async function (event, data) {
                    self.timeOpened = new Date();
                    if (data.dropdown.parent().hasClass('navpro-item-more')) {
                        data.dropdown.find('.navpro-wrapped-items > li').css('display', '');
                    }
                    data.dropdown.css('display', 'block');
                    self._prepareDropdownContent(data.dropdown);
                    self._updateDimensions(data.dropdown);
                    self.position(data.dropdown);
                })
                .on('menubeforeclose menu:beforeClose', function (event, data) {
                    if (data.dropdown.hasClass('persistent')) {
                        data.preventDefault = true;
                    }
                })
                .on('menuafterclose menu:afterClose', function (event, data) {
                    data.dropdown.css('display', '');
                })
                .on('click', '.navpro-close', function (event) {
                    var dropdown = $(this).closest('.navpro-dropdown');

                    if (!dropdown.length) {
                        dropdown = $(this).closest('.navpro-menu').find('.navpro-dropdown.shown');
                    }

                    event.preventDefault();

                    dropdown.each((i, el) => {
                        self.element[menuWidgetName]('close', $(el));
                    });
                });

            if (this.element.hasClass('navpro-click')) {
                $(document).on('click', e => {
                    $('.navpro-dropdown.shown', this.element)
                        .filter((i, el) => !el.contains(e.target))
                        .each((i, el) => {
                            this.element[menuWidgetName]('close', $(el));
                        });
                });
            }

            $(document).on('menuslideoutafterclose menuSlideout:afterClose', () => {
                if (!this.element.parent().hasClass('navpro-slideout')) {
                    return;
                }

                this.element.closest('.navpro-menu').find('.navpro-dropdown.shown').each((i, el) => {
                    this.element[menuWidgetName]('close', $(el));
                });
            });

            this.element[menuWidgetName]({
                    dropdown: '.navpro-dropdown',
                    icons: {
                        submenu: 'navpro-icon-caret',
                    },
                    mediaBreakpoint: this.options.mediaBreakpoint,
                    responsive: clickable ? false : true,
                    mode: clickable ? 'mobile' : 'desktop',
                    useInlineDisplay: false
                });
        },

        /** [closeAll description] */
        closeAll: function (except) {
            var self = this;

            $('.navpro-dropdown.shown').each(function () {
                var dropdown = $(this);

                if (except) {
                    if (dropdown.is(except) || dropdown.has(except).length) {
                        return;
                    }
                }

                self.element[menuWidgetName]('close', dropdown);
            });
        },

        /** [position description] */
        position: function (submenu) {
            if (this._shouldUseMobileFriendlyFallbacks()) {
                submenu.css('position', '');
            } else {
                submenu.position(this._getSubmenuPosition(submenu));
                submenu.children('.navpro-shevron').css('position', '').position({
                    my: 'center top',
                    at: 'center bottom',
                    of: submenu.parent('li'),
                    using: this._uiPosition
                });
            }
        },

        /** Recalculate position according to possible 'translate' effects */
        _uiPosition: function (position, feedback) {
            var el = feedback.element.element,
                transform = el.hasClass('navpro-shevron') ?
                    el.closest('.navpro-dropdown').css('transform') : el.css('transform'),
                values = transform.match(/-?\d+\.?\d+|\d+/g),
                translateX,
                translateY;

            if (values && values.length === 6) {
                translateX = parseInt(values[4], 10);
                translateY = parseInt(values[5], 10);

                if (translateY) {
                    position.top += translateY;
                } else if (translateX) {
                    position.left += translateX;
                }
            }

            el.css(position);
        },

        _getSubmenuPosition: function (submenu) {
            var within, width, constraints,
                level = parseInt(submenu.data('level'), 10),
                parentLi = submenu.closest('li'),
                isVertical = this._isOrientationVertical(),
                position = $.extend({
                    of: submenu.parent('li'),
                    using: this._uiPosition
                }, this.options.position);

            if (this.options['level' + level]?.position) {
                within = this.element;
                width = $(this.element).outerWidth();

                constraints = [
                    '.header.content',
                    '.column.main',
                    '.page-main',
                    '.footer.content',
                    '.container'
                ];

                if (isVertical) {
                    constraints.push('.page-wrapper');
                }

                // Constrain dropdown inside parent edges
                $(this.element)
                    .closest(constraints.join(','))
                    .each(function () {
                        var currentWidth = $(this).outerWidth();

                        if (currentWidth && currentWidth > width) {
                            within = this;
                        }
                    });

                position = $.extend(
                    {
                        within: within
                    },
                    position,
                    this.options['level' + level].position
                );
            }

            // manual dropdown positioning
            if (submenu.hasClass('navpro-stick-left') || parentLi.hasClass('navpro-stick-left')) {
                if (!isVertical && level === 0) {
                    position = this._switchPosition(position, 'right', 'left');
                    position = this._switchPosition(position, 'center', 'left');
                } else {
                    position.my = position.my.replace('right ', 'left ');
                    position.at = position.at.replace('left ', 'right ');
                }
            } else if (submenu.hasClass('navpro-stick-right') || parentLi.hasClass('navpro-stick-right')) {
                if (!isVertical && level === 0) {
                    position = this._switchPosition(position, 'left', 'right');
                    position = this._switchPosition(position, 'center', 'right');
                } else {
                    position.my = position.my.replace('left ', 'right ');
                    position.at = position.at.replace('right ', 'left ');
                }
            } else if (submenu.hasClass('navpro-stick-center') || parentLi.hasClass('navpro-stick-center')) {
                if (!isVertical && parentLi.hasClass('level0')) {
                    position = this._switchPosition(position, 'left', 'center');
                    position = this._switchPosition(position, 'right', 'center');
                }
            }

            // RTL support
            if ($('body').hasClass('rtl')) {
                if (position.my.indexOf('left ') === 0) {
                    position.my = position.my.replace('left ', 'right ');
                } else {
                    position.my = position.my.replace('right ', 'left ');
                }

                if (position.at.indexOf('left ') === 0) {
                    position.at = position.at.replace('left ', 'right ');
                } else {
                    position.at = position.at.replace('right ', 'left ');
                }
            }

            return position;
        },

        /**
         * @param  {Object} position
         * @param  {String} from
         * @param  {String} to
         * @return {Object}
         */
        _switchPosition: function (position, from, to) {
            from += ' ';
            to += ' ';

            position.my = position.my.replace(from, to);
            position.at = position.at.replace(from, to);

            return position;
        },

        _updateDimensions: function (dropdown) {
            var useMobileFallback = this._shouldUseMobileFriendlyFallbacks(),
                minHeightSelector = false;

            if (!dropdown) {
                dropdown = $('.navpro-dropdown.shown', this.element);
            }

            if ($(this.element).hasClass('navpro-stacked')) {
                minHeightSelector = '.navpro-dropdown';
            } else if ($(this.element).hasClass('navpro-amazon')) {
                minHeightSelector = '.navpro-departments .navpro-dropdown-level2';
            }

            if (minHeightSelector && dropdown.is(minHeightSelector)) {
                dropdown.css({
                    'min-height': useMobileFallback
                        ? ''
                        : dropdown.parent().closest('.navpro-dropdown').outerHeight()
                });
            }

            if (dropdown.is('.size-fullwidth, .size-fullscreen, .size-boxed, .size-xlarge, .size-large')) {
                dropdown.css({
                    'max-width': useMobileFallback ? '' : $(document.body).width()
                });
            }
        },

        /**
         * Used for:
         *  Height calculation for vertical columns mode
         *  Dropdown positioning
         * @return {Boolean}
         */
        _shouldUseMobileFriendlyFallbacks: function () {
            return this._isAccordion() ||
                window.innerWidth <= this._getMobileMaxWidth() && this._isTransformable();
        },

        /**
         * Get max width for mobile view
         * @return {Number}
         */
        _getMobileMaxWidth: function () {
            if (!this.mobileMaxWidth) {
                this.mobileMaxWidth = this.options.mediaBreakpoint.match(/\d+/)?.[0] || 767;
                +this.mobileMaxWidth;
            }
            return this.mobileMaxWidth;
        },

        /**
         * Checks if menu orientation is vertical
         * @returns {bool}
         * @private
         */
        _isOrientationVertical: function () {
            return $(this.element).parent('.navpro').hasClass('orientation-vertical');
        },

        /**
         * Checks if menu is accordion
         * @returns {bool}
         * @private
         */
        _isAccordion: function () {
            return $(this.element).parent('.navpro').hasClass('navpro-accordion');
        },

        /**
         * Checks if menu is tranformable into accordion
         * @returns {bool}
         * @private
         */
        _isTransformable: function () {
            return $(this.element).parent('.navpro').hasClass('navpro-transformable');
        },

        _prepareDropdownContent: function (dropdown) {
            if ($(this.element).hasClass('navpro-stacked') &&
                this._isOrientationVertical() &&
                $(this.element).parent('.navpro').hasClass('navpro-slideout') ||
                $(this.element).hasClass('navpro-mobile-slideout')
            ) {
                this._addCloseButtonsToSubmenus(dropdown);
                this._addParentCategoryLinkToSubmenus(dropdown);
            } else if ($(this.element).hasClass('navpro-closeable')) {
                this._addCloseButtonsToSubmenus(dropdown);
            }
        },

        /**
         * Add close buttons to the top of each dropdown
         */
        _addCloseButtonsToSubmenus: function (dropdown) {
            if (dropdown.find('> .navpro-close').length) {
                return;
            }

            var category = $(dropdown).parent('li').find('> a span').not('.ui-menu-icon').text();

            $(dropdown).prepend($(`
                <span class="navpro-close"
                    data-label-category="${category}"
                    data-label-back="${$t('Back')}"></span>
            `));
        },

        /**
         * Add category link to the top of each dropdown
         */
        _addParentCategoryLinkToSubmenus: function (dropdown) {
            if (dropdown.find('.all-category').length) {
                return;
            }

            var item = dropdown.parent('.category-item.parent'),
                category = $(item).children('a').find('span').not('.ui-menu-icon').text(),
                categoryUrl = $(item).children('a').attr('href'),
                categoryLink,
                categoryParent;

            categoryLink = $('<a>')
                .attr('href', categoryUrl)
                .text($t('All %1').replace('%1', category));

            categoryParent = $('<li>')
                .addClass('ui-menu-item all-category level category-item')
                .html(categoryLink.get(0).outerHTML);

            $(item).find('ul.children').first().prepend(categoryParent.get(0));
        }
    });

    return $.swissup.navpro;
});
