define([
    'jquery',
    'mage/template',
    'mage/translate'
], function ($, _template, $t) {
    'use strict';

    var _options,
        _itemPlaceholderTmpl = '<div id="<%= type %>-<%= item.id %>" class="<%= type %>-item-info"></div>',
        _itemTypes = ['product', 'category', 'page', 'autocomplete', 'popular'],
        templatesContainer;

    /**
     * Process template after load.
     * Add it into document.
     *
     * @param  {String} templateString
     */
    function _prepareTemplate(templateString) {
        // Insert template into template container.
        templatesContainer.append(templateString);
    }

    /**
     * @param {String} type
     */
    function _addSectionTitle(type) {
        var $wrapper,
            templateId;

        templateId = '#swissup-ajaxsearch-' + type + '-template-header';

        if ($(templateId).length) {
            $wrapper = $('.block-swissup-ajaxsearch-results .' + type + '-item-info-wrapper');

            $wrapper.prepend(
                _template(templateId, {
                    $t: $t,
                    hasGetViewAllUrl: _options.isProductViewAllEnabled,
                    getViewAllUrl: function () {
                        var form;
                        form = $('#search_mini_form');

                        return form ? form.attr('action') + '?' + form.serialize() : '#';
                    }
                })
            );
        }
    }

    /**
     * @param  {Object} item
     * @return {String}
     */
    function _getType(item) {
        var type = item._type || item.__typename || 'popular';

        type = type.replace('AjaxsearchItem', ''); // remove AjaxsearchItem
        type = type.charAt(0).toLowerCase() + type.slice(1); // Lowercase first letter

        return type;
    }

    /**
     * Load 'html' template with require and render search result item with it.
     *
     * @param  {Object} item
     */
    function _loadTemplateAndRenderItem(item) {
        require([
            'Magento_Catalog/js/price-utils',
            'text!Swissup_Ajaxsearch/template/x-magento-template/' + _getType(item) + '.html'
        ], function (utils, loadedText) {
            var itemHtmlId,
                templateId,
                $el;

            templateId = _options.templates[_getType(item)];

            if (!$(templateId).length) {
                _prepareTemplate(loadedText);
                _addSectionTitle(_getType(item));
            }

            item.final_price = item.hasOwnProperty('final_price') ?
                item.final_price : (item.hasOwnProperty('max_price') ? item.max_price : 0);

            if (item.price
                && item.price.regularPrice
                && item.price.regularPrice.amount
                && item.price.regularPrice.amount.hasOwnProperty('value')
            ) {
                item.final_price = item.price.regularPrice.amount.value;
            }

            if (item.price
                && item.price.minimalPrice
                && item.price.minimalPrice.amount
                && item.price.minimalPrice.amount.hasOwnProperty('value')
            ) {
                item.final_price = item.price.minimalPrice.amount.value;
            }

            $el = $(_template(templateId, {
                $t: $t,
                item: item,
                formatPrice: utils.formatPrice,
                priceFormat: _options.settings.priceFormat
            }));

            itemHtmlId = '#' + _getType(item) + '-' + item.id;
            $(itemHtmlId).empty().append(
                $el.children()
            );
        });
    }

    templatesContainer = $('<div>');
    templatesContainer.addClass('swissup-ajaxsearch-templates');
    templatesContainer.appendTo(document.body);

    return {
        /**
         *
         * @param {Object} options
         * @return {this}
         */
        setOptions: function (options) {
            _options = options;

            return this;
        },

        /**
         * @param  {Object} object
         * @return {String}
         */
        renderNotFound: function (object) {
            var type = 'notFound',
                item = $.extend({}, {
                    _type: type,
                    id: Math.random().toString(36).substr(2, 5)
                }, object);

            _loadTemplateAndRenderItem(item);

            return _template(_itemPlaceholderTmpl, {
                type: type,
                item: item
            });
        },

        /**
         *
         * @param  {Object} item
         * @return {String}
         */
        renderSuggestion: function renderSuggestion(item) {
            var type = _getType(item);

            if (type === 'debug') {
                console.log(item);
                // console.log(item._select);
            }

            if (_itemTypes.indexOf(type) !== -1) {
                // When there is no ID generate random string
                item.id = item.id || Math.random().toString(36).substr(2, 5);
                _loadTemplateAndRenderItem(item);
            }

            return _template(_itemPlaceholderTmpl, {
                type: type,
                item: item
            });
        },

        /**
         *
         */
        addWrappers: function () {
            var $results = $('.block-swissup-ajaxsearch-results');

            _itemTypes.forEach(function (type) {
                if ($results.find('.' + type + '-item-info').length) {
                    $results.find('.' + type + '-item-info').wrapAll('<div class="' + type + '-item-info-wrapper">');
                    _addSectionTitle(type);
                }
            });

            $results.find(_itemTypes.filter(function (t) {
                return t !== 'product';
            }).map(function (t) {
                return '.' + t + '-item-info-wrapper';
            }).join(',')).wrapAll('<div class="custom-item-info-wrapper">');

            return this;
        },

        /**
         *
         * @param  {Object} _element
         */
        recalcWidth: function (_element) {
            var categoryFilter = _element.data('swissupCategoryFilter'),
                $results = $('.block-swissup-ajaxsearch-results');

            $results.each(function (i, el) {
                var minWidth, offset, left, right, categoryElementWidth, isRtl;

                offset = _element.offset();
                left = offset.left;
                right = $(window).width() - left - _element.outerWidth(true);
                categoryElementWidth = categoryFilter ? categoryFilter.outerWidth(true) : 0;
                isRtl = $('body').hasClass('rtl');

                if (left > right && !isRtl || left < right && isRtl) {
                    left = 'auto';
                    right = 0;
                } else {
                    left = categoryElementWidth > 0 ? '-' + categoryElementWidth + 'px' : 0;
                    right = 'auto';
                }

                if (left === 'auto') {
                    $(el).removeClass('stick-to-start').addClass('stick-to-end');
                } else {
                    $(el).removeClass('stick-to-end').addClass('stick-to-start');
                }

                $(el).css('left', isRtl ? right : left);
                $(el).css('right', isRtl ? left : right);

                minWidth = _element.outerWidth(true) + categoryElementWidth;

                if ($(window).width() > minWidth) {
                    $(el).css('min-width', minWidth);
                }
            });

            return this;
        }
    };
});
