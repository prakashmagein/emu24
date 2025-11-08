define([
    'jquery',
    'underscore',
    'Magento_Customer/js/section-config',
    'Magento_Customer/js/customer-data'
], function ($, _, sectionConfig, customerData) {
    'use strict';

    /**
    Run force global customer data reload
    require(['Magento_Customer/js/customer-data', 'Magento_Customer/js/section-config'
    ], function (customerData, sectionConfig) {
        customerData.reload(sectionConfig.getSectionNames(), true);
    });
     */
    var isGlobalCustomerDataReload = false;
    $(document).on('customer-data-reload', function (event, data) {
        isGlobalCustomerDataReload = false;
        if (('object' != typeof data && !Array.isArray(data)) || 'function' != typeof sectionConfig.getSectionNames) {
            return;
        }
        let sections = data.hasOwnProperty('sections') ? data.sections.sort() : data.sort();
        if (sections.length === 0) {
            sections = Object.keys(data.response).sort();
        }
        const allSectionNames = sectionConfig.getSectionNames().sort();
        const expiredSectionNames = customerData.getExpiredSectionNames().sort();

        isGlobalCustomerDataReload = _.isEqual(sections, allSectionNames) || (expiredSectionNames.length > 0
            && _.isEqual(_.difference(sections, allSectionNames).sort(), expiredSectionNames));

        setTimeout(function () {
            isGlobalCustomerDataReload = false;
        }, 500);
    });

    let isAjaxProRequestSent = false;
    const checkRequest = (method, url) => {
        const ajaxproSections = ['ajaxpro-cart', 'ajaxpro-product'/*, 'ajaxpro-reinit'*/];
        if (typeof method !== 'string' || typeof url !== 'string') {
            return;
        }
        if (method?.match(/post|put|delete/i)) {
            const affectedSections = sectionConfig.getAffectedSections(url);
            if (affectedSections && affectedSections.length
                && _.intersection(ajaxproSections, affectedSections).length > 0
            ) {
                isAjaxProRequestSent = true;
            }
        }
        if (method?.match(/get/i) && url?.indexOf('customer/section/load') !== -1) {
            const params = new URLSearchParams((new URL(url)).search);
            const sectionsParam = params?.get('sections')?.split(',');
            if (sectionsParam && sectionsParam?.includes('ajaxpro-product')) {
                isAjaxProRequestSent = true;
            }
        }
    };

    /**
     * Events listener
     */
    $(document).on('ajaxSend', function (event, xhr, settings) {
        checkRequest((settings || xhr.settings).type, (settings || xhr.settings).url);
    });

    /**
     * Events listener
     */
    $(document).on('submit', function (event) {
        const form = $(event.target);
        checkRequest((form.attr('method') || form.prop('method')), (form.attr('action') || form.prop('prop')));
    });

    return {
        component: 'Swissup_Ajaxpro/js/is-ajaxpro-request',
        active: () => {
            return !isGlobalCustomerDataReload && isAjaxProRequestSent
        },
        reset: function () {
            isAjaxProRequestSent = false;
            isGlobalCustomerDataReload = false;
        }
    };
});
