define([
    'jquery',
    'underscore',
    'Swissup_Ajaxsearch/js/ajaxsearch/results',
    'Swissup_Ajaxsearch/js/ajaxsearch/mobile',
    'mage/utils/wrapper',
    'Swissup_Ajaxsearch/js/ajaxsearch/get-graphql-query',
    'Swissup_Ajaxsearch/js/lib/graphqlize',
    'Swissup_Ajaxsearch/js/lib/typeaheadbundle',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, _, Results, Mobile, wrapper, getGraphQlQuery, graphqlize) {
    'use strict';

    var _options = {
        classes: {
            container: '.block-swissup-ajaxsearch',
            mask: '.ajaxsearch-mask',
            formLabel: '#search_mini_form .search label'
        }
    },
    bloodhound,
    _element;

    /**
     * On ready init
     * @param  {Object} Bloodhound
     */
    function _init(Bloodhound) {
        var block, sourceAdapter, debouncedRecalcWidth;

        Results.setOptions(_options);

        block = _element.closest('.block.block-search');

        block.addClass(_options.classes.container.replace('.', ''))
            .addClass(_options.classes.additional);

        $(document.body).removeClass('swissup-ajaxsearch-loading')
            .removeClass('swissup-ajaxsearch-folded-loading');

        bloodhound = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: _options.url,
                wildcard: _options.wildcard,

                /**
                 * @param  {String} query
                 * @param  {Object} settings
                 * @return {Object}
                 */
                prepare: function (query, settings) {
                    var categoryVarName, categoryVarValue, options, useGraphql,
                        categoryFilter = _element.data('swissupCategoryFilter');

                    useGraphql = _options.useGraphql;
                    query = encodeURIComponent(query);
                    categoryVarValue = categoryFilter && categoryFilter.getVarValue();

                    if (categoryVarValue && !useGraphql) {
                        categoryVarName = categoryFilter.getVarName();
                        query += '&' + categoryVarName + '=' + categoryVarValue;
                    }
                    settings.url = settings.url.replace(_options.wildcard, query);

                    if (useGraphql) {
                        categoryVarValue = categoryVarValue ? categoryVarValue : 0;
                        const storeViewCode = _options.hasOwnProperty('storeViewCode') ? _options.storeViewCode : 'default';
                        options = $.extend({}, {
                            url: _options.hasOwnProperty('graphqlUrl') ? _options.graphqlUrl : '/graphql',
                            query: getGraphQlQuery(query, categoryVarValue),
                            headers: {
                                Store: storeViewCode
                            },
                        });
                        settings = graphqlize(options);
                        // console.log(settings);
                    }

                    return settings;
                },
                transform: function (response){
                    var items, suggestions;

                    if (response.hasOwnProperty('data')
                        && response.data.hasOwnProperty('ajaxsearch')
                        && response.data.ajaxsearch.hasOwnProperty('suggestions')
                    ) {
                        suggestions = response.data.ajaxsearch.suggestions;
                        items = [];

                        if (suggestions.hasOwnProperty('products')) {
                            suggestions.products.forEach((item) => {
                                items.push(item);
                            });
                        }

                        if (suggestions.hasOwnProperty('pages')) {
                            suggestions.pages.forEach((item) => {
                                items.push(item);
                            });
                        }

                        if (suggestions.hasOwnProperty('categories')) {
                            suggestions.categories.forEach((item) => {
                                items.push(item);
                            });
                        }

                        if (suggestions.hasOwnProperty('autocompletes')) {
                            suggestions.autocompletes.forEach((item) => {
                                items.push(item);
                            });
                        }

                        return items;

                    }

                    return response;
                }
            }
        });
        bloodhound.initialize();

        // _options.typeahead.options.minLength = 0;
        sourceAdapter = bloodhound.ttAdapter();
        sourceAdapter = wrapper.wrap(
            sourceAdapter,
            function (withAsync, query, sync, async) {
                if (query === '') {
                    query = '__popular__';
                }

                return withAsync(query, sync, async);
            }
        );

        if ($('body').hasClass('rtl')) {
            _element.attr('dir', 'rtl');
            // dir: "rtl"
        }

        _element.typeahead(_options.typeahead.options, {
            name: 'product',
            source: sourceAdapter,
            async: true,
            displayKey: 'title',
            limit: _options.typeahead.limit,
            templates: {
                notFound: Results.renderNotFound,
                suggestion: Results.renderSuggestion
            }

        }).on('typeahead:selected', function (event, item) {
            var type = item._type || false,
                element = $(this);

            if (type === 'product' && typeof item['url'] != 'undefined') {
                window.location.href = item['url'];
            } else if ((type === 'page' || type === 'category') &&
                typeof item.url != 'undefined') {

                window.location.href = item.url;
            } else if (type === 'popular') {
                // element.val(item.title);
                element.typeahead('val', item.title);
                element.trigger('focus');

                setTimeout(function () {
                    element.typeahead('open');
                }, 200);
            } else {
                this.form.submit();
            }
        }).on('typeahead:asyncrequest', function (event) {
            require(['Swissup_Ajaxsearch/js/ajaxsearch/loader'], function (Loader) {
                Loader.setContainer(_options.loader.container)
                    .setLoaderImage(_options.loader.loaderImage)
                    .startLoader(event);
            });
        })
        .on('typeahead:asynccancel typeahead:asyncreceive', function (event) {
            require(['Swissup_Ajaxsearch/js/ajaxsearch/loader'], function (Loader) {
                Loader.stopLoader(event);
            });
        })
        .on('typeahead:render', function () {
            Results.addWrappers();
            Results.recalcWidth(_element);
        });

        // Do not close results when click in empty region of the dropdown.
        // @see typeaheadbundle.js:1454
        $('.block-swissup-ajaxsearch-results').on('click', function (e) {
            e.stopPropagation();
        });

        _element.parent()
            .find('> :not(.tt-menu)')
            .wrapAll('<div class="input-inner-wrapper" style="position: relative">');

        block.find('form#search_mini_form .field.search').children().wrapAll('<div class="origin">');

        Mobile(_element, _options);

        debouncedRecalcWidth = _.debounce(function () {
            Results.recalcWidth(_element);
        }, 100);
        $(window).on('resize', debouncedRecalcWidth);
        debouncedRecalcWidth();
    }

    $.widget('swissup.ajaxsearch', {
        options: {
            url: '',
            wildcard: '_QUERY',
            loader: {
                container: '.block-swissup-ajaxsearch .actions .action',
                loaderImage: ''
            },
            typeahead: {
                options: {
                    highlight: true,
                    hint: true,
                    minLength: 3,
                    classNames: {}
                },
                limit: 10
            },
            settings: {}
        },

        /**
         * {@inheritdoc}
         */
        _init: function () {
            var self = this;

            this._super();
            _element = this.element;
            $.extend(true, _options, this.options.options)

            require([
                'bloodhound',
                'typeahead.js'
            ], function (Bloodhound) {
                // _init(Bloodhound);
                $(_init(Bloodhound));
                self._trigger('ready'); // trigger 'ajaxsearchready' event
                //  removes element from the accessibility tree
                self.element.siblings('.tt-hint').attr('aria-hidden', true);
            });

            return this;
        },

        /**
         *
         * @return {String}
         */
        version: function () {
            return this.options.settings.version;
        },

        getBloodhound: function () {
            return bloodhound;
        }
    });

    return $.swissup.ajaxsearch;
});
