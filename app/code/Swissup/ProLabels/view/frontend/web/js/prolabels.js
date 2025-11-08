define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('swissup.prolabels', {
        component: 'Swissup_ProLabels/js/prolabels',
        options: {
            parent: null,
            imageLabelsTarget: '',
            imageLabelsInsertion: 'appendTo',
            imageLabelsWrap: true,
            imageLabelsRenderAsync: false,
            contentLabelsTarget: '',
            contentLabelsInsertion: 'appendTo',
            labelsData: {},
            predefinedVars: {}
        },

        /**
         * [_create description]
         */
        _create: function () {
            window.swissupProlabels.render(this.options, this.element.get(0));
        },

        /**
         * {@inheritdoc}
         */
        destroy: function () {
            window.swissupProlabels.destroy(this.element.get(0));

            return this._super();
        }
    });

    return $.swissup.prolabels;
});
