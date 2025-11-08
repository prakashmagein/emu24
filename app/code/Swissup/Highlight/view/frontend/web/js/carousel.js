define([
    'jquery',
    'matchMedia',
    'Swissup_Swiper/js/swiper'
], function ($, mediaCheck) {
    'use strict';

    /**
     * Initialize content for loading slide
     *
     * @param  {HTMLElement} element
     */
    function _initLoading(element) {
        var loading = $('.swiper-slide.loading', element);

        if (!loading.children().length) {
            loading.html($('.swiper-slide', element).first().html());
        }
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

        return $.ajax({
                cache: true,
                url: swiper.params.dataSourceUrl,
                data: data
            })
            .done(function (json) {
                var $slide = $(json.html);

                if (!swiper) {
                    return;
                }

                if (swiper.initialized) {
                    // Insert new slide to swiper
                    swiper.addSlide(swiper.activeIndex, $slide);
                    // init magento scripts
                    $(swiper.slides[swiper.activeIndex]).trigger('contentUpdated');
                    // remove dummy slide with loading because when it is last page
                    if (json.isLastPage) {
                        swiper.removeSlide(swiper.activeIndex + 1);
                    }
                    swiper.update();
                } else {
                    // Insert new content (no swiper)
                    $slide
                        .insertBefore($('.swiper-slide.loading', swiper.$el))
                        .trigger('contentUpdated'); // init magento scripts
                    // remove dummy slide with loading because when it is last page
                    if (json.isLastPage) {
                        $('.swiper-slide.loading', swiper.$el).remove();
                    } else {
                        $('.swiper-slide.loading', swiper.$el).addClass('show-button');
                    }
                }

                $('input[name="form_key"]', swiper.$el).val($.mage.cookies.get('form_key'));
                // Listen hover for items and add class to container
                $('.product-item', $slide).hover(
                    () => swiper.el?.classList.add('product-item-hovered'),
                    () => swiper.el?.classList.remove('product-item-hovered')
                );
            });
    }

    return function (config, element) {
        var $element = $(element),
            options = $.extend({
                on: {
                    /**
                     * Listen slide change event
                     */
                    slideChange: function () {
                        var slide = this.slides[this.activeIndex];

                        if ($(slide).hasClass('loading')) {
                            _loadSlide(this);
                        }
                    },

                    sliderFirstMove: function () {
                        _initLoading(element);
                    }
                }
            }, config);

        // Listen hover for items and add class to container
        $('.product-item', $element).hover(
            () => element.classList.add('product-item-hovered'),
            () => element.classList.remove('product-item-hovered')
        );

        $element.closest('.block-content').one('scroll', () => _initLoading(element));

        if (!config.destroy) {
            return $element.swiper(options);
        }

        mediaCheck({
            media: config.destroy.media,

            /**
             * [entry description]
             */
            entry: function () {
                // Destroy swiper and listen click on loading slide
                if (element.swiper && element.swiper.destroy) {
                    element.swiper.destroy();
                }
                $('.swiper-slide.loading', element)
                    .addClass('show-button')
                    .on('click', function () {
                        $('.swiper-slide.loading', element).removeClass('show-button');
                        _loadSlide({
                            $el: $element,
                            params: config,
                            activeIndex: $('.swiper-slide.loading', element).index()
                        });
                    });
            },

            /**
             * [exit description]
             */
            exit: function () {
                // Initialize swiper and turn off click on loading slide
                $(element).swiper(options);
                $('.swiper-slide.loading', element).off('click');
            }
        });
    };
});
