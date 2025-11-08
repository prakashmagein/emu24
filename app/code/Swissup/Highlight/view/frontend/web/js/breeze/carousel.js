(function () {
    'use strict';

    /**
     * Initialize content for loading slide
     *
     * @param  {HTMLElement} element
     */
    function _initLoading(element) {
        $('.swiper-slide.loading', element).html(
            $('.swiper-slide', element).first().html()
        );
    }

    /**
     * Load slide via ajax
     *
     * @param  {Object} swiper
     */
    function _loadSlide(swiper) {
        var data = {},
            pageVar,
            blockData = swiper.params.blockData;

        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        pageVar = blockData.page_var_name ? blockData.page_var_name : 'p';
        data[pageVar] = swiper.activeIndex + 1;
        data.referer = window.location.href;
        data.block_data = blockData;
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

        return $.request.get({
                url: swiper.params.dataSourceUrl,
                data: data
            })
            .then(function (response) {
                var json = response.body;

                if (!swiper) {
                    return;
                }

                if (swiper.initialized) {
                    swiper.addSlide(swiper.activeIndex, $(json.html));

                    $(swiper.slides[swiper.activeIndex]).trigger('contentUpdated');

                    if (json.isLastPage) {
                        swiper.removeSlide(swiper.activeIndex + 1);
                    }

                    swiper.update();
                } else {
                    $(json.html)
                        .insertBefore($('.swiper-slide.loading', swiper.el))
                        .trigger('contentUpdated');

                    if (json.isLastPage) {
                        $('.swiper-slide.loading', swiper.el).remove();
                    } else {
                        $('.swiper-slide.loading', swiper.el).addClass('show-button');
                    }
                }

                $('input[name="form_key"]', swiper.el).val($.mage.cookies.get('form_key'));
            });
    }

    $.widget('highlightCarouselOld', {
        component: 'Swissup_Highlight/js/carousel',
        options: {
            on: {
                /**
                 * Listen slide change event
                 */
                slideChange: function () {
                    var slide = this.slides[this.activeIndex];

                    if ($(slide).hasClass('loading')) {
                        _loadSlide(this);
                    }
                }
            }
        },

        /** [create description] */
        create: function () {
            var mql;

            this._on({
                'mouseenter .product-item': () => {
                    this.element.addClass('product-item-hovered');
                },
                'mouseleave .product-item': () => {
                    this.element.removeClass('product-item-hovered');
                }
            });

            _initLoading(this.element);

            if (!this.options.destroy) {
                return this.element.swiper(this.options);
            }

            mql = window.matchMedia(this.options.destroy.media);
            mql.addListener(this.toggleMode.bind(this));
            this.toggleMode(mql);
        },

        /** [toggleMode description] */
        toggleMode: function (event) {
            if (event.matches) {
                this.toggleMobileMode();
            } else {
                this.toggleDesktopMode();
            }
        },

        /** [toggleMobileMode description] */
        toggleMobileMode: function () {
            var self = this;

            if (this.element.swiper && this.element.swiper.destroy) {
                this.element.swiper.destroy();
            }

            $('.swiper-slide.loading', this.element)
                .addClass('show-button')
                .on('click.highlight', function () {
                    $('.swiper-slide.loading', self.element).removeClass('show-button');
                    _loadSlide({
                        el: self.element,
                        params: self.options,
                        activeIndex: $('.swiper-slide.loading', self.element).index()
                    });
                });
        },

        /** [toggleDesktopMode description] */
        toggleDesktopMode: function () {
            this.element.swiper(this.options);
            $('.swiper-slide.loading', this.element).off('click.highlight');
        }
    });
})();
