/* global BASE_URL */
(function () {
    'use strict';

    $.widget('ajaxsearchCategory', {
        component: 'Swissup_Ajaxsearch/js/ajaxsearch/category',
        options: {
            graphqlUrl: BASE_URL.replace(/\/+$/, '') + '/graphql',
            elementCategoryId: '#swissup-ajaxsearch-filter-category',
            categoryVarName: 'cat'
        },

        /** [create description] */
        create: function () {
            this.block = this.element.closest('.block.block-search');
            this.category = $(this.options.elementCategoryId);
            this.category.wrap('<div class="swissup-ajaxsearch-filter-category-wrapper">');
            this.block.find('form#search_mini_form .field.search').prepend(this.category);
        }
    });
})();
