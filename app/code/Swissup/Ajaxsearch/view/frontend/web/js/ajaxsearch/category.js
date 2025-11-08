define([
    'jquery',
    'Swissup_Ajaxsearch/js/lib/graphqlize',
    'Swissup_Ajaxsearch/js/lib/select2.min'
], function ($, graphqlize) {
    'use strict';

    var _elementCategory, _options, _localCache;

    _localCache = {
        data: {},
        remove: function (id) {
            delete _localCache.data[id];
        },
        has: function (id) {
            return _localCache.data.hasOwnProperty(id) && _localCache.data[id] !== null;
        },
        get: function (id) {
            return _localCache.data[id];
        },
        set: function (id, cachedData) {
            _localCache.remove(id);
            _localCache.data[id] = cachedData;
        }
    };

    /**
     *
     * @param str
     * @returns {*}
     */
    function hashCode(str) {
        return str.split('').reduce((prevHash, currVal) =>
            (((prevHash << 5) - prevHash) + currVal.charCodeAt(0))|0, 0);
    }

    /**
     *
     * @param  {Object} _element
     */
    function _init(_element) {
        var block, options;

        block = _element.closest('.block.block-search');

        _elementCategory.prependTo($(block).find('form#search_mini_form .field.search'));

        _elementCategory.wrapAll('<div class="swissup-ajaxsearch-filter-category-wrapper">');

        _elementCategory.on('change', function () {
            var value,
                ajaxsearch = $(_element).data('swissupAjaxsearch'),
                bloodhound;

            bloodhound = ajaxsearch && ajaxsearch.getBloodhound();

            if (bloodhound) {
                bloodhound.clear();
            }

            value = _element.val();

            if (value !== '' && value !== '0') {
                // _element.focus();
                _element.typeahead('val', '');
                _element.typeahead('val', value);
                // _element.typeahead("input");
                // _element.trigger('input');
                // _element.typeahead('open');
                // _element.trigger('keyup');
                _element.trigger('focus');
                setTimeout(function () {
                    _element.typeahead('open');
                }, 200);
                // _element.focus();
            }
        });

        options = {
            dir: $('body').hasClass('rtl') ? 'rtl' : 'ltr',
            dropdownParent: _elementCategory.parent(),
            width: '100%',

            /**
             *
             * @return {String}
             */
            templateResult: function (item) {
                return item.text;
            },

            /**
             *
             * @type {String}
             */
            templateSelection: function (item) {
                var text = item.text;

                text = text.replace(/(?:^[\s\u00a0]+)|(?:[\s\u00a0]+$)/g, '');

                return text;
            },
            escapeMarkup: function(markup) {
                return markup;
            }
        };

        if (_options.isCategoryFilterLoadOptionsByGraphql) {
            options = $.extend(options, {
                ajax: {
                    url: _options.graphqlUrl,
                    dataType: 'json',
                    data: function (params) {
                        // console.log(params);
                        return {
                            search: params.term,
                            type: 'public'
                        }
                    },
                    cache: true,
                    // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
                    // delay: 20,
                    transport: function (params, success, failure) {
                        const search = params.hasOwnProperty('data')
                            && params.data.hasOwnProperty('search')
                            && params.data.search
                                ? params.data.search : '';
                        const storeViewCode = _options.hasOwnProperty('storeViewCode') ? _options.storeViewCode : 'default';
                        const category = _options.hasOwnProperty('currentCategoryId') ? _options.currentCategoryId : 0;

                        params = graphqlize($.extend(params, {
                            query: ['{',
                                'ajaxsearchCategoryOptions(search: "' + search + '", category: ' + category + ') {',
                                    'items {',
                                        'id,',
                                        'text,',
                                        '__typename ',
                                    '}',
                                '}',
                            '}'].join("\n"),
                            headers: {
                                Store: storeViewCode
                            },
                        }));

                        var hashId = hashCode(JSON.stringify(params));
                        var $request = false;

                        if (_localCache.has(hashId)) {
                            $request = _localCache.get(hashId);
                        } else {
                            $request = $.ajax(params);
                            _localCache.set(hashId, $request);
                        }

                        $request.then(success);
                        $request.fail(failure);

                        return $request;
                    },
                    processResults: function (data) {
                        var items;

                        items = data.hasOwnProperty('data')
                        && data.data.hasOwnProperty('ajaxsearchCategoryOptions')
                        && data.data.ajaxsearchCategoryOptions.hasOwnProperty('items') ? data.data.ajaxsearchCategoryOptions.items : [];

                        // Transforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: items
                        };
                    }
                }
            });
        }

        _elementCategory.select2(options);
    }

    /**
     *
     * @param  {Object} options
     */
    function constructor(options, element) {
        _options = $.extend({}, {
            graphqlUrl: BASE_URL.replace(/\/+$/, '') + '/graphql',
            elementCategoryId: '#swissup-ajaxsearch-filter-category',
            categoryVarName: 'cat'
        }, options);

        _elementCategory = $(_options.elementCategoryId);
        $(element).data('swissupCategoryFilter', {
            /**
             * Retrieve category jquery element
             * @return {Object}
             */
            getElement: function () {
                return _elementCategory;
            },

            /**
             *
             * @return {String}
             */
            getVarName: function () {
                return _options.categoryVarName;
            },

            /**
             *
             * @return {String|Boolean}
             */
            getVarValue: function () {
                var value;

                if (!_elementCategory.length) {

                    return false;
                }

                value = _elementCategory.val();

                if (value === '' || value === '0') {

                    return false;
                }

                return value;
            },

            /**
             *
             * @param  {mixed} value
             * @return {Int}
             */
            outerWidth: function (value) {
                var el;

                el = this.getElement();
                el = el.closest('.swissup-ajaxsearch-filter-category-wrapper');

                return el.length && el.is(':visible') ? el.outerWidth(value) : 0;
            },

            /**
             * Initialize category dropdown
             */
            init: function () {
                if (_elementCategory.length) {
                    _init($(element));
                }
            }
        });

        if ($(element).data('swissupAjaxsearch') &&
            $(element).data('swissupAjaxsearch').getBloodhound()) {
            $(element).data('swissupCategoryFilter').init();
        } else {
            $(element).on(
                'ajaxsearchready',
                $(element).data('swissupCategoryFilter').init
            );
        }
    };

    return function(options, element) {
        let mql = matchMedia(options.matchMedia);
        const check = ()=> {
            if (mql.matches) {
                constructor(options, element);
            }
            return mql.matches;
        };
        if (!check()) {
            mql.addListener(check);
        }
    }
});
