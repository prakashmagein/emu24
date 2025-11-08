define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, customerData) {
    'use strict';

    var _ignoreData = [
        'wishlist\\\/shared\\\/',
        'wishlist\\\/index\\\/fromcart\\\/'
    ];

    $.widget('swissup.ajaxcianDataPost', {

        options: {
            processStart: null,
            processStop: null,
            bind: true,
            attributeName: 'post-ajax',
            formKeyInputSelector: 'input[name="form_key"]'
        },

        loader: null,

        /**
         * Constructor
         */
        _create: function () {
            if (this.options.bind) {
                this._bind();
            }
        },

        /**
         * Bind new ajax function
         */
        _bind: function () {
            var self = this,
            dataPost = this.element.attr('data-post'), form;

            // disable tocart patching because it dublicate catalog-addto-cart widget
            // if (!dataPost &&
            //     isToCartPatchEnable === true &&
            //     this.element.hasClass('tocart') &&
            //     this.element.closest('form') &&
            //     typeof this.element.attr('data-' + this.options.attributeName) === 'undefined'
            // ) {
            //     form = this.element.closest('form');
            //     if (form.attr('data-role') !== 'tocart-form' ||
            //         typeof form.attr('action') === 'undefined'
            //     ) {
            //         return;
            //     }
            //     dataPost = {
            //         action: form.attr('action'),
            //     };
            //     dataPost.data = {};
            //     form.select(':input').serializeArray().forEach(function (param) {
            //         dataPost.data[param.name] = param.value;
            //     });
            //     dataPost = JSON.stringify(dataPost);
            // }

            if (!dataPost) {
                return;
            }

            if (Math.max.apply(Math, _ignoreData.map(dataPost.indexOf.bind(dataPost))) > -1) {
                return;
            }

            if (dataPost.indexOf('/wishlist\\\/') > -1 && !customerData.get('customer')().fullname) {
                return;
            }

            if (dataPost) {
                // $(document).undelegate('a[data-post]', 'click.dataPost0');
                this.element
                    .attr('data-' + this.options.attributeName, dataPost)
                    .removeAttr('data-post');
            }

            setTimeout(function () {
                self.element.on('click', function (e) {
                    e.preventDefault();
                    $.proxy(self._ajax, self, $(this))();
                });
            }, 500);
        },

        /**
         * Send ajax request
         * @param  {Element} element
         */
        _ajax: function (element) {
            var dataPost = $.extend(true, {}, element.data(this.options.attributeName), element.data('post')),
            parameters = dataPost.data || {},
            formKey = $(this.options.formKeyInputSelector).val(),
            url = dataPost.action,
            me = this;

            if (formKey) {
                parameters['form_key'] = formKey;
            }

            $.ajax({
                method: 'POST',
                url: url,
                dataType: 'json',
                data: parameters,

                /**
                 *
                 */
                beforeSend: function () {
                    element.css({
                        'color': 'transparent'
                    });
                    require(['Swissup_Ajaxpro/js/loader'], function (loader) {
                        if (!me.loader) {
                            loader.setLoaderImage(me.options.loaderImage)
                                .setLoaderImageMaxWidth(me.options.loaderImageMaxWidth);
                            me.loader = loader;
                        }

                        me.loader.startLoader(element);
                    });
                },

                /**
                 * @param  {Object} response
                 * @return {void}
                 */
                success: function (response) {
                    if (response && response.backUrl) {
                        if (response.backUrl.indexOf('customer/section/load/?sections=') !== -1) {
                            response.backUrl = url;
                        }
                        window.location = response.backUrl;

                        return;
                    }
                },

                complete: function () {
                    element.css({
                        'color': ''
                    });
                    require(['Swissup_Ajaxpro/js/loader'], function () {
                        me.loader.stopLoader(element);
                    });
                }
            });
        }
    });

    return $.swissup.ajaxcianDataPost;
});
