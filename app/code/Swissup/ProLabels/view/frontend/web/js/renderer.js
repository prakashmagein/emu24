const WRAPPER_CLASS_CONTENT = 'prolabels-content-wrapper';
const WRAPPER_CLASS_IMAGE = 'prolabels-wrapper';

const _wrap = (element, wrapperClass) => {
    const parent = element.parentNode;

    if (parent.classList.contains(wrapperClass)) {
        return parent;
    }

    let wrapper = document.createElement('div');
    
    wrapper.className = wrapperClass;
    parent.insertBefore(wrapper, element);
    wrapper.appendChild(element);
    
    return wrapper;
}

const _mutationCallback = function(mutationsList, observer) {
    for (const mutation of mutationsList) {
        if (mutation.type === 'childList') {
            mutation.addedNodes.forEach(node => {
                observer.__listen.forEach(({selector, context, cb}) => {
                    if (!context.contains(node)) return;
                    if (node.matches && node.matches(selector)) {
                        cb(node);
                    } else if (node.nodeType === 1) {
                        const elements = node.querySelectorAll(selector);
                        elements.forEach(cb);
                    }
                });
            });
        }
    }
}

const _mutationObserver = new MutationObserver(_mutationCallback);

const _async = (selector, context, cb) => {
    _mutationObserver.observe(context, { childList: true, subtree: true });
    _mutationObserver.__listen = _mutationObserver.__listen || [];
    _mutationObserver.__listen.push({
        selector,
        context,
        cb
    });
    context.querySelectorAll(selector).forEach(cb);
}

const _onReveal = (element, cb) => {
    const revealObserver = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting) {
            cb();
            revealObserver.unobserve(entries[0].target);
        }
    }, {
        rootMargin: '80px'
    });

    revealObserver.observe(element);

    return revealObserver;
};

const _getImageDimensionsPromise = (imageUrl) =>
    new Promise((resolve, reject) => {        
        const img = new Image();

        img.onload = () => 
            resolve({ width: img.width, height: img.height });

        img.onerror = (error) => reject(error);

        img.src = imageUrl;
    });

document.head.insertAdjacentHTML('beforeend', [
    '<style id="swissup.prolabels.style">',
        `.${WRAPPER_CLASS_IMAGE}, .${WRAPPER_CLASS_CONTENT} { position: relative; }`,
        `.${WRAPPER_CLASS_IMAGE} .absolute { position: absolute; }`,
        `.${WRAPPER_CLASS_IMAGE} .top-left { top: 0; left: 0; }`,
        `.${WRAPPER_CLASS_IMAGE} .top-center { top: 0; left: 50%; transform: translateX(-50%); z-index: 2;}`,
        `.${WRAPPER_CLASS_IMAGE} .top-right { top: 0; right: 0; }`,
        `.${WRAPPER_CLASS_IMAGE} .bottom-right { bottom: 0; right: 0; }`,
        `.${WRAPPER_CLASS_IMAGE} .bottom-center { bottom: 0; left: 50%; transform: translateX(-50%); z-index: 2; }`,
        `.${WRAPPER_CLASS_IMAGE} .bottom-left { bottom: 0; left: 0; }`,
        `.${WRAPPER_CLASS_IMAGE} .middle-left { top: 50%; left: 0; transform: translateY(-50%); }`,
        `.${WRAPPER_CLASS_IMAGE} .middle-right { right: 0; top: 50%; transform: translateY(-50%); }`,
        `.${WRAPPER_CLASS_IMAGE} .middle-center { top: 50%; left: 50%; transform: translateX(-50%) translateY(-50%); z-index: 2; }`,
        `:is(.${WRAPPER_CLASS_IMAGE}, .${WRAPPER_CLASS_CONTENT}) .prolabel { display: inline-block; background: none; line-height: normal; position: relative; vertical-align: top; z-index: 2; }`,
        `:is(.${WRAPPER_CLASS_IMAGE}, .${WRAPPER_CLASS_CONTENT}) .prolabel__inner { height: 100%; width: 100%; }`,
        `:is(.${WRAPPER_CLASS_IMAGE}, .${WRAPPER_CLASS_CONTENT}) .prolabel__wrapper { display: table; height: 100%; width: 100%; }`,
        `:is(.${WRAPPER_CLASS_IMAGE}, .${WRAPPER_CLASS_CONTENT}) .prolabel__content { display: table-cell; text-align: center; vertical-align: middle; }`,
    '</style>' 
].join(''));

class TargetResolver {
    constructor(options, element) {
        const context = options.parent ? element.closest(options.parent) : element;

        this.targetImageLabels = context?.querySelector?.(options.imageLabelsTarget);
        this.targetContentLabels = context?.querySelector?.(options.contentLabelsTarget);
        this.context = context;
    }

