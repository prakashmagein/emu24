(function () {
    'use strict';

    $.widget('askitQuestions', {
        /** [create description] */
        create: function () {
            var self = this,
                tabContent = $(this.element).closest('.data.item.content'),
                tab;

            this._on({
                'click .pages a': 'page',
                'click [data-vote]': 'vote',
                'change .limiter-options': 'limit'
            });

            if (tabContent.length) {
                tab = $('#tab-label-' + tabContent.attr('id'));

                if (tab.length && !tab.hasClass('active')) {
                    return tab.one('collapsible:beforeOpen', function () {
                        self.load();
                    });
                }
            }

            this.load();
        },

        /** [page description] */
        page: function (event) {
            event.preventDefault();
            this.load($(event.currentTarget).attr('href'), true);
        },

        /** [page description] */
        vote: function (event) {
            var params = $(event.currentTarget).data('vote');

            event.preventDefault();

            params.data.expanded = $('[id^=askit-item-trigger]:checked').map(function () {
                return this.id;
            }).get().join();

            this.load(params.action, params.data);
        },

        /** [page description] */
        limit: function (event) {
            event.stopPropagation();
            this.load($(event.currentTarget).val(), true);
        },

        /** [load description] */
        load: function (url, data, scrollUp) {
            if (typeof data === 'boolean') {
                scrollUp = data;
                data = false;
            }

            $(this.element).attr('aria-busy', true);

            return $.request.send({
                url: url || this.options.questionsUrl,
                type: data ? 'form' : 'html',
                method: data ? 'post' : 'get',
                data: data ? data : {
                    ajax: 1
                }
            }).then(function (response) {
                $(this.element).attr('aria-busy', false);

                if (!response?.body) {
                    return;
                }

                if (scrollUp && !this.element.find('.askit-item').first().isInViewport()) {
                    this.element.get(0).scrollIntoView(true);
                }

                $(this.element)
                    .empty()
                    .append(response.text.replace(' data-mage-init=\'{"redirectUrl": {"event":"change"}}\'', ''))
                    .trigger('contentUpdated');
            }.bind(this));
        }
    });

    $(document).on('breeze:mount:Swissup_Askit/js/process-questions', function (event, data) {
        $(data.el).askitQuestions(data.settings);
    });
})();
