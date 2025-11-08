/* eslint-disable max-nested-callbacks */
(() => {
    'use strict';

    $(document).on('breeze:load', () => {
        $.async(`
            .category-description,
            .products-list .product-items .product-item-description,
            .product.attribute.overview > .value,
            .testimonials-slider .testimonial-message
        `, el => {
            $.onReveal(el, () => $(el).lineClamp());
        });
    });

    // dom elements order should match tabindex
    $.lazy(() => {
        // setTimeout is used to move it after click on it with delay
        setTimeout(() => {
            $('.header.content .action.nav-toggle').appendTo('.page-header .header.content');
        }, 100);
    });

    // navigation
    $.lazy(() => {
        var maxMobileWidth = parseFloat(
            window.getComputedStyle(document.body).getPropertyValue('--navpro-accordion-max-width')
        );

        if (!$('.navpro-business').length) {
            return;
        }

        $(document).on('beforeOpenSlideout menuSlideout:beforeOpen', () => {
            var menu = $('.navpro-business').menu('instance');

            $('.navpro-business .shown').each((i, el) => {
                menu?.close($(el));
            });
        });

        function openFirstSuitableLevel2Dropdown() {
            var menu = $('.navpro-business').menu('instance'),
                dropdownToOpen = $('li.level1.parent').first().children('.navpro-dropdown');

            if (dropdownToOpen.length) {
                menu.open(dropdownToOpen);
                setTimeout(() => {
                    dropdownToOpen.addClass('close-later');
                }, 30);
            }
        }

        $('.navpro-business')
            .on('menubeforeopen menu:beforeOpen', (e, data) => {
                data.dropdown.removeClass('close-later');

                // open level2 too when level1 is opening (navpro-business design)
                if (data.dropdown.is('.navpro-dropdown-level1.size-fullwidth') &&
                    $(window).width() > maxMobileWidth
                ) {
                    $('.navpro-business').menu('instance')?.close($('.navpro-dropdown-level2.shown'));
                    openFirstSuitableLevel2Dropdown();
                }
            })
            // do not close dropdown when moving mouse over fullwidth dropdown edges
            .on('menubeforeclose menu:beforeClose', (e, data) => {
                if (!data.dropdown.is('.size-fullwidth .navpro-dropdown-level2') ||
                    $(window).width() <= maxMobileWidth
                ) {
                    return;
                }

                if (data.dropdown.hasClass('close-later')) {
                    return data.dropdown.removeClass('close-later');
                }

                data.dropdown.addClass('close-later');
                data.preventDefault = true;
            })
            .on('mouseenter', 'li.level1', function () {
                var menu = $('.navpro-business').menu('instance');

                if ($(window).width() <= maxMobileWidth) {
                    return;
                }

                setTimeout(() => {
                    $('.navpro-business .close-later').each((i, el) => {
                        if (!this.contains(el)) {
                            menu.close($(el));
                        }
                    });
                }, menu.options.dropdownHideDelay);
            });
    });
})();
