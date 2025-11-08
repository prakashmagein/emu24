define([
    'underscore',
    'pagebuilderCarousel'
], function (_) {
    'use strict';

    $.widget('highlightCarousel', {
        component: 'Swissup_Highlight/js/carousel-breeze-theme',

        create: function () {
            this.element
                .on('pagebuilderSlider:ready', this.onSliderReady.bind(this))
                .pagebuilderCarousel(this.options);
        },

        onSliderReady: function () {
            var mql;

            this.slider = this.element.pagebuilderCarousel('instance').slider();

            if (this.slider.options.destroy) {
                mql = window.matchMedia(this.slider.options.destroy.media);
                mql.addListener(this.toggleMode.bind(this));
                this.toggleMode(mql);
            }

            this._on({
                'pagebuilderSlider:slideChange': _.debounce(function () {
                    if (this.isMobile || !$(this.slider.slides.eq(this.slider.page)).hasClass('loading')) {
                        return;
                    }

                    this.loadSlide();
                }.bind(this), 800),
                'click .show-button': this.loadSlide
            });

            $.mixin(this.slider, {
                next: parent => {
                    this.createLoadingPlaceholder();
                    parent();
                }
            });

            this.slider.slider.one('scroll', this.createLoadingPlaceholder.bind(this));
        },

        createLoadingPlaceholder: function () {
            if (!$('.slide.loading', this.element).children().length) {
                $('.slide.loading', this.element).html($('.slide', this.element).first().html());
            }
        },

        toggleMode: function (event) {
            if (event.matches) {
                this.toggleMobileMode();
            } else {
                this.toggleDesktopMode();
            }
        },

        toggleMobileMode: function () {
            this.isMobile = true;
            $('.slide.loading', this.element).addClass('show-button');
        },

        toggleDesktopMode: function () {
            this.isMobile = false;
            $('.slide.loading', this.element).removeClass('show-button');
        },

        loadSlide: function () {
            var data = {},
                pageVar,
                blockData = this.slider.options.blockData;

            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            pageVar = blockData.page_var_name ? blockData.page_var_name : 'p';
            data[pageVar] = this.slider.page + 1;
            data.referer = window.location.href;
            data.block_data = blockData;
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            $('.slide.loading', this.element).removeClass('show-button');

            return $.request.get({
                    url: this.slider.options.dataSourceUrl,
                    data: data
                })
                .then(function (response) {
                    var json = response.body,
                        page = this.slider.page;

                    this.slider.addSlide(page, json.html);

                    setTimeout(function () {
                        $(this.slider.slides[page]).trigger('contentUpdated');
                    }.bind(this), 100);

                    if (this.isMobile) {
                        $('.slide.loading', this.element).addClass('show-button');
                    } else {
                        this.slider.scrollToPage(page, true);
                    }

                    if (json.isLastPage) {
                        this.slider.removeSlide(page + 1);
                    }
                }.bind(this));
        }
    });
});
