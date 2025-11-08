define([
    'jquery'
], function ($) {
    'use strict';

    /**
     *
     */
    return function (options, element) {
        var search;

        options = $.extend({}, {
            classes: {
                container: '.block-swissup-ajaxsearch',
                mask: '.ajaxsearch-mask'
            }
        }, options);

        search = {
            /**
             * Show
             */
            show: function () {
                this.calculateFieldPosition();

                // show fields
                $(options.classes.container).addClass('shown');
                $(options.classes.mask).addClass('shown');
                $(element).focus();
            },

            /**
             * Hide
             */
            hide: function () {
                $(options.classes.container).removeClass('shown');
                $(options.classes.mask).removeClass('shown');
                $(element).closest('div.control').addClass('inactive');
            },

            /**
             * Calculate and set
             */
            calculateFieldPosition: function () {
                // calculate offsetTop dynamically to guarantee that field
                // will appear in the right place (dynamic header height, etc.)
                // var header = $('.header.content'),
                //     headerOffset = header.offset(),
                //     zoomOffset = $('.action.search', options.classes.container).offset(),
                //     offsetTop = zoomOffset.top - headerOffset.top;

                // if (header.length === 0) {
                //     header = $('.header .container');
                // }

                // if ($('body').width() < 768) {
                //     // reset for small screens
                //     offsetTop = '';
                // } else if (offsetTop <= 0) {

                //     return;
                // }
                // $('.action.close', options.classes.container).css({
                //     top: offsetTop
                // });
                // $('.field.search', options.classes.container).css({
                //     paddingTop: offsetTop
                // });
            },

            /**
             *
             * @return {Boolean}
             */
            isVisible: function () {
                return $(options.classes.container).hasClass('shown') ||
                    !$(options.classes.container).find('div.control').hasClass('inactive');
            },

            /**
             * Initialize
             */
            init: function () {
                var self = this,
                    block = $(options.classes.container);

                block.append(
                    '<div class="' + options.classes.mask.substr(1) + '"></div>'
                );

                $(document.body).keydown(function (e) {

                    if (e.which === 27) {
                        self.hide();
                    }
                });

                $(window).resize(this.calculateFieldPosition.bind(this));

                $(options.classes.mask).on('click', this.hide.bind(this));

                $('.action.search', options.classes.container).removeAttr('disabled');

                $('.action.search', options.classes.container).on('click', function (e) {
                    if (!self.isVisible()) {
                        e.preventDefault();
                        self.show();
                    }
                });

                if (!block.find('.actions .action.close').length) {
                    block.find('.actions .action.search').after(options.closeBtn);
                }

                $(options.closeBtn).insertAfter(this.element);
                $('.action.close', options.classes.container).on('click', function (e) {
                    e.preventDefault();
                    self.hide();
                });
            }
        };

        if ($(element).data('swissupAjaxsearch') &&
            $(element).data('swissupAjaxsearch').getBloodhound()
        ) {
            search.init();
        } else {
            $(element).on('ajaxsearchready', search.init.bind(search));
        }
    };
});
