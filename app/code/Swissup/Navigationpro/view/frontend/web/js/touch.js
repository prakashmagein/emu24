define(['jquery', 'underscore'], function ($, _) {
    'use strict';

    var touchLocks = [],
        touchLocksTracked = 5,

        /**
         * @param {Event} e
         */
        trackTouch = _.throttle(function (e) {
            touchLocks.push(e.type);

            if (touchLocks.length > touchLocksTracked) {
                touchLocks.shift();
            }
        }, 50);

    $(document).on('touchstart mousemove', trackTouch);

    return {
        component: 'Swissup_Navigationpro/js/touch',

        /**
         * Check if user is currently touching a screen
         *
         * @returns {bool}
         */
        touching: function () {
            return touchLocks.indexOf('touchstart') !== -1;
        }
    };
});
