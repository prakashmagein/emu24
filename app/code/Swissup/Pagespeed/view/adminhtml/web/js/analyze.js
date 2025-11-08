define([
    'uiComponent',
    'jquery',
    'ko'
], function (Component, $, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_Pagespeed/analyze',
            apiUrl: 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed',
            developerUrl: 'https://developers.google.com/speed/pagespeed/insights/?url=',
        },
        element: {
            tab: '.swissup-pagespeed-tab',
            main: '.swissup-pagespeed-main-container',
            button: '#analyze'
        },
        css: {
            active: 'active',
            small: 'small',
            red: 'red',
            orange: 'orange',
            green: 'green'
        },
        scores: {
            mobile: 0,
            desktop: 0
        },
        finalUrl: ko.observable(),
        score: ko.observable(),
        color: ko.observable(),
        dashoffset: ko.observable(),

        /**
         *
         * @return {String}
         */
        getFinalUrl: function () {
            return this.finalUrl();
        },
        /**
         *
         * @return {String}
         */
        getInsightUrl: function () {
            return this.developerUrl + encodeURI(this.finalUrl());
        },
        /**
         *
         * @return {String}
         */
        getScore: function () {
            return this.score();
        },
        /**
         *
         * @return {String}
         */
        getColor: function () {
            return this.color();
        },
        /**
         *
         * @return {Integer}
         */
        getDashoffset: function () {
            return this.dashoffset();
        },

        /**
         * @inherit
         */
        initialize: function () {
            this._super();
            $(this.element.button).on('click', this.analyze.bind(this));
        },

        /**
         * @inherit
         */
        initObservable: function () {
            this.observe(['mobile', 'desktop']);

            return this;
        },

        /**
         *
         */
        analyze: function () {
            this.apiCall('mobile');
            this.apiCall('desktop');
        },

        /**
         *
         * @param  {String} strategy
         */
        apiCall: function (strategy) {
            $.ajax({
                url: this.apiUrl,
                type: 'GET',
                showLoader: true,
                data: {
                    url: this.baseUrl,
                    strategy: strategy,
                    locale: this.locale
                },
                success: (function (response) {
                    this.onSuccess(response, strategy);
                }).bind(this)
            });
        },
        /**
         *
         * @param  {Object} response
         * @param  {String} strategy
         */
        onSuccess: function (response, strategy) {
            var audits, score;

            this.finalUrl(response.id);

            score = response.lighthouseResult.categories.performance.score;
            this.scores[strategy] = score;
            // if (strategy === 'mobile') {
                this.setScore(score);
            // }
            audits = response.lighthouseResult.audits;

            this.setAudits(strategy, audits);
            $(this.element.tab).off()
                .on('click', function (e) {
                    this.activateTab(
                        $(e.currentTarget).data('swissup-pagespeed-tab')
                    );
                }.bind(this));

            this.activateTab(strategy);

            // $(this.element.button).hide();
            $(this.element.main).show();
        },
        /**
         *
         * @param  {String} strategy
         */
        activateTab: function (strategy)
        {
            this.setScore(this.scores[strategy]);
            $(this.element.tab).removeClass(this.css.active);
            $('[data-swissup-pagespeed-tab="' + strategy +  '"]').addClass(this.css.active);

            $('.swissup-pagespeed-tab-content').hide();
            $('.swissup-pagespeed-tab-content.' + strategy).show();
        },

        /**
         * @param {string} score
         */
        setScore: function (score) {
            let percent = score * 100,
            cssClass = this.css.small;

            const circleLength = 318;

            this.score(Math.ceil(percent));

            this.dashoffset(circleLength - circleLength * score);

            if (percent < 10) {
                cssClass = this.css.small;
            } else if (percent < 50) {
                cssClass = this.css.red;
            } else if (percent < 90) {
                cssClass = this.css.orange;
            } else {
                cssClass = this.css.green;
            }
            this.color(cssClass);
        },

        /**
         * @param {String} strategy
         * @param {Object} audits
         */
        setAudits: function (strategy, audits) {
            var importantAudits = [],
                k,
                audit,
                isOpportunity = false;

            const hightValue = 0.88;
            const opportunities = {
                'server-response-time' : '<span>' + 'Actually, you will see it often: the main requirement is to improve your server response time. To make it faster than now. There are multiple ways of doing that:' + '</span>' +
                    '<ul>' +
                        '<li>' + 'you may reduce the number of modules used in Magento https://github.com/yireo/magento2-replace-all' + '</li>' +
                        '<li>' + 'varnish, memecache' + '</li>' +
                        '<li>' + 'try cache warmer' + '</li>' +
                        '<li>' + 'php-fpm, php opcache, php 7' + '</li>' +
                    '</ul>' +
                    '<span>' + 'Plus a few more things that are supposed to be delivered by your provider' +
                    'the last thing you can do is to submit a support ticket. In case you still have a shared hosting and about 100 a kind of very important modules, you have to pay attention to steps mentioned above before asking for support' + '</span>',

                'render-blocking-resources': '<span>' + 'This item is hard to get rid. ' +
                    'we mean here that you have to load "not quite  necessary to download" resources later than in this moment right now. ' + '</span>' +
                    '<span>' + 'But you can do it by using defer js i optimize css delivery' + '</span>' +
                    '<ul>' +
                        '<li>' + '<a href="https://web.dev/render-blocking-resources">Render Blocking Resources</a>' + '</li>' +
                        '<li>' + '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/quickstart/#how-to-fix-2">Defer Js QuickStart</a>' + '</li>' +
                        '<li>' + '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/quickstart/#optimize-css-delivery">Optimize Css Delivery QuickStart</a>' + '</li>' +
                    '</ul>'
                    ,

                'uses-webp-images': '<span>' + 'Use the modern WebP image format.' + '</span>' +
                    '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/quickstart/#optimize-images">Optimize Images QuickStart</a>' +
                    '<span>' + 'it\'s up to you, but some old Safari products still contend with Google' + '/<span>' +
                    '<a href="https://caniuse.com/#feat=webp">Can I Use WebP</a>',
                'uses-responsive-images': '<label for="pagespeed_image_optimize_responsive">' + 'Enable Responsive Support - Yes ' + '</label>' +
                    '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/quickstart/#optimize-images">Optimize Images</a>',
                'redirects': '',
                'offscreen-images': 'Image lazy loading ' +
                    '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/configuration/#lazy-loader-for-images">Image Lazyload Configuration</a>',
                'unused-css-rules': '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/quickstart/#optimize-css-delivery">Optimise Css Delivery</a>',
                'uses-optimized-images': '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/quickstart/#optimize-images">Optimize Images</a>' +
                    '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/configuration/#optimize-catalog-images">Optimize Images Configuration</a>'+
                    '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/configuration/#image-processing-settings-section">Optimize Images Configuration #2</a>'
                ,
                'uses-text-compression': '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/quickstart/#enable-compression">Enable Compression</a>',
                'efficient-animated-content': '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/quickstart/#optimize-images">Optimize Images QuickStart</a>',
                'uses-rel-preload': '<span>' + 'Enable preload in main section' + '</span>',
                'unminified-css': '<span>' + 'Enable css minification ' + '<span>' + '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/configuration/#css-settings-section">Css Optimisation Configuration</a>',
                'unminified-javascript': '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/configuration/#javascript-settings-section">Js Optimisation Configuration</a>',
                'total-byte-weight': '',
                'dom-size': '',
                'time-to-first-byte': '',
                'uses-rel-preconnect': '<a href="https://docs.swissuplabs.com/m2/extensions/pagespeed/configuration/#dns-prefetch-section">Dns-prefetch & Preloadd Configuration</a>',
                'bootup-time': '',
                'third-party-summary': '',
                'uses-long-cache-ttl': '',
                'font-display': '<span>' + 'By including font-display: swap in all your @font-face styles. (even after that the warning will be still shown)' + '</span>'            };

            for (k in audits) {
                if (audits[k].score === null || audits[k].score > hightValue) {
                    continue;
                }
                audit = {};
                audit.key = k;
                audit.title = audits[k].title;

                isOpportunity = audits[k].hasOwnProperty('details')
                    && audits[k].details.hasOwnProperty('type')
                    && audits[k].details.type === 'opportunity';
                audit.opportunity = isOpportunity;

                audit.score = audits[k].score;
                audit.id = audits[k].id;
                audit.description = this.parseDescription(audits[k].description);
                audit.displayValue = audits[k].displayValue ? audits[k].displayValue : '';
                audit.recommendation = opportunities.hasOwnProperty(k) ? opportunities[k] : '';

                // console.log('-----------------');
                // console.log(k);
                // console.log(audit.description);
                // console.log(audit.recommendation);
                // console.log('-----------------');

                importantAudits.push(audit);
            }

            importantAudits.sort(function (a, b) {
                return a.score - b.score;
            });

            if (strategy === 'mobile') {
                this.mobile(importantAudits);
            } else {
                this.desktop(importantAudits);
            }
        },
        /**
         * @param {string} text
         * @returns {[]|{description: *}}
         */
        parseDescription: function (text) {
            var descriptionArr = [],
                splittedText,
                matches;

            matches = text.match(/\[[-\W\w\s]+\]\(.*?\)/gm);
            if (matches === null) {
                return { description: text }
            }

            splittedText = text.split(/(\[([-\W\w\s]+?)\]\((.*?)\))/gm);

            for (var i = 0; i < Math.floor(splittedText.length / 4); i++) {
                descriptionArr.push(
                    {
                        prevText: splittedText[i * 4],
                        link: splittedText[i * 4 + 3],
                        linkTitle: splittedText[i * 4 + 2]
                    }
                );
            }

            if (splittedText.length % 4 === 1) {
                descriptionArr.push(
                    {
                        prevText: splittedText[splittedText.length - 1],
                        link: ''
                    }
                );
            }

            descriptionArr.hasLink = true;

            return descriptionArr;
        }
    });
});
