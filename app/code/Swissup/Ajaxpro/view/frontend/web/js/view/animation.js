define([
    'jquery'
], function ($) {
    'use strict';

    // catalog-product-view
    $(document).on('ajax:addToCart', function (e, data) {
        var glideImageSrc, glider, gliderContainer, gliderId;
        var {form, sku} = data;

        sku = sku ? (sku).toString().replace(/[^a-z0-9\-_]+/g, '') : '';
        gliderId = 'glideimageclone' + sku;
        if ($('#maincontent .product-info-main').length > 0) {

            glideImageSrc = $(form).closest('.column.main')
                .find('.fotorama__stage__frame.fotorama__active .fotorama__img')
                .attr('src');

            gliderContainer = $('.product-info-main');

            if (glideImageSrc && gliderContainer.length > 0) {

                glider = "<div style='position: absolute' id='" + gliderId + "'>" +
                    "<img src='" + glideImageSrc + "' />" +
                "</div>"

                gliderContainer.append(glider);

                $('#' + gliderId).animate({
                    width: "10%",
                    right: "100",
                    top: "0",
                    height: "auto",
                    opacity: "0",
                }, function () {
                    $(this).detach()
                });
            }
        }
    });

    // catalog-category-view
    $(document).on('ajax:addToCart', function (e, data) {
        var targetElement, imageToDrag, clonedImage;
        var {form} = data;

        targetElement = $('.minicart-wrapper .counter.qty').eq(0);
        imageToDrag = $(form).closest('.product-item-info')
            .find('img.product-image-photo')
            .eq(0);

        if (imageToDrag.length > 0 && targetElement.length > 0) {
            clonedImage = imageToDrag.clone()
                .removeClass()
                .offset({
                    top: imageToDrag.offset().top,
                    left: imageToDrag.offset().left
                })
                .css({
                    'opacity': '0.5',
                    'position': 'absolute',
                    'height': imageToDrag.height() /2,
                    'width': imageToDrag.width() /2,
                    'z-index': '100'
                })
                .appendTo($('body'))
                .animate({
                    'top': targetElement.offset().top + 10,
                    'left': targetElement.offset().left + 15,
                    'height': imageToDrag.height() /2,
                    'width': imageToDrag.width() /2
                },
                400,
                'easeInOutExpo')
            ;

            clonedImage.animate({
                'width': 0,
                'height': 0
            }, function () {
                $(this).detach()
            });
        }
    });
});
