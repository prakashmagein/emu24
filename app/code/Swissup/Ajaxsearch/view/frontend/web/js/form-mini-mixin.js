define([
    'jquery',
    'Magento_Ui/js/modal/modal' // create 'jquery-ui-modules/widget' dependency
], function ($) {
    'use strict';

    return function (widget) {
        //disable standard quikSearch widget
        $.widget('mage.quickSearch', widget, {
            /**
             * @return void
             */
            _create: function () {
                if ($('body.swissup-ajaxsearch-loading').length > 0 ||
                    $('#swissup-ajaxsearch-init').length > 0 ||
                    $('.block-search').hasClass('block-swissup-ajaxsearch')
                ) {

                    return;
                }

                return this._super();
            }
        });

        return $.mage.quickSearch;
    };
});