    resolveContentLabelsWrapper(targetSelector, insertMethod = 'appendTo') {
        const target = targetSelector
            ? this.context.querySelector(targetSelector)
            : this.targetContentLabels;

        if (!target) {
            console.warn(`Target element "${targetSelector}" not found for content label.`);
            return null;
        }

        const _getWrapperCandidate = (method) => {
            switch (method) {
                case 'appendTo':
                    return target.children[target.children.length - 1];
                case 'insertAfter':
                    return target.nextElementSibling;
                case 'insertBefore':
                    return target.previousElementSibling;
                case 'prependTo':
                    return target.children[0];
                default:
                    console.warn(`Unsupported insert method: ${method}`);
                    return null;
            }
        };

        let wrapper = _getWrapperCandidate(insertMethod);
        if (!wrapper || !wrapper.classList.contains(WRAPPER_CLASS_CONTENT)) {
            wrapper = document.createElement('div');
            wrapper.classList.add(WRAPPER_CLASS_CONTENT);

            switch (insertMethod) {
                case 'appendTo':
                    target.insertAdjacentElement('beforeend', wrapper);
                    break;
                case 'insertAfter':
                    target.insertAdjacentElement('afterend', wrapper);
                    break;
                case 'insertBefore':
                    target.insertAdjacentElement('beforebegin', wrapper);
                    break;
                case 'prependTo':
                    target.insertAdjacentElement('afterbegin', wrapper);
                    break;
                default:
                    console.warn(`Unsupported insert method: ${insertMethod}`);
                    return null;
            }
        }

        return wrapper;
    }
}

class Label {
    constructor(data, variables) {
        this.data = data;
        this.variables = variables;
    }

    async getTextProcessed() {
        const { text, round_method, round_value } = this.data;
        const { processText } = await import(window.swissupProlabels.helper);

        return processText(text, this.variables, round_value, round_method);
    }

    async getImageCss() {
        if (!this.data.image) return '';
        const { width, height } = await _getImageDimensionsPromise(this.data.image);
        return [
            `background-image: url(${this.data.image});`,
            'background-size: contain;',
            'display: inline-block;',
            `height: ${height}px;`,
            `width: ${width}px;`
        ].join('');
    }

    getCustomCss() {
        return this.data.custom;
    }

    async getInlineCss() {
        return [
            this.getCustomCss(),
            await this.getImageCss(),
        ].filter(item => item).join(';');
    }

    getClassesCss() {
        const classes = ['prolabel'];

        if (this.data['css_class']) classes.push(this.data['css_class']);

        return classes.join(' ');
    }

    getUrl() {
        return this.data['custom_url'];
    }

    async toHtml() {
        const html = [
            `<span style="${await this.getInlineCss()}" class="${this.getClassesCss()}">`,
                '<span class="prolabel__inner">',
                    '<span class="prolabel__wrapper">',
                        '<span class="prolabel__content">',
                            await this.getTextProcessed(),
                        '</span>',
                    '</span>',
                '</span>',
            '</span>'
        ];

        if (this.getUrl()) {
            html.unshift(`<a href=${this.getUrl()}>`);
            html.push('</a>');
        }

        return html.join('');
    }
}

class Renderer {
    constructor(data, variables) {
        this.data = data;
        this.variables = variables;
        this.imageLabels = null;
        this.contentLabels = [];
        this.revealObserverImageLabels = null;
        this.revealObserverContentLabels = null;
    }

    getImageLabels() {
        return this.data.filter(item => item.position !== 'content');
    }

    getContentLabels() {
        return this.data.filter(item => item.position === 'content');
    }

    render(options, element) {
        const targetResolver = new TargetResolver(options, element);
        const {targetImageLabels, targetContentLabels} = targetResolver;

        if (options.imageLabelsRenderAsync) {
            _async(
                options.imageLabelsTarget,
                targetResolver.context,
                (element) => {
                    this.renderImageLabels(element, options.imageLabelsWrap);
                }
            );
        } else {
            this.revealObserverImageLabels = targetImageLabels && _onReveal(
                targetImageLabels,
                () => this.renderImageLabels(targetImageLabels, options.imageLabelsWrap)
            );
        }

        this.revealObserverContentLabels = targetContentLabels && _onReveal(
            targetContentLabels,
            () => this.renderContentLabels(targetResolver)
        );
    }

    renderImageLabels(target, wrapLabels = true) {
        const wrapper = document.createElement('div');
        
        wrapLabels
        && !target.classList.contains(WRAPPER_CLASS_IMAGE)
        && (target = _wrap(target, WRAPPER_CLASS_IMAGE));

        this.imageLabels?.remove?.();
        
        this.getImageLabels()
            .forEach(({position, items}) => {
                const container = document.createElement('div');

                container.className = `absolute ${position}`;
                items.forEach(async (item) => {
                    const label = new Label(item, this.variables);

                    container.insertAdjacentHTML('beforeend', await label.toHtml());
                });

                wrapper.appendChild(container);
            });

        target.appendChild(wrapper);
        this.imageLabels = wrapper;
    }

    renderContentLabels(targetResolver) {
        this.contentLabels.forEach((item) => item.remove());

        this.getContentLabels()
            .forEach(({position, items}) => {
                items.forEach(async (item) => {
                    const label = new Label(item, this.variables);
                    const wrapper = targetResolver.resolveContentLabelsWrapper(
                        item.target_element,
                        item.insert_method
                    );

                    if (!wrapper) return;
                    wrapper.insertAdjacentHTML('beforeend', await label.toHtml());
                    if (!this.contentLabels.includes(wrapper)) this.contentLabels.push(wrapper);
                });
            });
    }

    destroy() {
        this.revealObserverImageLabels?.disconnect?.();
        this.revealObserverContentLabels?.disconnect?.();
        this.imageLabels?.remove?.();
        this.contentLabels.forEach((item) => item.remove());
    }
}

export {
    Renderer
}
