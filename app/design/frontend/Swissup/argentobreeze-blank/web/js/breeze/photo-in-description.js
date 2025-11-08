(function () {
    'use strict';

    $.widget('argentoPhotoInDescription', {
        component: 'js/components/photo-in-description',
        options: {
            imageClass: 'argento-float-photo',
            addClasses: ''
        },

        /** [_create description] */
        _create: function () {
            this.gallery = $('[data-gallery-role=gallery-placeholder]');

            this.gallery.on('gallery:loaded', $.proxy(this._prepare, this));
            this.element.on('easytabs:contentLoaded', $.proxy(this._prepare, this));

            if (this.element.html()) {
                this._prepare();
            }
        },

        /** Initialize photo from product */
        _prepare: function () {
            var data,
                image,
                container;

            if (this.element.find('.' + this.options.imageClass).length) {
                return;
            }

            data = this.getImages();

            if (data.length < 2) {
                return;
            }

            image = data.slice(-1).pop(); // last item
            container = this._prepareContainer();
            this.element.prepend(container);

            if (image.type === 'video') {
                this._initializeVideo(container, image);
            } else {
                container.append('<img src="' + image.img + '">');
            }
        },

        /**
         * Initialize product video
         */
        _initializeVideo: function (container, video) {
            var gallery = this.gallery.data('gallery'),
                youtubeId = gallery.matchVideoId(video.videoUrl, 'youtube'),
                el;

            if (youtubeId) {
                el = $('<lite-youtube>');
                el.attr('videoid', youtubeId);
                el.attr('playlabel', video.caption);
            } else {
                el = $(gallery.renderVideo(video.videoUrl));
            }

            container.append(el.addClass('product-video responsive aspect-video'));
        },

        /**
         * @return {Cash}
         */
        _prepareContainer: function () {
            return $('<div>').addClass(this.options.imageClass + ' ' + this.options.addClasses);
        },

        /**
         * @return {Object}
         */
        getImages: function () {
            return this.gallery.data('gallery') ? this.gallery.data('gallery').options.data : [];
        }
    });
})();
