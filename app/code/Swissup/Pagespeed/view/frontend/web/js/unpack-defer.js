(function () {
    'use strict';

    const getDeferredScripts = () => {
        return [...document.getElementsByTagName('script')].filter(script => {
            return script.getAttribute('type')?.toLowerCase() === 'text/defer-javascript'
                && script.parentNode;
        });
    };

    const fixNonceAttribute = function (scriptNode) {
        if (scriptNode.hasAttribute('nonce') && scriptNode.hasAttribute('defered-nonce')) {
            let nonce = scriptNode.getAttribute('nonce'),
                deferedNonce = scriptNode.getAttribute('defered-nonce');

            if (nonce.length < 1 && deferedNonce.length > 0) {
                scriptNode.setAttribute('nonce', deferedNonce);
                scriptNode.removeAttribute('defered-nonce');
            }
        }
    };

    const unpack = () => {
        (window.requestIdleCallback || window.setTimeout)(() => {
            getDeferredScripts().forEach(script => {
                script.removeAttribute('type');
                fixNonceAttribute(script);
                script.parentNode.replaceChild(script, script);
            });
        });
    };

    if (['complete', 'loaded', 'interactive'].includes(document.readyState)) {
        unpack();
    } else {
        document.addEventListener('DOMContentLoaded', unpack);
    }
})();
