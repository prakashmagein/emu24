define([
    'jquery',
    'Swissup_Navigationpro/js/microtasks',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('swissup.navpromenu', {
        component: 'menu',
        widgetEventPrefix: 'menu',
        options: {
            menus: 'ul',
            dropdown: 'ul',
            icons: {
                submenu: '',
            },
            useInlineDisplay: true,
            responsive: true,
            expanded: false,
            showDelay: 42,
            hideDelay: 300,
            dropdownShowDelay: 100,
            dropdownHideDelay: 170,
            mediaBreakpoint: '(max-width: 767px)'
        },

        _create: function () {
            this._assignControls()._listen();

            this.mql = window.matchMedia(this.options.mediaBreakpoint)

            // Postponing initialization on mobile until menu became visible
            if (this.mql.matches && this.element.closest('.navigation').length) {
                this.mql.addEventListener('change', this.initMenu.bind(this), { once: true });
                if ($('html').hasClass('nav-before-open')) {
                    this.initMenu();
                } else {
                    $(document).one('beforeOpenSlideout', this.initMenu.bind(this));
                }
            } else {
                this.initMenu();
            }
        },

        initMenu: function () {
            var self = this;

            if (this.inited) {
                return;
            }
            this.inited = true;

            setTimeout(() => {
                if (this.options.responsive) {
                    this.mql.addEventListener('change', this.toggleMode.bind(this));
                    this.toggleMode(this.mql);
                } else if (this.options.mode === 'mobile') {
                    this.toggleMobileMode();
                } else {
                    this.toggleDesktopMode();
                }

                this._setActiveMenu(); // varnish fix
            });

            $('.li-item.level-top', this.element).addClass('ui-menu-item');
            $('.li-item:not(.level-top)', this.element).microtasks().addClass('ui-menu-item');
            $('li.parent > ul', this.element).microtasks().hide();
            $('li.parent > a', this.element).microtasks(200).prepend(
                `<span class="ui-menu-icon ui-icon ${this.options.icons.submenu}"></span>`
            );

            this.element.on('keydown.menu', 'li.parent', function (e) {
                var dropdown = $(this).children(self.options.dropdown),
                    visibleDropdowns = $(self.options.dropdown + '.shown');

                if (!['Enter', 'Escape', ' '].includes(e.key)) {
                    return;
                }

                if (e.key === 'Enter' && dropdown.hasClass('shown')) {
                    return;
                }

                if (e.key === 'Enter' || e.key === ' ') {
                    e.stopPropagation();
                    e.preventDefault();

                    visibleDropdowns.not(dropdown).each(function () {
                        if (!$(this).has(dropdown.get(0)).length) {
                            self.close($(this));
                        }
                    });

                    if (dropdown.hasClass('shown')) {
                        self.close(dropdown);
                    } else {
                        self.open(dropdown);
                    }
                } else if (e.key === 'Escape' && visibleDropdowns.length) {
                    e.stopPropagation();
                    self.close(visibleDropdowns.last());
                }
            });

            $('a', this.element).microtasks().on('click.menu', '.ui-icon', function () {
                var dropdown = $(this).closest('a').siblings(self.options.dropdown);

                if (!dropdown.length) {
                    return;
                }

                if (dropdown.hasClass('shown')) {
                    self.close(dropdown);
                } else {
                    self.open(dropdown);
                }

                return false;
            });
        },

        _assignControls: function () {
            this.controls = {
                toggleBtn: $('[data-action="toggle-nav"]')
            };

            return this;
        },

        _listen: function () {
            this.controls.toggleBtn
                .attr('tabindex', 0)
                .off('click')
                .on('click', this.toggle.bind(this))
                .on('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        this.toggle();
                    }
                });

            $(document).on('keydown', e => {
                if (e.key === 'Escape' && $('html').hasClass('nav-open')) {
                    this.toggle();
                }
            });
        },

        toggle: function () {
            var html = $('html');

            if (html.hasClass('nav-open')) {
                $(document).trigger('beforeCloseSlideout');
                html.removeClass('nav-open');
                setTimeout(function () {
                    html.removeClass('nav-before-open');
                }, this.options.hideDelay);
            } else {
                html.addClass('nav-before-open');
                $(document).trigger('beforeOpenSlideout');
                setTimeout(function () {
                    html.addClass('nav-open');
                }, this.options.showDelay);
            }
        },

        /** Toggles between mobile and desktop modes */
        toggleMode: function (event) {
            if (event.matches) {
                this.toggleMobileMode();
            } else {
                this.toggleDesktopMode();
            }
        },

        /** Enable desktop mode */
        toggleDesktopMode: function () {
            var self = this;

            $(self.options.dropdown + '.shown').each(function () {
                self.close($(this));
            });

            this.element
                .off('click.menu')
                .on('mouseenter.menu', 'li.parent', function () {
                    var dropdown = $(this).children(self.options.dropdown),
                        delay = self.options.dropdownShowDelay;

                    if (this.breezeTimeout) {
                        clearTimeout(this.breezeTimeout);
                        delete this.breezeTimeout;
                    }

                    if ($(self.options.dropdown + '.shown').length) {
                        delay = 50;
                    }

                    this.breezeTimeout = setTimeout(function () {
                        self.open(dropdown);
                    }, delay);
                })
                .on('mouseleave.menu', 'li.parent', function () {
                    if (this.breezeTimeout) {
                        clearTimeout(this.breezeTimeout);
                        delete this.breezeTimeout;
                    }

                    this.breezeTimeout = setTimeout(function () {
                        self.close($(this).children(self.options.dropdown));
                    }.bind(this), self.options.dropdownHideDelay);
                });

            this._trigger('afterToggleDesktopMode');
        },

        /** Enable mobile mode */
        toggleMobileMode: function () {
            var self = this;

            $(this.element)
                .off('mouseenter.menu mouseleave.menu')
                .on('click.menu', 'li.parent', function (e) {
                    var dropdown = $(this).children(self.options.dropdown);

                    if (!dropdown.length || dropdown.hasClass('shown')) {
                        return;
                    }

                    self.open(dropdown);
                    e.preventDefault();
                });

            this._trigger('afterToggleMobileMode');
        },

        open: function (dropdown) {
            this._trigger('beforeOpen', false, {
                dropdown: dropdown
            });

            setTimeout(() => {
                dropdown.addClass('shown')
                    .parent('li')
                    .addClass('opened');

                if (this.options.useInlineDisplay) {
                    dropdown.show();
                }
            }, 10);
        },

        close: function (dropdown) {
            var eventData = {
                dropdown: dropdown,
                preventDefault: false
            };

            this._trigger('beforeClose', false, eventData);

            if (eventData.preventDefault === true) {
                return;
            }

            dropdown.removeClass('shown')
                .parent('li')
                .removeClass('opened');

            if (this.options.useInlineDisplay) {
                dropdown.hide();
            }

            this._trigger('afterClose', false, {
                dropdown: dropdown
            });
        },

        _setActiveMenu: function () {
            var currentUrl = window.location.href.split('?')[0];

            if (!this._setActiveMenuForCategory(currentUrl)) {
                this._setActiveMenuForProduct(currentUrl);
            }
        },

        _setActiveMenuForCategory: function (url) {
            var activeCategoryLink = this.element.find('a[href="' + url + '"]'),
                classes,
                classNav;

            if (!activeCategoryLink || !activeCategoryLink.parent().is('.category-item, .li-item')) {
                return false;
            } else if (!activeCategoryLink.parent().hasClass('active')) {
                activeCategoryLink.parent().addClass('active');
                classes = activeCategoryLink.parent().attr('class');
                classNav = classes.match(/(nav\-)[0-9]+(\-[0-9]+)+/gi);

                if (classNav) {
                    this._setActiveParent(classNav[0]);
                }
            }

            return true;
        },

        _setActiveParent: function (childClassName) {
            var parentElement,
                parentClass = childClassName.substr(0, childClassName.lastIndexOf('-'));

            if (parentClass.includes('-')) {
                parentElement = this.element.find('.' + parentClass);

                if (parentElement) {
                    parentElement.addClass('has-active');
                }
                this._setActiveParent(parentClass);
            }
        },

        _setActiveMenuForProduct: function (currentUrl) {
            var categoryUrlExtension,
                lastUrlSection,
                possibleCategoryUrl,
                //retrieve first category URL to know what extension is used for category URLs
                firstCategoryUrl = this.element.children('li').find('a').attr('href');

            if (firstCategoryUrl) {
                lastUrlSection = firstCategoryUrl.substr(firstCategoryUrl.lastIndexOf('/'));
                categoryUrlExtension = lastUrlSection.includes('.')
                    ? lastUrlSection.substr(lastUrlSection.lastIndexOf('.'))
                    : '';

                possibleCategoryUrl = currentUrl.substr(0, currentUrl.lastIndexOf('/')) + categoryUrlExtension;
                this._setActiveMenuForCategory(possibleCategoryUrl);
            }
        }
    });

    return $.swissup.navpromenu;
});
