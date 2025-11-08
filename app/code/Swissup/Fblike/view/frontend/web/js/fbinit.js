/* global FB */
(function (factory) {
    'use strict';

    var facebookSdk = '';

    if (typeof FB === 'undefined') {
        facebookSdk = '//connect.facebook.net/' +
            document.documentElement.lang.replace('-', '_') +
            '/sdk.js?nomin=1';
    } else {
        FB.swissupThirdpartyInit = true;
    }

    if (typeof define === 'function' && define.amd) {
        define([facebookSdk], factory);
    } else {
        $(document).on('breeze:load', function () {
            $.loadScript(facebookSdk, function () {
                $(document).trigger('fblike:ready', {fb: factory()});
            });
        });
    }
}(function () {
    'use strict';

    var callbacks = [],
        isInitialized = !!FB.swissupThirdpartyInit,
        _initParams;

    /**
     * Get parameters for FB.init
     * @return {Object}
     */
    function _getInitParams() {
        var fbAppId;

        if (!_initParams) {
            fbAppId = document.querySelector('meta[property="fb:app_id"]');
            _initParams = {
                appId: fbAppId ? fbAppId.content : '',
                xfbml: false,
                version: 'v3.2'
            };
        }

        return _initParams;
    }

    /**
     * Initialize FB SDK after source is loaded
     */
    window.fbAsyncInit = function () {
        callbacks.forEach(function (callback) {
            callback();
        });
        callbacks = [];
        isInitialized = true;
    };

    /**
     * @param {Function} callback
     */
    FB.swissupReady = function (callback) {
        if (isInitialized) {
            callback();
        } else {
            callbacks.push(callback);
        }
    };

    /**
     * Initialize FB
     */
    FB.swissupFBInit = function () {
        FB.init(_getInitParams());
    };

    return FB;
}));
