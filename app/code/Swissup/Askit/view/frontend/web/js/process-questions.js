define([
    'jquery',
    'Magento_Ui/js/lib/view/utils/bindings'
], function ($) {
    'use strict';

    /**
     * Get list of questions with ajax
     *
     * @param  {String}      url
     * @param  {Boolean}     fromPages
     * @param  {HTMLElement} targetElement
     */
    function processQuestions(url, fromPages, targetElement, submitData) {
        $(targetElement).attr('aria-busy', true);

        if (fromPages == true) { //eslint-disable-line eqeqeq
            $('html, body').animate({
                scrollTop: $(targetElement).offset().top - 50
            }, 300);
        }

        $.ajax({
            url: url,
            cache: true,
            dataType: 'html',
            method: submitData ? 'POST' : 'GET',
            data: submitData ? submitData : {
                ajax: 1
            }
        }).done(function (data) {
            if (!data) return;

            // remove redirect widget for limiter select
            data = data.replace(' data-mage-init=\'{"redirectUrl": {"event":"change"}}\'', '');
            // data = data.replace(' data-mage-init=\'{"Swissup_Askit/js/view/data-vote": {}}\'', '');
            $(targetElement).html(data).trigger('contentUpdated');
            // listen clicks on page numbers
            $('.pages a', targetElement)
                .on('click', changePage.bind(this, targetElement)); //eslint-disable-line no-use-before-define

            // listen change limit
            $('.limiter-options', targetElement)
                .change(changeLimit.bind(this, targetElement)); //eslint-disable-line no-use-before-define

            // listen vote link click
            $('[data-vote]', targetElement)
                .on('click', voteAction.bind(this, targetElement)); //eslint-disable-line no-use-before-define

            // apply ko binding
            $(targetElement).children().applyBindings();
        }).always(function () {
            $(targetElement).attr('aria-busy', false).trigger('askit_quenstions:list_updated');
        });
    }

    /**
     * Listener for vote click.
     *
     * @param  {HTMLElement}  parentElement
     * @param  {jQuery.Event} event
     */
    function voteAction(parentElement, event) {
        var params = $(event.currentTarget).data('vote'),
            formKey = $('input[name="form_key"]').val(),
            expanded;

        expanded = $('[id^=askit-item-trigger]:checked')
            .map(function () {
                return this.id;
            }).get().join();
        params.data['form_key'] = formKey;
        params.data.expanded = expanded;
        processQuestions(params.action, false, parentElement, params.data);
        event.preventDefault();
    }

    /**
     * Listener for limit change.
     *
     * @param  {HTMLElement}  parentElement
     * @param  {jQuery.Event} event
     */
    function changeLimit(parentElement, event) {
        processQuestions($(event.currentTarget).val(), true, parentElement);
        event.stopPropagation();
    }

    /**
     * Listener for page click.
     *
     * @param  {HTMLElement}  parentElement
     * @param  {jQuery.Event} event
     */
    function changePage(parentElement, event) {
        processQuestions($(event.currentTarget).attr('href'), true, parentElement);
        event.preventDefault();
    }

    return function (config, element) {
        var tabContent = $(element).closest('.data.item.content'),
            tab;

        if (tabContent.length) {
            tab = $('#tab-label-' + tabContent.attr('id'));

            if (tab.length && !tab.hasClass('active')) {
                tab.one('beforeOpen', function () {
                    processQuestions(config.questionsUrl, false, element);
                });

                return;
            }
        }

        processQuestions(config.questionsUrl, false, element);
    };
});
