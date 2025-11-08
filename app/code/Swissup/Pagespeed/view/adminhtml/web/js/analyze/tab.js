define([
    'uiComponent',
    'jquery'
], function(Component, $) {

    return Component.extend({
        defaults: {
            strategy: '',
            imports: {
                'audits': '${ "analyze" }:${ $.strategy }'
            },
            template: 'Swissup_Pagespeed/tab',
            recommendations: '.swissup-pagespeed-recommendation-block',
            links: '.swissup-pagespeed-link-block',
            css: {
                hide: 'hide',
                active: 'active'
            }
        },

        /**
         * @inherit
         */
        initObservable: function () {
            this.observe(['audits']);
            return this;
        },
        /**
         *
         * @param  {Object} audit
         * @param  {Object} e
         */
        activate: function (audit, e) {
            var element = $('.' + this.strategy + ' [data-swissup-pagespeed-audit="'+ audit.id +'"]');

            $('.' + this.strategy + ' ' + this.links).removeClass(this.css.active);
            $(e.currentTarget).addClass(this.css.active);
            $('.' + this.strategy + ' ' + this.recommendations).addClass(this.css.hide);
            element.removeClass(this.css.hide);
        }
    });
});