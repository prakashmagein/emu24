(function () {
    'use strict';

    $.mixinSuper('quickSearch', {
        /** [sendRequest description] */
        sendRequest: function () {
            var result = this._super();

            this.submitBtn.prop('disabled', false);

            return result;
        }
    });

    $.widget('ajaxsearchFolded', {
        component: 'Swissup_Ajaxsearch/js/ajaxsearch/folded',
        options: {
            classes: {
                container: '.block-swissup-ajaxsearch',
                mask: '.ajaxsearch-mask'
            }
        },

        /** [create description] */
        create: function () {
            var block = this.element.closest('.block.block-search');

            if (!block.find('.actions .action.close').length) {
                block.find('.actions .action.search').after(this.options.closeBtn);
            }

            if (this.element.component('ajaxsearch')) {
                this.prepareMarkup();
            } else {
                this.element.on('ajaxsearch:afterCreate', this.prepareMarkup.bind(this));
            }
        },

        prepareMarkup: function () {
            var self = this;

            this.container = $(this.options.classes.container);

            if (!$(this.options.classes.mask).length) {
                this.container.append(
                    '<div class="' + this.options.classes.mask.substr(1) + '"></div>'
                );
            }
            this.mask = $(this.options.classes.mask);

            this.hide();

            this.mask.on('click', this.hide.bind(this));

            this.container.find('.action.search, [data-role=minisearch-label]')
                .removeAttr('disabled')
                .on('click', function (e) {
                    e.preventDefault();

                    if (!self.isVisible()) {
                        self.show();
                    }
                });

            $(this.options.closeBtn).insertAfter(this.element);
            this.container.find('.action.close').on('click', function (e) {
                e.preventDefault();
                self.hide();
            });

            this.element.on('focus', this.show.bind(this));

            $(document.body)
                .removeClass('swissup-ajaxsearch-folded-loading')
                .on('keydown', function (e) {
                    if (e.key === 'Escape') {
                        self.hide();
                    }
                });
        },

        /** Show */
        show: function () {
            if (this.container.hasClass('shown')) {
                return;
            }

            this.container.addClass('shown');
            this.mask.addClass('shown');
            setTimeout(function () {
                this.element.get(0).focus();
                this.element.get(0).selectionStart = 10000;
                this.element.get(0).selectionEnd = 10000;
            }.bind(this), 13);
        },

        /** Hide */
        hide: function () {
            this.container.removeClass('shown');
            this.mask.removeClass('shown');
        },

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            return this.container.hasClass('shown');
        }
    });
})();
