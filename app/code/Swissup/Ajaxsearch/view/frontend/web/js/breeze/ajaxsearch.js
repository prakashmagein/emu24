define([
    'jquery',
    'underscore',
    'Magento_Catalog/js/price-utils',
    'quickSearch'
], function ($, _, priceUtils) {
    'use strict';

    $.mixin('quickSearch', {
        component: false
    });

    $.widget('ajaxsearch', 'quickSearch', {
        component: 'Swissup_Ajaxsearch/js/ajaxsearch',
        templates: {},
        itemTypes: ['product', 'category', 'page', 'autocomplete', 'popular'],
        options: {
            itemClass: 'tt-suggestion',
            selectClass: 'tt-cursor',
            dropdown: '<div></div>',
            responseFieldElements: '.tt-suggestion'
        },

        /** [create description] */
        create: function () {
            var self = this;

            this.options = $.extend(this.options, this.options.options);
            this.options.url = this.options.url.replace('?q=_QUERY', '');
            this.options.dropdownClass = this._option('options/typeahead/options/classNames/dataset');
            this.options.minSearchLength = this._option('options/typeahead/options/minLength');

            this._super();
            this.prepareMarkup();
            this.setActiveState(false);

            this.autoComplete
                .attr('data-id', this.autoComplete.attr('id'))
                .removeAttr('id')
                .removeClass('search-autocomplete')
                .addClass(this._option('options/typeahead/options/classNames/menu'))
                .hide()
                .on('keydown.ajaxsearch', this.options.responseFieldElements, function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        self.submitSelectedItem();
                    }
                });
        },

        destroy: function () {
            this.autoComplete.attr('id', this.autoComplete.attr('data-id'));
            this._super();
        },

        /** [prepareMarkup description] */
        prepareMarkup: function () {
            var block = this.element.closest('.block.block-search');

            block.addClass(this.options.classes.container.replace('.', ''))
                .addClass(this.options.classes.additional);

            this.element.css('position', 'relative');

            if (!this.element.parent().hasClass('input-wrapper')) {
                $('<div class="input-wrapper" style="position: relative">')
                    .insertAfter(this.element)
                    .append(this.element)
                    .append(this.autoComplete);

                this.element.wrap('<div class="input-inner-wrapper" style="position: relative">');

                this.searchForm.find('.field.search').children().wrapAll('<div class="origin">');
            }

            $('body').removeClass('swissup-ajaxsearch-loading');
        },

        /** [setActiveState description] */
        setActiveState: function (isActive) {
            this._super(isActive);
            this.element.closest('.control').toggleClass('inactive', !isActive);

            if (isActive &&
                !this.options.minSearchLength &&
                !this.element.val() &&
                $('.block-swissup-ajaxsearch-results').is(':empty')
            ) {
                this.sendRequest('__popular__');
            }
        },

        /** [submitSelectedItem description] */
        submitSelectedItem: function () {
            var item = this.dataset[this.responseList.selectedIndex];

            if (!item) {
                return;
            }

            if (item._type === 'popular' && this.element.val() !== item.title) {
                this.element.val(item.title).trigger('input');
            } else if (item.url) {
                window.location.href = item.url;
            } else {
                this.searchForm.submit();
            }
        },

        /** [_selectEl description] */
        _selectEl: function (el, focus) {
            if (!el || el.hasClass('notFound-item-info')) {
                return;
            }

            return this._super(el, focus);
        },

        /** [canUseNavKeys description] */
        canUseNavKeys: function () {
            return this._super() && this.dataset[0] && this.dataset[0]._type !== 'notFound';
        },

        /** [sendRequest description] */
        sendRequest: function () {
            var result = this._super(),
                spinnerTarget = this.searchForm.find('.input-inner-wrapper');

            if (!result || !result.finally) {
                return result;
            }

            spinnerTarget.spinner(true);

            return result.finally(function () {
                spinnerTarget.spinner(false);
            });
        },

        /** [prepareResponse description] */
        prepareResponse: function (data) {
            this._super(data);

            if (!this.dataset.length && this.element.val()) {
                this.dataset = [{
                    _type: 'notFound',
                    id: Math.random().toString(36).substr(2, 5)
                }];
            }

            this.dataset = _.map(this.dataset, function (item) {
                item._type = item._type || 'popular';

                return item;
            });
        },

        /** [processResponse description] */
        processResponse: function () {
            this._super();
            this.addWrappers();
        },

        /** [showAutocomplete description] */
        showAutocomplete: function (content) {
            var offset = this.element.offset(),
                offsetRight = $(window).width() - offset.left - this.element.outerWidth(true),
                rect = this.element.get(0).getBoundingClientRect(),
                parentRect = this.element.offsetParent().get(0).getBoundingClientRect(),
                css = {
                    left: 'auto',
                    right: 'auto',
                    zIndex: 100
                };

            if (content) {
                content.find('.notFound-item-info').removeClass(this.options.itemClass);
            }

            this._super(content);

            if (offset.left > offsetRight) { // input is closer to the right edge of the screen
                css.left = 'auto';
                css.right = parentRect.right - rect.right;
                this.autoComplete.removeClass('stick-to-start').addClass('stick-to-end');
            } else {
                css.left = rect.left - parentRect.left;
                css.right = 'auto';
                this.autoComplete.removeClass('stick-to-end').addClass('stick-to-start');
            }

            this.autoComplete.css(css);
        },

        /** [render description] */
        renderItem: function (item) {
            return this.getItemTemplate(item)({
                item: item,
                formatPrice: priceUtils.formatPrice,
                priceFormat: this.options.settings.priceFormat
            });
        },

        /** [getTemplate description] */
        getItemTemplate: function (item) {
            if (!this.templates[item._type]) {
                this.templates[item._type] = _.template(
                    $(this.options.templates[item._type]).html()
                );
            }

            return this.templates[item._type];
        },

        /** [addWrappers description] */
        addWrappers: function () {
            var self = this,
                results = $('.block-swissup-ajaxsearch-results');

            this.itemTypes.forEach(function (type) {
                var headerId = '#swissup-ajaxsearch-' + type + '-template-header';

                if (results.find('.' + type + '-item-info').length) {
                    results.find('.' + type + '-item-info')
                        .wrapAll('<div class="' + type + '-item-info-wrapper">');

                    if ($(headerId).length) {
                        $('.block-swissup-ajaxsearch-results .' + type + '-item-info-wrapper')
                            .prepend(_.template($(headerId).html())({
                                $t: $.__,
                                hasGetViewAllUrl: self.options.isProductViewAllEnabled,
                                getViewAllUrl: function () {
                                    return self.searchForm.attr('action') + '?' + self.searchForm.serialize();
                                }
                            }));
                    }
                }
            });

            results.find(
                this.itemTypes
                    .filter(function (type) {
                        return type !== 'product';
                    })
                    .map(function (type) {
                        return '.' + type + '-item-info-wrapper';
                    })
                    .join(',')
            ).wrapAll('<div class="custom-item-info-wrapper">');
        }
    });
});
