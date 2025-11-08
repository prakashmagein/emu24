define(['jquery', 'Swissup_Stickyfill/js/sticky'], function ($, sticky) {
    'use strict';

    var result;

    /**
     * @param  {jQuery} menu
     * @return {String}
     */
    function getClassNames(menu) {
        return menu.className.match(/navpro-sticky|navpro-top\d+/g).join(' ');
    }

    /**
     * Get container to stick
     *
     * @param  {jQuery} menu
     * @return {jQuery}
     */
    function getContainer(menu) {
        var container = $(menu).closest('.nav-sections, .block-navpro, .navigation-wrapper');

        if (!container.length) {
            container = $(menu).closest('.navpro');
        }

        return container;
    }

    result = function (config, menu) {
        var container = getContainer(menu),
            mql;

        /** [toggle description] */
        function toggle(event) {
            if (event.matches) {
                sticky.remove(container);
            } else {
                sticky.add(container);
            }
        }

        // Copy sticky classes to parent container that will be stuck actually
        container.addClass(getClassNames(menu));

        mql = window.matchMedia('(max-width: 767px)'); // @see layout/_type-sticky.less
        mql.addListener(toggle);
        toggle(mql);
    };

    result.component = 'Swissup_Navigationpro/js/sticky';

    return result;
});
