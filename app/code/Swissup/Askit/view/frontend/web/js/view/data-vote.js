define([
    'jquery',
    'mage/dataPost'
], function ($) {
    'use strict';

    $.widget('swissup.dataVote', $.mage.dataPost, {

        options: {
            postTrigger: ['a[data-vote]']
        },

        /**
         * Handler for click.
         *
         * @param {Object} e
         * @private
         */
        _postDataAction: function (e) {
            var params = $(e.currentTarget).data('vote');

            e.preventDefault();
            this.postData(params);
        },

    });

    return $.swissup.dataVote;
});
