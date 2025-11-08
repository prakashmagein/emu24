define([
    'ko',
    'jquery',
    'Magento_Ui/js/lib/knockout/template/loader',
    'mage/template'
], function (ko, $, templateLoader, template) {
    'use strict';

    var blockLoaderTemplatePath = 'ui/block-loader',
        blockContentLoadingClass = '_block-content-loading',
        blockLoader,
        blockLoaderClass,
        loaderImageHref,
        loaderImageMaxWidth = false;

    /**
     *
     */
    function initLoaderTemplate() {
        templateLoader.loadTemplate(blockLoaderTemplatePath).done(function (blockLoaderTemplate) {
            blockLoader = template($.trim(blockLoaderTemplate), {
                loaderImageHref: loaderImageHref
            });
            blockLoader = $(blockLoader);

            if (loaderImageMaxWidth) {
                blockLoader.find('.loader img').css({
                    'max-width': loaderImageMaxWidth
                });
            }

            blockLoaderClass = '.' + blockLoader.attr('class');
            $(document).trigger('ajaxproloaderready');
        });
    }

    if (typeof loaderImageHref !== 'undefined') {
        initLoaderTemplate();
    }

    /**
     * Helper function to check if blockContentLoading class should be applied.
     * @param {Object} element
     * @returns {Boolean}
     */
    function isLoadingClassRequired(element) {
        var position = element.css('position');

        if (position === 'absolute' || position === 'fixed') {
            return false;
        }

        return true;
    }

    /**
     * Add loader to block.
     * @param {Object} element
     */
    function addBlockLoader(element) {
        element.find(':focus').blur();
        element.find('input:disabled, select:disabled').addClass('_disabled');
        element.find('input, select').prop('disabled', true);

        if (isLoadingClassRequired(element)) {
            element.addClass(blockContentLoadingClass);
        }
        element.append(blockLoader.clone());
    }

    /**
     * Remove loader from block.
     * @param {Object} element
     */
    function removeBlockLoader(element) {
        var timeout;

        if (!element.has(blockLoaderClass).length) {
            return;
        }
        timeout = 1000;

        setTimeout(function () {
            element.find(blockLoaderClass).remove();
            element.find('input:not("._disabled"), select:not("._disabled")').prop('disabled', false);
            element.find('input:disabled, select:disabled').removeClass('_disabled');
            element.removeClass(blockContentLoadingClass);
        }, timeout);
    }

    return {
        /**
         * Set loader image url
         * @param {String} loaderHref
         * @return {this}
         */
        setLoaderImage: function (loaderHref) {
            loaderImageHref = loaderHref;
            initLoaderTemplate();

            return this;
        },

        /**
         *
         * @param  {Int} maxWidth
         * @return {this}
         */
        setLoaderImageMaxWidth: function (maxWidth) {
            loaderImageMaxWidth = maxWidth;

            return this;
        },

        /**
         * Start full loader action
         */
        startLoader: function (element) {
            if (blockLoader) {
                addBlockLoader(element);
            } else {
                $(document).one('ajaxproloaderready', function () {
                    addBlockLoader(element);
                });
            }
        },

        /**
         * Stop full loader action
         */
        stopLoader: function (element) {
            removeBlockLoader(element);
        }
    };
});
