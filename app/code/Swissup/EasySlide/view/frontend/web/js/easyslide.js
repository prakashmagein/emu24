define([
    'jquery',
    'Magento_Ui/js/modal/modal' // create 'jquery-ui-modules/widget' dependency
], function ($) {
    'use strict';

    /** Update thumbnails */
    function _updateThumbs() {
        var thumbsSwiper = this,
            thumbsWidth,
            sliderHeight;

        if (thumbsSwiper.isVertical()) {
            sliderHeight = $(thumbsSwiper.el).siblings().height();
            thumbsWidth = $(thumbsSwiper.el).outerWidth();
            $(thumbsSwiper.el).css('max-height', sliderHeight + 'px');
            $(thumbsSwiper.el).siblings().css('max-width', 'calc(100% - ' + thumbsWidth + 'px)');
        }

        thumbsSwiper.update();
    }

    $.widget('swissup.easyslide', {
        component: 'Swissup_EasySlide/js/easyslide',
        options: {
            autoHeight: true,
            slidesPerView: 'auto',
            loop: true,
            roundLengths: true
        },

        _create: function () {
            this.swiperInitCallbacks = [this._fixThumbNotActivated.bind(this), this.ensureLoopConditions.bind(this)];
            this._removeInactiveSlides();
            this._bindSliderAdjustment();
            this._bindThumbsAdjustment();
            require(['Swissup_Swiper/js/swiper'], () => {
                this.element.swiper($.extend({}, this.options, {
                    on: {
                        init: swiper => this.swiperInitCallbacks.map(func => func(swiper)),
                        resize: swiper => { this.ensureLoopConditions(swiper) }
                    }
                }));
            });
        },

        _removeInactiveSlides: function () {
            const now = Math.floor(Date.now() / 1000);
            const remove = ($container) => {
                $container.find('.swiper-slide').each((i, slide) => {
                    const $slide = $(slide);
                    const activeFrom = $slide.data('activeFrom') || 0;
                    const activeTo = $slide.data('activeTo') || now;

                    if (activeFrom > now || now > activeTo)
                        $slide.remove();
                });
            };

            remove($(this.element));
            remove($(this.options.thumbs?.swiper?.el));
        },

        /**
         * Bind functions for slider adjustments
         */
        _bindSliderAdjustment: function () {
            var countSlides;

            if (this.options.startRandomSlide) {
                countSlides = $('.swiper-slide', this.element).length;
                this.options.initialSlide = Math.round(Math.random() * countSlides);
                this.swiperInitCallbacks.push(this.showSlider.bind(this));
            }
        },

        /** Bind functions for thumbnails adjustments */
        _bindThumbsAdjustment: function () {
            if (!this.options.thumbs || !this.options.thumbs.swiper) {
                return;
            }

            // Set max-height for thumbs when they are on side (vertical)
            if (this.options.thumbs.swiper.direction === 'vertical') {
                this.swiperInitCallbacks.push(_updateThumbs);
                this.options.thumbs.swiper.on = $.extend({}, this.options.thumbs.swiper.on, {
                    resize: _updateThumbs
                });
            }

            // Update thumbs in case slider has lazy load
            if (this.options.thumbs.swiper.lazy) {
                this.options.thumbs.swiper.on = $.extend({}, this.options.thumbs.swiper.on, {
                    lazyImageReady: _updateThumbs
                });
            }
        },

        _fixThumbNotActivated: function () {
            var thumbs = this.element.get(0).swiper.thumbs,
                thumbActiveClass;

            if (thumbs && thumbs.swiper) {
                thumbs.swiper.params.virtual = false;
                thumbActiveClass = thumbs.swiper.params.thumbs.slideThumbActiveClass;

                if (!$('.' + thumbActiveClass, thumbs.swiper.el).length) {
                    $(thumbs.swiper.slides).first().addClass(thumbActiveClass);
                }
            }
        },

        ensureLoopConditions: function(swiper, callback = () => {}) {
            const currentParams = swiper.params;
            let { slidesPerView, slidesPerGroup } = currentParams;
            const totalSlides = swiper.slides.length;

            if (slidesPerView === 'auto') {
                slidesPerView = Math.ceil(swiper.width / swiper.slides[0].offsetWidth) + 1;
            }

            const minSlides = parseInt(slidesPerView) + slidesPerGroup;

            if (totalSlides < minSlides || totalSlides % slidesPerGroup !== 0) {
                this.duplicateSlides(swiper, minSlides, slidesPerGroup, callback);
            }
        },

        duplicateSlides: function(swiper, minSlides, slidesPerGroup, callback = () => {}) {
            const totalSlides = swiper.slides.length;
            let slidesToAdd = minSlides - totalSlides;

            if (totalSlides % slidesPerGroup !== 0) {
                slidesToAdd += slidesPerGroup - (totalSlides % slidesPerGroup);
            }

            const fragment = document.createDocumentFragment();
            const slidesHTML = swiper.slides.map(slide => slide.outerHTML).join('');

            for (let i = 0; i < slidesToAdd; i++) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = slidesHTML;
                while (tempDiv.firstChild) {
                    fragment.appendChild(tempDiv.firstChild);
                }
            }

            swiper.wrapperEl.appendChild(fragment);
            swiper.update();
            callback();
        },

        /**
         * Show slider hidden with css opacity
         */
        showSlider: function () {
            this.element.css('opacity', 1);
        }
    });

    return $.swissup.easyslide;
});
