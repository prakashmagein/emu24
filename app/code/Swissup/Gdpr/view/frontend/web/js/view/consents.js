define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore'
], function ($, _) {
    'use strict';

    var init;

    function onReveal(element, callback, options = {}) {
        var revealObserver = new IntersectionObserver(entries => {
            var nodes = entries
                .filter(entry => entry.isIntersecting)
                .map(entry => entry.target);

            if (nodes.length) {
                callback($(nodes));
                nodes.forEach(el => revealObserver.unobserve(el));
            }
        }, options);

        $(element).each((i, el) => revealObserver.observe(el));

        return revealObserver;
    }

    function render(el, item) {
        require(['Swissup_Gdpr/js/view/consents-renderer'], renderer => renderer(el, item));
    }

    init = function (config) {
        $('.gdpr-js-consent').remove();

        _.each(config, function (item) {
            var selector = item.form;

            if (item.async) {
                selector += ' ' + item.async;
            }

            $.async(selector, function (el) {
                onReveal(el, () => render(el, item));
            });
        });
    };

    init.component = 'Swissup_Gdpr/js/view/consents';

    return init;
});
