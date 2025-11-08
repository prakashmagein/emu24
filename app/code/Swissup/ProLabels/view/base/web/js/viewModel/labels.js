define([
    'jquery',
    'ko',
    'underscore'
], function ($, ko, _) {
    'use strict';

    /**
     * Map all properties of object as observable for viewModel
     *
     * @param  {Object} object
     * @param  {Object} viewModel
     * @return void
     */
    function koMapping(object, viewModel) {
        $.each(object, function (key, value) {
            viewModel[key] = ko.observable(value);
        });
    }

    /**
     * Image on load function to add image to label
     *
     * @param  {Event} event
     * @return void
     */
    function imageOnload(event) {
        var koLabel = this,
            img = event.target;

        koLabel.imageCss(
            'background-image: url(' + img.src + '); ' +
            'width: ' + img.width + 'px; ' +
            'height: ' + img.height + 'px; '
        );
    }

    /**
     * Update image css for labels when label image is changed
     *
     * @param  {String} newValue
     * @return void
     */
    function updateImageCss(newValue) {
        var koLabel = this,
            img;

        if (newValue) {
            img = new Image();
            img.onload = $.proxy(imageOnload, koLabel);
            img.src = newValue;
        } else {
            koLabel.imageCss('');
        }
    }

    /**
     * Collect all classes into one string.
     *
     * @return {String}
     */
    function prepareCssClasses() {
        var koLabel = this;

        return 'prolabel' +
            (koLabel['css_class'] ? ' ' + koLabel['css_class']() : '');
    }

    /**
     * ko ViewModel for labels.
     *
     * Structure of labelsData parameter
     *
     * [
     *     {
     *         position: 'position1',
     *         items: [label1 (Object), label2 (Object) .. labelN (Object)]
     *     },
     *     {
     *         position: 'position2',
     *         items: [label1 (Object), label2 (Object) .. labelN (Object)]
     *     }
     *     ...
     * ]
     *
     * @param  {Array} labelsData
     * @param  {Object} predefinedVars
     */
    const labelsViewModel = function (labelsData, predefinedVars, processTextFn) {
        const _processText = function () {
            const koLabel = this;
            const { text, round_method, round_value } = koLabel;
    
            return processTextFn(
                text?.(),
                koLabel.root.predefinedVars,
                round_value?.(),
                round_method?.()
            );
        }
        var self = this;

        self.predefinedVars = predefinedVars || {};
        self.labelsData = [];
        $.each(labelsData, function () {
            var data = {};

            data.position = ko.observable(this.position);
            data.items = [];
            $.each(this.items, function () {
                var model = {};

                this.imageCss = '';
                this.image = this.image || '';
                this.custom = this.custom || '';
                koMapping(this, model);

                if (!_.isEmpty(model)) {
                    model.root = self;
                    model.textProcessed = ko.pureComputed(_processText, model);
                    model.cssClasses = ko.pureComputed(prepareCssClasses, model);
                    model.image.subscribe(updateImageCss, model);
                    updateImageCss.bind(model)(model.image());
                    data.items.push(model);
                }
            });

            self.labelsData.push(data);
        });
    };

    if ($.breeze)
        $.breezemap['Swissup_ProLabels/js/viewModel/labels'] = labelsViewModel;

    return labelsViewModel;
});
