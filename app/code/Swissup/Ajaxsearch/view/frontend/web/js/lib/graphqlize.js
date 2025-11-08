define([
    'jquery'
], function ($) {
    'use strict';

    /**
     *
     * @param options
     * @returns {*}
     */
    return function (options) {
        var params, headers, url, query;

        url = options.hasOwnProperty('url') ? options.url : '/graphql';
        query = options.hasOwnProperty('query') ? options.query : '{}'
        query = query.replace(/(\r\n|\n|\r)/gm, ' ').replace(/(\s+)/gm, ' ');
        params = JSON.stringify({
            query: query
        });

        headers = options.hasOwnProperty('headers') ? options.headers : {
            Store: 'default'
        };

        //https://www.tutorialspoint.com/graphql/graphql_jquery_integration.htm
        //https://devdocs.magento.com/guides/v2.3/graphql/send-request.html
        //https://zinoui.com/blog/jquery-ajax-headers
        // $.ajax($.extend({}, options, {
        return $.extend({}, options, {
            global: false,
            headers: headers,
            url: url,
            contentType: 'application/json',
            type: 'POST',
            data: params
        });
        // }));
    }
});
