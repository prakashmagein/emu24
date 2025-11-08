define([
    'jquery',
    'moment',
    'mageUtils',
    'mage/translate',
    'moment-timezone-with-data',
    'productGallery'
], function ($, moment, utils, $t) {
    'use strict';

    const getValue = ($input) => {
        // when checkbox return 1 or 0
        // in other cases return val()
        if ($input.attr('type') == 'checkbox')
            return $input.is(':checked') ? 1 : 0;
        else
            return $input.val();
    }

    const getPropertyName = (id) => {
        const map = {
            'slide-enabled': 'is_active',
            'slide-link-target': 'target',
            'slide-desc-position': 'desc_position',
            'slide-desc-background': 'desc_background'
        };

        return map[id] || id.replace('slide-', '');
    }

    /**
     * Slide gallery widget
     */
    $.widget('swissup.slideGallery', $.mage.productGallery, {
        /**
         * @inheritdoc
         */
        _bind: function () {
            const me = this;

            this._super();

            this._on({
                'click [data-action=newSlideImage]': (event) => {
                    $(event.currentTarget).closest('details').prop('open', false);
                    me.element.find('#fileupload, #fileUploader').trigger('click');
                },
                'click [data-action=newSlideHTML]': (event) => {
                    $(event.currentTarget).closest('details').prop('open', false);
                    me.element.trigger('addItem', [{
                        'media_type': 'html',
                        'title': 'HTML slide',
                        'description': "<div style=\"font-weight: bold\">\n\tRaw HTML slide\n</div>"
                    }]);
                }
            });
        },

        _formatDate: function (timestamp) {
            const formatter = this.options.formatter;

            return moment
                .unix(timestamp)
                .tz(formatter.storeTimeZone)
                .format(utils.convertToMomentFormat(formatter.dateFormat + ' ' + formatter.timeFormat));
        },

        _getActiveLabel: function (imageData) {
            const activeFrom = imageData.active_from;
            const activeTo = imageData.active_to;

            if (!activeFrom && !activeTo) return '';
            if (!activeTo) return this._formatDate(activeFrom) + ' - ...';
            if (!activeFrom) return '... - ' + this._formatDate(activeTo);

            return this._formatDate(activeFrom) + ' - ' + this._formatDate(activeTo);
        },

        /**
        * Set image as main
        * @param {Object} imageData
        */
        setBase: function (imageData) {
            return false;
        },

        /**
         * @inheritdoc
         */
        findElement: function (data) {
            if (typeof data.file === 'undefined') {
                return this.element.find(this.options.imageSelector).filter(function () {
                    return $(this).data('imageData').file_id === data.file_id;
                }).first();
            }

            return this._super(data);
        },

        /**
         * Listener for dialog open
         * @param  {Event} event
         */
        onDialogOpen: function (event) {
            var imageData = this.$dialog.data('imageData');

            if (!imageData['media_type'] || imageData['media_type'] === 'image') {
                // Set file ID currently editing for uploader
                this.element.find('.uploader').data('fileId', imageData['file_id']);
            }
        },

        onDialogClose: function (event) {
            const $dialog = this.$dialog;
            const imageData = $dialog.data('imageData');
            const $imageContainer = $dialog.data('imageContainer');

            $('[id^="slide-"]').trigger('change');
            $imageContainer.removeClass('active');

            if (!imageData['media_type'] || imageData['media_type'] === 'image') {
                // Unset file ID currently editing for uploader
                this.element.find('.uploader').data('fileId', '');
            }
        },

        _initDialog: function () {
            var $dialog = $(this.dialogContainerTmpl());

            $dialog.modal({
                'type': 'slide',
                title: $t('Slide Detail'),
                buttons: [],
                opened: function () {
                    $dialog.trigger('open');
                },
                closed: function () {
                    $dialog.trigger('close');
                }
            });

            this.$dialog = $dialog;
            this._bindDialogListeners();
        },

        _bindDialogListeners: function () {
            const $dialog = this.$dialog;
            const me = this;

            $dialog.on('open', this.onDialogOpen.bind(this));
            $dialog.on('close', this.onDialogClose.bind(this));

            // Listen changes in dialog popup
            $dialog.on('change', (e) => {
                const $target = $(e.target);
                const name = $target.attr('name');
                const value = getValue($target);
                const property = getPropertyName($target.attr('id') || '');
                const imageData = $dialog.data('imageData');
                const $imageContainer = $dialog.data('imageContainer');

                // Try to update proper imageData value
                if (($target.attr('id') || '').indexOf('slide-') === 0) {
                    $('input[type="hidden"][name="' + name + '"]').val(value);
                    imageData[property] = value;
                }

                // Toogle visibility
                if ($target.is('#slide-enabled')) {
                    me.element.trigger('updateVisibility', {
                        disabled: (value === 0),
                        imageData: imageData
                    });
                }

                // Update slide title
                if ($target.is('#slide-title')) {
                    $imageContainer
                        .find(me.options.imgTitleSelector)
                        .text(value);
                }

                // Update slide active
                if ($target.is('#slide-active_from') || $target.is('#slide-active_to')) {
                    imageData.activeLabel = this._getActiveLabel(imageData);
                    $imageContainer
                        .find('.item-active')
                        .text(imageData.activeLabel);
                }
            });

            $dialog.on('click', '[data-action="change-image"]', (e) => {
                me.element.find('#fileupload, #fileUploader').trigger('click');
            });
        },

        /**
         * {@inheritdoc}
         */
        _addItem: function (event, uploadedImageData) {
            const fileId = this.element.find('.uploader').data('fileId');

            var $slide, imageData;

            uploadedImageData.activeLabel = this._getActiveLabel(uploadedImageData);

            if (fileId) {
                // There is fileId assigned to uploader.
                // It means image uploading to specific slide
                $slide = this.findElement({'file_id': fileId});
                imageData = $slide.data('imageData');
                imageData.file = uploadedImageData.file;
                imageData.url = uploadedImageData.url;
                imageData.size = uploadedImageData.size;
                imageData.sizeLabel = byteConvert(imageData.size);

                this.$dialog.find('.image-panel-preview img').attr('src', imageData.url);

                $slide.find('[name$="\[file\]"]').val(imageData.file);
                $slide.find(this.options.imageElementSelector).attr('src', imageData.url);
                $slide.find('[data-role="image-size"]').text(imageData.sizeLabel);
            } else {
                imageData = $.extend({
                        'is_active': 1
                    }, uploadedImageData);

                this._super(event, imageData);
            }
        },

        /**
         * {@inheritdoc}
         */
        _onOpenDialog: function (e, imageData) {
            this._super(e, imageData);

            if (imageData['media_type'] && imageData['media_type'] == 'html') { //eslint-disable-line eqeqeq
                this._showDialog(imageData);
            }
        },

        /**
         * {@inheritdoc}
         */
        _showDialog: function (imageData) {
            const me = this;
            const $dialog = this.$dialog;
            this._super(imageData);

            $dialog.find('.image-panel-preview img').on('load', (event) => {
                const img = event.target;
                const sizeSpan = $dialog.find(me.options.imageSizeLabel).find('[data-message]');
                const sizeText = sizeSpan.attr('data-message').replace('{size}', imageData.sizeLabel);
                const resolutionSpan = $dialog.find(me.options.imageResolutionLabel).find('[data-message]');
                const resolutionText = resolutionSpan
                    .attr('data-message')
                    .replace('{width}^{height}', img.naturalWidth + 'x' + img.naturalHeight);

                // Update image resolution
                resolutionSpan.text(resolutionText);
                // Update image size
                sizeSpan.text(sizeText);
            });

            $dialog.trigger('contentUpdated');
        }
    });

    return $.swissup.slideGallery;
});
