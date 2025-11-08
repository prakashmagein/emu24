define(['jquery', 'mage/translate', 'domReady!'], function ($, $t) {
    'use strict';

    if (!$('.navpro-nowrap').length) {
        return;
    }

    /**
     * Prepare and return "More.." container
     *
     * @param  {Element} menu
     * @return {jQuery}
     */
    function getContainer(menu) {
        if (menu.length) {
            menu = menu[0];
        }

        if (menu.navproNowrapContainer) {
            return menu.navproNowrapContainer;
        }

        menu.navproNowrapContainer = $(
            '<li style="display: none" class="li-item level0 size-small level-top parent last caret-hidden navpro-item-more">' +
                '<a class="nav-a level-top nav-a-icon-more" href="#" title="' + $t('View more') + '">' +
                    '<svg viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">' + //eslint-disable-line max-len
                        '<path d="M4,12 C5.1045695,12 6,11.1045695 6,10 C6,8.8954305 5.1045695,8 4,8 C2.8954305,8 2,8.8954305 2,10 C2,11.1045695 2.8954305,12 4,12 Z M10,12 C11.1045695,12 12,11.1045695 12,10 C12,8.8954305 11.1045695,8 10,8 C8.8954305,8 8,8.8954305 8,10 C8,11.1045695 8.8954305,12 10,12 Z M16,12 C17.1045695,12 18,11.1045695 18,10 C18,8.8954305 17.1045695,8 16,8 C14.8954305,8 14,8.8954305 14,10 C14,11.1045695 14.8954305,12 16,12 Z"></path>' + //eslint-disable-line max-len
                    '</svg>' +
                    '<span style="visibility: hidden; width: 0; overflow: hidden; white-space: nowrap;">' + $t('View more') + '</span>' +
                '</a>' +
                '<div class="navpro-dropdown navpro-dropdown-level1 size-small" data-level="0" role="menu">' +
                    '<div class="navpro-dropdown-inner">' +
                        '<div class="navpro-row gutters">' +
                            '<div class="navpro-col navpro-col-12">' +
                                '<ul class="children navpro-wrapped-items" data-columns="1"></ul>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<span class="navpro-shevron"></span>' +
                '</div>' +
            '</li>'
        ).appendTo(menu);

        return menu.navproNowrapContainer;
    }

    /**
     * Apply css class changes, when moving menu item
     *
     * @param  {Object} item
     * @param  {Number} increment
     */
    function changeLevel(item, increment) {
        var className = $(item).attr('class'),
            dataLevel = $(item).data('level'),
            level = className.match(/level(\d+)/);

        className = className.replace(
            'level' + level[1],
            'level' + (parseInt(level[1], 10) + increment)
        );

        $(item).attr('class', className);

        if (dataLevel !== undefined) {
            $(item).data('level', parseInt(dataLevel, 10) + increment);
        }
    }

    /**
     * Move items into the "More.." dropdown
     * @param {Object} items
     */
    function hideItems(items) {
        items.removeClass('level-top').children('a').removeClass('level-top');

        setTimeout(() => {
            items.appendTo(getContainer(items.parent()).find('.navpro-wrapped-items'));
        });

        setTimeout(() => {
            items.each(function () {
                changeLevel(this, 1);
            });
        });

        setTimeout(() => {
            items.find('.navpro-dropdown').each(function () {
                changeLevel(this, 1);
            });
        });
    }

    /**
     * Move items out from the "More.." dropdown
     * @param {Object} items
     */
    function showItems(items) {
        var menu = items.parents('.navpro-menu');

        if (!menu.length) {
            return;
        }

        items.insertBefore(getContainer(menu));
        items.addClass('level-top').children('a').addClass('level-top');
        items.each(function () {
            changeLevel(this, -1);
        });
        items.find('.navpro-dropdown').each(function () {
            changeLevel(this, -1);
        });
        items.css('display', '');
    }

    /**
     * Move items into "More.." submenu
     *
     * @param {Element} menu
     */
    function build(menu) {
        var itemsToMove = $(),
            container = getContainer(menu),
            windowWidth = $(window).width(),
            offset = menu.className.match(/navpro-nowrap-offset-(\d+)/),
            liWidth = 0,
            moreItemWidth = 0,
            // Reserve slightly less space for the "More" trigger so an
            // additional top-level item can remain visible on wider screens.
            moreWidth = Math.max(32, Math.round(container.outerWidth())),
            left, right, isFullyVisible, firstItem;

        undoBuild(menu);

        offset = offset ? offset[1] : 0;
        firstItem = $(menu).children('li').first();

        if (firstItem.get(0).offsetLeft < 0 ||
            firstItem.get(0).offsetLeft < $(menu).get(0).offsetLeft ||
            firstItem.get(0).offsetLeft + firstItem.outerWidth() > windowWidth) {

            $(menu).addClass('navpro-nowrap-justify-start');
        }

        right = Math.round(Math.min(
            $(menu).offset().left + $(menu).outerWidth(),
            windowWidth
        ));

        $(menu).children('li').not('.navpro-item-more').each(function () {
            liWidth += parseInt($(this).width(), 10);
        });

        if ($(menu).width() - liWidth <= 30) {
            right -= offset;
        }

        itemsToMove = $($(menu).children('li').get().reverse()).filter(function (i, el) {
            left = $(el).offset().left;

            if ($('body').hasClass('rtl')) {
                isFullyVisible = Math.round(left - moreItemWidth) > 0;
            } else {
                isFullyVisible = Math.round(left + $(el).outerWidth() + moreItemWidth) <= right;
            }

            if (isFullyVisible) {
                return false;
            }

            // do not move "More..." item. Move previous item instead.
            if ($(el).hasClass('navpro-item-more')) {
                return false;
            }

            moreItemWidth = moreWidth;

            return true;
        });

        setTimeout(() => {
            $(menu).addClass('navpro-nowrap-ready');
            $(menu).removeClass('navpro-nowrap-justify-start');
            container.css('display', itemsToMove.length ? '' : 'none');
        }, 5);

        if (!itemsToMove.length) {
            return;
        }

        hideItems($(itemsToMove.get().reverse()));
    }

    function undoBuild(menu) {
        var container = getContainer(menu).css('display', 'none');
        showItems(container.find('.navpro-wrapped-items > li'));
        $(menu).removeClass('navpro-nowrap-ready').data('navpro-width', '');
    }

    function update() {
        $('.navpro.orientation-horizontal .navpro-nowrap').each(function () {
            if (!$(this).data('navpro-width') || $(this).outerWidth() !== $(this).data('navpro-width')) {
                build(this);
                $(this).data('navpro-width', $(this).outerWidth());
            }
        });
    }

    var lastStarted;
    function start() {
        var started = new Date();

        if (lastStarted && started - lastStarted < 1500) {
            return;
        }

        lastStarted = started;
        update();
        $('.navpro.orientation-vertical .navpro-nowrap').removeClass('navpro-nowrap');
        $(document).on($.breeze ? 'breeze:resize-x' : 'navpro:windowResize', update);
    }

    function stop() {
        $('.navpro.orientation-horizontal .navpro-nowrap').each(function () {
            undoBuild(this);
        });
        $(document).off($.breeze ? 'breeze:resize-x' : 'navpro:windowResize', update);
        $(document).off('breeze:load', start);
    }

    (() => {
        var mediaBreakpoint = '(max-width: '
                + window.getComputedStyle(document.body).getPropertyValue('--navpro-accordion-max-width')
                + ')',
            mql = window.matchMedia(mediaBreakpoint);

        mql.addEventListener('change', e => {
            if (e.matches) {
                stop();
            } else {
                start();
                $(document).on('breeze:load', start);
            }
        });

        if (!mql.matches) {
            start();
            $(document).on('breeze:load', start);
        }
    })();
});