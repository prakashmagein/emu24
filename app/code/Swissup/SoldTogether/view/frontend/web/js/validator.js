const messageTmpl = (type, text) => `
    <div class="message ${type}">
        <div>${text}</div>
    </div>
`;

const validator = {
    /**
     * @param  {HTMLElement|HTMLElement[]}  options
     * @return {Boolean}
     */
    isValidOptions: function (options) {
        let isValid = true;
        const inputs = [];
        
        (Array.isArray(options) ? options : [options])
            .forEach((option) =>
                inputs.push(...option.querySelectorAll('.swatch-input, .super-attribute'))
            );
        for (let input of inputs) {
            if (!input.value) {
                isValid = false;
                break;
            }
        }
        return isValid;
    },

    /**
     * @param  {Object} message - {type: string, text: string}
     * @param  {HTMLElement} container
     */
    showMessage: function (message, container) {
        container.insertAdjacentHTML('beforeend', messageTmpl(message.type, message.text));
    }
};

export {
    validator
};
