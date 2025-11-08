(function(_window) {
    const idleCallback = _window.requestIdleCallback || _window.setTimeout;
    function _require(scriptShortName/*, i*/) {
        return function () {
            if (typeof _window.require === 'function') {
                // console.log('idle ' + ' ' + i + ' ' + scriptShortName);
                _window.require([
                    scriptShortName
                ], function (/*scriptResponse*/) {
                    // console.log(scriptResponse);
                });
            }
        }
    }
    _window.addEventListener('load', function() {
        let defined = [];

        /**
         * Execute something after the current tick
         * of the event loop. Override for other envs
         * that have a better solution than setTimeout.
         * @param {Function} fn function to execute later.
         */
        _window.require.nextTick = typeof requestIdleCallback !== 'undefined' ?
            (function (fn) {requestIdleCallback(fn, {timeout: 1})}) :
            (
                typeof setTimeout !== 'undefined' ? function (fn) {
                    setTimeout(fn, 4);
                } : function (fn) { fn(); }
            )
        ;

        const _requireAll = () => {
            // console.log('_requireAll');
            if (typeof _window.require === 'function' && _window.require.hasOwnProperty('s')) {
                defined = Object.keys(_window.require.s.contexts._.defined)
                    .filter(key => {
                        return !key.startsWith('vimeo/'); //fix for https://github.com/magento/magento2/issues/36435
                    });;
            }
            // console.log(defined);

            for(let i=0,l=defined.length;i<l;i++) {
                idleCallback(_require(defined[i], i));
            }
        };
        _requireAll();
        //
        // _window.addEventListener('load', (event) => {
        //     document.addEventListener('scroll', _requireAll, {once: true});
        //     document.addEventListener('mousemove', _requireAll, {once: true});
        //     document.addEventListener('click', _requireAll, {once: true});
        //     document.addEventListener('touch', _requireAll, {once: true});
        // });
    });
}(window));