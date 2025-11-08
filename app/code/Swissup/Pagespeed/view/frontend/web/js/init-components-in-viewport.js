(function (_window, _document){
    // init data-mage-init js component(s) in viewport
    const initDataMageComponent = (element) => {
        _window.require([
            'jquery',
            'mage/apply/main'
        ], function ($, mage) {
            let dataMageInit = $(element).data('mage-init');
            if (typeof dataMageInit === 'string') {
                dataMageInit = JSON.parse(dataMageInit);
            }
            $.each(dataMageInit, function(component, config) {
                mage.applyFor(element, config, component);
            });
            element.removeAttribute('data-mage-init');
        });
        intersectionObserver.unobserve(element);
    };
    const intersectionObserver = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (entry.isIntersecting && entry.target) {
                const target = entry.target;
                if (target.hasAttribute('data-mage-init')) {
                    initDataMageComponent(target);
                }
            }
        }
    });

    // _document.querySelectorAll('[data-bind*="mageInit:"]'
    _document.querySelectorAll('[data-mage-init]').forEach((el) => {
        intersectionObserver.observe(el);
    });
    _document.querySelectorAll('.page-header [data-mage-init], .nav-sections [data-mage-init]')
        .forEach(initDataMageComponent);
})(window, document);
