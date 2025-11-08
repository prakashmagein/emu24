define([
    'jquery',
    'Magento_Ui/js/grid/columns/thumbnail',
    'mage/template',
    'text!Swissup_Easybanner/template/grid/cells/banner-content/preview.html',
    'Magento_Ui/js/modal/modal'
], function ($, Thumbnail, mageTemplate, previewTemplate) {
    'use strict';

    return Thumbnail.extend({
        defaults: {
            bodyTmpl: 'Swissup_Easybanner/grid/cells/banner-content'
        },

        /**
         * Add html code preview
         *
         * @param  {Object} row
         * @return {jQuery}
         */
        preview: function (row) {
            var modalHtml = mageTemplate(
                    previewTemplate,
                    {
                        mode: row.mode,
                        html: row.html,
                        src: this.getOrigSrc(row),
                        alt: this.getAlt(row),
                        link: this.getLink(row),
                        linkText: $.mage.__('Edit Banner')
                    }
                ),
                previewPopup = $('<div>').html(modalHtml);

            previewPopup.modal({
                title: this.getAlt(row),
                innerScroll: true,
                modalClass: '_image-box',
                buttons: []
            }).trigger('openModal');
        },

        /**
         * Get field handler per row.
         *
         * @param {Object} row
         * @returns {Function}
         */
        getFieldHandler: function (row) {
            if (row.mode === 'html' || (row.mode === 'image' && row.image)) {
                return this._super();
            }
        }
    });
});
