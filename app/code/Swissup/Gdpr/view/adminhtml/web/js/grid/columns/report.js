define([
    'jquery',
    'mageUtils',
    'Magento_Ui/js/grid/columns/thumbnail',
    'mage/template',
    'text!Swissup_Gdpr/template/grid/cells/report/preview.html',
    'Magento_Ui/js/modal/modal'
], function ($, utils, Thumbnail, mageTemplate, previewTemplate) {
    'use strict';

    return Thumbnail.extend({
        defaults: {
            bodyTmpl: 'Swissup_Gdpr/grid/cells/report'
        },

        /**
         * Add html code preview
         *
         * @param  {Object} row
         */
        preview: function (row) {
            var modalHtml = mageTemplate(
                    previewTemplate,
                    {
                        html: row.report
                    }
                ),
                previewPopup = $('<div>').html(modalHtml),
                url = this.retryUrl;

            previewPopup.modal({
                title: 'Report',
                innerScroll: true,
                modalClass: '_image-box',
                buttons: [{
                    text: 'Retry',

                    /** @inheritdoc */
                    click: function () {
                        utils.submit({
                            url: url,
                            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                            data: {
                                id: row.entity_id
                            }
                            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                        });
                    }
                }]
            }).trigger('openModal');
        },

        /**
         * Get field handler per row.
         *
         * @param {Object} row
         * @returns {Function}
         */
        getFieldHandler: function (row) {
            if (row.report) {
                return this._super();
            }
        }
    });
});
