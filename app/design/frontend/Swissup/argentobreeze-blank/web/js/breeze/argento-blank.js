(() => {
    'use strict';

    $.async('.marquee3k', (el) => {
        $.onReveal(el, () => require(['marquee3k'], () => {
            if ($('.marquee3k.is-init').length) {
                return;
            }
            window.Marquee3k?.init();
        }));
    });
})();
