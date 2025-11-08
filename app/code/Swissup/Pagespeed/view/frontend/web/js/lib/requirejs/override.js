(function (_window, _document){
    if (_window?.require?.config) {
        const original = _window.require.config;
        let deps = [];

        _window.require.config = function (config) {
            if (config.hasOwnProperty('deps')) {
                const newDeps = config.deps;
                if (!newDeps.includes("jsbuild")) {
                    deps = [...deps, ...newDeps];
                    delete config.deps;
                }
            }

            return original(config);
        };

        const once = function (fn, context) {
            let result;
            return () => {
                if (fn) {
                    result = fn.apply(context || this, arguments);
                    fn = null;
                }
                return result;
            };
        };

        const userInteractionListener = once(() => {
            _window.require.config = original;
            return _window.require(deps);
            // return original({deps});
        });

        const addEventListeners = (event) => {
            const eventListenerOptions = {
                once: true
            };
            _document.addEventListener('scroll', userInteractionListener, eventListenerOptions);
            _document.addEventListener('mousemove', userInteractionListener, eventListenerOptions);
            _document.addEventListener('click', userInteractionListener, eventListenerOptions);
            _document.addEventListener('touch', userInteractionListener, eventListenerOptions);
            _document.addEventListener("focusin", userInteractionListener, eventListenerOptions);
        };

        if (_window.requestIdleCallback) {
            _window.requestIdleCallback(() => {
                addEventListeners();
                // userInteractionListener();
            });
        } else {
            _window.addEventListener('DOMContentLoaded', addEventListeners);
        }
    }
})(window, document);
