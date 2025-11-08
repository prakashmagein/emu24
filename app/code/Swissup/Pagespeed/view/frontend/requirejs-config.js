var config = {
    map: {
        '*': {
            'mage/bootstrap':'Swissup_Pagespeed/js/lib/mage/bootstrap'
        }
    },
    shim: {
        'Swissup_Pagespeed/js/lib/loadCSS': {
            exports: 'loadCSS'
        },
        'Swissup_Pagespeed/js/lib/cssrelpreload': {
            deps: [
                'Swissup_Pagespeed/js/lib/loadCSS'
            ]
        }
    }
};
